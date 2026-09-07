<?php

/**
 * Message security info
 *
 * Adds a "DKIM" link to the message header's links row (next to Summary /
 * Headers / Plain text). The link is always present; its icon colour reflects
 * the verdict (green = valid & aligned, amber = valid but not aligned / no
 * DKIM, red = failed, grey = present but unverified). Clicking it opens a
 * popup with the parsed SPF / DKIM / DMARC results, plus the raw
 * Authentication-Results and any administrator-configured extra headers.
 *
 * How the verdict is determined
 * -----------------------------
 * The cryptographic checks are the job of the receiving mail server, which
 * records the outcome in the `Authentication-Results` header (RFC 8601), e.g.
 *
 *   Authentication-Results: mx.example.org; dkim=pass header.d=example.com;
 *       spf=pass smtp.mailfrom=example.com; dmarc=pass header.from=example.com
 *
 * This plugin reads that header for the pass/fail results and the relevant
 * domains, and compares the DKIM signing domain to the visible From address.
 * When no `Authentication-Results` is present it falls back to detecting the
 * raw `DKIM-Signature` header (reported as "present but unverified"); it does
 * not verify the signature itself.
 *
 * Because `Authentication-Results` headers added by hops you don't control can
 * be forged, configure `message_security_info_trusted_authserv` with your own mail
 * server's authserv-id(s) for a trustworthy result.
 *
 * The overall status is the *worst* of the per-mechanism verdicts: SPF, DKIM and
 * DMARC are independent assertions about the same message, so the weakest one
 * governs, and a DMARC pass does not excuse a weaker result beside it. Two kinds
 * of message are the exception, and both are settled on the finding rather than
 * on the status, so the headline still reads straight off the rows: relayed
 * mail, whose SPF a mailing list breaks and whose own DMARC policy has already
 * forgiven that, and mail the user submitted themselves, which never travelled
 * and so was never the thing SPF and DMARC are a check on. Within DKIM the rule
 * is reversed — several signatures are alternatives, so the best of them is the
 * one reported. `evaluate`, `demote_relayed_spf`, `excuse_local_submission` and
 * `best_dkim` carry the reasoning.
 *
 * @license GNU GPLv3+
 * @author Claude
 */
class message_security_info extends rcube_plugin
{
    public $task = 'mail|settings';

    /** @var rcmail */
    private $rc;

    /** Preference key (also the admin config key) for the extra header list. */
    private const HEADERS_PREF = 'message_security_info_extra_headers';

    /** Settings section id. */
    private const SECTION = 'message_security_info';

    /** Headers always fetched (besides any configured extras). */
    private const BASE_HEADERS = ['DKIM-SIGNATURE', 'AUTHENTICATION-RESULTS', 'RECEIVED-SPF'];

    /** Where to look for each method's domain inside an Authentication-Results entry. */
    private const DOMAIN_KEYS = [
        'dkim' => ['header\.d', 'header\.i'],
        'spf' => ['smtp\.mailfrom', 'smtp\.helo', 'envelope-from'],
        'dmarc' => ['header\.from'],
    ];

    /** The authentication mechanisms, in report order. */
    private const METHODS = ['spf', 'dkim', 'dmarc'];

    /** Best-first ranking used to pick between several DKIM signatures. */
    private const DKIM_PREFERENCE = ['pass', 'warn', 'unknown', 'fail'];

    #[\Override]
    public function init()
    {
        $this->rc = rcmail::get_instance();

        // Ensure the headers are fetched from IMAP. Registered for the listing
        // action ('') too, because with message caching the headers are
        // fetched before the message is opened.
        $this->add_hook('storage_init', [$this, 'storage_init']);

        if ($this->rc->task === 'settings') {
            $this->add_texts('localization/');
            $this->add_hook('preferences_sections_list', [$this, 'prefs_sections']);
            $this->add_hook('preferences_list', [$this, 'prefs_list']);
            $this->add_hook('preferences_save', [$this, 'prefs_save']);
        } elseif ($this->rc->task === 'mail') {
            // The details popup is shown with rcmail.simple_dialog(), which
            // Elastic renders in the *parent* window — not inside the message
            // frame. Its stylesheet therefore has to be present in the parent
            // too, so load the CSS for the whole mail task. The script and the
            // message hook are only needed where the message (and its env) is
            // actually rendered.
            $this->include_stylesheet('message_security_info.css');

            if ($this->rc->action === 'show' || $this->rc->action === 'preview') {
                $this->add_texts('localization/', true);
                $this->include_script('message_security_info.js');
                $this->add_hook('message_objects', [$this, 'message_objects']);
                $this->add_hook('message_headers_output', [$this, 'headers_output']);
            }
        }
    }

