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
 |   Compose a new mail message and send it or store as draft            |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_send extends rcmail_action
{
    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        // remove all scripts and act as called in frame
        $rcmail->output->reset();
        $rcmail->output->framed = true;

        $COMPOSE_ID = rcube_utils::get_input_string('_id', rcube_utils::INPUT_GPC);
        $COMPOSE    =& $_SESSION['compose_data_'.$COMPOSE_ID];

        // Sanity checks
        if (!isset($COMPOSE['id'])) {
            rcube::raise_error([
                    'code' => 500,
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'message' => "Invalid compose ID"
                ], true, false
            );

            $rcmail->output->show_message('internalerror', 'error');
            $rcmail->output->send('iframe');
        }

        $saveonly  = !empty($_GET['_saveonly']);
        $savedraft = !empty($_POST['_draft']) && !$saveonly;
        $SENDMAIL  = new rcmail_sendmail($COMPOSE, [
                'sendmail'      => true,
                'saveonly'      => $saveonly,
                'savedraft'     => $savedraft,
                'error_handler' => function(...$args) use ($rcmail) {
                    call_user_func_array([$rcmail->output, 'show_message'], $args);
                    $rcmail->output->send('iframe');
                },
                'keepformatting' => !empty($_POST['_keepformatting']),
        ]);

        if (!isset($COMPOSE['attachments'])) {
            $COMPOSE['attachments'] = [];
        }

        // Collect input for message headers
        $headers = $SENDMAIL->headers_input();

        $COMPOSE['param']['message-id'] = $headers['Message-ID'];

        $message_id      = $headers['Message-ID'];
        $message_charset = $SENDMAIL->options['charset'];
        $message_body    = rcube_utils::get_input_string('_message', rcube_utils::INPUT_POST, true, $message_charset);
        $isHtml          = (bool) rcube_utils::get_input_string('_is_html', rcube_utils::INPUT_POST);

        // Reset message body and attachments in Mailvelope mode
        if (isset($_POST['_pgpmime'])) {
            $pgp_mime     = rcube_utils::get_input_string('_pgpmime', rcube_utils::INPUT_POST);
            $isHtml       = false;
            $message_body = '';

            // clear unencrypted attachments
            if (!empty($COMPOSE['attachments'])) {
                foreach ((array) $COMPOSE['attachments'] as $attach) {
                    $rcmail->plugins->exec_hook('attachment_delete', $attach);
                }
            }

            $COMPOSE['attachments'] = [];
        }

        if ($isHtml) {
            $bstyle = [];

            if ($font_size = $rcmail->config->get('default_font_size')) {
                $bstyle[] = 'font-size: ' . $font_size;
            }
            if ($font_family = $rcmail->config->get('default_font')) {
                $bstyle[] = 'font-family: ' . self::font_defs($font_family);
            }

            // append doctype and html/body wrappers
            $bstyle       = !empty($bstyle) ? (" style='" . implode('; ', $bstyle) . "'") : '';
            $message_body = '<html><head>'
                . '<meta http-equiv="Content-Type" content="text/html; charset='
                . ($message_charset ?: RCUBE_CHARSET) . '" /></head>'
                . "<body" . $bstyle . ">\r\n" . $message_body;
        }

        if (!$savedraft) {
            if ($isHtml) {
                $b_style   = 'padding: 0 0.4em; border-left: #1010ff 2px solid; margin: 0';
                $pre_style = 'margin: 0; padding: 0; font-family: monospace';

                $message_body = preg_replace(
                    [
                        // remove empty signature div
                        '/<div id="_rc_sig">(&nbsp;)?<\/div>[\s\r\n]*$/',
                        // replace signature's div ID (#6073)
                        '/ id="_rc_sig"/',
                        // add inline css for blockquotes and container
                        '/<blockquote>/',
                        '/<div class="pre">/',
                        // convert TinyMCE's new-line sequences (#1490463)
                        '/<p>&nbsp;<\/p>/',
                    ],
                    [
                        '',
                        ' id="signature"',
                        '<blockquote type="cite" style="'.$b_style.'">',
                        '<div class="pre" style="'.$pre_style.'">',
                        '<p><br /></p>',
                    ],
                    $message_body
                );

                rcube_utils::preg_error([
                        'line'    => __LINE__,
                        'file'    => __FILE__,
                        'message' => "Could not format HTML!"
                    ], true);
            }

            // Check spelling before send
            if (
                $rcmail->config->get('spellcheck_before_send')
                && $rcmail->config->get('enable_spellcheck')
                && empty($COMPOSE['spell_checked'])
                && !empty($message_body)
            ) {
                $language     = rcube_utils::get_input_string('_lang', rcube_utils::INPUT_GPC);
                $message_body = str_replace("\r\n", "\n", $message_body);
                $spellchecker = new rcube_spellchecker($language);
                $spell_result = $spellchecker->check($message_body, $isHtml);

                if ($error = $spellchecker->error()) {
                    rcube::raise_error([
                            'code' => 500, 'file' => __FILE__, 'line' => __LINE__,
                            'message' => "Spellcheck error: " . $error
                        ],
                        true, false
                    );
                }
                else {
                    $COMPOSE['spell_checked'] = true;

                    if (!$spell_result) {
                        if ($isHtml) {
                            $result['words']      = $spellchecker->get();
                            $result['dictionary'] = (bool) $rcmail->config->get('spellcheck_dictionary');
                        }
                        else {
                            $result = $spellchecker->get_xml();
                        }

                        $rcmail->output->show_message('mispellingsfound', 'error');
                        $rcmail->output->command('spellcheck_resume', $result);
                        $rcmail->output->send('iframe');
                    }
                }
            }

            // generic footer for all messages
            if ($footer = $SENDMAIL->generic_message_footer($isHtml)) {
                $message_body .= "\r\n" . $footer;
            }
        }

        if ($isHtml) {
            $message_body .= "\r\n</body></html>\r\n";
        }

        // sort attachments to make sure the order is the same as in the UI (#1488423)
        if ($files = rcube_utils::get_input_string('_attachments', rcube_utils::INPUT_POST)) {
            $files = explode(',', $files);
            $files = array_flip($files);
            foreach ($files as $idx => $val) {
                if (!empty($COMPOSE['attachments'][$idx])) {
                    $files[$idx] = $COMPOSE['attachments'][$idx];
                    unset($COMPOSE['attachments'][$idx]);
                }
            }

            $COMPOSE['attachments'] = array_merge(array_filter($files), (array) $COMPOSE['attachments']);
        }

        // Since we can handle big messages with disk usage, we need more time to work
        @set_time_limit(360);

        // create PEAR::Mail_mime instance, set headers, body and params
        $MAIL_MIME = $SENDMAIL->create_message($headers, $message_body, $isHtml, $COMPOSE['attachments']);

        // add stored attachments, if any
        if (is_array($COMPOSE['attachments'])) {
            self::add_attachments($SENDMAIL, $MAIL_MIME, $COMPOSE['attachments'], $isHtml);
        }

        // compose PGP/Mime message
        if (!empty($pgp_mime)) {
            $MAIL_MIME->addAttachment(new Mail_mimePart('Version: 1', [
                    'content_type' => 'application/pgp-encrypted',
                    'description'  => 'PGP/MIME version identification',
            ]));

            $MAIL_MIME->addAttachment(new Mail_mimePart($pgp_mime, [
                    'content_type' => 'application/octet-stream',
                    'filename'     => 'encrypted.asc',
                    'disposition'  => 'inline',
            ]));

            $MAIL_MIME->setContentType('multipart/encrypted', ['protocol' => 'application/pgp-encrypted']);
            $MAIL_MIME->setParam('preamble', 'This is an OpenPGP/MIME encrypted message (RFC 2440 and 3156)');
        }

        // This hook allows to modify the message before send or save action
        $plugin    = $rcmail->plugins->exec_hook('message_ready', ['message' => $MAIL_MIME]);
        $MAIL_MIME = $plugin['message'];

        // Deliver the message over SMTP
        if (!$savedraft && !$saveonly) {
            $sent = $SENDMAIL->deliver_message($MAIL_MIME);
        }

        // Save the message in Drafts/Sent
        $saved = $SENDMAIL->save_message($MAIL_MIME);

        // raise error if saving failed
        if (!$saved && $savedraft) {
            self::display_server_error('errorsaving');
            // start the auto-save timer again
            $rcmail->output->command('auto_save_start');
            $rcmail->output->send('iframe');
        }

        $store_target = $SENDMAIL->options['store_target'];
        $store_folder = $SENDMAIL->options['store_folder'];

        // delete previous saved draft
        $drafts_mbox = $rcmail->config->get('drafts_mbox');
        $old_id      = rcube_utils::get_input_string('_draft_saveid', rcube_utils::INPUT_POST);

        if ($old_id && (!empty($sent) || $saved)) {
            $deleted = $rcmail->storage->delete_message($old_id, $drafts_mbox);

            // raise error if deletion of old draft failed
            if (!$deleted) {
                rcube::raise_error([
                        'code'    => 800,
                        'type'    => 'imap',
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => "Could not delete message from $drafts_mbox"
                    ],
                    true, false
                );
            }
        }

        if ($savedraft) {
            // remember new draft-uid ($saved could be an UID or true/false here)
            if ($saved && is_bool($saved)) {
                $index = $rcmail->storage->search_once($drafts_mbox, 'HEADER Message-ID ' . $message_id);
                $saved = $index->max();
            }

            if ($saved) {
                $plugin = $rcmail->plugins->exec_hook('message_draftsaved', [
                        'msgid'  => $message_id,
                        'uid'    => $saved,
                        'folder' => $store_target
                ]);

                // display success
                $rcmail->output->show_message(!empty($plugin['message']) ? $plugin['message'] : 'messagesaved', 'confirmation');

                // update "_draft_saveid" and the "cmp_hash" to prevent "Unsaved changes" warning
                $COMPOSE['param']['draft_uid'] = $plugin['uid'];
                $rcmail->output->command('set_draft_id', $plugin['uid']);
                $rcmail->output->command('compose_field_hash', true);
            }

            // start the auto-save timer again
            $rcmail->output->command('auto_save_start');
        }
        else {
            // Collect folders which could contain the composed message,
            // we'll refresh the list if currently opened folder is one of them (#1490238)
            $folders    = [];
            $save_error = false;

            if (!$saveonly) {
                if (in_array($COMPOSE['mode'], ['reply', 'forward', 'draft'])) {
                    $folders[] = $COMPOSE['mailbox'];
                }
                if (!empty($COMPOSE['param']['draft_uid']) && $drafts_mbox) {
                    $folders[] = $drafts_mbox;
                }
            }

            if ($store_folder && !$saved) {
                $params = $saveonly ? null : ['prefix' => true];
                self::display_server_error('errorsavingsent', null, null, $params);

                if ($saveonly) {
                    $rcmail->output->send('iframe');
                }

                $save_error = true;
            }
            else {
                $rcmail->plugins->exec_hook('attachments_cleanup', ['group' => $COMPOSE_ID]);
                $rcmail->session->remove('compose_data_' . $COMPOSE_ID);
                $_SESSION['last_compose_session'] = $COMPOSE_ID;

                $rcmail->output->command('remove_compose_data', $COMPOSE_ID);

                if ($store_folder) {
                    $folders[] = $store_target;
                }
            }

            $msg = $rcmail->gettext($saveonly ? 'successfullysaved' : 'messagesent');

            $rcmail->output->command('sent_successfully', 'confirmation', $msg, $folders, $save_error);
        }

        $rcmail->output->send('iframe');
    }

    public static function add_attachments($SENDMAIL, $message, $attachments, $isHtml)
    {
        $rcmail = rcmail::get_instance();

        foreach ($attachments as $id => $attachment) {
            // This hook retrieves the attachment contents from the file storage backend
            $attachment = $rcmail->plugins->exec_hook('attachment_get', $attachment);
            $is_inline  = false;
            $dispurl    = null;

            if ($isHtml) {
                $dispurl      = '/[\'"]\S+display-attachment\S+file=rcmfile' . preg_quote($attachment['id']) . '[\'"]/';
                $message_body = $message->getHTMLBody();
                $is_inline    = preg_match($dispurl, $message_body);
            }

            $ctype = isset($attachment['mimetype']) ? $attachment['mimetype'] : '';
            $ctype = str_replace('image/pjpeg', 'image/jpeg', $ctype); // #1484914

            // inline image
            if ($is_inline) {
                // Mail_Mime does not support many inline attachments with the same name (#1489406)
                // we'll generate cid: urls here to workaround this
                $cid = preg_replace('/[^0-9a-zA-Z]/', '', uniqid(time(), true));
                if (preg_match('#(@[0-9a-zA-Z\-\.]+)#', $SENDMAIL->options['from'], $matches)) {
                    $cid .= $matches[1];
                }
                else {
                    $cid .= '@localhost';
                }

                if ($dispurl && !empty($message_body)) {
                    $message_body = preg_replace($dispurl, '"cid:' . $cid . '"', $message_body);

                    rcube_utils::preg_error([
                            'line'    => __LINE__,
                            'file'    => __FILE__,
                            'message' => "Could not replace an image reference!"
                        ], true
                    );

                    $message->setHTMLBody($message_body);
                }

                if (!empty($attachment['data'])) {
                    $message->addHTMLImage($attachment['data'], $ctype, $attachment['name'], false, $cid);
                }
                else {
                    $message->addHTMLImage($attachment['path'], $ctype, $attachment['name'], true, $cid);
                }
            }
            else {
                $file    = !empty($attachment['data']) ? $attachment['data'] : $attachment['path'];
                $folding = (int) $rcmail->config->get('mime_param_folding');

                $message->addAttachment($file,
                    $ctype,
                    $attachment['name'],
                    empty($attachment['data']),
                    $ctype == 'message/rfc822' ? '8bit' : 'base64',
                    'attachment',
                    $attachment['charset'] ?? null,
                    '', '',
                    $folding ? 'quoted-printable' : null,
                    $folding == 2 ? 'quoted-printable' : null,
                    '', RCUBE_CHARSET
                );
            }
        }
    }
}
