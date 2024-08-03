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
 |   Compose a new mail message with all headers and attachments         |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_compose extends rcmail_action_mail_index
{
    protected static $COMPOSE_ID;
    protected static $COMPOSE;
    protected static $MESSAGE;
    protected static $MESSAGE_BODY;
    protected static $CID_MAP   = [];
    protected static $HTML_MODE = false;
    protected static $SENDMAIL;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        self::$COMPOSE_ID = rcube_utils::get_input_string('_id', rcube_utils::INPUT_GET);
        self::$COMPOSE    = null;

        if (self::$COMPOSE_ID && !empty($_SESSION['compose_data_' . self::$COMPOSE_ID])) {
            self::$COMPOSE =& $_SESSION['compose_data_' . self::$COMPOSE_ID];
        }

        // give replicated session storage some time to synchronize
        $retries = 0;
        while (self::$COMPOSE_ID && !is_array(self::$COMPOSE) && $rcmail->db->is_replicated() && $retries++ < 5) {
            usleep(500000);
            $rcmail->session->reload();
            if ($_SESSION['compose_data_' . self::$COMPOSE_ID]) {
                self::$COMPOSE =& $_SESSION['compose_data_' . self::$COMPOSE_ID];
            }
        }

        // Nothing below is called during message composition, only at "new/forward/reply/draft" initialization or
        // if a compose-ID is given (i.e. when the compose step is opened in a new window/tab).
        if (!is_array(self::$COMPOSE)) {
            // Infinite redirect prevention in case of broken session (#1487028)
            if (self::$COMPOSE_ID) {
                // if we know the message with specified ID was already sent
                // we can ignore the error and compose a new message (#1490009)
                if (
                    !isset($_SESSION['last_compose_session'])
                    || self::$COMPOSE_ID != $_SESSION['last_compose_session']
                ) {
                    rcube::raise_error(['code' => 450], false, true);
                }
            }

            self::$COMPOSE_ID = uniqid(mt_rand());
            $params     = rcube_utils::request2param(rcube_utils::INPUT_GET, 'task|action', true);

            $_SESSION['compose_data_' . self::$COMPOSE_ID] = [
                'id'      => self::$COMPOSE_ID,
                'param'   => $params,
                'mailbox' => isset($params['mbox']) && strlen($params['mbox'])
                    ? $params['mbox'] : $rcmail->storage->get_folder(),
            ];

            self::$COMPOSE =& $_SESSION['compose_data_' . self::$COMPOSE_ID];
            self::process_compose_params(self::$COMPOSE);

            // check if folder for saving sent messages exists and is subscribed (#1486802)
            if (!empty(self::$COMPOSE['param']['sent_mbox'])) {
                $sent_folder = self::$COMPOSE['param']['sent_mbox'];
                rcmail_sendmail::check_sent_folder($sent_folder, true);
            }

            // redirect to a unique URL with all parameters stored in session
            $rcmail->output->redirect([
                    '_action' => 'compose',
                    '_id'     => self::$COMPOSE['id'],
                    '_search' => !empty($_REQUEST['_search']) ? $_REQUEST['_search'] : null,
            ]);
        }

        // add some labels to client
        $rcmail->output->add_label('notuploadedwarning', 'savingmessage', 'siginserted', 'responseinserted',
            'messagesaved', 'converting', 'editorwarning', 'discard',
            'fileuploaderror', 'sendmessage', 'newresponse', 'responsename', 'responsetext', 'save',
            'savingresponse', 'restoresavedcomposedata', 'restoremessage', 'delete', 'restore', 'ignore',
            'selectimportfile', 'messageissent', 'loadingdata', 'nopubkeyfor', 'nopubkeyforsender',
            'encryptnoattachments','encryptedsendialog','searchpubkeyservers', 'importpubkeys',
            'encryptpubkeysfound',  'search', 'close', 'import', 'keyid', 'keylength', 'keyexpired',
            'keyrevoked', 'keyimportsuccess', 'keyservererror', 'attaching', 'namex', 'attachmentrename'
        );

        $rcmail->output->set_pagetitle($rcmail->gettext('compose'));

        $rcmail->output->set_env('compose_id', self::$COMPOSE['id']);
        $rcmail->output->set_env('session_id', session_id());
        $rcmail->output->set_env('mailbox', $rcmail->storage->get_folder());
        $rcmail->output->set_env('top_posting', intval($rcmail->config->get('reply_mode')) > 0);
        $rcmail->output->set_env('sig_below', $rcmail->config->get('sig_below'));
        $rcmail->output->set_env('save_localstorage', (bool) $rcmail->config->get('compose_save_localstorage'));
        $rcmail->output->set_env('is_sent', false);
        $rcmail->output->set_env('mimetypes', self::supported_mimetypes());
        $rcmail->output->set_env('keyservers', $rcmail->config->keyservers());
        $rcmail->output->set_env('mailvelope_main_keyring', $rcmail->config->get('mailvelope_main_keyring'));

        $drafts_mbox     = $rcmail->config->get('drafts_mbox');
        $config_show_sig = $rcmail->config->get('show_sig', 1);

        // add config parameters to client script
        if (strlen($drafts_mbox)) {
            $rcmail->output->set_env('drafts_mailbox', $drafts_mbox);
            $rcmail->output->set_env('draft_autosave', $rcmail->config->get('draft_autosave'));
        }

        // default font for HTML editor
        $font = self::font_defs($rcmail->config->get('default_font'));
        if ($font && !is_array($font)) {
            $rcmail->output->set_env('default_font', $font);
        }

        // default font size for HTML editor
        if ($font_size = $rcmail->config->get('default_font_size')) {
            $rcmail->output->set_env('default_font_size', $font_size);
        }

        $compose_mode = null;
        $msg_uid      = null;
        $options      = [];

        // get reference message and set compose mode
        if (!empty(self::$COMPOSE['param']['draft_uid'])) {
            $msg_uid      = self::$COMPOSE['param']['draft_uid'];
            $compose_mode = rcmail_sendmail::MODE_DRAFT;
            $rcmail->output->set_env('draft_id', $msg_uid);
            $rcmail->storage->set_folder($drafts_mbox);
        }
        else if (!empty(self::$COMPOSE['param']['reply_uid'])) {
            $msg_uid      = self::$COMPOSE['param']['reply_uid'];
            $compose_mode = rcmail_sendmail::MODE_REPLY;
        }
        else if (!empty(self::$COMPOSE['param']['forward_uid'])) {
            $msg_uid      = self::$COMPOSE['param']['forward_uid'];
            $compose_mode = rcmail_sendmail::MODE_FORWARD;
            self::$COMPOSE['forward_uid']   = $msg_uid;
            self::$COMPOSE['as_attachment'] = !empty(self::$COMPOSE['param']['attachment']);
        }
        else if (!empty(self::$COMPOSE['param']['uid'])) {
            $msg_uid = self::$COMPOSE['param']['uid'];
            $compose_mode = rcmail_sendmail::MODE_EDIT;
        }

        self::$COMPOSE['mode'] = $compose_mode;

        if ($compose_mode) {
            $rcmail->output->set_env('compose_mode', $compose_mode);
        }

        if ($compose_mode == rcmail_sendmail::MODE_EDIT || $compose_mode == rcmail_sendmail::MODE_DRAFT) {
            // don't add signature in draft/edit mode, we'll also not remove the old-one
            // but only on page display, later we should be able to change identity/sig (#1489229)
            if ($config_show_sig == 1 || $config_show_sig == 2) {
                $rcmail->output->set_env('show_sig_later', true);
            }
        }
        else if ($config_show_sig == 1) {
            $rcmail->output->set_env('show_sig', true);
        }
        else if ($config_show_sig == 2 && empty($compose_mode)) {
            $rcmail->output->set_env('show_sig', true);
        }
        else if (
            $config_show_sig == 3
            && ($compose_mode == rcmail_sendmail::MODE_REPLY || $compose_mode == rcmail_sendmail::MODE_FORWARD)
        ) {
            $rcmail->output->set_env('show_sig', true);
        }

        if (!empty($msg_uid) && (empty(self::$COMPOSE['as_attachment']) || $compose_mode == rcmail_sendmail::MODE_DRAFT)) {
            $mbox_name = $rcmail->storage->get_folder();

            // set format before rcube_message construction
            // use the same format as for the message view
            if (isset($_SESSION['msg_formats'][$mbox_name . ':' . $msg_uid])) {
                $rcmail->config->set('prefer_html', $_SESSION['msg_formats'][$mbox_name . ':' . $msg_uid]);
            }
            else {
                $prefer_html = $rcmail->config->get('prefer_html')
                    || $rcmail->config->get('htmleditor')
                    || $compose_mode == rcmail_sendmail::MODE_DRAFT
                    || $compose_mode == rcmail_sendmail::MODE_EDIT;

                $rcmail->config->set('prefer_html', $prefer_html);
            }

            self::$MESSAGE = new rcube_message($msg_uid);

            // make sure message is marked as read
            if (
                !empty(self::$MESSAGE->headers)
                && self::$MESSAGE->context === null
                && empty(self::$MESSAGE->headers->flags['SEEN'])
            ) {
                $rcmail->storage->set_flag($msg_uid, 'SEEN');
            }

            if (!empty(self::$MESSAGE->headers->charset)) {
                $rcmail->storage->set_charset(self::$MESSAGE->headers->charset);
            }

            if (empty(self::$MESSAGE->headers)) {
                // error
            }
            else if ($compose_mode == rcmail_sendmail::MODE_FORWARD || $compose_mode == rcmail_sendmail::MODE_REPLY) {
                if ($compose_mode == rcmail_sendmail::MODE_REPLY) {
                    self::$COMPOSE['reply_uid'] = self::$MESSAGE->context === null ? $msg_uid : null;

                    if (!empty(self::$COMPOSE['param']['all'])) {
                        self::$COMPOSE['reply_all'] = self::$COMPOSE['param']['all'];
                    }
                }
                else {
                    self::$COMPOSE['forward_uid'] = $msg_uid;
                }

                self::$COMPOSE['reply_msgid'] = self::$MESSAGE->headers->messageID;
                self::$COMPOSE['references']  = trim(self::$MESSAGE->headers->references . " " . self::$MESSAGE->headers->messageID);

                // Save the sent message in the same folder of the message being replied to
                if (
                    $rcmail->config->get('reply_same_folder')
                    && ($sent_folder = self::$COMPOSE['mailbox'])
                    && rcmail_sendmail::check_sent_folder($sent_folder, false)
                ) {
                    self::$COMPOSE['param']['sent_mbox'] = $sent_folder;
                }
            }
            else if ($compose_mode == rcmail_sendmail::MODE_DRAFT || $compose_mode == rcmail_sendmail::MODE_EDIT) {
                if ($compose_mode == rcmail_sendmail::MODE_DRAFT) {
                    if ($draft_info = self::$MESSAGE->headers->get('x-draft-info')) {
                        // get reply_uid/forward_uid to flag the original message when sending
                        $info = rcmail_sendmail::draftinfo_decode($draft_info);

                        if (!empty($info['type'])) {
                            if ($info['type'] == 'reply') {
                                self::$COMPOSE['reply_uid'] = $info['uid'];
                            }
                            else if ($info['type'] == 'forward') {
                                self::$COMPOSE['forward_uid'] = $info['uid'];
                            }
                        }

                        if (!empty($info['dsn']) && $info['dsn'] === 'on') {
                            $options['dsn_enabled'] = true;
                        }

                        self::$COMPOSE['mailbox'] = $info['folder'] ?? null;

                        // Save the sent message in the same folder of the message being replied to
                        if (
                            $rcmail->config->get('reply_same_folder')
                            && ($sent_folder = self::$COMPOSE['mailbox'])
                            && rcmail_sendmail::check_sent_folder($sent_folder, false)
                        ) {
                            self::$COMPOSE['param']['sent_mbox'] = $sent_folder;
                        }
                    }

                    if (($msgid = self::$MESSAGE->headers->get('message-id')) && !preg_match('/^mid:[0-9]+$/', $msgid)) {
                        self::$COMPOSE['param']['message-id'] = $msgid;
                    }

                    // use message UID as draft_id
                    $rcmail->output->set_env('draft_id', $msg_uid);
                }

                if ($in_reply_to = self::$MESSAGE->headers->get('in-reply-to')) {
                    self::$COMPOSE['reply_msgid'] = '<' . $in_reply_to . '>';
                }

                self::$COMPOSE['references'] = self::$MESSAGE->headers->references;
            }
        }
        else {
            self::$MESSAGE = new stdClass();

            // apply mailto: URL parameters
            if (!empty(self::$COMPOSE['param']['in-reply-to'])) {
                self::$COMPOSE['reply_msgid'] = '<' . trim(self::$COMPOSE['param']['in-reply-to'], '<> ') . '>';
            }

            if (!empty(self::$COMPOSE['param']['references'])) {
                self::$COMPOSE['references'] = self::$COMPOSE['param']['references'];
            }
        }

        if (!empty(self::$COMPOSE['reply_msgid'])) {
            $rcmail->output->set_env('reply_msgid', self::$COMPOSE['reply_msgid']);
        }

        $options['message'] = self::$MESSAGE;

        // Initialize helper class to build the UI
        self::$SENDMAIL = new rcmail_sendmail(self::$COMPOSE, $options);

        // process self::$MESSAGE body/attachments, set self::$MESSAGE_BODY/$HTML_MODE vars and some session data
        self::$MESSAGE_BODY = self::prepare_message_body();

        // register UI objects (Note: some objects are registered by rcmail_sendmail above)
        $rcmail->output->add_handlers([
                'composebody'           => [$this, 'compose_body'],
                'composeobjects'        => [$this, 'compose_objects'],
                'composeattachmentlist' => [$this, 'compose_attachment_list'],
                'composeattachmentform' => [$this, 'compose_attachment_form'],
                'composeattachment'     => [$this, 'compose_attachment_field'],
                'filedroparea'          => [$this, 'compose_file_drop_area'],
                'editorselector'        => [$this, 'editor_selector'],
                'addressbooks'          => [$this, 'addressbook_list'],
                'addresslist'           => [$this, 'contacts_list'],
                'responseslist'         => [$this, 'compose_responses_list'],
        ]);

        $rcmail->output->include_script('publickey.js');

        self::spellchecker_init();

        $rcmail->output->send('compose');
    }

    // process compose request parameters
    public static function process_compose_params(&$COMPOSE)
    {
        if (!empty($COMPOSE['param']['to'])) {
            $mailto = explode('?', $COMPOSE['param']['to'], 2);

            // #1486037: remove "mailto:" prefix
            $COMPOSE['param']['to'] = preg_replace('/^mailto:/i', '', $mailto[0]);
            // #1490346: decode the recipient address
            // #1490510: use raw encoding for correct "+" character handling as specified in RFC6068
            $COMPOSE['param']['to'] = rawurldecode($COMPOSE['param']['to']);

            // Supported case-insensitive tokens in mailto URL
            $url_tokens = ['to', 'cc', 'bcc', 'reply-to', 'in-reply-to', 'references', 'subject', 'body'];

            if (!empty($mailto[1])) {
                parse_str($mailto[1], $query);
                foreach ($query as $f => $val) {
                    if (($key = array_search(strtolower($f), $url_tokens)) !== false) {
                        $f = $url_tokens[$key];
                    }

                    // merge mailto: addresses with addresses from 'to' parameter
                    if ($f == 'to' && !empty($COMPOSE['param']['to'])) {
                        $to_addresses  = rcube_mime::decode_address_list($COMPOSE['param']['to'], null, true, null, true);
                        $add_addresses = rcube_mime::decode_address_list($val, null, true);

                        foreach ($add_addresses as $addr) {
                            if (!in_array($addr['mailto'], $to_addresses)) {
                                $to_addresses[]         = $addr['mailto'];
                                $COMPOSE['param']['to'] = (!empty($to_addresses) ? ', ' : '') . $addr['string'];
                            }
                        }
                    }
                    else {
                        $COMPOSE['param'][$f] = $val;
                    }
                }
            }
        }

        // resolve _forward_uid=* to an absolute list of messages from a search result
        if (
            !empty($COMPOSE['param']['forward_uid'])
            && $COMPOSE['param']['forward_uid'] == '*'
            && !empty($_SESSION['search'][1])
            && is_object($_SESSION['search'][1])
        ) {
            $COMPOSE['param']['forward_uid'] = $_SESSION['search'][1]->get();
        }

        // clean HTML message body which can be submitted by URL
        if (!empty($COMPOSE['param']['body'])) {
            if ($COMPOSE['param']['html'] = strpos($COMPOSE['param']['body'], '<') !== false) {
                $wash_params              = ['safe' => false];
                $COMPOSE['param']['body'] = self::prepare_html_body($COMPOSE['param']['body'], $wash_params);
            }
        }

        $rcmail = rcmail::get_instance();

        // select folder where to save the sent message
        $COMPOSE['param']['sent_mbox'] = $rcmail->config->get('sent_mbox');

        // pipe compose parameters thru plugins
        $plugin = $rcmail->plugins->exec_hook('message_compose', $COMPOSE);

        $COMPOSE['param'] = array_merge($COMPOSE['param'], $plugin['param']);

        // add attachments listed by message_compose hook
        if (!empty($plugin['attachments'])) {
            foreach ($plugin['attachments'] as $attach) {
                // we have structured data
                if (is_array($attach)) {
                    $attachment = $attach + ['group' => self::$COMPOSE_ID];
                }
                // only a file path is given
                else {
                    $filename   = basename($attach);
                    $attachment = [
                        'group'    => self::$COMPOSE_ID,
                        'name'     => $filename,
                        'mimetype' => rcube_mime::file_content_type($attach, $filename),
                        'size'     => filesize($attach),
                        'path'     => $attach,
                    ];
                }

                // save attachment if valid
                if (
                    (!empty($attachment['data']) && !empty($attachment['name']))
                    || (!empty($attachment['path']) && file_exists($attachment['path']))
                ) {
                    $attachment = $rcmail->plugins->exec_hook('attachment_save', $attachment);
                }

                if (!empty($attachment['status']) && empty($attachment['abort'])) {
                    unset($attachment['data'], $attachment['status'], $attachment['abort']);
                    $COMPOSE['attachments'][$attachment['id']] = $attachment;
                }
            }
        }
    }

    public static function compose_editor_mode()
    {
        static $useHtml;

        if ($useHtml !== null) {
            return $useHtml;
        }

        $rcmail       = rcmail::get_instance();
        $html_editor  = intval($rcmail->config->get('htmleditor'));
        $compose_mode = self::$COMPOSE['mode'];

        if (isset(self::$COMPOSE['param']['html']) && is_bool(self::$COMPOSE['param']['html'])) {
            $useHtml = self::$COMPOSE['param']['html'];
        }
        else if (isset($_POST['_is_html'])) {
            $useHtml = !empty($_POST['_is_html']);
        }
        else if ($compose_mode == rcmail_sendmail::MODE_DRAFT || $compose_mode == rcmail_sendmail::MODE_EDIT) {
            $useHtml = self::message_is_html();
        }
        else if ($compose_mode == rcmail_sendmail::MODE_REPLY) {
            $useHtml = $html_editor == 1 || ($html_editor >= 2 && self::message_is_html());
        }
        else if ($compose_mode == rcmail_sendmail::MODE_FORWARD) {
            $useHtml = $html_editor == 1 || $html_editor == 4
                || ($html_editor == 3 && self::message_is_html());
        }
        else {
            $useHtml = $html_editor == 1 || $html_editor == 4;
        }

        return $useHtml;
    }

    public static function message_is_html()
    {
        return rcmail::get_instance()->config->get('prefer_html')
            && (self::$MESSAGE instanceof rcube_message)
            && self::$MESSAGE->has_html_part(true);
    }

    public static function spellchecker_init()
    {
        $rcmail = rcmail::get_instance();

        // Set language list
        if ($rcmail->config->get('enable_spellcheck')) {
            $spellchecker = new rcube_spellchecker();
        }
        else {
            return;
        }

        $spellcheck_langs = $spellchecker->languages();

        if (empty($spellcheck_langs)) {
            if ($err = $spellchecker->error()) {
                rcube::raise_error(['code' => 500,
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Spell check engine error: " . trim($err)],
                    true, false
                );
            }
        }
        else {
            $dictionary = (bool) $rcmail->config->get('spellcheck_dictionary');
            $lang       = $_SESSION['language'];

            // if not found in the list, try with two-letter code
            if (empty($spellcheck_langs[$lang])) {
                $lang = strtolower(substr($lang, 0, 2));
            }

            if (empty($spellcheck_langs[$lang])) {
                $lang = 'en';
            }

            // include GoogieSpell
            $rcmail->output->include_script('googiespell.js');
            $rcmail->output->add_script(sprintf(
                "var googie = new GoogieSpell('%s/images/googiespell/','%s&lang=', %s);\n".
                "googie.lang_chck_spell = \"%s\";\n".
                "googie.lang_rsm_edt = \"%s\";\n".
                "googie.lang_close = \"%s\";\n".
                "googie.lang_revert = \"%s\";\n".
                "googie.lang_no_error_found = \"%s\";\n".
                "googie.lang_learn_word = \"%s\";\n".
                "googie.setLanguages(%s);\n".
                "googie.setCurrentLanguage('%s');\n".
                "googie.setDecoration(false);\n".
                "googie.decorateTextarea(rcmail.env.composebody);\n",
                $rcmail->output->asset_url($rcmail->output->get_skin_path()),
                $rcmail->url(['_task' => 'utils', '_action' => 'spell', '_remote' => 1]),
                !empty($dictionary) ? 'true' : 'false',
                rcube::JQ(rcube::Q($rcmail->gettext('checkspelling'))),
                rcube::JQ(rcube::Q($rcmail->gettext('resumeediting'))),
                rcube::JQ(rcube::Q($rcmail->gettext('close'))),
                rcube::JQ(rcube::Q($rcmail->gettext('revertto'))),
                rcube::JQ(rcube::Q($rcmail->gettext('nospellerrors'))),
                rcube::JQ(rcube::Q($rcmail->gettext('addtodict'))),
                rcube_output::json_serialize($spellcheck_langs),
                $lang
            ), 'foot');

            $rcmail->output->add_label('checking');
            $rcmail->output->set_env('spell_langs', $spellcheck_langs);
            $rcmail->output->set_env('spell_lang', $lang);
        }
    }

    public static function prepare_message_body()
    {
        $rcmail = rcmail::get_instance();
        $body   = '';

        // use posted message body
        if (!empty($_POST['_message'])) {
            $body   = rcube_utils::get_input_string('_message', rcube_utils::INPUT_POST, true);
            $isHtml = (bool) rcube_utils::get_input_string('_is_html', rcube_utils::INPUT_POST);
        }
        else if (!empty(self::$COMPOSE['param']['body'])) {
            $body   = self::$COMPOSE['param']['body'];
            $isHtml = !empty(self::$COMPOSE['param']['html']);
        }
        // forward as attachment
        else if (self::$COMPOSE['mode'] == rcmail_sendmail::MODE_FORWARD && !empty(self::$COMPOSE['as_attachment'])) {
            $isHtml = self::compose_editor_mode();

            self::write_forward_attachments();
        }
        // reply/edit/draft/forward
        else if (!empty(self::$COMPOSE['mode'])
            && (self::$COMPOSE['mode'] != rcmail_sendmail::MODE_REPLY || intval($rcmail->config->get('reply_mode')) != -1)
        ) {
            $isHtml   = self::compose_editor_mode();
            $messages = [];

            // Create a (fake) image attachments map. We need it before we handle
            // the message body. After that we'll go throughout the list and check
            // which images were used in the body and attach them for real or skip.
            if ($isHtml) {
                self::$CID_MAP = self::cid_map(self::$MESSAGE);
            }

            // set is_safe flag (before HTML body washing)
            if (self::$COMPOSE['mode'] == rcmail_sendmail::MODE_DRAFT) {
                self::$MESSAGE->is_safe = true;
            }
            else {
                self::check_safe(self::$MESSAGE);
            }

            if (!empty(self::$MESSAGE->parts)) {
                // collect IDs of message/rfc822 parts
                foreach (self::$MESSAGE->mime_parts() as $part) {
                    if ($part->mimetype == 'message/rfc822') {
                        $messages[] = $part->mime_id;
                    }
                }

                foreach (self::$MESSAGE->parts as $part) {
                    if (!empty($part->realtype) && $part->realtype == 'multipart/encrypted') {
                        // find the encrypted message payload part
                        if ($pgp_mime_part = self::$MESSAGE->get_multipart_encrypted_part()) {
                            $rcmail->output->set_env('pgp_mime_message', [
                                    '_mbox' => $rcmail->storage->get_folder(),
                                    '_uid'  => self::$MESSAGE->uid,
                                    '_part' => $pgp_mime_part->mime_id,
                            ]);
                        }
                        continue;
                    }

                    // skip no-content and attachment parts (#1488557)
                    if ($part->type != 'content' || !$part->size || self::$MESSAGE->is_attachment($part)) {
                        continue;
                    }

                    // skip all content parts inside the message/rfc822 part
                    foreach ($messages as $mimeid) {
                        if (strpos($part->mime_id, $mimeid . '.') === 0) {
                            continue 2;
                        }
                    }

                    if ($part_body = self::compose_part_body($part, $isHtml)) {
                        $body .= ($body ? ($isHtml ? '<br/>' : "\n") : '') . $part_body;
                    }
                }
            }

            // compose reply-body
            if (self::$COMPOSE['mode'] == rcmail_sendmail::MODE_REPLY) {
                $body = self::create_reply_body($body, $isHtml);

                if (!empty(self::$MESSAGE->pgp_mime)) {
                    $rcmail->output->set_env('compose_reply_header', self::get_reply_header(self::$MESSAGE));
                }
            }
            // forward message body inline
            else if (self::$COMPOSE['mode'] == rcmail_sendmail::MODE_FORWARD) {
                $body = self::create_forward_body($body, $isHtml);
            }
            // load draft message body
            else if (
                self::$COMPOSE['mode'] == rcmail_sendmail::MODE_DRAFT
                || self::$COMPOSE['mode'] == rcmail_sendmail::MODE_EDIT
            ) {
                $body = self::create_draft_body($body, $isHtml);
            }

            // Save forwarded files (or inline images) as attachments
            // This will also update inline images location in the body
            self::write_compose_attachments(self::$MESSAGE, $isHtml, $body);
        }
        // new message
        else {
            $isHtml = self::compose_editor_mode();
        }

        $plugin = $rcmail->plugins->exec_hook('message_compose_body', [
                'body'    => $body,
                'html'    => $isHtml,
                'mode'    => self::$COMPOSE['mode'],
                'message' => self::$MESSAGE,
        ]);

        $body = $plugin['body'];
        unset($plugin);

        // add blocked.gif attachment (#1486516)
        $regexp = '/ src="' . preg_quote($rcmail->output->asset_url('program/resources/blocked.gif'), '/') . '"/';
        if ($isHtml && preg_match($regexp, $body)) {
            $content = self::get_resource_content('blocked.gif');

            if ($content && ($attachment = self::save_image('blocked.gif', 'image/gif', $content))) {
                self::$COMPOSE['attachments'][$attachment['id']] = $attachment;
                $url = sprintf('%s&_id=%s&_action=display-attachment&_file=rcmfile%s',
                    $rcmail->comm_path, self::$COMPOSE['id'], $attachment['id']);
                $body = preg_replace($regexp, ' src="' . $url . '"', $body);
            }
        }

        self::$HTML_MODE = $isHtml;

        return $body;
    }

    /**
     * Prepare message part body for composition
     *
     * @param rcube_message_part $part   Message part
     * @param bool               $isHtml Use HTML mode
     *
     * @return string Message body text
     */
    public static function compose_part_body($part, $isHtml = false)
    {
        if (!$part instanceof rcube_message_part) {
            return '';
        }

        // Check if we have enough memory to handle the message in it
        // #1487424: we need up to 10x more memory than the body
        if (!rcube_utils::mem_check($part->size * 10)) {
            return '';
        }

        // fetch part if not available
        $body = self::$MESSAGE->get_part_body($part->mime_id, true);

        // message is cached but not exists (#1485443), or other error
        if ($body === false) {
            return '';
        }

        $rcmail = rcmail::get_instance();

        // register this part as pgp encrypted
        if (strpos($body, '-----BEGIN PGP MESSAGE-----') !== false) {
            self::$MESSAGE->pgp_mime = true;
            $rcmail->output->set_env('pgp_mime_message', [
                    '_mbox' => $rcmail->storage->get_folder(),
                    '_uid'  => self::$MESSAGE->uid,
                    '_part' => $part->mime_id,
            ]);
        }

        $strip_signature = self::$COMPOSE['mode'] != rcmail_sendmail::MODE_DRAFT
            && self::$COMPOSE['mode'] != rcmail_sendmail::MODE_EDIT
            && $rcmail->config->get('strip_existing_sig', true);

        $flowed = !empty($part->ctype_parameters['format']) && $part->ctype_parameters['format'] == 'flowed';
        $delsp  = $flowed && !empty($part->ctype_parameters['delsp']) && $part->ctype_parameters['delsp'] == 'yes';

        if ($isHtml) {
            if ($part->ctype_secondary == 'html') {
                $body = self::prepare_html_body($body);
            }
            else if ($part->ctype_secondary == 'enriched') {
                $body = rcube_enriched::to_html($body);
            }
            else {
                // try to remove the signature
                if ($strip_signature) {
                    $body = self::remove_signature($body);
                }

                // add HTML formatting
                $body = self::plain_body($body, $flowed, $delsp);
            }
        }
        else {
            if ($part->ctype_secondary == 'enriched') {
                $body = rcube_enriched::to_html($body);
                $part->ctype_secondary = 'html';
            }

            if ($part->ctype_secondary == 'html') {
                // set line length for body wrapping
                $line_length = $rcmail->config->get('line_length', 72);

                // use html part if it has been used for message (pre)viewing
                // decrease line length for quoting
                $len  = self::$COMPOSE['mode'] == rcmail_sendmail::MODE_REPLY ? $line_length-2 : $line_length;
                $body = $rcmail->html2text($body, ['width' => $len]);
            }
            else {
                if ($part->ctype_secondary == 'plain' && $flowed) {
                    $body = rcube_mime::unfold_flowed($body, null, $delsp);
                }

                // try to remove the signature
                if ($strip_signature) {
                    $body = self::remove_signature($body);
                }
            }
        }

        return $body;
    }

    public static function compose_body($attrib)
    {
        list($form_start, $form_end) = self::$SENDMAIL->form_tags($attrib);
        unset($attrib['form']);

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmComposeBody';
        }

        // If desired, set this textarea to be editable by TinyMCE
        $attrib['data-html-editor'] = true;
        if (self::$HTML_MODE) {
            $attrib['class'] = trim(($attrib['class'] ?? '') . ' mce_editor');
            $attrib['data-html-editor-content-element'] = $attrib['id'] . '-content';
        }

        $attrib['name'] = '_message';

        $rcmail   = rcmail::get_instance();
        $textarea = new html_textarea($attrib);
        $hidden   = new html_hiddenfield();

        $hidden->add(['name' => '_draft_saveid', 'value' => $rcmail->output->get_env('draft_id')]);
        $hidden->add(['name' => '_draft', 'value' => '']);
        $hidden->add(['name' => '_is_html', 'value' => self::$HTML_MODE ? "1" : "0"]);
        $hidden->add(['name' => '_framed', 'value' => '1']);

        $rcmail->output->set_env('composebody', $attrib['id']);

        $content = $hidden->show() . "\n";

        // We're adding a hidden textarea with the HTML content to workaround browsers' performance
        // issues with rendering/loading long content. It will be copied to the main editor (#8108)
        if (self::$HTML_MODE && strlen(self::$MESSAGE_BODY) > 50 * 1024) {
            $contentArea = new html_textarea(['style' => 'display:none', 'id' => $attrib['id'] . '-content']);
            $content .= $contentArea->show(self::$MESSAGE_BODY) . "\n" . $textarea->show();
        }
        else {
            $content .= $textarea->show(self::$MESSAGE_BODY);
        }

        // include HTML editor
        self::html_editor();

        return "$form_start\n$content\n$form_end\n";
    }

    public static function create_reply_body($body, $bodyIsHtml)
    {
        $rcmail       = rcmail::get_instance();
        $reply_mode   = (int) $rcmail->config->get('reply_mode');
        $reply_indent = $reply_mode != 2;

        // In top-posting without quoting it's better to use multi-line header
        if ($reply_mode == 2) {
            $prefix = self::get_forward_header(self::$MESSAGE, $bodyIsHtml, false);
        }
        else {
            $prefix = self::get_reply_header(self::$MESSAGE);
            if ($bodyIsHtml) {
                $prefix = '<p id="reply-intro">' . rcube::Q($prefix) . '</p>';
            }
            else {
                $prefix .= "\n";
            }
        }

        if (!$bodyIsHtml) {
            // quote the message text
            if ($reply_indent) {
                $body = self::quote_text($body);
            }

            if ($reply_mode > 0) { // top-posting
                $prefix = "\n\n\n" . $prefix;
                $suffix = '';
            }
            else {
                $suffix = "\n";
            }
        }
        else {
            $suffix = '';

            if ($reply_indent) {
                $prefix .= '<blockquote>';
                $suffix .= '</blockquote>';
            }

            if ($reply_mode == 2) {
                // top-posting, no indent
            }
            else if ($reply_mode > 0) {
                // top-posting
                $prefix = '<br>' . $prefix;
            }
            else {
                $suffix .= '<p><br/></p>';
            }
        }

        return $prefix . $body . $suffix;
    }

    public static function get_reply_header($message)
    {
        if (empty($message->headers)) {
            return '';
        }

        $rcmail = rcmail::get_instance();
        $list   = rcube_mime::decode_address_list($message->get_header('from'), 1, false, $message->headers->charset);
        $from   = array_pop($list);

        return $rcmail->gettext([
                'name' => 'mailreplyintro',
                'vars' => [
                    'date'   => $rcmail->format_date($message->get_header('date'), $rcmail->config->get('date_long')),
                    'sender' => !empty($from['name']) ? $from['name'] : rcube_utils::idn_to_utf8($from['mailto']),
                ]
        ]);
    }

    public static function create_forward_body($body, $bodyIsHtml)
    {
        return self::get_forward_header(self::$MESSAGE, $bodyIsHtml) . trim($body, "\r\n");
    }

    public static function get_forward_header($message, $bodyIsHtml = false, $extended = true)
    {
        if (empty($message->headers)) {
            return '';
        }

        $rcmail = rcmail::get_instance();
        $date   = $rcmail->format_date($message->get_header('date'), $rcmail->config->get('date_long'));

        if (!$bodyIsHtml) {
            $prefix = "\n\n\n-------- " . $rcmail->gettext('originalmessage') . " --------\n";
            $prefix .= $rcmail->gettext('subject') . ': ' . $message->subject . "\n";
            $prefix .= $rcmail->gettext('date')    . ': ' . $date . "\n";
            $prefix .= $rcmail->gettext('from')    . ': ' . $message->get_header('from') . "\n";
            $prefix .= $rcmail->gettext('to')      . ': ' . $message->get_header('to') . "\n";

            if ($extended && ($cc = $message->get_header('cc'))) {
                $prefix .= $rcmail->gettext('cc') . ': ' . $cc . "\n";
            }

            if ($extended && ($replyto = $message->get_header('reply-to')) && $replyto != $message->get_header('from')) {
                $prefix .= $rcmail->gettext('replyto') . ': ' . $replyto . "\n";
            }

            $prefix .= "\n";
        }
        else {
            $prefix = sprintf(
                "<br /><p>-------- " . $rcmail->gettext('originalmessage') . " --------</p>" .
                "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody>" .
                "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">%s: </th><td>%s</td></tr>" .
                "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">%s: </th><td>%s</td></tr>" .
                "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">%s: </th><td>%s</td></tr>" .
                "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">%s: </th><td>%s</td></tr>",
                $rcmail->gettext('subject'), rcube::Q($message->subject),
                $rcmail->gettext('date'), rcube::Q($date),
                $rcmail->gettext('from'), rcube::Q($message->get_header('from'), 'replace'),
                $rcmail->gettext('to'), rcube::Q($message->get_header('to'), 'replace')
            );

            if ($extended && ($cc = $message->get_header('cc'))) {
                $prefix .= sprintf("<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">%s: </th><td>%s</td></tr>",
                    $rcmail->gettext('cc'), rcube::Q($cc, 'replace'));
            }

            if ($extended && ($replyto = $message->get_header('reply-to')) && $replyto != $message->get_header('from')) {
                $prefix .= sprintf("<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">%s: </th><td>%s</td></tr>",
                    $rcmail->gettext('replyto'), rcube::Q($replyto, 'replace'));
            }

            $prefix .= "</tbody></table><br>";
        }

        return $prefix;
    }

    public static function create_draft_body($body, $bodyIsHtml)
    {
        // Return the draft body as-is
        return $body;
    }

    // Clean up HTML content of Draft/Reply/Forward (part of the message)
    public static function prepare_html_body($body, $wash_params = [])
    {
        static $part_no;

        // Set attributes of the part container
        $container_id = self::$COMPOSE['mode'] . 'body' . (++$part_no);
        $wash_params += [
            'safe'         => self::$MESSAGE->is_safe,
            'css_prefix'   => 'v' . $part_no,
            'add_comments' => false,
        ];

        if (self::$COMPOSE['mode'] == rcmail_sendmail::MODE_DRAFT) {
            // convert TinyMCE's empty-line sequence (#1490463)
            $body = preg_replace('/<p>\xC2\xA0<\/p>/', '<p><br /></p>', $body);
            // remove <body> tags (not their content)
            $wash_params['ignore_elements'] = ['body'];
        }
        else {
            $wash_params['container_id'] = $container_id;
        }

        // Make the HTML content safe and clean
        return self::wash_html($body, $wash_params, self::$CID_MAP);
    }

    // Removes signature from the message body
    public static function remove_signature($body)
    {
        $rcmail = rcmail::get_instance();
        $body   = str_replace("\r\n", "\n", $body);
        $len    = strlen($body);
        $sig_max_lines = $rcmail->config->get('sig_max_lines', 15);

        while (($sp = strrpos($body, "-- \n", !empty($sp) ? -$len + $sp - 1 : 0)) !== false) {
            if ($sp == 0 || $body[$sp-1] == "\n") {
                // do not touch blocks with more that X lines
                if (substr_count($body, "\n", $sp) < $sig_max_lines) {
                    $body = substr($body, 0, max(0, $sp-1));
                }
                break;
            }
        }

        return $body;
    }

    public static function write_compose_attachments(&$message, $bodyIsHtml, &$message_body)
    {
        if (!empty($message->pgp_mime) || !empty(self::$COMPOSE['forward_attachments'])) {
            return;
        }

        $messages           = [];
        $loaded_attachments = [];

        if (!empty(self::$COMPOSE['attachments'])) {
            foreach ((array) self::$COMPOSE['attachments'] as $attachment) {
                $loaded_attachments[$attachment['name'] . $attachment['mimetype']] = $attachment;
            }
        }

        $rcmail   = rcmail::get_instance();
        $has_html = $message->has_html_part();

        foreach ((array) $message->mime_parts() as $pid => $part) {
            if ($part->mimetype == 'message/rfc822') {
                $messages[] = $part->mime_id;
            }

            if (
                $part->disposition == 'attachment'
                || ($part->disposition == 'inline' && $bodyIsHtml)
                || $part->filename
                || $part->mimetype == 'message/rfc822'
            ) {
                // skip parts that aren't valid attachments
                if ($part->ctype_primary == 'multipart' || $part->mimetype == 'application/ms-tnef') {
                    continue;
                }

                // skip message attachments in reply mode
                if ($part->ctype_primary == 'message' && self::$COMPOSE['mode'] == rcmail_sendmail::MODE_REPLY) {
                    continue;
                }

                // skip version.txt parts of multipart/encrypted messages
                if (!empty($message->pgp_mime) && $part->mimetype == 'application/pgp-encrypted' && $part->filename == 'version.txt') {
                    continue;
                }

                // skip attachments included in message/rfc822 attachment (#1486487, #1490607)
                foreach ($messages as $mimeid) {
                    if (strpos($part->mime_id, $mimeid . '.') === 0) {
                        continue 2;
                    }
                }

                $replace = null;

                // Skip inline images when not used in the body
                // Note: Apple Mail sends PDF files marked as inline (#7382)
                // Note: Apple clients send inline images even if there's no HTML body (#7414)
                if ($has_html && $part->disposition == 'inline' && $part->mimetype != 'application/pdf') {
                    if (!$bodyIsHtml) {
                        continue;
                    }

                    $idx = $part->content_id ? ('cid:' . $part->content_id) : $part->content_location ?? null;

                    if ($idx && isset(self::$CID_MAP[$idx]) && strpos($message_body, self::$CID_MAP[$idx]) !== false) {
                        $replace = self::$CID_MAP[$idx];
                    }
                    else {
                        continue;
                    }
                }
                // skip any other attachment on Reply
                else if (self::$COMPOSE['mode'] == rcmail_sendmail::MODE_REPLY) {
                    continue;
                }

                $key = self::attachment_name($part) . $part->mimetype;

                if (!empty($loaded_attachments[$key])) {
                    $attachment = $loaded_attachments[$key];
                }
                else {
                    $attachment = self::save_attachment($message, $pid, self::$COMPOSE['id']);
                }

                if ($attachment) {
                    if ($replace) {
                        $url = sprintf('%s&_id=%s&_action=display-attachment&_file=rcmfile%s',
                            $rcmail->comm_path, self::$COMPOSE['id'], $attachment['id']);

                        $message_body = str_replace($replace, $url, $message_body);
                    }
                }
            }
        }

        self::$COMPOSE['forward_attachments'] = true;
    }

    /**
     * Create a map of attachment content-id/content-locations
     */
    public static function cid_map($message)
    {
        if (!empty($message->pgp_mime)) {
            return [];
        }

        $messages = [];
        $map      = [];

        foreach ((array) $message->mime_parts() as $pid => $part) {
            if ($part->mimetype == 'message/rfc822') {
                $messages[] = $part->mime_id;
            }

            if (!empty($part->content_id) || !empty($part->content_location)) {
                // skip attachments included in message/rfc822 attachment (#1486487, #1490607)
                foreach ($messages as $mimeid) {
                    if (strpos($part->mime_id, $mimeid . '.') === 0) {
                        continue 2;
                    }
                }

                $url = sprintf('RCMAP%s', md5($message->folder . '/' . $message->uid . '/' . $pid));
                $idx = !empty($part->content_id) ? ('cid:' . $part->content_id) : $part->content_location;

                $map[$idx] = $url;
            }
        }

        return $map;
    }

    // Creates attachment(s) from the forwarded message(s)
    public static function write_forward_attachments()
    {
        if (!empty(self::$MESSAGE->pgp_mime)) {
            return;
        }

        $rcmail      = rcmail::get_instance();
        $storage     = $rcmail->get_storage();
        $names       = [];
        $refs        = [];
        $size_errors = 0;
        $size_limit  = parse_bytes($rcmail->config->get('max_message_size'));
        $total_size  = 10 * 1024; // size of message body, to start with

        $loaded_attachments = [];

        if (!empty(self::$COMPOSE['attachments'])) {
            foreach ((array) self::$COMPOSE['attachments'] as $attachment) {
                $loaded_attachments[$attachment['name'] . $attachment['mimetype']] = $attachment;
                $total_size += $attachment['size'];
            }
        }

        if (self::$COMPOSE['forward_uid'] == '*') {
            $index = $storage->index(null, self::sort_column(), self::sort_order());
            self::$COMPOSE['forward_uid'] = $index->get();
        }
        else if (!is_array(self::$COMPOSE['forward_uid']) && strpos(self::$COMPOSE['forward_uid'], ':')) {
            self::$COMPOSE['forward_uid'] = rcube_imap_generic::uncompressMessageSet(self::$COMPOSE['forward_uid']);
        }
        else if (is_string(self::$COMPOSE['forward_uid'])) {
            self::$COMPOSE['forward_uid'] = explode(',', self::$COMPOSE['forward_uid']);
        }

        foreach ((array) self::$COMPOSE['forward_uid'] as $uid) {
            $message = new rcube_message($uid);

            if (empty($message->headers)) {
                continue;
            }

            if (!empty($message->headers->charset)) {
                $storage->set_charset($message->headers->charset);
            }

            if (empty(self::$MESSAGE->subject)) {
                self::$MESSAGE->subject = $message->subject;
            }

            if ($message->headers->get('bcc', false) || $message->headers->get('resent-bcc', false)) {
                self::$COMPOSE['has_bcc'] = true;
            }

            // generate (unique) attachment name
            $name = strlen($message->subject) ? mb_substr($message->subject, 0, 64) : 'message_rfc822';
            if (!empty($names[$name])) {
                $names[$name]++;
                $name .= '_' . $names[$name];
            }
            $names[$name] = 1;
            $name .= '.eml';

            if (!empty($loaded_attachments[$name . 'message/rfc822'])) {
                continue;
            }

            if ($size_limit && $size_limit < $total_size + $message->headers->size) {
                $size_errors++;
                continue;
            }

            $total_size += $message->headers->size;

            self::save_attachment($message, null, self::$COMPOSE['id'], ['filename' => $name]);

            if ($message->headers->messageID) {
                $refs[] = $message->headers->messageID;
            }
        }

        // set In-Reply-To and References headers
        if (count($refs) == 1) {
            self::$COMPOSE['reply_msgid'] = $refs[0];
        }

        if (!empty($refs)) {
            self::$COMPOSE['references'] = implode(' ', $refs);
        }

        if ($size_errors) {
            $limit = self::show_bytes($size_limit);
            $error = $rcmail->gettext([
                    'name' => 'msgsizeerrorfwd',
                    'vars' => ['num' => $size_errors, 'size' => $limit]
            ]);
            $script = sprintf("%s.display_message('%s', 'error');", rcmail_output::JS_OBJECT_NAME, rcube::JQ($error));
            $rcmail->output->add_script($script, 'docready');
        }
    }

    /**
     * Saves an image as attachment
     */
    public static function save_image($path, $mimetype = '', $data = null)
    {
        $is_file = false;

        // handle attachments in memory
        if (empty($data)) {
            $data    = file_get_contents($path);
            $is_file = true;
        }

        $name = self::basename($path);

        if (empty($mimetype)) {
            if ($is_file) {
                $mimetype = rcube_mime::file_content_type($path, $name);
            }
            else {
                $mimetype = rcube_mime::file_content_type($data, $name, 'application/octet-stream', true);
            }
        }

        $attachment = [
            'group'    => self::$COMPOSE['id'],
            'name'     => $name,
            'mimetype' => $mimetype,
            'data'     => $data,
            'size'     => strlen($data),
        ];

        $attachment = rcmail::get_instance()->plugins->exec_hook('attachment_save', $attachment);

        if ($attachment['status']) {
            unset($attachment['data'], $attachment['status'], $attachment['content_id'], $attachment['abort']);
            return $attachment;
        }

        return false;
    }

    /**
     * Unicode-safe basename()
     */
    public static function basename($filename)
    {
        // basename() is not unicode safe and locale dependent
        if (stristr(PHP_OS, 'win') || stristr(PHP_OS, 'netware')) {
            return preg_replace('/^.*[\\\\\\/]/', '', $filename);
        }
        else {
            return preg_replace('/^.*[\/]/', '', $filename);
        }
    }

    /**
     * Handler for template object 'composeObjects'
     *
     * @param array $attrib HTML attributes
     *
     * @return string HTML content
     */
    public static function compose_objects($attrib)
    {
        if (empty($attrib['id'])) {
            $attrib['id'] = 'compose-objects';
        }

        $rcmail  = rcmail::get_instance();
        $content = [];

        // Add a warning about Bcc recipients
        if (!empty(self::$COMPOSE['has_bcc'])) {
            $msg        = html::span(null, rcube::Q($rcmail->gettext('bccemail')));
            $msg_attrib = ['id' => 'bcc-warning', 'class' => 'boxwarning'];
            $content[]  = html::div($msg_attrib, $msg);
        }

        $plugin = $rcmail->plugins->exec_hook('compose_objects',
            ['content' => $content, 'message' => self::$MESSAGE]);

        $content = implode("\n", $plugin['content']);

        return $content ? html::div($attrib, $content) : '';
    }

    /**
     * Attachments list object for templates
     */
    public static function compose_attachment_list($attrib)
    {
        // add ID if not given
        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmAttachmentList';
        }

        $rcmail = rcmail::get_instance();
        $out    = '';
        $button = '';
        $jslist = [];

        if (!empty($attrib['icon_pos']) && $attrib['icon_pos'] == 'left') {
            self::$COMPOSE['icon_pos'] = 'left';
        }
        $icon_pos = self::$COMPOSE['icon_pos'] ?? null;

        if (!empty(self::$COMPOSE['attachments'])) {
            if (!empty($attrib['deleteicon'])) {
                $button = html::img([
                        'src' => $rcmail->output->asset_url($attrib['deleteicon'], true),
                        'alt' => $rcmail->gettext('delete')
                ]);
            }
            else if (self::get_bool_attr($attrib, 'textbuttons')) {
                $button = rcube::Q($rcmail->gettext('delete'));
            }

            foreach (self::$COMPOSE['attachments'] as $id => $a_prop) {
                if (empty($a_prop)) {
                    continue;
                }

                $link_content = sprintf(
                    '<span class="attachment-name" onmouseover="rcube_webmail.long_subject_title_ex(this)">%s</span>'
                        . ' <span class="attachment-size">(%s)</span>',
                    rcube::Q($a_prop['name']),
                    self::show_bytes($a_prop['size'])
                );

                $content_link = html::a([
                        'href'     => '#load',
                        'class'    => 'filename',
                        'onclick'  => sprintf(
                            "return %s.command('load-attachment','rcmfile%s', this, event)",
                            rcmail_output::JS_OBJECT_NAME,
                            $id
                        ),
                        'tabindex' => !empty($attrib['tabindex']) ? $attrib['tabindex'] : '0',
                    ],
                    $link_content
                );

                $delete_link = html::a([
                        'href'    => '#delete',
                        'title'   => $rcmail->gettext('delete'),
                        'onclick' => sprintf(
                            "return %s.command('remove-attachment','rcmfile%s', this, event)",
                            rcmail_output::JS_OBJECT_NAME,
                            $id
                        ),
                        'class'      => 'delete',
                        'tabindex'   => !empty($attrib['tabindex']) ? $attrib['tabindex'] : '0',
                        'aria-label' => $rcmail->gettext('delete') . ' ' . $a_prop['name'],
                    ],
                    $button
                );

                $out .= html::tag('li', [
                        'id'    => 'rcmfile' . $id,
                        'class' => rcube_utils::file2class($a_prop['mimetype'], $a_prop['name']),
                    ],
                    $icon_pos == 'left' ? $delete_link.$content_link : $content_link.$delete_link
                );

                $jslist['rcmfile'.$id] = [
                    'name'     => $a_prop['name'],
                    'complete' => true,
                    'mimetype' => $a_prop['mimetype']
                ];
            }
        }

        if (!empty($attrib['deleteicon'])) {
            self::$COMPOSE['deleteicon'] = $rcmail->output->asset_url($attrib['deleteicon'], true);
        }
        else if (self::get_bool_attr($attrib, 'textbuttons')) {
            self::$COMPOSE['textbuttons'] = true;
        }
        if (!empty($attrib['cancelicon'])) {
            $rcmail->output->set_env('cancelicon', $rcmail->output->asset_url($attrib['cancelicon'], true));
        }
        if (!empty($attrib['loadingicon'])) {
            $rcmail->output->set_env('loadingicon', $rcmail->output->asset_url($attrib['loadingicon'], true));
        }

        $rcmail->output->set_env('attachments', $jslist);
        $rcmail->output->add_gui_object('attachmentlist', $attrib['id']);

        // put tabindex value into data-tabindex attribute
        if (isset($attrib['tabindex'])) {
            $attrib['data-tabindex'] = $attrib['tabindex'];
            unset($attrib['tabindex']);
        }

        return html::tag('ul', $attrib, $out, html::$common_attrib);
    }

    /**
     * Attachment upload form object for templates
     */
    public static function compose_attachment_form($attrib)
    {
        $rcmail = rcmail::get_instance();

        // Limit attachment size according to message size limit
        $limit = parse_bytes($rcmail->config->get('max_message_size')) / 1.33;

        return self::upload_form($attrib, 'uploadform', 'send-attachment', ['multiple' => true], $limit);
    }

    /**
     * Register a certain container as active area to drop files onto
     */
    public static function compose_file_drop_area($attrib)
    {
        $rcmail = rcmail::get_instance();

        if (!empty($attrib['id'])) {
            $rcmail->output->add_gui_object('filedrop', $attrib['id']);
            $rcmail->output->set_env('filedrop', ['action' => 'upload', 'fieldname' => '_attachments']);
        }
    }

    /**
     * Editor mode selector object for templates
     */
    public static function editor_selector($attrib)
    {
        $rcmail = rcmail::get_instance();

        // determine whether HTML or plain text should be checked
        $useHtml = self::compose_editor_mode();

        if (empty($attrib['editorid'])) {
            $attrib['editorid'] = 'rcmComposeBody';
        }

        if (empty($attrib['name'])) {
            $attrib['name'] = 'editorSelect';
        }

        $attrib['onchange'] = "return rcmail.command('toggle-editor', {id: '".$attrib['editorid']."', html: this.value == 'html'}, '', event)";

        $select = new html_select($attrib);

        $select->add(rcube::Q($rcmail->gettext('htmltoggle')), 'html');
        $select->add(rcube::Q($rcmail->gettext('plaintoggle')), 'plain');

        return $select->show($useHtml ? 'html' : 'plain');
    }

    /**
     * Addressbooks list object for templates
     */
    public static function addressbook_list($attrib = [])
    {
        $rcmail = rcmail::get_instance();

        $attrib += ['id' => 'rcmdirectorylist'];

        $line_templ = html::tag('li',
            ['id' => 'rcmli%s', 'class' => '%s'],
            html::a([
                    'href'    => '#list',
                    'rel'     => '%s',
                    'onclick' => "return ".rcmail_output::JS_OBJECT_NAME.".command('list-addresses','%s',this)"
                ],
                '%s'
            )
        );

        $out = '';

        foreach ($rcmail->get_address_sources(false, true) as $j => $source) {
            $id = strval(strlen($source['id']) ? $source['id'] : $j);
            $js_id = rcube::JQ($id);

            // set class name(s)
            $class_name = 'addressbook';
            if (!empty($source['class_name'])) {
                $class_name .= ' ' . $source['class_name'];
            }

            $out .= sprintf($line_templ,
                rcube_utils::html_identifier($id,true),
                $class_name,
                $source['id'],
                $js_id,
                !empty($source['name']) ? $source['name'] : $id
            );
        }

        $rcmail->output->add_gui_object('addressbookslist', $attrib['id']);

        return html::tag('ul', $attrib, $out, html::$common_attrib);
    }

    /**
     * Contacts list object for templates
     */
    public static function contacts_list($attrib = [])
    {
        $rcmail = rcmail::get_instance();

        $attrib += ['id' => 'rcmAddressList'];

        // set client env
        $rcmail->output->add_gui_object('contactslist', $attrib['id']);
        $rcmail->output->set_env('pagecount', 0);
        $rcmail->output->set_env('current_page', 0);
        $rcmail->output->include_script('list.js');

        return $rcmail->table_output($attrib, [], ['name'], 'ID');
    }

    /**
     * Responses list object for templates
     *
     * @param array $attrib Object attributes
     *
     * @return string HTML content
     */
    public static function compose_responses_list($attrib)
    {
        $rcmail = rcmail::get_instance();

        $attrib += ['id' => 'rcmresponseslist', 'tagname' => 'ul', 'cols' => 1, 'itemclass' => ''];

        $list = new html_table($attrib);

        foreach ($rcmail->get_compose_responses() as $response) {
            $item = html::a([
                    'href'         => '#response-' . urlencode($response['id']),
                    'class'        => rtrim('insertresponse ' . $attrib['itemclass']),
                    'unselectable' => 'on',
                    'tabindex'     => '0',
                    'onclick'      => sprintf(
                        "return %s.command('insert-response', '%s', this, event)",
                        rcmail_output::JS_OBJECT_NAME,
                        rcube::JQ($response['id'])
                    ),
                ],
                rcube::Q($response['name'])
            );

            $list->add([], $item);
        }

        // add placeholder text when there are no responses available
        if (!empty($attrib['list-placeholder']) && $list->size() == 0) {
            $list->add([], html::a([
                    'href'          => '#',
                    'class'         => rtrim('insertresponse placeholder disabled'),
                    'unselectable'  => 'on',
                    'tabindex'      => '0',
                    'aria-disabled' => 'true',
                ],
                rcube::Q($rcmail->gettext($attrib['list-placeholder']))
            ));
        }

        $rcmail->output->add_gui_object('responseslist', $attrib['id']);

        return $list->show();
    }

    public static function save_attachment($message, $pid, $compose_id, $params = [])
    {
        $rcmail  = rcmail::get_instance();
        $storage = $rcmail->get_storage();

        if ($pid) {
            // attachment requested
            $part     = $message->mime_parts[$pid];
            $size     = $part->size;
            $mimetype = $part->ctype_primary . '/' . $part->ctype_secondary;
            $filename = !empty($params['filename']) ? $params['filename'] : self::attachment_name($part);
        }
        else if ($message instanceof rcube_message) {
            // the whole message requested
            $size     = $message->size ?? null;
            $mimetype = 'message/rfc822';
            $filename = !empty($params['filename']) ? $params['filename'] : 'message_rfc822.eml';
        }
        else if (is_string($message)) {
            // the whole message requested
            $size     = strlen($message);
            $data     = $message;
            $mimetype = $params['mimetype'];
            $filename = $params['filename'];
        }
        else {
            return;
        }

        if (!isset($data)) {
            $data = null;
            $path = null;

            // don't load too big attachments into memory
            if (!rcube_utils::mem_check($size)) {
                $path = rcube_utils::temp_filename('attmnt');

                if ($fp = fopen($path, 'w')) {
                    if ($pid) {
                        // part body
                        $message->get_part_body($pid, false, 0, $fp);
                    }
                    else {
                        // complete message
                        $storage->get_raw_body($message->uid, $fp);
                    }

                    fclose($fp);
                }
                else {
                    return false;
                }
            }
            else if ($pid) {
                // part body
                $data = $message->get_part_body($pid);
            }
            else {
                // complete message
                $data = $storage->get_raw_body($message->uid);
            }
        }

        $attachment = [
            'group'      => $compose_id,
            'name'       => $filename,
            'mimetype'   => $mimetype,
            'content_id' => !empty($part) && isset($part->content_id) ? $part->content_id : null,
            'data'       => $data,
            'path'       => $path ?? null,
            'size'       => isset($path) ? filesize($path) : strlen($data),
            'charset'    => !empty($part) ? $part->charset : ($params['charset'] ?? null),
        ];

        $attachment = $rcmail->plugins->exec_hook('attachment_save', $attachment);

        if ($attachment['status']) {
            unset($attachment['data'], $attachment['status'], $attachment['content_id'], $attachment['abort']);

            // rcube_session::append() replaces current session data with the old values
            // (in rcube_session::reload()). This is a problem in 'compose' action, because before
            // the first append() use we set some important data in the session.
            // It also overwrites attachments list. Fixing reload() is not so simple if possible
            // as we don't really know what has been added and what removed in meantime.
            // So, for now we'll do not use append() on 'compose' action (#1490608).

            if ($rcmail->action == 'compose') {
                self::$COMPOSE['attachments'][$attachment['id']] = $attachment;
            }
            else {
                $rcmail->session->append('compose_data_' . $compose_id . '.attachments', $attachment['id'], $attachment);
            }

            return $attachment;
        }
        else if (!empty($path)) {
            @unlink($path);
        }

        return false;
    }

    /**
     * Add quotation (>) to a replied message text.
     *
     * @param string $text Text to quote
     *
     * @return string The quoted text
     */
    public static function quote_text($text)
    {
        $lines = preg_split('/\r?\n/', trim($text));
        $out   = '';

        foreach ($lines as $line) {
            $quoted = isset($line[0]) && $line[0] == '>';
            $out .= '>' . ($quoted ? '' : ' ') . $line . "\n";
        }

        return rtrim($out, "\n");
    }
}