    /**
     * Settings: add a dedicated "Message security" section.
     */
    public function prefs_sections($args)
    {
        $args['list'][self::SECTION] = [
            'id' => self::SECTION,
            'section' => $this->gettext('securitysection'),
        ];

        return $args;
    }

    /**
     * Settings: render the per-user extra-headers field.
     */
    public function prefs_list($args)
    {
        if ($args['section'] !== self::SECTION) {
            return $args;
        }

        $args['blocks']['main']['name'] = $this->gettext('securitysection');

        if (empty($args['current'])) {
            // Lazy-load placeholder until the section is actually opened.
            $args['blocks']['main']['content'] = true;
        } elseif (!$this->pref_locked()) {
            // Admin may lock the setting via dont_override; otherwise render it.
            $field_id = 'rcmfd_message_security_extra_headers';
            $textarea = new html_textarea(['name' => '_extra_headers', 'id' => $field_id, 'rows' => 8, 'cols' => 40]);

            $args['blocks']['main']['options']['extra_headers'] = [
                'title' => html::label($field_id, rcube::Q($this->gettext('extraheaders'))),
                'content' => $textarea->show(implode("\n", $this->extra_headers()))
                    . html::div('hint', rcube::Q($this->gettext('extraheadershint'))),
            ];
        }

        return $args;
    }

    /**
     * Settings: save the per-user extra-headers list (to users.preferences).
     */
    public function prefs_save($args)
    {
        if ($args['section'] !== self::SECTION || $this->pref_locked()) {
            return $args;
        }

        $raw = rcube_utils::get_input_string('_extra_headers', rcube_utils::INPUT_POST);
        $headers = [];

        foreach (preg_split('/[\r\n,]+/', $raw) as $line) {
            $line = trim($line);
            // Valid header names only; de-duplicate (case-insensitively).
            if ($line !== '' && preg_match('/^[A-Za-z0-9-]+$/', $line)
                && !in_array(strtolower($line), array_map('strtolower', $headers), true)
            ) {
                $headers[] = $line;
            }
        }

        $args['prefs'][self::HEADERS_PREF] = $headers;

        return $args;
    }

    /**
     * Whether the extra-headers preference is locked by the administrator.
     */
    private function pref_locked()
    {
        return in_array(self::HEADERS_PREF, (array) $this->rc->config->get('dont_override', []), true);
    }

    /**
     * Whether a given check (spf, dkim, dmarc, tls, submission) is enabled by
     * the administrator. All are on by default.
     */
    private function method_enabled($method)
    {
        return (bool) $this->rc->config->get('message_security_info_check_' . $method, true);
    }

    /**
     * Tell the core which headers to fetch from IMAP.
     */
    public function storage_init($p)
    {
        $headers = self::BASE_HEADERS;

        // Received lines are what the transport (TLS) and submission checks read.
        if ($this->method_enabled('tls') || $this->method_enabled('submission')) {
            $headers[] = 'RECEIVED';
        }

        $headers = array_merge($headers, array_map('strtoupper', $this->extra_headers()));
        $p['fetch_headers'] = trim(($p['fetch_headers'] ?? '') . ' ' . implode(' ', array_unique($headers)));

        return $p;
    }

    /**
     * Compute the verdict + popup details and hand them to the client, which
     * builds the header link and the dialog (see message_security_info.js).
     */
    public function message_objects($p)
    {
        $message = $p['message'] ?? null;

        if (!$message || empty($message->headers) || $this->skip_folder($message)) {
            return $p;
        }

        // With every authentication mechanism disabled there is nothing to
        // evaluate — show no link, no icon, no popup.
        if (!$this->method_enabled('spf') && !$this->method_enabled('dkim') && !$this->method_enabled('dmarc')) {
            return $p;
        }

        $headers = $message->headers;
        $auth = $this->parse_authresults($headers);
        $security = $this->security_fields($headers, $auth);
        $verdict = $this->evaluate($security);

        $env = [
            'status' => $verdict['status'],
            'summary' => $verdict['summary'],
            'rows' => $this->summary_rows($headers, $security),
            'headers' => $this->raw_headers($headers),
        ];

        // The marker placed on the message's From header is the DKIM verdict
        // itself — lifted out, never recomputed — so the row glyph, the header
        // marker and its tooltip can never end up disagreeing with each other.
        if (isset($security['dkim'])) {
            $env['dkim_from'] = $security['dkim']['verdict'];
        }

        $this->rc->output->set_env('message_security_info', $env);

        // Emphasise a problem with a notice bar above the message body. Mapping
        // the status to the skin's alert class (warning/error) lets the Elastic
        // skin render it as a proper coloured alert. The pass and the unverified
        // (unknown) cases stay as just the header link.
        $alert = ['warn' => 'warning', 'fail' => 'error'][$verdict['status']] ?? null;
        if ($alert) {
            $p['content'][] = html::div(
                ['class' => $alert . ' msgsec-bar', 'role' => 'note'],
                html::span(['class' => 'msgsec-bar-label'], rcube::Q($this->gettext('linktitle')))
                    . ' ' . rcube::Q($verdict['summary'])
            );
        }

        return $p;
    }

