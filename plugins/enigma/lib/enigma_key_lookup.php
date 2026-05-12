<?php

use GuzzleHttp\Exception\RequestException;

/*
 +-------------------------------------------------------------------------+
 | Key lookup engine of the Enigma Plugin                                  |
 |                                                                         |
 | Copyright (C) The Roundcube Dev Team                                    |
 |                                                                         |
 | Licensed under the GNU General Public License version 3 or              |
 | any later version with exceptions for skins & plugins.                  |
 | See the README file for a full license statement.                       |
 +-------------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+
*/

/**
 * PGP public key lookup engine for the Enigma plugin.
 */
class enigma_key_lookup
{
    /**
     * Find a public key in DNS (according to Kolab Web-Of-Anti-Trust).
     *
     * @param string $recipient Email address
     */
    public static function woat($recipient): ?string
    {
        $woat = rcmail::get_instance()->config->get('enigma_woat');

        if (empty($woat)) {
            return null;
        }

        [$local, $domain] = explode('@', $recipient);

        // Do this for configured domains only
        if (is_array($woat) && !in_array_nocase($domain, $woat)) {
            return null;
        }

        // remove parts behind a recipient delimiter ("jeroen+Trash" => "jeroen")
        $local = preg_replace('/\+.*$/', '', $local);

        $fqdn = sha1($local) . '._woat.' . $domain;

        // Fetch the TXT record(s)
        if (($records = dns_get_record($fqdn, \DNS_TXT)) === false) {
            return null;
        }

        foreach ($records as $record) {
            if (str_starts_with($record['txt'], 'v=woat1,')) {
                $entry = explode('public_key=', $record['txt']);
                if (count($entry) == 2) {
                    // For now we support only one key
                    return $entry[1];
                }
            }
        }

        return null;
    }

    /**
     * Find a public key in a HKP v1 server.
     *
     * @param string $recipient Email address
     */
    public static function keyserver($recipient): ?string
    {
        $rcmail = rcmail::get_instance();
        $keyserver = $rcmail->config->get('enigma_keyserver');

        if (empty($keyserver)) {
            return null;
        }

        $recipient = strtolower($recipient);

        if (is_array($keyserver)) {
            [$local, $domain] = explode('@', $recipient);

            if (!empty($keyserver[$domain])) {
                $keyserver = $keyserver[$domain];
            } elseif (!empty($keyserver['*'])) {
                $keyserver = $keyserver['*'];
            } else {
                return null;
            }
        }

        // TODO: Support key server discovery

        // Get keys metadata
        try {
            $client = $rcmail->get_http_client();

            $response = $client->get($keyserver . '/pks/lookup?op=index&options=mr&search=' . rawurlencode($recipient));

            $source = $response->getBody();
            $ctype = $response->getHeader('Content-Type');
            $ctype = !empty($ctype) ? $ctype[0] : '';
        } catch (\Exception $e) {
            if (!($e instanceof RequestException) || $e->getResponse()->getStatusCode() != 404) {
                rcube::raise_error($e, true, false);
            }
        }

        if (!isset($source) || !str_starts_with($source, 'info:1:')) {
            return null;
        }

        // Process the keys metadata
        // TODO: Ignore revoked/expired keys/identities?
        $keyid = null;
        $list = [];
        foreach (preg_split('/\r?\n/', $source) as $line) {
            if (str_starts_with($line, 'pub:')) {
                $tokens = explode(':', $line);
                $keyid = $tokens[1] ?? null;
            } elseif ($keyid && str_starts_with($line, 'uid:')) {
                $tokens = explode(':', $line);
                $identity = strtolower(rawurldecode($tokens[1] ?? ''));
                if (str_contains($identity, $recipient)) {
                    $list[] = $keyid;
                }
            } else {
                $keyid = null;
            }
        }

        // Get keys
        $output = [];
        foreach (array_unique($list) as $id) {
            try {
                $response = $client->get($keyserver . '/pks/lookup?op=get&options=mr&search=0x' . $id);

                $source = $response->getBody();
                $ctype = $response->getHeader('Content-Type');
                $ctype = !empty($ctype) ? $ctype[0] : '';

                if ($ctype === 'application/pgp-keys'
                    && str_starts_with($source, '-----BEGIN PGP PUBLIC KEY BLOCK-----')
                ) {
                    $output[] = $source;
                }
            } catch (\Exception $e) {
                if (!($e instanceof RequestException) || $e->getResponse()->getStatusCode() != 404) {
                    rcube::raise_error($e, true, false);
                }
            }
        }

        return implode("\n", $output);
    }
}
