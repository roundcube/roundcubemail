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
 |   Display a mail message similar as a usual mail application does     |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_show extends rcmail_action_mail_index
{
    protected static $MESSAGE;
    protected static $CLIENT_MIMETYPES = [];

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        self::$PRINT_MODE = $rcmail->action == 'print';

        // Read browser capabilities and store them in session
        if ($caps = rcube_utils::get_input_string('_caps', rcube_utils::INPUT_GET)) {
            $browser_caps = [];
            foreach (explode(',', $caps) as $cap) {
                $cap = explode('=', $cap);
                $browser_caps[$cap[0]] = $cap[1];
            }

            $_SESSION['browser_caps'] = $browser_caps;
        }

        $msg_id    = rcube_utils::get_input_string('_uid', rcube_utils::INPUT_GET);
        $uid       = preg_replace('/\.[0-9.]+$/', '', $msg_id);
        $mbox_name = $rcmail->storage->get_folder();

        // similar code as in program/steps/mail/get.inc
        if ($uid) {
            // set message format (need to be done before rcube_message construction)
            if (!empty($_GET['_format'])) {
                $prefer_html = $_GET['_format'] == 'html';
                $rcmail->config->set('prefer_html', $prefer_html);
                $_SESSION['msg_formats'][$mbox_name.':'.$uid] = $prefer_html;
            }
            else if (isset($_SESSION['msg_formats'][$mbox_name.':'.$uid])) {
                $rcmail->config->set('prefer_html', $_SESSION['msg_formats'][$mbox_name.':'.$uid]);
            }

            $MESSAGE = new rcube_message($msg_id, $mbox_name, !empty($_GET['_safe']));

            self::$MESSAGE = $MESSAGE;

            // if message not found (wrong UID)...
            if (empty($MESSAGE->headers)) {
                self::message_error();
            }

            self::$CLIENT_MIMETYPES = self::supported_mimetypes();

            // show images?
            self::check_safe($MESSAGE);

            // set message charset as default
            if (!empty($MESSAGE->headers->charset)) {
                $rcmail->storage->set_charset($MESSAGE->headers->charset);
            }

            if (!isset($_SESSION['writeable_abook'])) {
                $_SESSION['writeable_abook'] = $rcmail->get_address_sources(true) ? true : false;
            }

            $rcmail->output->set_pagetitle(abbreviate_string($MESSAGE->subject, 128, '...', true));

            // set environment
            $rcmail->output->set_env('uid', $msg_id);
            $rcmail->output->set_env('safemode', $MESSAGE->is_safe);
            $rcmail->output->set_env('message_context', $MESSAGE->context);
            $rcmail->output->set_env('message_flags', array_keys(array_change_key_case((array) $MESSAGE->headers->flags)));
            $rcmail->output->set_env('sender', !empty($MESSAGE->sender) ? $MESSAGE->sender['string'] : '');
            $rcmail->output->set_env('mailbox', $mbox_name);
            $rcmail->output->set_env('username', $rcmail->get_user_name());
            $rcmail->output->set_env('permaurl', $rcmail->url(['_action' => 'show', '_uid' => $msg_id, '_mbox' => $mbox_name]));
            $rcmail->output->set_env('has_writeable_addressbook', $_SESSION['writeable_abook']);
            $rcmail->output->set_env('delimiter', $rcmail->storage->get_hierarchy_delimiter());
            $rcmail->output->set_env('mimetypes', self::$CLIENT_MIMETYPES);

            if ($MESSAGE->headers->get('list-post', false)) {
                $rcmail->output->set_env('list_post', true);
            }

            // set configuration
            self::set_env_config(['delete_junk', 'flag_for_deletion', 'read_when_deleted',
                'skip_deleted', 'display_next', 'forward_attachment', 'mailvelope_main_keyring']);

            // set special folders
            foreach (['drafts', 'trash', 'junk'] as $mbox) {
                if ($folder = $rcmail->config->get($mbox . '_mbox')) {
                    $rcmail->output->set_env($mbox . '_mailbox', $folder);
                }
            }

            if ($MESSAGE->has_html_part()) {
                $prefer_html = $rcmail->config->get('prefer_html');
                $rcmail->output->set_env('optional_format', $prefer_html ? 'text' : 'html');
            }

            $rcmail->output->add_label('checkingmail', 'deletemessage', 'movemessagetotrash',
                'movingmessage', 'deletingmessage', 'markingmessage', 'replyall', 'replylist',
                'bounce', 'bouncemsg', 'sendingmessage');

            // check for unset disposition notification
            self::mdn_request_handler($MESSAGE);

            if (empty($MESSAGE->headers->flags['SEEN']) && $MESSAGE->context === null) {
                $v = intval($rcmail->config->get('mail_read_time'));
                if ($v > 0) {
                    $rcmail->output->set_env('mail_read_time', $v);
                }
                else if ($v == 0) {
                    $rcmail->output->command('set_unread_message', $MESSAGE->uid, $mbox_name);
                    $rcmail->plugins->exec_hook('message_read', [
                            'uid'     => $MESSAGE->uid,
                            'mailbox' => $mbox_name,
                            'message' => $MESSAGE,
                    ]);

                    $set_seen_flag = true;
                }
            }
        }

        $rcmail->output->add_handlers([
                'mailboxname'        => [$this, 'mailbox_name_display'],
                'messageattachments' => [$this, 'message_attachments'],
                'messageobjects'     => [$this, 'message_objects'],
                'messagesummary'     => [$this, 'message_summary'],
                'messageheaders'     => [$this, 'message_headers'],
                'messagefullheaders' => [$this, 'message_full_headers'],
                'messagebody'        => [$this, 'message_body'],
                'contactphoto'       => [$this, 'message_contactphoto'],
        ]);

        if ($rcmail->action == 'print' && $rcmail->output->template_exists('messageprint')) {
            $rcmail->output->send('messageprint', false);
        }
        else if ($rcmail->action == 'preview' && $rcmail->output->template_exists('messagepreview')) {
            $rcmail->output->send('messagepreview', false);
        }
        else {
            $rcmail->output->send('message', false);
        }

        // mark message as read
        if (!empty($set_seen_flag)) {
            if ($rcmail->storage->set_flag(self::$MESSAGE->uid, 'SEEN', $mbox_name)) {
                if ($count = self::get_unseen_count($mbox_name)) {
                    self::set_unseen_count($mbox_name, $count - 1);
                }
            }
        }

        exit;
    }

    /**
     * Handler for the template object 'messageattachments'.
     *
     * @param array $attrib Named parameters
     *
     * @return string HTML content showing the message attachments list
     */
    public static function message_attachments($attrib)
    {
        if (empty(self::$MESSAGE->attachments)) {
            return '';
        }

        $rcmail = rcmail::get_instance();
        $out    =
        $ol     = '';
        $attachments = [];

        foreach (self::$MESSAGE->attachments as $attach_prop) {
            $filename = self::attachment_name($attach_prop, true);
            $filesize = self::message_part_size($attach_prop);
            $mimetype = rcube_mime::fix_mimetype($attach_prop->mimetype);
            $class    = rcube_utils::file2class($mimetype, $filename);
            $id       = 'attach' . $attach_prop->mime_id;

            if ($mimetype == 'application/octet-stream' && ($type = rcube_mime::file_ext_type($filename))) {
                $mimetype = $type;
            }

            // Skip inline images
            if (strpos($mimetype, 'image/') === 0 && !self::is_attachment(self::$MESSAGE, $attach_prop)) {
                continue;
            }

            if (!empty($attrib['maxlength']) && mb_strlen($filename) > $attrib['maxlength']) {
                $title    = $filename;
                $filename = abbreviate_string($filename, $attrib['maxlength']);
            }
            else {
                $title = '';
            }

            $item = html::span('attachment-name', rcube::Q($filename))
                . html::span('attachment-size', '(' . rcube::Q($filesize) . ')');

            $li_class = $class;

            if (!self::$PRINT_MODE) {
                $link_attrs = [
                    'href'        => self::$MESSAGE->get_part_url($attach_prop->mime_id, false),
                    'onclick'     => sprintf('%s.command(\'load-attachment\',\'%s\',this); return false',
                        rcmail_output::JS_OBJECT_NAME, $attach_prop->mime_id),
                    'onmouseover' => $title ? '' : 'rcube_webmail.long_subject_title_ex(this, 0)',
                    'title'       => $title,
                    'class'       => 'filename',
                ];

                if ($mimetype != 'message/rfc822' && empty($attach_prop->size)) {
                    $li_class .= ' no-menu';
                    $link_attrs['onclick'] = sprintf('%s.alert_dialog(%s.get_label(\'emptyattachment\')); return false',
                            rcmail_output::JS_OBJECT_NAME, rcmail_output::JS_OBJECT_NAME);
                    $rcmail->output->add_label('emptyattachment');
                }

                $item = html::a($link_attrs, $item);
                $attachments[$attach_prop->mime_id] = $mimetype;
            }

            $ol .= html::tag('li', ['class' => $li_class, 'id' => $id], $item);
        }

        $out = html::tag('ul', $attrib, $ol, html::$common_attrib);

        $rcmail->output->set_env('attachments', $attachments);
        $rcmail->output->add_gui_object('attachments', $attrib['id']);

        return $out;
    }

    public static function remote_objects_msg()
    {
        $rcmail = rcmail::get_instance();

        $attrib['id']    = 'remote-objects-message';
        $attrib['class'] = 'notice';
        $attrib['style'] = 'display: none';

        $msg = html::span(null, rcube::Q($rcmail->gettext('blockedresources')));

        $buttons = html::a([
                'href'    => "#loadremote",
                'onclick' => rcmail_output::JS_OBJECT_NAME . ".command('load-remote')"
            ],
            rcube::Q($rcmail->gettext('allow'))
        );

        // add link to save sender in addressbook and reload message
        $show_images = $rcmail->config->get('show_images');
        if (!empty(self::$MESSAGE->sender['mailto']) && ($show_images == 1 || $show_images == 3)) {
            $arg = $show_images == 3 ? rcube_addressbook::TYPE_TRUSTED_SENDER : 'true';
            $buttons .= ' ' . html::a([
                    'href'    => "#loadremotealways",
                    'onclick' => rcmail_output::JS_OBJECT_NAME . ".command('load-remote', $arg)",
                    'style'   => "white-space:nowrap"
                ],
                rcube::Q($rcmail->gettext(['name' => 'alwaysallow', 'vars' => ['sender' => self::$MESSAGE->sender['mailto']]]))
            );
        }

        $rcmail->output->add_gui_object('remoteobjectsmsg', $attrib['id']);

        return html::div($attrib, $msg . '&nbsp;' . html::span('boxbuttons', $buttons));
    }

    /**
     * Display a warning whenever a suspicious email address has been found in the message.
     *
     * @return string HTML content of the warning element
     */
    public static function suspicious_content_warning()
    {
        if (empty(self::$SUSPICIOUS_EMAIL)) {
            return '';
        }

        $rcmail = rcmail::get_instance();

        $attrib = [
            'id'    => 'suspicious-content-message',
            'class' => 'notice',
        ];

        $msg = html::span(null, rcube::Q($rcmail->gettext('suspiciousemail')));

        return html::div($attrib, $msg);
    }

    public static function message_buttons()
    {
        $rcmail = rcmail::get_instance();
        $delim  = $rcmail->storage->get_hierarchy_delimiter();
        $dbox   = $rcmail->config->get('drafts_mbox');

        // the message is not a draft
        if (!empty(self::$MESSAGE->context)
            || (
                !empty(self::$MESSAGE->folder)
                && (self::$MESSAGE->folder != $dbox && strpos(self::$MESSAGE->folder, $dbox.$delim) !== 0)
            )
        ) {
            return '';
        }

        $attrib['id']    = 'message-buttons';
        $attrib['class'] = 'information notice';

        $msg = html::span(null, rcube::Q($rcmail->gettext('isdraft')))
            . '&nbsp;'
            . html::a([
                    'href'    => "#edit",
                    'onclick' => rcmail_output::JS_OBJECT_NAME.".command('edit')"
                ],
                rcube::Q($rcmail->gettext('edit'))
            );

        return html::div($attrib, $msg);
    }

    /**
     * Handler for the template object 'messageobjects' that contains
     * warning/info boxes, buttons, etc. related to the displayed message.
     *
     * @param array $attrib Named parameters
     *
     * @return string HTML content showing the message objects
     */
    public static function message_objects($attrib)
    {
        if (empty($attrib['id'])) {
            $attrib['id'] = 'message-objects';
        }

        $rcmail  = rcmail::get_instance();
        $content = [
            self::message_buttons(),
            self::remote_objects_msg(),
            self::suspicious_content_warning(),
        ];

        $plugin = $rcmail->plugins->exec_hook('message_objects',
            ['content' => $content, 'message' => self::$MESSAGE]);

        $content = implode("\n", $plugin['content']);

        return html::div($attrib, $content);
    }

    /**
     * Handler for the template object 'contactphoto'.
     *
     * @param array $attrib Named parameters
     *
     * @return string HTML content for the IMG tag
     */
    public static function message_contactphoto($attrib)
    {
        $rcmail        = rcmail::get_instance();
        $error_handler = false;
        $placeholder   = 'data:image/gif;base64,' . rcmail_output::BLANK_GIF;

        if (!empty($attrib['placeholder'])) {
            $placeholder = $rcmail->output->abs_url($attrib['placeholder'], true);
            $placeholder = $rcmail->output->asset_url($placeholder);

            // set error handler on <img>
            $error_handler     = true;
            $attrib['onerror'] = "this.onerror = null; this.src = '$placeholder';";
        }

        if (!empty(self::$MESSAGE->sender)) {
            $photo_img = $rcmail->url([
                    '_task'   => 'addressbook',
                    '_action' => 'photo',
                    '_email'  => self::$MESSAGE->sender['mailto'],
                    '_error'  => $error_handler ? 1 : null,
                    '_bgcolor' => $attrib['bg-color'] ?? null
            ]);
        }
        else {
            $photo_img = $placeholder;
        }

        return html::img(['src' => $photo_img, 'alt' => $rcmail->gettext('contactphoto')] + $attrib);
    }

    /**
     * Returns table with message headers
     */
    public static function message_headers($attrib, $headers = null)
    {
        static $sa_attrib;

        // keep header table attrib
        if (is_array($attrib) && !$sa_attrib && empty($attrib['valueof'])) {
            $sa_attrib = $attrib;
        }
        else if (!is_array($attrib) && is_array($sa_attrib)) {
            $attrib = $sa_attrib;
        }

        if (!isset(self::$MESSAGE)) {
            return false;
        }

        $rcmail = rcmail::get_instance();

        // get associative array of headers object
        if (!$headers) {
            $headers_obj = self::$MESSAGE->headers;
            $headers     = get_object_vars(self::$MESSAGE->headers);
        }
        else if (is_object($headers)) {
            $headers_obj = $headers;
            $headers     = get_object_vars($headers_obj);
        }
        else {
            $headers_obj = rcube_message_header::from_array($headers);
        }

        // show these headers
        $standard_headers = ['subject', 'from', 'sender', 'to', 'cc', 'bcc', 'replyto',
            'mail-reply-to', 'mail-followup-to', 'date', 'priority'];
        $exclude_headers = !empty($attrib['exclude']) ? explode(',', $attrib['exclude']) : [];
        $output_headers  = [];

        $attr_max     = $attrib['max'] ?? null;
        $attr_addicon = $attrib['addicon'] ?? null;
        $charset      = !empty($headers['charset']) ? $headers['charset'] : null;

        foreach ($standard_headers as $hkey) {
            $value = null;
            if (!empty($headers[$hkey])) {
                $value = $headers[$hkey];
            }
            else if (!empty($headers['others'][$hkey])) {
                $value = $headers['others'][$hkey];
            }
            else if (empty($attrib['valueof'])) {
                continue;
            }

            if (in_array($hkey, $exclude_headers)) {
                continue;
            }

            $ishtml       = false;
            $header_title = $rcmail->gettext(preg_replace('/(^mail-|-)/', '', $hkey));
            $header_value = null;

            if ($hkey == 'date') {
                $header_value = $rcmail->format_date($value,
                    self::$PRINT_MODE ? $rcmail->config->get('date_long', 'x') : null);
            }
            else if ($hkey == 'priority') {
                $header_value = html::span('prio' . $value, rcube::Q(self::localized_priority($value)));
                $ishtml       = true;
            }
            else if ($hkey == 'replyto') {
                if ($value != $headers['from']) {
                    $header_value = self::address_string($value, $attr_max, true, $attr_addicon, $charset, $header_title);
                    $ishtml = true;
                }
            }
            else if ($hkey == 'mail-reply-to') {
                if ((!isset($headers['replyto']) || $value != $headers['replyto']) && $value != $headers['from']) {
                    $header_value = self::address_string($value, $attr_max, true, $attr_addicon, $charset, $header_title);
                    $ishtml = true;
                }
            }
            else if ($hkey == 'sender') {
                if ($value != $headers['from']) {
                    $header_value = self::address_string($value, $attr_max, true, $attr_addicon, $charset, $header_title);
                    $ishtml = true;
                }
            }
            else if ($hkey == 'mail-followup-to') {
                $header_value = self::address_string($value, $attr_max, true, $attr_addicon, $charset, $header_title);
                $ishtml = true;
            }
            else if (in_array($hkey, ['from', 'to', 'cc', 'bcc'])) {
                $header_value = self::address_string($value, $attr_max, true, $attr_addicon, $charset, $header_title);
                $ishtml = true;
            }
            else if ($hkey == 'subject' && empty($value)) {
                $header_value = $rcmail->gettext('nosubject');
            }
            else {
                $value        = is_array($value) ? implode(' ', $value) : $value;
                $header_value = trim(rcube_mime::decode_header($value, $charset));
            }

            if (empty($header_value)) {
                continue;
            }

            $output_headers[$hkey] = [
                'title' => $header_title,
                'value' => $header_value,
                'raw'   => $value,
                'html'  => $ishtml,
            ];
        }

        $plugin = $rcmail->plugins->exec_hook('message_headers_output', [
                'output'  => $output_headers,
                'headers' => $headers_obj,
                'exclude' => $exclude_headers,       // readonly
                'folder'  => self::$MESSAGE->folder, // readonly
                'uid'     => self::$MESSAGE->uid,    // readonly
        ]);

        // single header value is requested
        if (!empty($attrib['valueof'])) {
            if (empty($plugin['output'][$attrib['valueof']])) {
                return '';
            }

            $row = $plugin['output'][$attrib['valueof']];
            return !empty($row['html']) ? $row['value'] : rcube::SQ($row['value']);
        }

        // compose html table
        $table = new html_table(['cols' => 2]);

        foreach ($plugin['output'] as $hkey => $row) {
            $val = !empty($row['html']) ? $row['value'] : rcube::SQ($row['value']);

            $table->add(['class' => 'header-title'], rcube::SQ($row['title']));
            $table->add(['class' => 'header ' . $hkey], $val);
        }

        return $table->show($attrib);
    }

    /**
     * Returns element with "From|To <sender|recipient> on <date>"
     */
    public static function message_summary($attrib)
    {
        if (!isset(self::$MESSAGE) || empty(self::$MESSAGE->headers)) {
            return;
        }

        $rcmail = rcmail::get_instance();
        $header = self::$MESSAGE->context ? 'from' : self::message_list_smart_column_name();
        $label  = 'shortheader' . $header;
        $date   = $rcmail->format_date(self::$MESSAGE->headers->date, $rcmail->config->get('date_long', 'x'));
        $user   = self::$MESSAGE->headers->$header;

        if (!$user && $header == 'to' && !empty(self::$MESSAGE->headers->cc)) {
            $user = self::$MESSAGE->headers->cc;
        }
        if (!$user && $header == 'to' && !empty(self::$MESSAGE->headers->bcc)) {
            $user = self::$MESSAGE->headers->bcc;
        }

        $vars[$header] = self::address_string($user, 1, true, $attrib['addicon'], self::$MESSAGE->headers->charset);
        $vars['date']  = html::span('text-nowrap', $date);

        if (empty($user)) {
            $label = 'shortheaderdate';
        }

        $out = html::span(null, $rcmail->gettext(['name' => $label, 'vars' => $vars]));

        return html::div($attrib, $out);
    }

    /**
     * Convert Priority header value into a localized string
     */
    public static function localized_priority($value)
    {
        $labels_map = [
            '1' => 'highest',
            '2' => 'high',
            '3' => 'normal',
            '4' => 'low',
            '5' => 'lowest',
        ];

        if ($value && !empty($labels_map[$value])) {
            return rcmail::get_instance()->gettext($labels_map[$value]);
        }

        return '';
    }

    /**
     * Returns block to show full message headers
     */
    public static function message_full_headers($attrib)
    {
        $rcmail = rcmail::get_instance();

        $html = html::div(['id' => "all-headers", 'class' => "all", 'style' => 'display:none'],
            html::div(['id' => 'headers-source'], ''));

        $html .= html::div([
                'class'   => "more-headers show-headers",
                'onclick' => "return ".rcmail_output::JS_OBJECT_NAME.".command('show-headers','',this)",
                'title'   => $rcmail->gettext('togglefullheaders')
            ], '');

        $rcmail->output->add_gui_object('all_headers_row', 'all-headers');
        $rcmail->output->add_gui_object('all_headers_box', 'headers-source');

        return html::div($attrib, $html);
    }

    /**
     * Handler for the 'messagebody' GUI object
     *
     * @param array $attrib Named parameters
     *
     * @return string HTML content showing the message body
     */
    public static function message_body($attrib)
    {
        if (
            empty(self::$MESSAGE)
            || (!is_array(self::$MESSAGE->parts) && empty(self::$MESSAGE->body))
        ) {
            return '';
        }

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmailMsgBody';
        }

        $rcmail    = rcmail::get_instance();
        $safe_mode = self::$MESSAGE->is_safe || !empty($_GET['_safe']);
        $out       = '';
        $part_no   = 0;

        $header_attrib = [];
        foreach ($attrib as $attr => $value) {
            if (preg_match('/^headertable([a-z]+)$/i', $attr, $regs)) {
                $header_attrib[$regs[1]] = $value;
            }
        }

        if (!empty(self::$MESSAGE->parts)) {
            foreach (self::$MESSAGE->parts as $part) {
                if ($part->type == 'headers') {
                    $out .= html::div('message-partheaders', self::message_headers(count($header_attrib) ? $header_attrib : null, $part->headers));
                }
                else if ($part->type == 'content') {
                    // unsupported (e.g. encrypted)
                    if (!empty($part->realtype)) {
                        if ($part->realtype == 'multipart/encrypted' || $part->realtype == 'application/pkcs7-mime') {
                            if (
                                !empty($_SESSION['browser_caps']['pgpmime'])
                                && ($pgp_mime_part = self::$MESSAGE->get_multipart_encrypted_part())
                            ) {
                                $out .= html::span('part-notice', $rcmail->gettext('externalmessagedecryption'));
                                $rcmail->output->set_env('pgp_mime_part', $pgp_mime_part->mime_id);
                                $rcmail->output->set_env('pgp_mime_container', '#' . $attrib['id']);
                                $rcmail->output->add_label('loadingdata');
                            }

                            if (!self::$MESSAGE->encrypted_part) {
                                $out .= html::span('part-notice', $rcmail->gettext('encryptedmessage'));
                            }
                        }
                        continue;
                    }
                    else if (!$part->size) {
                        continue;
                    }
                    // Check if we have enough memory to handle the message in it
                    // #1487424: we need up to 10x more memory than the body
                    else if (!rcube_utils::mem_check($part->size * 10)) {
                        $out .= self::part_too_big_message(self::$MESSAGE, $part->mime_id);
                        continue;
                    }

                    // fetch part body
                    $body = self::$MESSAGE->get_part_body($part->mime_id, true);

                    // message is cached but not exists (#1485443), or other error
                    if ($body === false) {
                        // Don't bail out if it is only one-of-many part of the message (#6854)
                        if (strlen($out)) {
                            $out .= html::span('part-notice', $rcmail->gettext('messageopenerror'));
                            continue;
                        }

                        self::message_error();
                    }

                    $plugin = $rcmail->plugins->exec_hook('message_body_prefix',
                        ['part' => $part, 'prefix' => '', 'message' => self::$MESSAGE]);

                    // Set attributes of the part container
                    $container_class  = $part->ctype_secondary == 'html' ? 'message-htmlpart' : 'message-part';
                    $container_id     = $container_class . (++$part_no);
                    $container_attrib = ['class' => $container_class, 'id' => $container_id];

                    $body_args = [
                        'safe'         => $safe_mode,
                        'plain'        => !$rcmail->config->get('prefer_html'),
                        'css_prefix'   => 'v' . $part_no,
                        'body_class'   => 'rcmBody',
                        'container_id'     => $container_id,
                        'container_attrib' => $container_attrib,
                    ];

                    // Parse the part content for display
                    $body = self::print_body($body, $part, $body_args);

                    // check if the message body is PGP encrypted
                    if (strpos($body, '-----BEGIN PGP MESSAGE-----') !== false) {
                        $rcmail->output->set_env('is_pgp_content', '#' . $container_id);
                    }

                    if ($part->ctype_secondary == 'html') {
                        $body = self::html4inline($body, $body_args);
                    }

                    $out .= html::div($body_args['container_attrib'], $plugin['prefix'] . $body);
                }
            }
        }
        else {
            // Check if we have enough memory to handle the message in it
            // #1487424: we need up to 10x more memory than the body
            if (isset(self::$MESSAGE->body) && !rcube_utils::mem_check(strlen(self::$MESSAGE->body) * 10)) {
                $out .= self::part_too_big_message(self::$MESSAGE, 0);
            }
            else {
                $plugin = $rcmail->plugins->exec_hook('message_body_prefix',
                    ['part' => self::$MESSAGE, 'prefix' => '']);

                $out .= html::div('message-part',
                    $plugin['prefix'] . self::plain_body(self::$MESSAGE->body));
            }
        }

        // list images after mail body
        if ($rcmail->config->get('inline_images', true) && !empty(self::$MESSAGE->attachments)) {
            $thumbnail_size   = $rcmail->config->get('image_thumbnail_size', 240);
            $show_label       = rcube::Q($rcmail->gettext('showattachment'));
            $download_label   = rcube::Q($rcmail->gettext('download'));

            foreach (self::$MESSAGE->attachments as $attach_prop) {
                // Content-Type: image/*...
                if ($mimetype = self::part_image_type($attach_prop)) {
                    // Skip inline images
                    if (!self::is_attachment(self::$MESSAGE, $attach_prop)) {
                        continue;
                    }

                    // display thumbnails
                    if ($thumbnail_size) {
                        $supported = in_array($mimetype, self::$CLIENT_MIMETYPES);
                        $show_link_attr = [
                            'href'    => self::$MESSAGE->get_part_url($attach_prop->mime_id, false),
                            'onclick' => sprintf(
                                '%s.command(\'load-attachment\',\'%s\',this); return false',
                                rcmail_output::JS_OBJECT_NAME,
                                $attach_prop->mime_id
                            )
                        ];
                        $download_link_attr = [
                            'href'  => $show_link_attr['href'] . '&_download=1',
                        ];
                        $show_link     = html::a($show_link_attr + ['class' => 'open'], $show_label);
                        $download_link = html::a($download_link_attr + ['class' => 'download'], $download_label);

                        $out .= html::p(['class' => 'image-attachment', 'style' => $supported ? '' : 'display:none'],
                            html::a($show_link_attr + ['class' => 'image-link', 'style' => sprintf('width:%dpx', $thumbnail_size)],
                                html::img([
                                    'class' => 'image-thumbnail',
                                    'src'   => self::$MESSAGE->get_part_url($attach_prop->mime_id, 'image') . '&_thumb=1',
                                    'title' => $attach_prop->filename,
                                    'alt'   => $attach_prop->filename,
                                    'style' => sprintf('max-width:%dpx; max-height:%dpx', $thumbnail_size, $thumbnail_size),
                                    'onload' => $supported ? '' : '$(this).parents(\'p.image-attachment\').show()',
                                ])
                            ) .
                            html::span('image-filename', rcube::Q($attach_prop->filename)) .
                            html::span('image-filesize', rcube::Q(self::message_part_size($attach_prop))) .
                            html::span('attachment-links', ($supported ? $show_link . '&nbsp;' : '') . $download_link) .
                            html::br(['style' => 'clear:both'])
                        );
                    }
                    else {
                        $out .= html::tag('fieldset', 'image-attachment',
                            html::tag('legend', 'image-filename', rcube::Q($attach_prop->filename)) .
                            html::p(['align' => 'center'],
                                html::img([
                                    'src'   => self::$MESSAGE->get_part_url($attach_prop->mime_id, 'image'),
                                    'title' => $attach_prop->filename,
                                    'alt'   => $attach_prop->filename,
                                ])
                            )
                        );
                    }
                }
            }
        }

        // tell client that there are blocked remote objects
        if (self::$REMOTE_OBJECTS && !$safe_mode) {
            $rcmail->output->set_env('blockedobjects', true);
        }

        $rcmail->output->add_gui_object('messagebody', $attrib['id']);

        return html::div($attrib, $out);
    }

    /**
     * Returns a HTML notice element for too big message parts
     *
     * @param rcube_message $message Email message object
     * @param string        $part_id Message part identifier
     *
     * @return string HTML content
     */
    public static function part_too_big_message($message, $part_id)
    {
        $rcmail = rcmail::get_instance();
        $token  = $rcmail->get_request_token();
        $url    = $rcmail->url([
                'task'     => 'mail',
                'action'   => 'get',
                'download' => 1,
                'uid'      => $message->uid,
                'part'     => $part_id,
                'mbox'     => $message->folder,
                'token'    => $token,
        ]);

        return html::span('part-notice', $rcmail->gettext('messagetoobig')
            . '&nbsp;' . html::a($url, $rcmail->gettext('download')));
    }

    /**
     * Handle disposition notification requests
     *
     * @param rcube_message $message Email message object
     */
    public static function mdn_request_handler($message)
    {
        $rcmail = rcmail::get_instance();

        if ($message->headers->mdn_to
            && $message->context === null
            && !empty($message->sender['mailto'])
            && empty($message->headers->flags['MDNSENT'])
            && empty($message->headers->flags['SEEN'])
            && ($rcmail->storage->check_permflag('MDNSENT') || $rcmail->storage->check_permflag('*'))
            && $message->folder != $rcmail->config->get('drafts_mbox')
            && $message->folder != $rcmail->config->get('sent_mbox')
        ) {
            $mdn_cfg = intval($rcmail->config->get('mdn_requests'));
            $exists  = $mdn_cfg == 1;

            // Check sender existence in contacts
            // 3 and 4 = my contacts, 5 and 6 = trusted senders
            if ($mdn_cfg == 3 || $mdn_cfg == 4 || $mdn_cfg == 5 || $mdn_cfg == 6) {
                $type = rcube_addressbook::TYPE_TRUSTED_SENDER;

                if ($mdn_cfg == 3 || $mdn_cfg == 4) {
                    $type |= rcube_addressbook::TYPE_WRITEABLE | rcube_addressbook::TYPE_RECIPIENT;
                }

                if ($rcmail->contact_exists($message->sender['mailto'], $type)) {
                    $exists = 1;
                }
            }

            if ($exists) {
                // Send MDN
                if (rcmail_action_mail_sendmdn::send_mdn($message, $smtp_error)) {
                    $rcmail->output->show_message('receiptsent', 'confirmation');
                }
                else if ($smtp_error && is_string($smtp_error)) {
                    $rcmail->output->show_message($smtp_error, 'error');
                }
                else if ($smtp_error && !empty($smtp_error['label'])) {
                    $rcmail->output->show_message($smtp_error['label'], 'error', $smtp_error['vars']);
                }
                else {
                    $rcmail->output->show_message('errorsendingreceipt', 'error');
                }
            }
            else if ($mdn_cfg != 2 && $mdn_cfg != 4 && $mdn_cfg != 6) {
                // Ask the user
                $rcmail->output->add_label('sendreceipt', 'mdnrequest', 'send', 'sendalwaysto', 'ignore');
                $rcmail->output->set_env('mdn_request_save', $mdn_cfg == 3 || $mdn_cfg == 5 ? $mdn_cfg : 0);
                $rcmail->output->set_env('mdn_request_sender', $message->sender);
                $rcmail->output->set_env('mdn_request', true);
            }
        }
    }

    /**
     * Check whether the message part is a normal attachment
     *
     * @param rcube_message      $message Message object
     * @param rcube_message_part $part    Message part
     *
     * @return bool
     */
    protected static function is_attachment($message, $part)
    {
        // Inline attachment with Content-Id specified
        if (!empty($part->content_id) && $part->disposition == 'inline') {
            return false;
        }

        // Any image attached to multipart/related message (#7184)
        $parent_id = preg_replace('/\.[0-9]+$/', '', $part->mime_id);
        $parent = $message->mime_parts[$parent_id] ?? null;

        if ($parent && $parent->mimetype == 'multipart/related') {
            return false;
        }

        return true;
    }
}