    /**
     * Skip own outgoing mail (Sent/Drafts), where a DKIM verdict is noise.
     */
    private function skip_folder($message)
    {
        if (!$this->rc->config->get('message_security_info_skip_sent', true)) {
            return false;
        }

        $folder = $message->folder ?? null;
        $special = array_filter([
            $this->rc->config->get('sent_mbox'),
            $this->rc->config->get('drafts_mbox'),
        ]);

        return $folder !== null && in_array($folder, $special, true);
    }

    /**
     * Overall sender-authentication verdict driving the link icon + tooltip.
     *
     * The worst of the per-mechanism verdicts wins. SPF, DKIM and DMARC are
     * independent assertions about the same message, so the weakest of them
     * governs; a DMARC pass is not treated as authoritative on its own. DMARC
     * passing means the domain owner's policy was met — it does not mean every
     * mechanism agreed, and the disagreement is exactly the interesting part.
     *
     * The cases where a mechanism is not taken at face value — relayed mail,
     * whose SPF the sender's own DMARC policy has already forgiven, and the
     * user's own submitted mail, which SPF and DMARC never judged — are settled
     * before this method sees them, in demote_relayed_spf() and
     * excuse_local_submission(), so that the adjusted row is the one the reader
     * can see. Nothing may be forgiven here: an exception applied at this point
     * would put a `warn` headline above a visible `✗ FAIL` row, with no rule
     * connecting the two.
     *
     * Mechanisms disabled by the administrator are absent from $security and so
     * are excluded entirely.
     *
     * @param array<string, array> $security the per-mechanism findings
     *
     * @return array{status:string, summary:string}
     */
    private function evaluate($security)
    {
        $status = $this->combine_statuses(array_column($security, 'verdict'));

        return ['status' => $status, 'summary' => $this->gettext('summary' . $status)];
    }

    /**
     * The SPF/DKIM/DMARC findings: one fixed-shape entry per enabled mechanism.
     *
     * A disabled mechanism is absent entirely — nothing was evaluated, so there
     * is nothing to report. Every entry present has the same keys, so callers
     * can read them without special-casing; see security_entry() for what each
     * of them means.
     *
     * @return array<string, array>
     */
    private function security_fields($headers, $auth)
    {
        $from = $this->from_domain($headers);
        $sigs = $this->normalize($headers->get('DKIM-Signature', false));
        $sig_domain = !empty($sigs) ? $this->signature_domain($sigs[0]) : null;

        // SPF is often only in a Received-SPF header, not Authentication-Results.
        $results = [
            'spf' => ($auth['spf'][0] ?? null) ?: $this->spf_from_received($headers),
            'dkim' => $this->best_dkim($auth['dkim'], $from, $sig_domain),
            'dmarc' => $auth['dmarc'][0] ?? null,
        ];

        $security = [];

        foreach (self::METHODS as $method) {
            if ($this->method_enabled($method)) {
                $security[$method] = $this->security_entry($method, $results[$method], $from, $sig_domain);
            }
        }

        // The two cases where a result does not mean what it says. They cannot
        // both apply — one needs DMARC to have passed and the other reaches
        // findings DMARC failed — so the order between them carries no meaning.
        $security = $this->demote_relayed_spf($security);

        return $this->excuse_local_submission($security, $this->submission($headers));
    }

