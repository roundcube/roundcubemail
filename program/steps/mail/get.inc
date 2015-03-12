<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/get.inc                                            |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Delivering a specific part of a mail message                        |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/


// show loading page
if (!empty($_GET['_preload'])) {
    $_get = $_GET + array('_mimewarning' => 1, '_embed' => 1);
    unset($_get['_preload']);
    $url = $RCMAIL->url($_get);
    $message = $RCMAIL->gettext('loadingdata');

    header('Content-Type: text/html; charset=' . RCUBE_CHARSET);
    print "<html>\n<head>\n"
        . '<meta http-equiv="refresh" content="0; url='.rcube::Q($url).'">' . "\n"
        . '<meta http-equiv="content-type" content="text/html; charset='.RCUBE_CHARSET.'">' . "\n"
        . "</head>\n<body>\n$message\n</body>\n</html>";
    exit;
}

ob_end_clean();


// similar code as in program/steps/mail/show.inc
if (!empty($_GET['_uid'])) {
    $uid = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_GET);
    $RCMAIL->config->set('prefer_html', true);
    $MESSAGE = new rcube_message($uid);
}

// check connection status
check_storage_status();

$part_id = rcube_utils::get_input_value('_part', rcube_utils::INPUT_GPC);

// show part page
if (!empty($_GET['_frame'])) {
    if ($part_id && ($part = $MESSAGE->mime_parts[$part_id])) {
        $filename = rcmail_attachment_name($part);
        $OUTPUT->set_pagetitle($filename);
    }

    // register UI objects
    $OUTPUT->add_handlers(array(
        'messagepartframe'    => 'rcmail_message_part_frame',
        'messagepartcontrols' => 'rcmail_message_part_controls',
    ));

    $OUTPUT->set_env('mailbox', $RCMAIL->storage->get_folder());
    $OUTPUT->set_env('uid', $uid);
    $OUTPUT->set_env('part', $part_id);
    $OUTPUT->set_env('filename', $filename);

    $OUTPUT->send('messagepart');
    exit;
}

// render thumbnail of an image attachment
else if ($_GET['_thumb']) {
    $pid = rcube_utils::get_input_value('_part', rcube_utils::INPUT_GET);
    if ($part = $MESSAGE->mime_parts[$pid]) {
        $thumbnail_size = $RCMAIL->config->get('image_thumbnail_size', 240);
        $temp_dir       = $RCMAIL->config->get('temp_dir');
        list(,$ext)     = explode('/', $part->mimetype);
        $mimetype       = $part->mimetype;
        $file_ident     = $MESSAGE->headers->messageID . ':' . $part->mime_id . ':' . $part->size . ':' . $part->mimetype;
        $cache_basename = $temp_dir . '/' . md5($file_ident . ':' . $RCMAIL->user->ID . ':' . $thumbnail_size);
        $cache_file     = $cache_basename . '.' . $ext;

        // render thumbnail image if not done yet
        if (!is_file($cache_file)) {
            if ($fp = fopen(($orig_name = $cache_basename . '.orig.' . $ext), 'w')) {
                $MESSAGE->get_part_body($part->mime_id, false, 0, $fp);
                fclose($fp);

                $image = new rcube_image($orig_name);
                if ($imgtype = $image->resize($thumbnail_size, $cache_file, true)) {
                    $mimetype = 'image/' . $imgtype;
                    unlink($orig_name);
                }
                else {
                    rename($orig_name, $cache_file);
                }
            }
        }

        if (is_file($cache_file)) {
            header('Content-Type: ' . $mimetype);
            readfile($cache_file);
        }
    }

    exit;
}

