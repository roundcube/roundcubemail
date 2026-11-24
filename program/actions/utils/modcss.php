<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Modify CSS source from a URL                                        |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_utils_modcss extends rcmail_action
{
    private const MAX_SIZE = 5 * 1024 * 1024;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    #[\Override]
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        $url = rcube_utils::get_input_string('_u', rcube_utils::INPUT_GET);
        $url = preg_replace('![^a-z0-9.-]!i', '', $url);

        if ($url === null || empty($_SESSION['modcssurls'][$url])) {
            $rcmail->output->sendExitError(403, 'Unauthorized request');
        }

        $realurl = $_SESSION['modcssurls'][$url];

        // don't allow any other connections than http(s)
        if (!preg_match('~^https?://~i', $realurl, $matches)) {
            $rcmail->output->sendExitError(403, 'Invalid URL');
        }

        // allow only hosts that resolve to an IP address
        $host = parse_url($realurl, \PHP_URL_HOST);
        if (!($ip = self::host2ip($host))) {
            $rcmail->output->sendExitError(403, 'Invalid URL host');
        }

        // do not reach to a local network for security
        if (rcube_utils::is_local_ip($ip)) {
            $rcmail->output->sendExitError(403, 'Local network host');
        }

        // TODO: block access to self ($_SERVER['HTTP_HOST']?)

        $source = false;
        $ctype = null;

        try {
            // Be strict, and limit the response size
            $options = [
                'allow_redirects' => false,
                'read_timeout' => 10,
                'stream' => true,
                'curl' => [
                    // Use the host's IP address
                    \CURLOPT_RESOLVE => [$host . ':' . (str_contains($ip, ':') ? "[{$ip}]" : $ip)],
                ],
            ];

            $client = rcube::get_instance()->get_http_client($options);
            $response = $client->get($realurl);

            $ctype = $response->getHeader('Content-Type');
            $ctype = !empty($ctype) ? $ctype[0] : '';

            $stream = $response->getBody();
            $source = '';
            while (!$stream->eof() && strlen($source) < self::MAX_SIZE) {
                $source .= $stream->read(1024);
            }
            $stream->close();
        } catch (\Exception $e) {
            rcube::raise_error($e, true, false);
        }

        $cid = rcube_utils::get_input_string('_c', rcube_utils::INPUT_GET);
        $prefix = rcube_utils::get_input_string('_p', rcube_utils::INPUT_GET);

        $container_id = preg_replace('/[^a-z0-9]/i', '', $cid);
        $css_prefix = preg_replace('/[^a-z0-9]/i', '', $prefix);

        if ($source !== false && self::is_valid_css($source, $ctype)) {
            $rcmail->output->sendExit(
                rcube_utils::mod_css_styles($source, $container_id, false, $css_prefix),
                [
                    'Content-Type: text/css',
                    'X-Content-Type-Options: nosniff',
                ]
            );
        }

        $rcmail->output->sendExitError(404, 'Invalid response returned by server');
    }

    /**
     * Do basic response validation
     */
    private static function is_valid_css(string $source, $ctype): bool
    {
        // Because we can't block all requests to local services we have to be strict
        // on what we proxy to the browser. Here we check if we deal with css or not.

        return preg_match('~^text/(css|plain)~i', (string) $ctype)
            // exclude json and html/xml (ignore leading whitespace and BOM)
            && !preg_match('~^[{<]~', ltrim($source, " \f\n\r\t\v\x00\xFE\xFF\xEF\xBB\xBF"))
            && str_contains($source, '{')
            && str_contains($source, '}')
            && str_contains($source, ':');
    }

    /**
     * Resolve hostname into an IP address
     */
    private static function host2ip($host): ?string
    {
        if (!empty($host)) {
            // Is it already a valid IP address?
            if (inet_pton($host) !== false) {
                return $host;
            }

            // Find IPv4 address
            if (($ip = gethostbyname($host)) && $ip !== $host) {
                return $ip;
            }

            // Find IPv6 address (ignore warnings)
            set_error_handler(static function () {}, \E_WARNING);
            $dns = dns_get_record($host, \DNS_AAAA);
            restore_error_handler();

            if ($dns) {
                foreach ($dns as $record) {
                    if ($record['type'] == 'AAAA') {
                        return $record['ipv6'];
                    }
                }
            }
        }

        return null;
    }
}