    /**
     * One mechanism's finding, in the fixed shape security_fields() documents:
     *
     * - present     the mechanism is in effect for this message. False when
     *               nothing was reported at all, and equally when the reported
     *               result was 'none' (no SPF/DKIM/DMARC on the sending side).
     * - verified    the receiving server checked this, rather than the message
     *               merely carrying an unverified claim (an unchecked
     *               DKIM-Signature is present but not verified).
     * - status      the raw protocol result, upper case (PASS, FAIL, SOFTFAIL,
     *               NONE, TEMPERROR, …), or null when there is no result.
     * - domain      the domain the result is about — DKIM's signing domain, or
     *               the envelope/From domain SPF and DMARC judged.
     * - aligned     whether `domain` matches the From domain (DKIM only, since
     *               that is the only mechanism this compares); null where the
     *               question does not apply or cannot be answered.
     * - verdict     the normalised severity: pass, warn, fail, unknown or none
     *               (none = contributes nothing to the overall status). The row
     *               glyph is drawn from this, so a finding can never show a
     *               glyph that disagrees with its own verdict.
     * - description a human-readable note: the alignment note, or why there is
     *               no result. null when there is nothing to add.
     */
    private function security_entry($method, $entry, $from, $sig_domain)
    {
        if (empty($entry)) {
            // No verified result. A DKIM-Signature with nothing to confirm it is
            // still worth reporting: the message is signed, the server did not
            // check it. Alignment is deliberately left unanswered — an unverified
            // signature can claim any domain, so a match means nothing here.
            $signed = $method === 'dkim' && $sig_domain !== null;

            return [
                'present' => $signed,
                'verified' => false,
                'status' => null,
                'domain' => $signed ? $sig_domain : null,
                'aligned' => null,
                'verdict' => $signed ? 'unknown' : 'none',
                'description' => $this->gettext($signed ? 'unverified' : 'notpresent'),
            ];
        }

        $result = strtolower($entry['result']);
        $domain = $entry['domain'] ?? null;

        if (!$domain) {
            // A result that named no domain of its own falls back to the
            // DKIM-Signature above, which is the only other domain on offer.
            $domain = $method === 'dkim' ? $sig_domain : null;
        }

        $aligned = null;
        $description = null;

        if ($method === 'dkim' && $domain && $from) {
            $aligned = $this->aligned($domain, $from);
            $description = $aligned
                ? $this->gettext('aligned')
                : $this->gettext(['name' => 'notaligned', 'vars' => ['from' => $from]]);
        }

        return [
            'present' => $result !== 'none',
            'verified' => true,
            'status' => strtoupper($result),
            'domain' => $domain,
            'aligned' => $aligned,
            // Judged on the domain the row displays, not on the raw result: a
            // result that named no domain of its own falls back to the
            // DKIM-Signature above, and the verdict has to follow it there or it
            // would contradict the line it is printed beside.
            'verdict' => $this->method_status($method, ['result' => $result, 'domain' => $domain], $from),
            'description' => $description,
        ];
    }

    /**
     * The DKIM result that counts, out of every signature the server checked.
     *
     * A message is commonly signed more than once — by the author's domain and
     * by the sending platform, say. The signatures are alternatives, not a set
     * of conditions to satisfy: one that verifies and is aligned with the From
     * domain authenticates the message no matter what the other one says. So
     * the best signature wins here, which is the opposite of combine_statuses()
     * — that reduces the *mechanisms*, which are independent claims.
     *
     * Ties keep the earliest signature, so a single-signature message reports
     * exactly what it did before.
     *
     * @return array|null the winning entry, or null when there is none
     */
    private function best_dkim($entries, $from, $sig_domain)
    {
        $best = null;
        $best_rank = null;

        foreach ($entries as $entry) {
            $domain = ($entry['domain'] ?? null) ?: $sig_domain;
            $status = $this->method_status('dkim', ['result' => $entry['result'], 'domain' => $domain], $from);

            // An unranked status ('none') sorts last: it says nothing about the
            // message, so any real signature beside it is the better answer.
            $rank = array_search($status, self::DKIM_PREFERENCE, true);
            $rank = $rank === false ? count(self::DKIM_PREFERENCE) : $rank;

            if ($best_rank === null || $rank < $best_rank) {
                $best = $entry;
                $best_rank = $rank;
            }
        }

        return $best;
    }

    /**
     * Demote an SPF failure that DMARC has already forgiven.
     *
     * Forwarders and mailing lists relay a message under their own envelope
     * sender, which breaks SPF for mail that is otherwise entirely genuine.
     * When DMARC passed, the receiving server has already weighed that failure
     * and accepted the message on a surviving, aligned DKIM signature — so
     * reporting it as a failure would be a false alarm for legitimately relayed
     * mail.
     *
     * The demotion is applied to the finding itself, not just to the overall
     * status, so the SPF row's own glyph and the headline agree on what
     * happened, and the row explains why it is only a warning.
     *
     * @param array<string, array> $security
     *
     * @return array<string, array>
     */
    private function demote_relayed_spf($security)
    {
        if (($security['spf']['verdict'] ?? null) !== 'fail'
            || ($security['dmarc']['verdict'] ?? null) !== 'pass'
        ) {
            return $security;
        }

        $security['spf']['verdict'] = 'warn';
        $security['spf']['description'] = $this->gettext('spfrelayed');

        return $security;
    }

    /**
     * submission_info(), or null while the check is switched off.
     *
     * The one place the flag is read, so the Submission row and the verdict can
     * never disagree about whether this message was submitted.
     *
     * @return array{user:?string, client:?string}|null
     */
    private function submission($headers)
    {
        return $this->method_enabled('submission') ? $this->submission_info($headers) : null;
    }

