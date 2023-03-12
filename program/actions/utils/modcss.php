<?php

/**
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
    public function run($args = [])
    {
        $url = preg_replace('![^a-z0-9.-]!i', '', $_GET['_u']);

        if ($url === null || !($realurl = $_SESSION['modcssurls'][$url])) {
            header('HTTP/1.1 403 Forbidden');
            exit("Unauthorized request");
        }

        // don't allow any other connections than http(s)
        if (!preg_match('~^https?://~i', $realurl, $matches)) {
            header('HTTP/1.1 403 Forbidden');
            exit("Invalid URL");
        }

        $source = false;
        $ctype  = null;

        try {
            $client   = rcube::get_instance()->get_http_client();
            $response = $client->get($realurl);

            if (!empty($response)) {
                $ctype  = $response->getHeader('Content-Type');
                $ctype  = !empty($ctype) ? $ctype[0] : '';
                $source = $response->getBody();
            }
        }
        catch (Exception $e) {
            rcube::raise_error($e, true, false);
        }

        $ctype_regexp = '~^text/(css|plain)~i';
        $container_id = isset($_GET['_c']) ? preg_replace('/[^a-z0-9]/i', '', $_GET['_c']) : '';
        $css_prefix   = isset($_GET['_p']) ? preg_replace('/[^a-z0-9]/i', '', $_GET['_p']) : '';

        if ($source !== false && $ctype && preg_match($ctype_regexp, $ctype)) {
            header('Content-Type: text/css');
            echo rcube_utils::mod_css_styles($source, $container_id, false, $css_prefix);
            exit;
        }

        header('HTTP/1.0 404 Not Found');
        exit("Invalid response returned by server");
    }
}
