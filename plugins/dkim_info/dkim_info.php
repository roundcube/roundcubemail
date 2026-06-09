<?php

/**
 * DKIM info
 *
 * Shows a verdict banner above a received message describing its DKIM status
 * and whether the signing domain aligns with the visible From address:
 *
 *   - DKIM valid and aligned with From   → confirmation (green)
 *   - DKIM valid but signing domain ≠ From → warning  (possible spoofing)
 *   - DKIM check failed                  → error
 *   - DKIM signature present but the receiving server did not verify it → info
 *   - No DKIM signature at all           → warning (clearly visible)
 *
 * How the verdict is determined
 * -----------------------------
 * The cryptographic DKIM check is the job of the receiving mail server, which
 * records its result in the `Authentication-Results` header (RFC 8601), e.g.
 *
 *   Authentication-Results: mx.example.org; dkim=pass header.d=example.com; ...
 *
 * This plugin reads that header (the trustworthy source) for the pass/fail
 * verdict and the signing domain. When no `Authentication-Results` is present,
 * it falls back to detecting the raw `DKIM-Signature` header and reports the
 * signature as "present but unverified" — it does NOT attempt to verify the
 * signature itself (that would require the raw RFC822 message plus DNS key
 * lookups, which is beyond an initial version).
 *
 * Because `Authentication-Results` headers added by hops you don't control can
 * be forged, configure `dkim_info_trusted_authserv` with your own mail
 * server's authserv-id(s) for a trustworthy result.
 *
 * @license GNU GPLv3+
 * @author Claude
 */
class dkim_info extends rcube_plugin
{
    public $task = 'mail';

    /** @var rcmail */
    private $rc;

    /** Extra headers we need fetched from IMAP. */
    private const FETCH_HEADERS = 'DKIM-SIGNATURE AUTHENTICATION-RESULTS';

    #[\Override]
    public function init()
    {
        $this->rc = rcmail::get_instance();

        // Ensure the headers are fetched from IMAP. Registered for the listing
        // action ('') too, because with message caching the headers are
        // fetched before the message is opened.
        $this->add_hook('storage_init', [$this, 'storage_init']);

        if ($this->rc->action === 'show' || $this->rc->action === 'preview') {
            $this->add_texts('localization/', true);
            $this->include_stylesheet('dkim_info.css');
            $this->add_hook('message_objects', [$this, 'message_objects']);
        }
    }

    /**
     * Tell the core to fetch the DKIM-related headers from IMAP.
     */
    public function storage_init($p)
    {
        $p['fetch_headers'] = trim(($p['fetch_headers'] ?? '') . ' ' . self::FETCH_HEADERS);

        return $p;
    }

    /**
     * Inject the verdict banner above the message.
     */
    public function message_objects($p)
    {
        $message = $p['message'] ?? null;

        if (!$message || empty($message->headers) || $this->skip_folder($message)) {
            return $p;
        }

        $verdict = $this->evaluate($message->headers);
        $p['content'][] = $this->render($verdict);

        return $p;
    }

    /**
     * Skip own outgoing mail (Sent/Drafts), where a DKIM verdict is noise.
     */
    private function skip_folder($message)
    {
        if (!$this->rc->config->get('dkim_info_skip_sent', true)) {
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
     * Determine the DKIM verdict from the message headers.
     *
     * @return array{level:string, label:string, vars:array}
     */
    private function evaluate($headers)
    {
        $from_domain = $this->from_domain($headers);
        $auth = $this->dkim_from_authresults($headers);

        if (!empty($auth)) {
            $passes = array_filter($auth, static fn ($r) => $r['result'] === 'pass');
            $aligned = array_filter($passes, fn ($r) => $this->aligned($r['domain'], $from_domain));

            if (!empty($aligned)) {
                $r = reset($aligned);

                return [
                    'level' => 'confirmation',
                    'label' => 'dkimpassaligned',
                    'vars' => ['domain' => $r['domain'] ?: $from_domain],
                ];
            }

            if (!empty($passes)) {
                $r = reset($passes);

                return [
                    'level' => 'warning',
                    'label' => 'dkimpassunaligned',
                    'vars' => ['domain' => $r['domain'] ?: '?', 'from' => $from_domain ?: '?'],
                ];
            }

            // No pass — report the (worst) non-pass result, ignoring "none".
            $failed = array_filter($auth, static fn ($r) => $r['result'] !== 'none');
            if (!empty($failed)) {
                $r = reset($failed);

                return [
                    'level' => 'error',
                    'label' => 'dkimfail',
                    'vars' => ['result' => $r['result']],
                ];
            }
        }

        // No usable Authentication-Results — fall back to raw signature presence.
        $sigs = $this->normalize($headers->get('DKIM-Signature', false));

        if (!empty($sigs)) {
            $domain = $this->signature_domain($sigs[0]);

            return [
                'level' => 'information',
                'label' => $this->aligned($domain, $from_domain) ? 'dkimunverified' : 'dkimunverifiedunaligned',
                'vars' => ['domain' => $domain ?: '?', 'from' => $from_domain ?: '?'],
            ];
        }

        return [
            'level' => 'warning',
            'label' => 'dkimnone',
            'vars' => [],
        ];
    }

    /**
     * Extract DKIM results from the Authentication-Results header(s).
     *
     * @return array<array{result:string, domain:?string}>
     */
    private function dkim_from_authresults($headers)
    {
        $trusted = (array) $this->rc->config->get('dkim_info_trusted_authserv', []);
        $trusted = array_map('strtolower', $trusted);
        $results = [];

        foreach ($this->normalize($headers->get('Authentication-Results', false)) as $ar) {
            $ar = trim(preg_replace('/\s+/', ' ', $ar));
            $segments = explode(';', $ar);
            $authserv = strtolower(trim(strtok($segments[0], ' ')));

            if (!empty($trusted) && !in_array($authserv, $trusted, true)) {
                continue;
            }

            // First segment is the authserv-id; the rest are method results.
            array_shift($segments);

            foreach ($segments as $segment) {
                if (!preg_match('/^\s*dkim\s*=\s*([a-z]+)/i', $segment, $m)) {
                    continue;
                }

                $result = strtolower($m[1]);
                $domain = null;

                if (preg_match('/header\.d\s*=\s*([^\s;]+)/i', $segment, $dm)) {
                    $domain = strtolower(trim($dm[1], '<>'));
                } elseif (preg_match('/header\.i\s*=\s*@?([^\s;]+)/i', $segment, $im)) {
                    $i = trim($im[1], '<>');
                    $at = strrpos($i, '@');
                    $domain = strtolower($at !== false ? substr($i, $at + 1) : $i);
                }

                $results[] = ['result' => $result, 'domain' => $domain];
            }
        }

        return $results;
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

    /**
     * Build the banner HTML.
     */
    private function render($verdict)
    {
        $text = $this->gettext(['name' => $verdict['label'], 'vars' => $verdict['vars']]);

        return html::div(
            ['class' => 'dkim-info box' . $verdict['level'] . ' dkim-' . $verdict['level'], 'role' => 'note'],
            html::span(['class' => 'dkim-info-label'], rcube::Q($this->gettext('dkimstatus')))
                . html::span(null, rcube::Q($text))
        );
    }
}