    /**
     * Whether the user submitted this message themselves, rather than received it.
     *
     * Mail a user hands to their own server over an authenticated SMTP session
     * never travels the internet, so the SPF and DMARC checks made on it answer
     * a question that was not asked: SPF is a rule about *relay* from an
     * arbitrary address, and the client here is one that logged in.
     * excuse_local_submission() is what acts on that; this method only reads.
     *
     * Two conditions, and both are required:
     *
     * - The message has exactly one Received hop. A message that reached the
     *   server any other way carries the hops that brought it, so this is what
     *   separates a fresh submission from one re-injected through the same
     *   server — a client's "redirect"/"bounce" keeps the original chain and
     *   prepends to it, and its inner results must keep their verdict.
     * - That hop is an authenticated submission: the RFC 3848 transmission type
     *   ends in "A" (ESMTPA, ESMTPSA, LMTPA, LMTPSA), which is a receiving MTA
     *   recording that the client authenticated (RFC 4954).
     *
     * Both are read from the header the receiving MTA wrote itself, and a forged
     * Received can only ever appear *below* it, so neither can be claimed by the
     * message. The reverse is not true: a server that hands local mail to a
     * separate delivery agent adds a second hop, and this then reports nothing.
     * That is the safe direction to be wrong in.
     *
     * @return array{user:?string, client:?string}|null null when this is not a
     *                                                  message the user submitted
     */
    private function submission_info($headers)
    {
        $received = $this->normalize($headers->get('Received', false));
        if (count($received) !== 1) {
            return null;
        }

        $top = preg_replace('/\s+/', ' ', $received[0]);

        // RFC 3848: the trailing "A" is the authenticated form, with an optional
        // "S" before it for TLS. tls_info() reads the same clause for the "S".
        if (!preg_match('/\bwith\s+(?:UTF8)?(?:ESMTP|SMTP|LMTP)S?A\b/i', $top)) {
            return null;
        }

        // Everything the client is described by precedes the "by <our host>"
        // clause; past that point the addresses belong to the server itself.
        $client_part = preg_split('/\bby\s/', $top, 2)[0];
        $client = preg_match('/\[(?:IPv6:)?([0-9a-fA-F.:]+)\]/', $client_part, $cm) ? $cm[1] : null;

        // Postfix names the account it authenticated. Other MTAs word this
        // differently or not at all, which is why it is not what we match on.
        $user = preg_match('/\bAuthenticated sender:\s*([^)\s]+)/i', $top, $um) ? $um[1] : null;

        return ['user' => $user, 'client' => $client];
    }

    /**
     * Stop counting SPF and DMARC on a message the user submitted themselves.
     *
     * Mail handed to your own server over an authenticated session is not
     * relayed mail, and SPF is a rule about relay: it asks whether the
     * connecting address may send for the domain, and the answer for a laptop on
     * the LAN is no — correctly, and about nothing. DMARC then fails as an
     * arithmetic consequence of that SPF failure rather than as a finding of its
     * own. Reporting either as evidence of forgery is a false alarm on the
     * user's own outgoing mail. submission_info() says what is required before
     * this applies, and why a message cannot claim it for itself.
     *
     * These verdicts become 'none' rather than a softer failure, because the
     * claim is not that the failure was forgiven — nothing weighed it — but that
     * the mechanism did not judge this message at all. 'none' is already the
     * value for that, and combine_statuses() already answers a message with
     * nothing left to judge with a visible 'warn', so no message can go quiet by
     * this route. The raw result is untouched and still shown, with a
     * description saying why it is not counted.
     *
     * Only adverse verdicts are dropped. A mechanism that passed is left to
     * speak for itself: this is here to remove a false alarm, not to withhold
     * what did hold.
     *
     * The user's account, not their identity, is what authenticated — so this
     * never promotes anything to 'pass'. A server that does not bind the login
     * to the From address lets an authenticated user write any sender they like,
     * and nothing in the message says whether yours does.
     *
     * @param array<string, array>                     $security
     * @param array{user:?string, client:?string}|null $submission
     *
     * @return array<string, array>
     */
    private function excuse_local_submission($security, $submission)
    {
        if ($submission === null) {
            return $security;
        }

        foreach (['spf', 'dmarc'] as $method) {
            if (in_array($security[$method]['verdict'] ?? null, ['fail', 'warn'], true)) {
                $security[$method]['verdict'] = 'none';
                $security[$method]['description'] = $this->gettext('localsubmission');
            }
        }

        return $security;
    }

    /**
     * Map one SPF/DKIM result to pass / warn / fail / unknown / none (DKIM also
     * weighs From-alignment). "none" means it does not contribute.
     */
    private function method_status($method, $entry, $from)
    {
        switch ($entry['result']) {
            case 'pass':
                $status = $method === 'dkim' && !$this->aligned($entry['domain'] ?? null, $from) ? 'warn' : 'pass';
                break;
            case 'fail':
                $status = 'fail';
                break;
            case 'softfail':
            case 'neutral':
            case 'policy':
            case 'permerror':
                $status = 'warn';
                break;
            case 'temperror':
                $status = 'unknown';
                break;
            default: // none, etc.
                $status = 'none';
        }

        return $status;
    }