else if (strlen($part_id)) {
    if ($part = $MESSAGE->mime_parts[$part_id]) {
        $mimetype = rcmail_fix_mimetype($part->mimetype);

        // allow post-processing of the message body
        $plugin = $RCMAIL->plugins->exec_hook('message_part_get', array(
            'uid'      => $MESSAGE->uid,
            'id'       => $part->mime_id,
            'mimetype' => $mimetype,
            'part'     => $part,
            'download' => !empty($_GET['_download'])
        ));

        if ($plugin['abort']) {
            exit;
        }

        // overwrite modified vars from plugin
        $mimetype   = $plugin['mimetype'];
        $extensions = rcube_mime::get_mime_extensions($mimetype);

        if ($plugin['body']) {
            $body = $plugin['body'];
        }

        // compare file mimetype with the stated content-type headers and file extension to avoid malicious operations
        if (!empty($_REQUEST['_embed']) && empty($_REQUEST['_nocheck'])) {
            $file_extension = strtolower(pathinfo($part->filename, PATHINFO_EXTENSION));

            // 1. compare filename suffix with expected suffix derived from mimetype
            $valid = $file_extension && in_array($file_extension, (array)$extensions) || empty($extensions) || !empty($_REQUEST['_mimeclass']);

            // 2. detect the real mimetype of the attachment part and compare it with the stated mimetype and filename extension
            if ($valid || !$file_extension || $mimetype == 'application/octet-stream' || stripos($mimetype, 'text/') === 0) {
                $tmp_body = $body ?: $MESSAGE->get_part_body($part->mime_id, false, 2048);

                // detect message part mimetype
                $real_mimetype = rcube_mime::file_content_type($tmp_body, $part->filename, $mimetype, true, true);
                list($real_ctype_primary, $real_ctype_secondary) = explode('/', $real_mimetype);

                // accept text/plain with any extension
                if ($real_mimetype == 'text/plain' && $real_mimetype == $mimetype) {
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

                // fix mimetype for images wrongly declared as octet-stream
                if ($mimetype == 'application/octet-stream' && strpos($real_mimetype, 'image/') === 0 && $valid_extension) {
                    $mimetype = $real_mimetype;
                }

                // "fix" real mimetype the same way the original is before comparison
                $real_mimetype = rcmail_fix_mimetype($real_mimetype);

                $valid = $real_mimetype == $mimetype && $valid_extension;
            }
            else {
                $real_mimetype = $mimetype;
            }

            // show warning if validity checks failed
            if (!$valid) {
                // send blocked.gif for expected images
                if (empty($_REQUEST['_mimewarning']) && strpos($mimetype, 'image/') === 0) {
                    // Do not cache. Failure might be the result of a misconfiguration, thus real content should be returned once fixed. 
                    $content = $RCMAIL->get_resource_content('blocked.gif');
                    $OUTPUT->nocacheing_headers();
                    header("Content-Type: image/gif");
                    header("Content-Transfer-Encoding: binary");
                    header("Content-Length: " . strlen($content));
                    echo $content;
                }
                else {  // html warning with a button to load the file anyway
                    $OUTPUT = new rcmail_html_page();
                    $OUTPUT->write(html::tag('html', null, html::tag('body', 'embed',
                        html::div(array('class' => 'rcmail-inline-message rcmail-inline-warning'),
                            $RCMAIL->gettext(array(
                                'name' => 'attachmentvalidationerror',
                                'vars' => array(
                                    'expected' => $mimetype . ($file_extension ? " (.$file_extension)" : ''),
                                    'detected' => $real_mimetype . ($extensions[0] ? " (.$extensions[0])" : ''),
                                )
                            ))
                            . html::p(array('class' => 'rcmail-inline-buttons'),
                                html::tag('button', array(
                                    'onclick' => "location.href='" . $RCMAIL->url(array_merge($_GET, array('_nocheck' => 1))) . "'"
                                ),
                                $RCMAIL->gettext('showanyway'))
                            )
                        ))));
                }

                exit;
            }
        }


        // TIFF to JPEG conversion, if needed
        $tiff_support = !empty($_SESSION['browser_caps']) && !empty($_SESSION['browser_caps']['tif']);
        if (!empty($_REQUEST['_embed']) && !$tiff_support
            && rcube_image::is_convertable('image/tiff')
            && rcmail_part_image_type($part) == 'image/tiff'
        ) {
            $tiff2jpeg = true;
            $mimetype  = 'image/jpeg';
        }


        $browser = $RCMAIL->output->browser;
        list($ctype_primary, $ctype_secondary) = explode('/', $mimetype);

        if (!$plugin['download'] && $ctype_primary == 'text') {
            header("Content-Type: text/$ctype_secondary; charset=" . ($part->charset ? $part->charset : RCUBE_CHARSET));
        }
        else {
            header("Content-Type: $mimetype");
            header("Content-Transfer-Encoding: binary");
        }

        // deliver part content
        if ($ctype_primary == 'text' && $ctype_secondary == 'html' && empty($plugin['download'])) {
            // Check if we have enough memory to handle the message in it
            // #1487424: we need up to 10x more memory than the body
            if (!rcube_utils::mem_check($part->size * 10)) {
                $out = '<body>' . $RCMAIL->gettext('messagetoobig'). ' '
                    . html::a('?_task=mail&_action=get&_download=1&_uid='.$MESSAGE->uid.'&_part='.$part->mime_id
                        .'&_mbox='. urlencode($MESSAGE->folder), $RCMAIL->gettext('download')) . '</body></html>';
            }
            else {
                // get part body if not available
                if (!isset($body)) {
                    $body = $MESSAGE->get_part_body($part->mime_id, true);
                }

                // show images?
                rcmail_check_safe($MESSAGE);

                // render HTML body
                $out = rcmail_print_body($body, $part, array('safe' => $MESSAGE->is_safe, 'inline_html' => false));

                // insert remote objects warning into HTML body
                if ($REMOTE_OBJECTS) {
                    $body_start = 0;
                    if ($body_pos = strpos($out, '<body')) {
                        $body_start = strpos($out, '>', $body_pos) + 1;
                    }

                    $out = substr($out, 0, $body_start)
                        . html::div(array('class' => 'rcmail-inline-message rcmail-inline-warning'),
                            rcube::Q($RCMAIL->gettext('blockedimages')) . '&nbsp;' .
                            html::tag('button',
                                array('onclick' => "location.href='" . $RCMAIL->url(array_merge($_GET, array('_safe' => 1))) . "'"),
                                rcube::Q($RCMAIL->gettext('showimages')))
                        )
                        . substr($out, $body_start);
                }
            }

            // check connection status
            if ($part->size && empty($body)) {
                check_storage_status();
            }

            $OUTPUT = new rcmail_html_page();
            $OUTPUT->write($out);
        }
        else {
            // don't kill the connection if download takes more than 30 sec.
            @set_time_limit(0);

            $filename = rcmail_attachment_name($part);

            if ($browser->ie)
                $filename = rawurlencode($filename);
            else
                $filename = addcslashes($filename, '"');

            $disposition = !empty($plugin['download']) ? 'attachment' : 'inline';

            // Workaround for nasty IE bug (#1488844)
            // If Content-Disposition header contains string "attachment" e.g. in filename
            // IE handles data as attachment not inline
            if ($disposition == 'inline' && $browser->ie && $browser->ver < 9) {
                $filename = str_ireplace('attachment', 'attach', $filename);
            }

            // add filename extension if missing
            if (!pathinfo($filename, PATHINFO_EXTENSION) && ($extensions = rcube_mime::get_mime_extensions($mimetype))) {
                $filename .= '.' . $extensions[0];
            }

            header("Content-Disposition: $disposition; filename=\"$filename\"");

            // handle tiff to jpeg conversion
            if (!empty($tiff2jpeg)) {
                $temp_dir  = unslashify($RCMAIL->config->get('temp_dir'));
                $file_path = tempnam($temp_dir, 'rcmAttmnt');

                // write content to temp file
                if ($body) {
                    $saved = file_put_contents($file_path, $body);
                }
                else if ($part->size) {
                    $fd    = fopen($file_path, 'w');
                    $saved = $MESSAGE->get_part_body($part->mime_id, false, 0, $fd);
                    fclose($fd);
                }

                // convert image to jpeg and send it to the browser
                if ($saved) {
                    $image = new rcube_image($file_path);
                    if ($image->convert(rcube_image::TYPE_JPG, $file_path)) {
                        header("Content-Length: " . filesize($file_path));
                        readfile($file_path);
                    }
                    unlink($file_path);
                }
            }
            // do content filtering to avoid XSS through fake images
            else if (!empty($_REQUEST['_embed']) && $browser->ie && $browser->ver <= 8) {
                if ($body) {
                    echo preg_match('/<(script|iframe|object)/i', $body) ? '' : $body;
                    $sent = true;
                }
                else if ($part->size) {
                    $stdout = fopen('php://output', 'w');
                    stream_filter_register('rcube_content', 'rcube_content_filter') or die('Failed to register content filter');
                    stream_filter_append($stdout, 'rcube_content');
                    $sent = $MESSAGE->get_part_body($part->mime_id, true, 0, $stdout);
                }
            }
            // send part as-it-is
            else {
                if ($body && empty($plugin['download'])) {
                    header("Content-Length: " . strlen($body));
                    echo $body;
                    $sent = true;
                }
                else if ($part->size) {
                    if ($size = (int)$part->d_parameters['size']) {
                        header("Content-Length: $size");
                    }

                    $sent = $MESSAGE->get_part_body($part->mime_id, false, 0, -1);
                }
            }

            // check connection status
            if ($part->size && !$sent) {
                check_storage_status();
            }
        }

        exit;
    }
}
// print message
else {
    // send correct headers for content type
    header("Content-Type: text/html");

    $cont = "<html>\n<head><title></title>\n</head>\n<body>";
    $cont .= rcmail_message_body(array());
    $cont .= "\n</body>\n</html>";

    $OUTPUT = new rcmail_html_page();
    $OUTPUT->write($cont);

    exit;
}


// if we arrive here, the requested part was not found
header('HTTP/1.1 404 Not Found');
exit;

/**
 * Handles nicely storage connection errors
 */
function check_storage_status()
{
    $error = rcmail::get_instance()->storage->get_error_code();

    // Check if we have a connection error
    if ($error == rcube_imap_generic::ERROR_BAD) {
        ob_end_clean();

        // Get action is often executed simultanously.
        // Some servers have MAXPERIP or other limits.
        // To workaround this we'll wait for some time
        // and try again (once).
        // Note: Random sleep interval is used to minimize concurency
        // in getting message parts

        if (!isset($_GET['_redirected'])) {
            usleep(rand(10,30)*100000); // 1-3 sec.
            header('Location: ' . $_SERVER['REQUEST_URI'] . '&_redirected=1');
        }
        else {
            rcube::raise_error(array(
                'code' => 500, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => 'Unable to get/display message part. IMAP connection error'),
                true, true);
        }

        // Don't kill session, just quit (#1486995)
        exit;
    }
}

/**
 * Attachment properties table
 */
function rcmail_message_part_controls($attrib)
{
    global $MESSAGE, $RCMAIL;

    $part = asciiwords(rcube_utils::get_input_value('_part', rcube_utils::INPUT_GPC));
    if (!is_object($MESSAGE) || !is_array($MESSAGE->parts)
        || !($_GET['_uid'] && $_GET['_part']) || !$MESSAGE->mime_parts[$part]
    ) {
        return '';
    }

    $part  = $MESSAGE->mime_parts[$part];
    $table = new html_table(array('cols' => 2));

    $table->add('title', rcube::Q($RCMAIL->gettext('namex')).':');
    $table->add('header', rcube::Q(rcmail_attachment_name($part)));

    $table->add('title', rcube::Q($RCMAIL->gettext('type')).':');
    $table->add('header', rcube::Q($part->mimetype));

    $table->add('title', rcube::Q($RCMAIL->gettext('size')).':');
    $table->add('header', rcube::Q($RCMAIL->message_part_size($part)));

    return $table->show($attrib);
}

/**
 * Attachment preview frame
 */
function rcmail_message_part_frame($attrib)
{
    global $MESSAGE, $RCMAIL;

    $part = $MESSAGE->mime_parts[asciiwords(rcube_utils::get_input_value('_part', rcube_utils::INPUT_GPC))];
    $ctype_primary = strtolower($part->ctype_primary);

    $attrib['src'] = './?' . str_replace('_frame=', ($ctype_primary=='text' ? '_embed=' : '_preload='), $_SERVER['QUERY_STRING']);

    $RCMAIL->output->add_gui_object('messagepartframe', $attrib['id']);

    return html::iframe($attrib);
}
