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
        } elseif ($this->rc->action === 'show' || $this->rc->action === 'preview') {
            $this->add_texts('localization/', true);
            $this->include_stylesheet('message_security_info.css');
            $this->include_script('message_security_info.js');
            $this->add_hook('message_objects', [$this, 'message_objects']);
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

        // Lazy-load placeholder until the section is actually opened.
        if (empty($args['current'])) {
            $args['blocks']['main']['content'] = true;

            return $args;
        }

        // Admin may lock the setting via dont_override.
        if ($this->pref_locked()) {
            return $args;
        }

        $field_id = 'rcmfd_message_security_extra_headers';
        $textarea = new html_textarea(['name' => '_extra_headers', 'id' => $field_id, 'rows' => 8, 'cols' => 40]);

        $args['blocks']['main']['options']['extra_headers'] = [
            'title' => html::label($field_id, rcube::Q($this->gettext('extraheaders'))),
            'content' => $textarea->show(implode("\n", $this->extra_headers()))
                . html::div('hint', rcube::Q($this->gettext('extraheadershint'))),
        ];

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
     * Whether a given check (spf, dkim, dmarc, tls) is enabled by the
     * administrator. All are on by default.
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

        // Received lines are only needed for the transport (TLS) check.
        if ($this->method_enabled('tls')) {
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
        $verdict = $this->evaluate($headers, $auth);

        $this->rc->output->set_env('message_security_info', [
            'status' => $verdict['status'],
            'summary' => $verdict['summary'],
            'rows' => $this->summary_rows($headers, $auth),
            'headers' => $this->raw_headers($headers),
        ]);

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
     * DMARC, when it actually evaluated, is authoritative — it already implies
     * an aligned SPF or DKIM pass. Otherwise the present SPF and DKIM results
     * are combined, omitting whichever is missing. Mechanisms disabled by the
     * administrator are excluded entirely.
     *
     * @return array{status:string, summary:string}
     */
    private function evaluate($headers, $auth)
    {
        // DMARC is the overall verdict when enabled and it yielded a real result.
        if ($this->method_enabled('dmarc')) {
            $dmarc = $auth['dmarc'][0]['result'] ?? null;
            if ($dmarc === 'pass') {
                return ['status' => 'pass', 'summary' => $this->gettext('summarypass')];
            }
            if ($dmarc === 'fail') {
                return ['status' => 'fail', 'summary' => $this->gettext('summaryfail')];
            }
        }

        // No usable DMARC: combine the SPF and DKIM results that are present.
        $from = $this->from_domain($headers);
        $statuses = [];

        if ($this->method_enabled('spf')) {
            $spf = ($auth['spf'][0] ?? null) ?: $this->spf_from_received($headers);
            if ($spf) {
                $statuses[] = $this->method_status('spf', $spf, $from);
            }
        }

        if ($this->method_enabled('dkim')) {
            if (!empty($auth['dkim'])) {
                $statuses[] = $this->method_status('dkim', $auth['dkim'][0], $from);
            } elseif (!empty($this->normalize($headers->get('DKIM-Signature', false)))) {
                // Signature present but not verified by the receiving server.
                $statuses[] = 'unknown';
            }
        }

        $status = $this->combine_statuses($statuses);

        return ['status' => $status, 'summary' => $this->gettext('summary' . $status)];
    }

    /**
     * Map one SPF/DKIM result to pass / warn / fail / unknown / none (DKIM also
     * weighs From-alignment). "none" means it does not contribute.
     */
    private function method_status($method, $entry, $from)
    {
        switch ($entry['result']) {
            case 'pass':
                return $method === 'dkim' && !$this->aligned($entry['domain'], $from) ? 'warn' : 'pass';
            case 'fail':
                return 'fail';
            case 'softfail':
            case 'neutral':
            case 'policy':
            case 'permerror':
                return 'warn';
            case 'temperror':
                return 'unknown';
            default: // none, etc.
                return 'none';
        }
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
     * Parsed SPF/DKIM/DMARC rows for the top of the popup.
     *
     * @return array<array{label:string, value:string}>
     */
    private function summary_rows($headers, $auth)
    {
        $from = $this->from_domain($headers);
        $sigs = $this->normalize($headers->get('DKIM-Signature', false));
        $sig_domain = !empty($sigs) ? $this->signature_domain($sigs[0]) : null;

        // SPF is often only in a Received-SPF header, not Authentication-Results.
        $spf = $auth['spf'][0] ?? $this->spf_from_received($headers);

        $rows = [];
        if ($this->method_enabled('spf')) {
            $rows[] = ['label' => $this->gettext('spf'), 'value' => $this->format_method($spf)];
        }
        if ($this->method_enabled('dkim')) {
            $rows[] = ['label' => $this->gettext('dkim'), 'value' => $this->format_dkim($auth['dkim'][0] ?? null, $sig_domain, $from)];
        }
        if ($this->method_enabled('dmarc')) {
            $rows[] = ['label' => $this->gettext('dmarc'), 'value' => $this->format_method($auth['dmarc'][0] ?? null)];
        }
        if ($this->method_enabled('tls')) {
            $rows[] = ['label' => $this->gettext('tls'), 'value' => $this->format_tls($this->tls_info($headers))];
        }

        return $rows;
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
        if ($detail !== null) {
            return ['encrypted' => true, 'detail' => $detail];
        }

        return null;
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
     * Format an SPF/DMARC result line, e.g. "PASS — example.com".
     */
    private function format_method($entry)
    {
        if (empty($entry)) {
            return $this->gettext('notpresent');
        }

        $value = strtoupper($entry['result']);

        return !empty($entry['domain']) ? $value . ' — ' . $entry['domain'] : $value;
    }

    /**
     * Format the DKIM result line, including From-alignment.
     */
    private function format_dkim($entry, $sig_domain, $from)
    {
        if (empty($entry)) {
            return $sig_domain
                ? $this->gettext('unverified') . ' — ' . $sig_domain
                : $this->gettext('notpresent');
        }

        $domain = $entry['domain'] ?: $sig_domain;
        $value = strtoupper($entry['result']);

        if ($domain) {
            $value .= ' — ' . $domain;

            if ($from) {
                $value .= $this->aligned($domain, $from)
                    ? ' (' . $this->gettext('aligned') . ')'
                    : ' (' . $this->gettext(['name' => 'notaligned', 'vars' => ['from' => $from]]) . ')';
            }
        }

        return $value;
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