    /**
     * Reduce per-method statuses to one. Worst wins (fail > warn > pass);
     * "none" entries are dropped; only-unknown stays unknown; nothing to check
     * at all is a (visible) warning.
     */
    private function combine_statuses($statuses)
    {
        $statuses = array_filter($statuses, static fn ($s) => $s !== 'none');

        if (empty($statuses)) {
            return 'warn';
        }
        foreach (['fail', 'warn', 'pass'] as $level) {
            if (in_array($level, $statuses, true)) {
                return $level;
            }
        }

        return 'unknown';
    }

    /**
     * Parse all DKIM/SPF/DMARC results from the Authentication-Results header(s).
     *
     * @return array{dkim:array, spf:array, dmarc:array}
     */
    private function parse_authresults($headers)
    {
        $trusted = array_map('strtolower', (array) $this->rc->config->get('message_security_info_trusted_authserv', []));
        $out = ['dkim' => [], 'spf' => [], 'dmarc' => []];

        foreach ($this->normalize($headers->get('Authentication-Results', false)) as $ar) {
            $ar = trim(preg_replace('/\s+/', ' ', $ar));
            $segments = explode(';', $ar);
            $authserv = strtolower(trim(strtok($segments[0], ' ')));

            if (!empty($trusted) && !in_array($authserv, $trusted, true)) {
                continue;
            }

            array_shift($segments); // drop the authserv-id

            foreach ($segments as $segment) {
                if (!preg_match('/^\s*(dkim|spf|dmarc)\s*=\s*([a-z]+)/i', $segment, $m)) {
                    continue;
                }

                $method = match (strtolower($m[1])) {
                    'dkim' => 'dkim',
                    'spf' => 'spf',
                    'dmarc' => 'dmarc',
                    default => null,
                };

                if ($method === null) {
                    continue;
                }

                $out[$method][] = [
                    'result' => strtolower($m[2]),
                    'domain' => $this->extract_domain($method, $segment),
                ];
            }
        }

        return $out;
    }

    /**
     * Pull the relevant domain out of one Authentication-Results method entry.
     */
    private function extract_domain($method, $segment)
    {
        foreach (self::DOMAIN_KEYS[$method] ?? [] as $key) {
            if (preg_match('/' . $key . '\s*=\s*@?([^\s;]+)/i', $segment, $m)) {
                $value = strtolower(trim($m[1], '<>'));
                $at = strrpos($value, '@');

                return $at !== false ? substr($value, $at + 1) : $value;
            }
        }

        return null;
    }

    /**
     * The popup's top rows: the From address, one row per enabled mechanism,
     * then — on a message the user submitted themselves — the submission line,
     * and finally the transport line.
     *
     * `verdict` is what the row's glyph is drawn from, and is absent on the two
     * rows that carry no verdict at all — the From address and the transport.
     *
     * @param array<string, array> $security
     *
     * @return array<array{label:string, value:string, verdict?:string}>
     */
    private function summary_rows($headers, $security)
    {
        // The sender address the SPF/DKIM/DMARC results are judged against.
        $rows = [[
            'label' => $this->gettext('from'),
            'value' => $this->from_address($headers) ?? $this->gettext('notpresent'),
        ]];

        foreach ($security as $method => $entry) {
            $rows[] = [
                'label' => $this->gettext($method),
                'value' => $this->security_line($entry),
                'verdict' => $entry['verdict'],
            ];
        }

        // Names the evidence the SPF and DMARC findings above were read in the
        // light of, so a reader who sees them not counted can see why.
        $submission = $this->submission($headers);
        if ($submission !== null) {
            $rows[] = ['label' => $this->gettext('submission'), 'value' => $this->format_submission($submission)];
        }

        if ($this->method_enabled('tls')) {
            $rows[] = ['label' => $this->gettext('tls'), 'value' => $this->format_tls($this->tls_info($headers))];
        }

        return $rows;
    }

    /**
     * One security finding as a display line, e.g. "PASS — example.com".
     *
     * An unaligned PASS carries its mismatch note on a second line; any other
     * noteworthy result carries it parenthesised.
     */
    private function security_line($entry)
    {
        $description = $entry['description'];

        if ($entry['status'] === null) {
            // Nothing was verified: either no result at all, or a signature the
            // server did not check. Either way the description says which.
            return $entry['domain'] ? $description . ' — ' . $entry['domain'] : $description;
        }

        $value = $entry['status'];
        if ($entry['domain']) {
            $value .= ' — ' . $entry['domain'];
        }

        if ($description === null || ($entry['status'] === 'PASS' && $entry['aligned'])) {
            // A clean, aligned PASS needs no further comment.
            return $value;
        }

        return $entry['status'] === 'PASS'
            ? $value . "\n" . $description
            : $value . ' (' . $description . ')';
    }

