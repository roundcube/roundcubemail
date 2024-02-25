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
 |   Attachment uploads handler for the compose form                     |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_attachment_upload extends rcmail_action_mail_index
{
    // only process ajax requests
    protected static $mode = self::MODE_AJAX;

    protected static $SESSION_KEY;
    protected static $COMPOSE;
    protected static $COMPOSE_ID;
    protected static $file_id;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        self::init();

        // clear all stored output properties (like scripts and env vars)
        $rcmail->output->reset();

        $uploadid = rcube_utils::get_input_string('_uploadid', rcube_utils::INPUT_GPC);
        $uri      = rcube_utils::get_input_string('_uri', rcube_utils::INPUT_POST);

        // handle dropping a reference to an attachment part of some message
        if ($uri) {
            $attachment = null;

            $url = parse_url($uri);

            if (!empty($url['query'])) {
                parse_str($url['query'], $params);
            }

            if (
                !empty($params) && isset($params['_mbox']) && strlen($params['_mbox'])
                && !empty($params['_uid']) && !empty($params['_part'])
            ) {
                // @TODO: at some point we might support drag-n-drop between
                // two different accounts on the same server, for now make sure
                // this is the same server and the same user
                list($host, $port) = rcube_utils::explode(':', $_SERVER['HTTP_HOST']);

                if (
                    $host == $url['host']
                    && $port == ($url['port'] ?? null)
                    && $rcmail->get_user_name() == rawurldecode($url['user'])
                ) {
                    $message = new rcube_message($params['_uid'], $params['_mbox']);

                    if ($message && !empty($message->headers)) {
                        $attachment = rcmail_action_mail_compose::save_attachment($message, $params['_part'], self::$COMPOSE_ID);
                    }
                }
            }

            $plugin = $rcmail->plugins->exec_hook('attachment_from_uri', [
                    'attachment' => $attachment,
                    'uri'        => $uri,
                    'compose_id' => self::$COMPOSE_ID
            ]);

            if ($plugin['attachment']) {
                self::attachment_success($plugin['attachment'], $uploadid);
            }
            else {
                $rcmail->output->command('display_message', $rcmail->gettext('filelinkerror'), 'error');
                $rcmail->output->command('remove_from_attachment_list', $uploadid);
            }

            $rcmail->output->send();
        }

        // handle file(s) upload
        if (is_array($_FILES['_attachments']['tmp_name'])) {
            $multiple = count($_FILES['_attachments']['tmp_name']) > 1;
            $errors   = [];

            foreach ($_FILES['_attachments']['tmp_name'] as $i => $filepath) {
                // Process uploaded attachment if there is no error
                $err = $_FILES['_attachments']['error'][$i];

                if (!$err) {
                    $filename = $_FILES['_attachments']['name'][$i];
                    $filesize = $_FILES['_attachments']['size'][$i];
                    $filetype = rcube_mime::file_content_type($filepath, $filename, $_FILES['_attachments']['type'][$i]);

                    if ($err = self::check_message_size($filesize, $filetype)) {
                        if (!in_array($err, $errors)) {
                            $rcmail->output->command('display_message', $err, 'error');
                            $rcmail->output->command('remove_from_attachment_list', $uploadid);
                            $errors[] = $err;
                        }

                        continue;
                    }

                    $attachment = $rcmail->plugins->exec_hook('attachment_upload', [
                            'path'     => $filepath,
                            'name'     => $filename,
                            'size'     => $filesize,
                            'mimetype' => $filetype,
                            'group'    => self::$COMPOSE_ID,
                    ]);
                }

                if (!$err && !empty($attachment['status']) && empty($attachment['abort'])) {
                    // store new attachment in session
                    unset($attachment['status'], $attachment['abort']);
                    $rcmail->session->append(self::$SESSION_KEY . '.attachments', $attachment['id'], $attachment);

                    self::attachment_success($attachment, $uploadid);
                }
                else {  // upload failed
                    if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE) {
                        $size = self::show_bytes(rcube_utils::max_upload_size());
                        $msg  = $rcmail->gettext(['name' => 'filesizeerror', 'vars' => ['size' => $size]]);
                    }
                    else if (!empty($attachment['error'])) {
                        $msg = $attachment['error'];
                    }
                    else {
                        $msg = $rcmail->gettext('fileuploaderror');
                    }

                    if (!empty($attachment['error']) || $err != UPLOAD_ERR_NO_FILE) {
                        if (!in_array($msg, $errors)) {
                            $rcmail->output->command('display_message', $msg, 'error');
                            $rcmail->output->command('remove_from_attachment_list', $uploadid);
                            $errors[] = $msg;
                        }
                    }
                }
            }
        }
        else if (self::upload_failure()) {
            $rcmail->output->command('remove_from_attachment_list', $uploadid);
        }

        // send html page with JS calls as response
        $rcmail->output->command('auto_save_start', false);
        $rcmail->output->send('iframe');
    }

    public static function init()
    {
        self::$COMPOSE_ID  = rcube_utils::get_input_string('_id', rcube_utils::INPUT_GPC);
        self::$COMPOSE     = null;
        self::$SESSION_KEY = 'compose_data_' . self::$COMPOSE_ID;

        if (self::$COMPOSE_ID && !empty($_SESSION[self::$SESSION_KEY])) {
            self::$COMPOSE =& $_SESSION[self::$SESSION_KEY];
        }

        if (!self::$COMPOSE) {
            die("Invalid session var!");
        }

        self::$file_id = rcube_utils::get_input_string('_file', rcube_utils::INPUT_GPC);
        self::$file_id = preg_replace('/^rcmfile/', '', self::$file_id) ?: 'unknown';
    }

    public static function get_attachment()
    {
        return self::$COMPOSE['attachments'][self::$file_id] ?? null;
    }

    public static function attachment_success($attachment, $uploadid)
    {
        $rcmail = rcmail::get_instance();
        $id     = $attachment['id'];

        if (!empty(self::$COMPOSE['deleteicon']) && is_file(self::$COMPOSE['deleteicon'])) {
            $button = html::img([
                    'src' => self::$COMPOSE['deleteicon'],
                    'alt' => $rcmail->gettext('delete')
            ]);
        }
        else if (!empty(self::$COMPOSE['textbuttons'])) {
            $button = rcube::Q($rcmail->gettext('delete'));
        }
        else {
            $button = '';
        }

        $link_content = sprintf(
            '<span class="attachment-name">%s</span><span class="attachment-size">(%s)</span>',
            rcube::Q($attachment['name']), self::show_bytes($attachment['size'])
        );

        $content_link = html::a([
                'href'    => "#load",
                'class'   => 'filename',
                'onclick' => sprintf(
                    "return %s.command('load-attachment','rcmfile%s', this, event)",
                    rcmail_output::JS_OBJECT_NAME,
                    $id
                ),
            ], $link_content);

        $delete_link = html::a([
                'href'    => "#delete",
                'onclick' => sprintf(
                    "return %s.command('remove-attachment','rcmfile%s', this, event)",
                    rcmail_output::JS_OBJECT_NAME,
                    $id
                ),
                'title'   => $rcmail->gettext('delete'),
                'class'   => 'delete',
                'aria-label' => $rcmail->gettext('delete') . ' ' . $attachment['name'],
            ], $button);

        if (!empty(self::$COMPOSE['icon_pos']) && self::$COMPOSE['icon_pos'] == 'left') {
            $content = $delete_link . $content_link;
        }
        else {
            $content = $content_link . $delete_link;
        }

        $rcmail->output->command('add2attachment_list', "rcmfile$id", [
                'html'      => $content,
                'name'      => $attachment['name'],
                'mimetype'  => $attachment['mimetype'],
                'classname' => rcube_utils::file2class($attachment['mimetype'], $attachment['name']),
                'complete'  => true
            ],
            $uploadid
        );
    }

    /**
     * Checks if the attached file will fit in message size limit.
     * Calculates size of all attachments and compares with the limit.
     *
     * @param int    $filesize File size
     * @param string $filetype File mimetype
     *
     * @return string Error message if the limit is exceeded
     */
    public static function check_message_size($filesize, $filetype)
    {
        $rcmail = rcmail::get_instance();
        $limit  = parse_bytes($rcmail->config->get('max_message_size'));
        $size   = 10 * 1024; // size of message body

        if (!$limit) {
            return;
        }

        // add size of already attached files
        if (!empty(self::$COMPOSE['attachments'])) {
            foreach ((array) self::$COMPOSE['attachments'] as $att) {
                // All attachments are base64-encoded except message/rfc822 (see sendmail.inc)
                $multip = $att['mimetype'] == 'message/rfc822' ? 1 : 1.33;
                $size  += $att['size'] * $multip;
            }
        }

        // add size of the new attachment
        $multip = $filetype == 'message/rfc822' ? 1 : 1.33;
        $size  += $filesize * $multip;

        if ($size > $limit) {
            $limit = self::show_bytes($limit);
            return $rcmail->gettext(['name' => 'msgsizeerror', 'vars' => ['size' => $limit]]);
        }
    }
}
