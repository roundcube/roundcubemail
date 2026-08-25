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

        $source = false;
        $ctype = null;

        try {
            $client = rcube::get_instance()->get_http_client(['allow_redirects' => false]);
            $response = $client->get($realurl);

            $source = $response->getBody();

            $ctype = $response->getHeader('Content-Type');
            $ctype = !empty($ctype) ? $ctype[0] : '';
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
                ['Content-Type: text/css']
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
            && !preg_match('~^[{<]~', $source) // exclude json and html
            && str_contains($source, '{')
            && str_contains($source, '}')
            && str_contains($source, ':');
    }
}