    /**
     * Parse a Received-SPF header (RFC 7208) into {result, domain}.
     *
     * Example: "Pass (mailfrom) identity=mailfrom; client-ip=1.2.3.4;
     *           envelope-from=user@example.com; ..."
     */
    private function spf_from_received($headers)
    {
        foreach ($this->normalize($headers->get('Received-SPF', false)) as $line) {
            if (!preg_match('/^\s*([a-z]+)/i', $line, $m)) {
                continue;
            }

            $domain = null;
            if (preg_match('/envelope-from\s*=\s*<?([^\s;>]+)/i', $line, $em)) {
                $value = strtolower(trim($em[1], '<>'));
                $at = strrpos($value, '@');
                $domain = $at !== false ? substr($value, $at + 1) : $value;
            }

            return ['result' => strtolower($m[1]), 'domain' => $domain];
        }

        return null;
    }

    /**
     * Whether the message reached us over an encrypted SMTP connection, read
     * from the topmost Received header (the most recent hop — typically our own
     * receiving server). Informational only; never affects the icon verdict.
     *
     * @return array{encrypted:bool, detail:?string}|null null when undeterminable
     */
    private function tls_info($headers)
    {
        $received = $this->normalize($headers->get('Received', false));
        if (empty($received)) {
            return null;
        }

        $top = preg_replace('/\s+/', ' ', $received[0]);

        // Cipher/version detail, when the receiving MTA logged it, e.g.
        // "(using TLSv1.3 ...)" or "(version=TLS1_3 cipher=...)".
        $detail = null;
        if (preg_match('/\b(TLSv?[\d._]+|SSLv?[\d._]+)/i', $top, $m)) {
            $detail = str_replace('_', '.', $m[1]);
        }

        // RFC 3848 transmission types: an "S" right after the SMTP/LMTP base
        // means STARTTLS/TLS was used (ESMTPS, ESMTPSA, LMTPS, ...). An "A"
        // (ESMTPA) is authentication without TLS.
        if (preg_match('/\bwith\s+(?:UTF8)?(?:ESMTP|SMTP|LMTP)(S)?A?\b/i', $top, $m)) {
            return ['encrypted' => !empty($m[1]), 'detail' => $detail];
        }

        // A version clause without a recognised transmission type still implies TLS.
        return $detail !== null ? ['encrypted' => true, 'detail' => $detail] : null;
    }

    /**
     * Raw header lines for the bottom of the popup (Authentication-Results,
     * Received-SPF, then any configured extras). Absent headers are skipped.
     *
     * @return array<array{name:string, value:string}>
     */
    private function raw_headers($headers)
    {
        $out = [];

        foreach (array_merge(['Authentication-Results', 'Received-SPF'], $this->extra_headers()) as $name) {
            foreach ($this->normalize($headers->get($name, false)) as $value) {
                $out[] = ['name' => $name, 'value' => $value];
            }
        }

        return $out;
    }

    /**
     * Format the submission line: who authenticated, and from where.
     */
    private function format_submission($submission)
    {
        $who = $submission['user'] !== null
            ? $this->gettext(['name' => 'submissionuser', 'vars' => ['user' => $submission['user']]])
            : $this->gettext('submissionanon');

        return $submission['client'] !== null
            ? $this->gettext(['name' => 'submissionfrom', 'vars' => ['what' => $who, 'client' => $submission['client']]])
            : $who;
    }

    /**
     * Format the transport (TLS) result line for the popup.
     */
    private function format_tls($tls)
    {
        if ($tls === null) {
            return $this->gettext('tlsunknown');
        }
        if (empty($tls['encrypted'])) {
            return $this->gettext('tlsplain');
        }

        return !empty($tls['detail'])
            ? $this->gettext('tlsencrypted') . ' — ' . $tls['detail']
            : $this->gettext('tlsencrypted');
    }

    /**
     * Extra headers to show in the popup. This is the per-user preference when
     * the user has set one (stored in users.preferences and merged into config
     * at login), otherwise the administrator's default.
     *
     * @return string[]
     */
    private function extra_headers()
    {
        return array_values(array_filter(
            (array) $this->rc->config->get(self::HEADERS_PREF, []),
            static fn ($h) => is_string($h) && $h !== ''
        ));
    }

