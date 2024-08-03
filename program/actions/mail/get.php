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
 |   Delivering a specific uploaded file or mail message attachment      |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_get extends rcmail_action_mail_index
{
    protected static $attachment;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        // This resets X-Frame-Options for framed output (#6688)
        $rcmail->output->page_headers();

        // show loading page
        if (!empty($_GET['_preload'])) {
            unset($_GET['_preload']);
            unset($_GET['_safe']);

            $url     = $rcmail->url($_GET + ['_mimewarning' => 1, '_embed' => 1]);
            $message = $rcmail->gettext('loadingdata');

            header('Content-Type: text/html; charset=' . RCUBE_CHARSET);
            print "<html>\n<head>\n"
                . '<meta http-equiv="refresh" content="0; url='.rcube::Q($url).'">' . "\n"
                . '<meta http-equiv="content-type" content="text/html; charset=' . RCUBE_CHARSET . '">' . "\n"
                . "</head>\n<body>\n$message\n</body>\n</html>";
            exit;
        }

        $attachment = new rcmail_attachment_handler;
        $mimetype = $attachment->mimetype;
        $filename = $attachment->filename;

        self::$attachment = $attachment;

        // show part page
        if (!empty($_GET['_frame'])) {
            $rcmail->output->set_pagetitle($filename);

            // register UI objects
            $rcmail->output->add_handlers([
                    'messagepartframe'    => [$this, 'message_part_frame'],
                    'messagepartcontrols' => [$this, 'message_part_controls'],
            ]);

            $part_id = rcube_utils::get_input_string('_part', rcube_utils::INPUT_GET);
            $uid     = rcube_utils::get_input_string('_uid', rcube_utils::INPUT_GET);

            // message/rfc822 preview (Note: handle also multipart/ parts, they can
            // come from Enigma, which replaces message/rfc822 with real mimetype)
            if ($part_id && ($mimetype == 'message/rfc822' || strpos($mimetype, 'multipart/') === 0)) {
                $uid = preg_replace('/\.[0-9.]+/', '', $uid);
                $uid .= '.' . $part_id;

                $rcmail->output->set_env('is_message', true);
            }

            $rcmail->output->set_env('mailbox', $rcmail->storage->get_folder());
            $rcmail->output->set_env('uid', $uid);
            $rcmail->output->set_env('part', $part_id);
            $rcmail->output->set_env('filename', $filename);
            $rcmail->output->set_env('mimetype', $mimetype);

            $rcmail->output->send('messagepart');
        }

        // render thumbnail of an image attachment
        if (!empty($_GET['_thumb']) && $attachment->is_valid()) {
            $thumbnail_size = $rcmail->config->get('image_thumbnail_size', 240);
            $file_ident     = $attachment->ident;
            $thumb_name     = 'thumb' . md5($file_ident . ':' . $rcmail->user->ID . ':' . $thumbnail_size);
            $cache_file     = rcube_utils::temp_filename($thumb_name, false, false);

            // render thumbnail image if not done yet
            if (!is_file($cache_file) && $attachment->body_to_file($orig_name = rcube_utils::temp_filename('attmnt'))) {
                $image = new rcube_image($orig_name);

                if ($imgtype = $image->resize($thumbnail_size, $cache_file, true)) {
                    $mimetype = 'image/' . $imgtype;
                }
                else {
                    // Resize failed, we need to check the file mimetype
                    // So, we do not exit here, but goto generic file body handler below
                    $_GET['_thumb']     = 0;
                    $_REQUEST['_embed'] = 1;
                }
            }

            if (!empty($_GET['_thumb'])) {
                if (is_file($cache_file)) {
                    $rcmail->output->future_expire_header(3600);
                    header('Content-Type: ' . $mimetype);
                    header('Content-Length: ' . filesize($cache_file));
                    readfile($cache_file);
                }

                exit;
            }
        }

        // Handle attachment body (display or download)
        if (empty($_GET['_thumb']) && $attachment->is_valid()) {
            // require CSRF protected url for downloads
            if (!empty($_GET['_download'])) {
                $rcmail->request_security_check(rcube_utils::INPUT_GET);
            }

            $extensions = rcube_mime::get_mime_extensions($mimetype);

            // compare file mimetype with the stated content-type headers and file extension to avoid malicious operations
            if (!empty($_REQUEST['_embed']) && empty($_REQUEST['_nocheck'])) {
                $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                // 1. compare filename suffix with expected suffix derived from mimetype
                $valid = $file_extension && in_array($file_extension, (array)$extensions)
                    || empty($extensions)
                    || !empty($_REQUEST['_mimeclass']);

                // 2. detect the real mimetype of the attachment part and compare it with the stated mimetype and filename extension
                if ($valid || !$file_extension || $mimetype == 'application/octet-stream' || stripos($mimetype, 'text/') === 0) {
                    $tmp_body = $attachment->body(2048);

                    // detect message part mimetype
                    $real_mimetype = rcube_mime::file_content_type($tmp_body, $filename, $mimetype, true, true);
                    list($real_ctype_primary, $real_ctype_secondary) = explode('/', $real_mimetype);

                    // accept text/plain with any extension
                    if ($real_mimetype == 'text/plain' && self::mimetype_compare($real_mimetype, $mimetype)) {
                        $valid_extension = true;
                    }
                    // ignore differences in text/* mimetypes. Filetype detection isn't very reliable here
                    else if ($real_ctype_primary == 'text' && strpos($mimetype, $real_ctype_primary) === 0) {
                        $real_mimetype   = $mimetype;
                        $valid_extension = true;
                    }
                    // ignore filename extension if mimeclass matches (#1489029)
                    else if (!empty($_REQUEST['_mimeclass']) && $real_ctype_primary == $_REQUEST['_mimeclass']) {
                        $valid_extension = true;
                    }
                    else {
                        // get valid file extensions
                        $extensions      = rcube_mime::get_mime_extensions($real_mimetype);
                        $valid_extension = !$file_extension || empty($extensions) || in_array($file_extension, (array)$extensions);
                    }

                    if (
                        // fix mimetype for files wrongly declared as octet-stream
                        ($mimetype == 'application/octet-stream' && $valid_extension)
                        // force detected mimetype for images (#8158)
                        || (strpos($real_mimetype, 'image/') === 0)
                    ) {
                        $mimetype = $real_mimetype;
                    }

                    // "fix" real mimetype the same way the original is before comparison
                    $real_mimetype = rcube_mime::fix_mimetype($real_mimetype);

                    $valid = $valid_extension && self::mimetype_compare($real_mimetype, $mimetype);
                }
                else {
                    $real_mimetype = $mimetype;
                }

                // show warning if validity checks failed
                if (!$valid) {
                    // send blocked.gif for expected images
                    if (empty($_REQUEST['_mimewarning']) && strpos($mimetype, 'image/') === 0) {
                        // Do not cache. Failure might be the result of a misconfiguration,
                        // thus real content should be returned once fixed.
                        $content = self::get_resource_content('blocked.gif');
                        $rcmail->output->nocacheing_headers();
                        header("Content-Type: image/gif");
                        header("Content-Transfer-Encoding: binary");
                        header("Content-Length: " . strlen($content));
                        echo $content;
                    }
                    // html warning with a button to load the file anyway
                    else {
                        $rcmail->output = new rcmail_html_page();
                        $rcmail->output->register_inline_warning(
                            $rcmail->gettext([
                                    'name' => 'attachmentvalidationerror',
                                    'vars' => [
                                        'expected' => $mimetype . (!empty($file_extension) ? rcube::Q(" (.{$file_extension})") : ''),
                                        'detected' => $real_mimetype . (!empty($extensions[0]) ? " (.{$extensions[0]})" : ''),
                                    ]
                            ]),
                            $rcmail->gettext('showanyway'),
                            $rcmail->url(array_merge($_GET, ['_nocheck' => 1]))
                        );

                        $rcmail->output->write();
                    }

                    exit;
                }
            }

            // TIFF/WEBP to JPEG conversion, if needed
            foreach (['tiff', 'webp'] as $type) {
                $img_support = !empty($_SESSION['browser_caps']) && !empty($_SESSION['browser_caps'][$type]);
                if (
                    !empty($_REQUEST['_embed'])
                    && !$img_support
                    && $attachment->image_type() == 'image/' . $type
                    && rcube_image::is_convertable('image/' . $type)
                ) {
                    $convert2jpeg = true;
                    $mimetype     = 'image/jpeg';
                    break;
                }
            }

            // deliver part content
            if ($mimetype == 'text/html' && empty($_GET['_download'])) {
                $rcmail->output = new rcmail_html_page();
                $out = '';

                // Check if we have enough memory to handle the message in it
                // #1487424: we need up to 10x more memory than the body
                if (!rcube_utils::mem_check($attachment->size * 10)) {
                    $rcmail->output->register_inline_warning(
                        $rcmail->gettext('messagetoobig'),
                        $rcmail->gettext('download'),
                        $rcmail->url(array_merge($_GET, ['_download' => 1]))
                    );
                }
                else {
                    // render HTML body
                    $out = $attachment->html();

                    // insert remote objects warning into HTML body
                    if (self::$REMOTE_OBJECTS) {
                        $rcmail->output->register_inline_warning(
                            $rcmail->gettext('blockedresources'),
                            $rcmail->gettext('allow'),
                            $rcmail->url(array_merge($_GET, ['_safe' => 1]))
                        );
                    } else {
                        // Use strict security policy to make sure no javascript is executed
                        // TODO: Make the above "blocked resources button" working with strict policy
                        // TODO: Move this to rcmail_html_page::write()?
                        header("Content-Security-Policy: script-src 'none'");
                    }
                }

                $rcmail->output->write($out);
                exit;
            }

            // add filename extension if missing
            if (!pathinfo($filename, PATHINFO_EXTENSION) && ($extensions = rcube_mime::get_mime_extensions($mimetype))) {
                $filename .= '.' . $extensions[0];
            }

            $rcmail->output->download_headers($filename, [
                    'type'         => $mimetype,
                    'type_charset' => $attachment->charset,
                    'disposition'  => !empty($_GET['_download']) ? 'attachment' : 'inline',
            ]);

            // handle tiff to jpeg conversion
            if (!empty($convert2jpeg)) {
                $file_path = rcube_utils::temp_filename('attmnt');

                // convert image to jpeg and send it to the browser
                if ($attachment->body_to_file($file_path)) {
                    $image = new rcube_image($file_path);
                    if ($image->convert(rcube_image::TYPE_JPG, $file_path)) {
                        header("Content-Length: " . filesize($file_path));
                        readfile($file_path);
                    }
                }
            }
            else {
                $attachment->output($mimetype);
            }

            exit;
        }

        // if we arrive here, the requested part was not found
        header('HTTP/1.1 404 Not Found');
        exit;
    }

    /**
     * Compares two mimetype strings with making sure that
     * e.g. image/bmp and image/x-ms-bmp are treated as equal.
     */
    public static function mimetype_compare($type1, $type2)
    {
        $regexp = '~/(x-ms-|x-)~';
        $type1  = preg_replace($regexp, '/', $type1);
        $type2  = preg_replace($regexp, '/', $type2);

        return $type1 === $type2;
    }

    /**
     * Attachment properties table
     */
    public static function message_part_controls($attrib)
    {
        if (!self::$attachment->is_valid()) {
            return '';
        }

        $rcmail = rcmail::get_instance();
        $table  = new html_table(['cols' => 2]);

        $table->add('title', rcube::Q($rcmail->gettext('namex')).':');
        $table->add('header', rcube::Q(self::$attachment->filename));

        $table->add('title', rcube::Q($rcmail->gettext('type')).':');
        $table->add('header', rcube::Q(self::$attachment->mimetype));

        $table->add('title', rcube::Q($rcmail->gettext('size')).':');
        $table->add('header', rcube::Q(self::$attachment->size()));

        return $table->show($attrib);
    }

    /**
     * Attachment preview frame
     */
    public static function message_part_frame($attrib)
    {
        $rcmail = rcmail::get_instance();

        if ($rcmail->output->get_env('is_message')) {
            $url = [
                'task'   => 'mail',
                'action' => 'preview',
                'uid'    => $rcmail->output->get_env('uid'),
                'mbox'   => $rcmail->output->get_env('mailbox'),
            ];
        }
        else {
            $mimetype = $rcmail->output->get_env('mimetype');
            $url      = $_GET;
            $url[strpos($mimetype, 'text/') === 0 ? '_embed' : '_preload'] = 1;
            unset($url['_frame']);
        }

        $url['_framed'] = 1; // For proper X-Frame-Options:deny handling

        $attrib['src'] = $rcmail->url($url);

        $rcmail->output->add_gui_object('messagepartframe', $attrib['id']);

        return html::iframe($attrib);
    }
}