    /**
     * The domain of the visible From address.
     */
    private function from_domain($headers)
    {
        $from = $headers->from ?? null;
        if (!$from) {
            return null;
        }

        $list = rcube_mime::decode_address_list($from, 1, true);
        $first = !empty($list) ? reset($list) : null;
        $addr = $first['mailto'] ?? '';

        if ($addr === '' && preg_match('/[\w.+-]+@([\w.-]+)/', $from, $m)) {
            return strtolower($m[1]);
        }

        $at = strrpos($addr, '@');

        return $at !== false ? strtolower(substr($addr, $at + 1)) : null;
    }

    /**
     * The decoded From display name and address as ['name' => …, 'addr' => …],
     * or null when there is no From header. The name has control/formatting
     * characters (bidi overrides, zero-width, …) stripped so it can't spoof the
     * UI, while keeping the visible — possibly confusable — glyphs. `name` is
     * empty when there is no real display name (Roundcube otherwise fills it
     * with the address).
     */
    private function from_parts($headers)
    {
        $from = $headers->from ?? null;
        if (!$from) {
            return null;
        }

        $list = rcube_mime::decode_address_list($from, 1, true);
        $first = !empty($list) ? reset($list) : null;

        $name = trim((string) ($first['name'] ?? ''));
        $addr = strtolower(trim((string) ($first['mailto'] ?? '')));

        if ($addr === '' && preg_match('/[\w.+-]+@[\w.-]+/', $from, $m)) {
            $addr = strtolower($m[0]);
        }

        $clean = preg_replace('/[\p{Cc}\p{Cf}]/u', '', $name);
        if ($clean !== null) {
            $name = trim($clean);
        }

        // Drop the name when it is really just the address repeated.
        if ($name !== '' && strcasecmp($name, $addr) === 0) {
            $name = '';
        }

        return ['name' => $name, 'addr' => $addr];
    }

    /**
     * The visible From value for display: the human-readable display name
     * (when present) alongside the address, as `Name <local@domain>`. Showing
     * both makes a deceptive/obfuscated display name — a common phishing trick —
     * obvious next to the real address the DKIM/SPF/DMARC checks apply to.
     * Returns null when there is no From header.
     */
    private function from_address($headers)
    {
        $parts = $this->from_parts($headers);
        if (!$parts) {
            return null;
        }

        if ($parts['addr'] === '') {
            return $parts['name'] !== '' ? $parts['name'] : null;
        }

        return $parts['name'] !== ''
            ? $parts['name'] . ' <' . $parts['addr'] . '>'
            : $parts['addr'];
    }

    /**
     * message_headers_output hook: when the From header shows a display name
     * (which hides the real address behind it), append the actual address so a
     * deceptive name — e.g. a homograph of a trusted sender — can't disguise
     * where the mail really came from. A green DKIM/DMARC verdict only proves
     * the signing domain, not that the visible name is honest.
     */
    public function headers_output($args)
    {
        if (empty($args['output']['from']) || empty($args['headers'])) {
            return $args;
        }

        $parts = $this->from_parts($args['headers']);
        // Nothing to add when there is no distinct name or no address — the
        // address is then already the visible text.
        if (!$parts || $parts['name'] === '' || $parts['addr'] === '') {
            return $args;
        }

        $addr = html::span('msgsec-from-addr', '&lt;' . rcube::Q($parts['addr']) . '&gt;');

        if (!empty($args['output']['from']['html'])) {
            $args['output']['from']['value'] .= ' ' . $addr;
        } else {
            // Plain-text value: switch to HTML so our escaped span is rendered.
            $args['output']['from']['value'] = rcube::Q($args['output']['from']['value']) . ' ' . $addr;
            $args['output']['from']['html'] = true;
        }

        return $args;
    }

    /**
     * The d= signing domain of a raw DKIM-Signature header value.
     */
    private function signature_domain($signature)
    {
        if (preg_match('/(?:^|;)\s*d\s*=\s*([^;\s]+)/i', (string) $signature, $m)) {
            return strtolower(trim($m[1]));
        }

        return null;
    }

    /**
     * Relaxed alignment: equal, or one a subdomain of the other.
     *
     * Note: this is a pragmatic check, not a Public-Suffix-List organizational
     * domain comparison, so e.g. two unrelated `*.co.uk` domains are not
     * treated as aligned, but a true org-domain match is also not guaranteed.
     */
    private function aligned($domain, $from)
    {
        if (!$domain || !$from) {
            return false;
        }

        $domain = strtolower($domain);
        $from = strtolower($from);

        return $domain === $from
            || str_ends_with($from, '.' . $domain)
            || str_ends_with($domain, '.' . $from);
    }

    /**
     * Normalize a header value (string|array|null) to a list of strings.
     */
    private function normalize($value)
    {
        if ($value === null || $value === '') {
            return [];
        }

        return array_values(array_filter((array) $value, static fn ($v) => is_string($v) && $v !== ''));
    }
}
