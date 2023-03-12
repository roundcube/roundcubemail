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
 |   Common code for generating and saving/sending mail message          |
 |   with support for common user interface elements                     |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Common code for generating and saving/sending mail message
 * with support for common user interface elements.
 *
 * @package Webmail
 */
class rcmail_sendmail
{
    public $data    = [];
    public $options = [];

    protected $parse_data = [];
    protected $message_form;
    protected $rcmail;
    protected $temp_files = [];

    // define constants for message compose mode
    const MODE_NONE    = 'none';
    const MODE_REPLY   = 'reply';
    const MODE_FORWARD = 'forward';
    const MODE_DRAFT   = 'draft';
    const MODE_EDIT    = 'edit';


    /**
     * Object constructor
     *
     * @param array $data    Compose data
     * @param array $options Operation options:
     *    savedraft (bool) - Enable save-draft mode
     *    sendmail (bool) - Enable send-mail mode
     *    saveonly (bool) - Enable save-only mode
     *    message (object) - Message object to get some data from
     *    error_handler (callback) - Error handler
     *    dsn_enabled (bool) - Enable DSN
     */
    public function __construct($data = [], $options = [])
    {
        $this->rcmail  = rcube::get_instance();
        $this->data    = (array) $data;
        $this->options = (array) $options;

        $this->options['sendmail_delay'] = (int) $this->rcmail->config->get('sendmail_delay');

        if (empty($options['error_handler'])) {
            $this->options['error_handler'] = function() { return false; };
        }

        if (empty($this->data['mode'])) {
            $this->data['mode'] = self::MODE_NONE;
        }

        if (!empty($this->options['message'])) {
            $this->compose_init($this->options['message']);
        }
    }

    /**
     * Object destructor to cleanup temporary files
     */
    public function __destruct()
    {
        foreach ($this->temp_files as $file) {
            @unlink($file);
        }
    }

    /**
     * Collect input data for message headers
     *
     * @return array Message headers
     */
    public function headers_input()
    {
        if (!empty($this->options['sendmail']) && $this->options['sendmail_delay']) {
            $last_time = $this->rcmail->config->get('last_message_time');
            $wait_sec  = time() - $this->options['sendmail_delay'] - intval($last_time);

            if ($wait_sec < 0) {
                return $this->options['error_handler']('senttooquickly', 'error', ['sec' => $wait_sec * -1]);
            }
        }

        // set default charset
        if (empty($this->options['charset'])) {
            $charset = rcube_utils::get_input_string('_charset', rcube_utils::INPUT_POST) ?: $this->rcmail->output->get_charset();
            $this->options['charset'] = $charset;
        }

        $charset = $this->options['charset'];

        $this->parse_data = [];

        $mailto  = $this->email_input_format(rcube_utils::get_input_string('_to', rcube_utils::INPUT_POST, true, $charset), true);
        $mailcc  = $this->email_input_format(rcube_utils::get_input_string('_cc', rcube_utils::INPUT_POST, true, $charset), true);
        $mailbcc = $this->email_input_format(rcube_utils::get_input_string('_bcc', rcube_utils::INPUT_POST, true, $charset), true);

        if (!empty($this->parse_data['INVALID_EMAIL']) && empty($this->options['savedraft'])) {
            return $this->options['error_handler']('emailformaterror', 'error', ['email' => $this->parse_data['INVALID_EMAIL']]);
        }

        if (($max_recipients = (int) $this->rcmail->config->get('max_recipients')) > 0) {
            if ($this->parse_data['RECIPIENT_COUNT'] > $max_recipients) {
                return $this->options['error_handler']('toomanyrecipients', 'error', ['max' => $max_recipients]);
            }
        }

        if (empty($mailto) && !empty($mailcc)) {
            $mailto = $mailcc;
            $mailcc = null;
        }
        else if (empty($mailto)) {
            $mailto = 'undisclosed-recipients:;';
        }

        $dont_override = (array) $this->rcmail->config->get('dont_override');
        $mdn_enabled   = in_array('mdn_default', $dont_override) ? $this->rcmail->config->get('mdn_default') : !empty($_POST['_mdn']);
        $dsn_enabled   = in_array('dsn_default', $dont_override) ? $this->rcmail->config->get('dsn_default') : !empty($_POST['_dsn']);
        $subject       = rcube_utils::get_input_string('_subject', rcube_utils::INPUT_POST, true, $charset);
        $from          = rcube_utils::get_input_string('_from', rcube_utils::INPUT_POST, true, $charset);
        $replyto       = rcube_utils::get_input_string('_replyto', rcube_utils::INPUT_POST, true, $charset);
        $followupto    = rcube_utils::get_input_string('_followupto', rcube_utils::INPUT_POST, true, $charset);
        $from_string   = '';

        // Get sender name and address from identity...
        if (is_numeric($from)) {
            if (is_array($identity_arr = $this->get_identity($from))) {
                if ($identity_arr['mailto']) {
                    $from = $identity_arr['mailto'];
                }
                if ($identity_arr['string']) {
                    $from_string = $identity_arr['string'];
                }
            }
            else {
                $from = null;
            }
        }
        else {
            // ... if there is no identity record, this might be a custom from
            $from_addresses = rcube_mime::decode_address_list($from, null, true, $charset);

            if (count($from_addresses) == 1) {
                $from        = $from_addresses[1]['mailto'];
                $from_string = $from_addresses[1]['string'];
            }
            // ... otherwise it's empty or invalid
            else {
                $from = null;
            }
        }

        // check 'From' address (identity may be incomplete)
        if (empty($this->options['savedraft']) && empty($this->options['saveonly']) && empty($from)) {
            return $this->options['error_handler']('nofromaddress', 'error');
        }

        if (!$from_string && $from) {
            $from_string = $from;
        }

        $from_string = rcube_charset::convert($from_string, RCUBE_CHARSET, $charset);

        if (!empty($this->data['param']['message-id'])) {
            $message_id  = $this->data['param']['message-id'];
        }
        else {
            $message_id = $this->rcmail->gen_message_id($from);
        }

        // Don't allow CRLF in subject (#8404)
        $subject = trim(preg_replace('|\r?\n|', ' ', $subject));

        $this->options['dsn_enabled'] = $dsn_enabled;
        $this->options['from']        = $from;
        $this->options['mailto']      = $mailto;

        // compose headers array
        $headers = [
            'Received'         => $this->header_received(),
            'Date'             => $this->rcmail->user_date(),
            'From'             => $from_string,
            'To'               => $mailto,
            'Cc'               => $mailcc,
            'Bcc'              => $mailbcc,
            'Subject'          => $subject,
            'Reply-To'         => $this->email_input_format($replyto),
            'Mail-Reply-To'    => $this->email_input_format($replyto),
            'Mail-Followup-To' => $this->email_input_format($followupto),
            'In-Reply-To'      => $this->data['reply_msgid'] ?? null,
            'References'       => $this->data['references'] ?? null,
            'User-Agent'       => $this->rcmail->config->get('useragent'),
            'Message-ID'       => $message_id,
            'X-Sender'         => $from,
        ];

        if (!empty($identity_arr['organization'])) {
            $headers['Organization'] = $identity_arr['organization'];
        }

        if ($mdn_enabled) {
            $headers['Disposition-Notification-To'] = $from_string;
        }

        if (!empty($_POST['_priority'])) {
            $priority     = intval($_POST['_priority']);
            $a_priorities = [1 => 'highest', 2 => 'high', 4 => 'low', 5 => 'lowest'];

            if (!empty($a_priorities[$priority])) {
                $headers['X-Priority'] = sprintf("%d (%s)", $priority, ucfirst($a_priorities[$priority]));
            }
        }

        // remember reply/forward UIDs in special headers
        if (!empty($this->options['savedraft'])) {
            $draft_info = [];

            // Note: We ignore <UID>.<PART> forwards/replies here
            if (
                !empty($this->data['reply_uid'])
                && ($uid = $this->data['reply_uid'])
                && !preg_match('/^\d+\.[0-9.]+$/', $uid)
            ) {
                $draft_info['type']   = 'reply';
                $draft_info['uid']    = $uid;
                $draft_info['folder'] = $this->data['mailbox'];
            }
            else if (
                !empty($this->data['forward_uid'])
                && ($uid = rcube_imap_generic::compressMessageSet($this->data['forward_uid']))
                && !preg_match('/^\d+[0-9.]+$/', $uid)
            ) {
                $draft_info['type']   = 'forward';
                $draft_info['uid']    = $uid;
                $draft_info['folder'] = $this->data['mailbox'];
            }

            if ($dsn_enabled) {
                $draft_info['dsn'] = 'on';
            }

            if (!empty($draft_info)) {
                $headers['X-Draft-Info'] = $this->draftinfo_encode($draft_info);
            }
        }

        return array_filter($headers);
    }

    /**
     * Set charset and transfer encoding on the message
     *
     * @param Mail_mime $message Message object
     * @param bool      $flowed  Enable format=flowed
     */
    public function set_message_encoding($message, $flowed = false)
    {
        $text_charset      = $this->options['charset'];
        $transfer_encoding = '7bit';
        $head_encoding     = 'quoted-printable';

        // choose encodings for plain/text body and message headers
        if (preg_match('/ISO-2022/i', $text_charset)) {
            $head_encoding = 'base64'; // RFC1468
        }
        else if (preg_match('/[^\x00-\x7F]/', $message->getTXTBody())) {
            $transfer_encoding = $this->rcmail->config->get('force_7bit') ? 'quoted-printable' : '8bit';
        }
        else if ($this->options['charset'] == 'UTF-8') {
            $text_charset = 'US-ASCII';
        }

        if ($flowed) {
            $text_charset .= ";\r\n format=flowed";
        }

        // encoding settings for mail composing
        $message->setParam('text_encoding', $transfer_encoding);
        $message->setParam('html_encoding', 'quoted-printable');
        $message->setParam('head_encoding', $head_encoding);
        $message->setParam('head_charset', $this->options['charset']);
        $message->setParam('html_charset', $this->options['charset']);
        $message->setParam('text_charset', $text_charset);
    }

    /**
     * Create a message to be saved/sent
     *
     * @param array  $headers     Message headers
     * @param string $body        Message body
     * @param bool   $isHtml      The body is HTML or not
     * @param array  $attachments Optional message attachments array
     *
     * @return Mail_mime Message object
     */
    public function create_message($headers, $body, $isHtml = false, $attachments = [])
    {
        $charset = $this->options['charset'];

        if (!empty($this->options['keepformatting'])) {
            $flowed = false;
        } else {
            $flowed = !empty($this->options['savedraft']) || $this->rcmail->config->get('send_format_flowed', true);
        }

        // create PEAR::Mail_mime instance
        $MAIL_MIME = new Mail_mime("\r\n");

        // Check if we have enough memory to handle the message in it
        // It's faster than using files, so we'll do this if we only can
        if (is_array($attachments)) {
            $memory = 0;
            foreach ($attachments as $attachment) {
                $memory += $attachment['size'];
            }

            // Yeah, Net_SMTP needs up to 12x more memory, 1.33 is for base64
            if (!rcube_utils::mem_check($memory * 1.33 * 12)) {
                $MAIL_MIME->setParam('delay_file_io', true);
            }
        }

        $plugin = $this->rcmail->plugins->exec_hook('message_outgoing_body', [
                'body'    => $body,
                'type'    => $isHtml ? 'html' : 'plain',
                'message' => $MAIL_MIME
        ]);

        // For HTML-formatted messages, construct the MIME message with both
        // the HTML part and the plain-text part
        if ($isHtml) {
            $MAIL_MIME->setHTMLBody($plugin['body']);

            $plain_body = $this->rcmail->html2text($plugin['body'], ['width' => 0, 'charset' => $charset]);
            $plain_body = $this->format_plain_body($plain_body, $flowed);

            // There's no sense to use multipart/alternative if the text/plain
            // part would be blank. Completely blank text/plain part may confuse
            // some mail clients (#5283)
            if (strlen(trim($plain_body)) > 0) {
                $plugin = $this->rcmail->plugins->exec_hook('message_outgoing_body', [
                        'body'    => $plain_body,
                        'type'    => 'alternative',
                        'message' => $MAIL_MIME
                ]);

                // add a plain text version of the e-mail as an alternative part.
                $MAIL_MIME->setTXTBody($plugin['body']);
            }

            // Extract image Data URIs into message attachments (#1488502)
            $this->extract_inline_images($MAIL_MIME, $this->options['from']);
        }
        else {
            $body = $this->format_plain_body($plugin['body'], $flowed);

            $MAIL_MIME->setTXTBody($body, false, true);
        }

        // encoding settings for mail composing
        $this->set_message_encoding($MAIL_MIME, $flowed);

        // pass headers to message object
        $MAIL_MIME->headers($headers);

        return $MAIL_MIME;
    }

    /**
     * Prepare plain text content for the message (format=flowed and wrapping)
     *
     * @param string $body   Plain text message body
     * @param bool   $flowed Enable format=flowed formatting
     *
     * @return string Formatted content
     */
    protected function format_plain_body($body, $flowed = false)
    {
        if (empty($this->options['keepformatting'])) {
            // set line length for body wrapping
            $line_length = $this->rcmail->config->get('line_length', 72);
            $charset     = $this->options['charset'];

            if ($flowed) {
                $body = rcube_mime::format_flowed($body, min($line_length + 2, 79), $charset);
            }
            else {
                $body = rcube_mime::wordwrap($body, $line_length, "\r\n", false, $charset);
            }

            $body = wordwrap($body, 998, "\r\n", true);
        }

        // make sure all line endings are CRLF (#1486712)
        $body = preg_replace('/\r?\n/', "\r\n", $body);

        return $body;
    }

    /**
     * Message delivery, and setting Replied/Forwarded flag on success
     *
     * @param Mail_mime $message    Message object
     * @param bool      $disconnect Close SMTP connection after delivery
     *
     * @return bool True on success, False on failure
     */
    public function deliver_message($message, $disconnect = true)
    {
        // Handle Delivery Status Notification request
        $smtp_opts     = ['dsn' => $this->options['dsn_enabled']];
        $smtp_error    = null;
        $mailbody_file = null;

        $sent = $this->rcmail->deliver_message($message,
            $this->options['from'],
            $this->options['mailto'],
            $smtp_error, $mailbody_file, $smtp_opts, $disconnect
        );

        // return to compose page if sending failed
        if (!$sent) {
            // remove temp file
            if ($mailbody_file) {
                unlink($mailbody_file);
            }

            if ($smtp_error && is_string($smtp_error)) {
                $this->options['error_handler']($smtp_error, 'error');
            }
            else if ($smtp_error && !empty($smtp_error['label'])) {
                $this->options['error_handler']($smtp_error['label'], 'error', $smtp_error['vars']);
            }
            else {
                $this->options['error_handler']('sendingfailed', 'error');
            }

            return false;
        }

        if ($mailbody_file) {
            $this->temp_files[$message->headers()['Message-ID']] = $mailbody_file;
        }

        // save message sent time
        if ($this->options['sendmail_delay']) {
            $this->rcmail->user->save_prefs(['last_message_time' => time()]);
        }

        // Collect recipients' addresses
        $this->collect_recipients($message);

        // set replied/forwarded flag
        if (!empty($this->data['reply_uid'])) {
            foreach (rcmail::get_uids($this->data['reply_uid'], $this->data['mailbox']) as $mbox => $uids) {
                // skip <UID>.<PART> replies
                if (!preg_match('/^\d+\.[0-9.]+$/', implode(',', (array) $uids))) {
                    $this->rcmail->storage->set_flag($uids, 'ANSWERED', $mbox);
                }
            }
        }
        else if (!empty($this->data['forward_uid'])) {
            foreach (rcmail::get_uids($this->data['forward_uid'], $this->data['mailbox']) as $mbox => $uids) {
                // skip <UID>.<PART> forwards
                if (!preg_match('/^\d+\.[0-9.]+$/', implode(',', (array) $uids))) {
                    $this->rcmail->storage->set_flag($uids, 'FORWARDED', $mbox);
                }
            }
        }

        return true;
    }

    /**
     * Save the message into Drafts folder (in savedraft mode)
     * or in Sent mailbox if specified/configured
     *
     * @param Mail_mime $message Message object
     *
     * @return mixed Operation status
     */
    public function save_message($message)
    {
        $store_folder = false;
        $store_target = null;
        $saved        = false;

        // Determine which folder to save message
        if (!empty($this->options['savedraft'])) {
            $store_target = $this->rcmail->config->get('drafts_mbox');
        }
        else if (!$this->rcmail->config->get('no_save_sent_messages')) {
            if (isset($_POST['_store_target'])) {
                $store_target = rcube_utils::get_input_string('_store_target', rcube_utils::INPUT_POST, true);
            }
            else {
                $store_target = $this->rcmail->config->get('sent_mbox');
            }
        }

        if ($store_target) {
            $storage = $this->rcmail->get_storage();

            // check if folder is subscribed
            if ($storage->folder_exists($store_target, true)) {
                $store_folder = true;
            }
            // folder may be existing but not subscribed (#1485241)
            else if (!$storage->folder_exists($store_target)) {
                $store_folder = $storage->create_folder($store_target, true);
            }
            else if ($storage->subscribe($store_target)) {
                $store_folder = true;
            }

            // append message to sent box
            if ($store_folder) {
                // message body in file
                $msg_id = $message->headers()['Message-ID'];

                if ($message->getParam('delay_file_io') && empty($this->temp_files[$msg_id])) {
                    $msg_file = rcube_utils::temp_filename('msg');
                    $msg = $message->saveMessageBody($msg_file);

                    if (!is_a($msg, 'PEAR_Error')) {
                        $this->temp_files[$msg_id] = $msg_file;
                    }
                }

                if (!empty($this->temp_files[$msg_id])) {
                    $msg     = $this->temp_files[$msg_id];
                    $headers = $message->txtHeaders();
                }
                else if (empty($msg)) {
                    $msg     = $message->getMessage();
                    $headers = '';
                }

                if (is_a($msg, 'PEAR_Error')) {
                    rcube::raise_error([
                        'code' => 650, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Could not create message: ".$msg->getMessage()],
                        true, false);
                }
                else {
                    $is_file = !empty($this->temp_files[$msg_id]);
                    $saved   = $storage->save_message($store_target, $msg, $headers, $is_file, ['SEEN']);
                }
            }

            // raise error if saving failed
            if (!$saved) {
                rcube::raise_error(['code' => 800, 'type' => 'imap',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Could not save message in $store_target"], true, false);
            }
        }

        $this->options['store_target'] = $store_target;
        $this->options['store_folder'] = $store_folder;

        return $saved;
    }

    /**
     * If enabled, returns Received header content to be prepended
     * to message headers
     *
     * @return string|null Received header content
     */
    public function header_received()
    {
        if ($this->rcmail->config->get('http_received_header')) {
            $nldlm       = "\r\n\t";
            $http_header = 'from ';

            // FROM/VIA
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $hosts        = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'], 2);
                $http_header .= $this->received_host($hosts[0]) . $nldlm . ' via ';
            }

            $http_header .= $this->received_host($_SERVER['REMOTE_ADDR']);

            // BY
            $http_header .= $nldlm . 'by ' . rcube_utils::server_name('HTTP_HOST');

            // WITH
            $http_header .= $nldlm . 'with HTTP (' . $_SERVER['SERVER_PROTOCOL']
                . ' ' . $_SERVER['REQUEST_METHOD'] . '); ' . date('r');

            return wordwrap($http_header, 69, $nldlm);
        }
    }

    /**
     * Converts host address into host spec. for Received header
     */
    protected function received_host($host)
    {
        $hostname = gethostbyaddr($host);
        $result   = $this->encrypt_host($hostname);

        if ($host != $hostname) {
            $result .= ' (' . $this->encrypt_host($host) . ')';
        }

        return $result;
    }

    /**
     * Encrypt host IP or hostname for Received header
     */
    protected function encrypt_host($host)
    {
        if ($this->rcmail->config->get('http_received_header_encrypt')) {
            return $this->rcmail->encrypt($host);
        }

        if (!preg_match('/[^0-9:.]/', $host)) {
            return "[$host]";
        }

        return $host;
    }

    /**
     * Returns user identity record
     *
     * @param int $id Identity ID
     *
     * @return array|false User identity data, False if there's no such identity
     */
    public function get_identity($id)
    {
        if ($sql_arr = $this->rcmail->user->get_identity($id)) {
            $out = $sql_arr;

            if (!empty($this->options['charset']) && $this->options['charset'] != RCUBE_CHARSET) {
                foreach ($out as $k => $v) {
                    $out[$k] = rcube_charset::convert($v, RCUBE_CHARSET, $this->options['charset']);
                }
            }

            $out['mailto'] = $sql_arr['email'];
            $out['string'] = format_email_recipient($sql_arr['email'], $sql_arr['name']);

            return $out;
        }

        return false;
    }

    /**
     * Extract image attachments from HTML message (data URIs)
     *
     * @param Mail_mime $message Message object
     * @param string    $from    Sender email address
     */
    public static function extract_inline_images($message, $from)
    {
        $body   = $message->getHTMLBody();
        $offset = 0;
        $list   = [];
        $domain = 'localhost';
        $regexp = '#img[^>]+src=[\'"](data:([^;]*);base64,([a-z0-9+/=\r\n]+))([\'"])#i';

        if (preg_match_all($regexp, $body, $matches, PREG_OFFSET_CAPTURE)) {
            // get domain for the Content-ID, must be the same as in Mail_Mime::get()
            if (preg_match('#@([0-9a-zA-Z\-\.]+)#', $from, $m)) {
                $domain = $m[1];
            }

            foreach ($matches[1] as $idx => $m) {
                $data = preg_replace('/\r\n/', '', $matches[3][$idx][0]);
                $data = base64_decode($data);

                if (empty($data)) {
                    continue;
                }

                $hash      = md5($data) . '@' . $domain;
                $mime_type = $matches[2][$idx][0];

                if (empty($mime_type)) {
                    $mime_type = rcube_mime::image_content_type($data);
                }

                // add the image to the MIME message
                if (empty($list[$hash])) {
                    $ext         = preg_replace('#^[^/]+/#', '', $mime_type);
                    $name        = substr($hash, 0, 8) . '.' . $ext;
                    $list[$hash] = $name;

                    $message->addHTMLImage($data, $mime_type, $name, false, $hash);
                }

                $name = $list[$hash];
                $body = substr_replace($body, $name, $m[1] + $offset, strlen($m[0]));
                $offset += strlen($name) - strlen($m[0]);
            }
        }

        $message->setHTMLBody($body);
    }

    /**
     * Parse and cleanup email address input (and count addresses)
     *
     * @param string $mailto Address input
     * @param bool   $count  Do count recipients (count saved in $this->parse_data['RECIPIENT_COUNT'])
     * @param bool   $check  Validate addresses (errors saved in $this->parse_data['INVALID_EMAIL'])
     *
     * @return string Canonical recipients string (comma separated)
     */
    public function email_input_format($mailto, $count = false, $check = true)
    {
        if (!isset($this->parse_data['RECIPIENT_COUNT'])) {
            $this->parse_data['RECIPIENT_COUNT'] = 0;
        }

        if (empty($mailto)) {
            return '';
        }

        // convert to UTF-8 to preserve \x2c(,) and \x3b(;) used in ISO-2022-JP;
        if ($charset = $this->options['charset']) {
            if ($charset != RCUBE_CHARSET) {
                $mailto = rcube_charset::convert($mailto, $charset, RCUBE_CHARSET);
            }
            if (preg_match('/ISO-2022/i', $charset)) {
                $use_base64 = true;
            }
        }

        // simplified email regexp, supporting quoted local part
        $email_regexp = '(\S+|("[^"]+"))@\S+';

        $delim   = ',;';
        $regexp  = ["/[$delim]\s*[\r\n]+/", '/[\r\n]+/', "/[$delim]\s*\$/m", '/;/', '/(\S{1})(<'.$email_regexp.'>)/U'];
        $replace = [', ', ', ', '', ',', '\\1 \\2'];

        // replace new lines and strip ending ', ', make address input more valid
        $mailto = trim(preg_replace($regexp, $replace, $mailto));
        $items  = rcube_utils::explode_quoted_string("[$delim]", $mailto);
        $result = [];

        foreach ($items as $item) {
            $item = trim($item);
            // address in brackets without name (do nothing)
            if (preg_match('/^<'.$email_regexp.'>$/', $item)) {
                $item     = rcube_utils::idn_to_ascii(trim($item, '<>'));
                $result[] = $item;
            }
            // address without brackets and without name (add brackets)
            else if (preg_match('/^'.$email_regexp.'$/', $item)) {
                // Remove trailing non-letter characters (#7899)
                $item     = preg_replace('/[^[:alnum:]]+$/u', '', $item);
                $item     = rcube_utils::idn_to_ascii($item);
                $result[] = $item;
            }
            // address with name (handle name)
            else if (preg_match('/<*'.$email_regexp.'>*$/', $item, $matches)) {
                $address = $matches[0];
                $name    = trim(str_replace($address, '', $item));
                if ($name[0] == '"' && $name[strlen($name)-1] == '"') {
                    $name = substr($name, 1, -1);
                }

                // encode "name" field
                if (!empty($use_base64)) {
                    $name = rcube_charset::convert($name, RCUBE_CHARSET, $charset);
                    $name = Mail_mimePart::encodeMB($name, $charset, 'base64');
                }
                else {
                    $name = stripcslashes($name);
                }

                $address  = rcube_utils::idn_to_ascii(trim($address, '<>'));
                $result[] = format_email_recipient($address, $name);
                $item     = $address;
            }

            // check address format
            $item = trim($item, '<>');
            if ($item && $check && !rcube_utils::check_email($item)) {
                $this->parse_data['INVALID_EMAIL'] = $item;
                return;
            }
        }

        if ($count) {
            $this->parse_data['RECIPIENT_COUNT'] += count($result);
        }

        return implode(', ', $result);
    }

    /**
     * Returns configured generic message footer
     *
     * @param bool $isHtml Return HTML or Plain text version of the footer?
     *
     * @return string|null Footer content
     */
    public function generic_message_footer($isHtml)
    {
        if ($isHtml && ($file = $this->rcmail->config->get('generic_message_footer_html'))) {
            $html_footer = true;
        }
        else {
            $file = $this->rcmail->config->get('generic_message_footer');
            $html_footer = false;
        }

        if ($file && realpath($file)) {
            // sanity check
            if (!preg_match('/\.(php|ini|conf)$/', $file) && strpos($file, '/etc/') === false) {
                $footer = file_get_contents($file);
                if ($isHtml && !$html_footer) {
                    $t2h    = new rcube_text2html($footer, false);
                    $footer = $t2h->get_html();
                }

                if (!empty($this->options['charset']) && $this->options['charset'] != RCUBE_CHARSET) {
                    $footer = rcube_charset::convert($footer, RCUBE_CHARSET, $this->options['charset']);
                }

                return $footer;
            }
        }
    }

    /**
     * Encode data array into a string for use in X-Draft-Info header
     *
     * @param array $data Data array
     *
     * @return string Decoded data as a string
     */
    public static function draftinfo_encode($data)
    {
        $parts = [];

        foreach ($data as $key => $val) {
            $encode  = $key == 'folder' || strpos($val, ';') !== false;
            $parts[] = $key . '=' . ($encode ? 'B::' . base64_encode($val) : $val);
        }

        return implode('; ', $parts);
    }

    /**
     * Decode X-Draft-Info header value into an array
     *
     * @param string $str Encoded data string (see self::draftinfo_encode())
     *
     * @return array Decoded data
     */
    public static function draftinfo_decode($str)
    {
        $info = [];

        foreach (preg_split('/;\s+/', $str) as $part) {
            list($key, $val) = explode('=', $part, 2);
            if (strpos($val, 'B::') === 0) {
                $val = base64_decode(substr($val, 3));
            }
            else if ($key == 'folder') {
                $val = base64_decode($val);
            }

            $info[$key] = $val;
        }

        return $info;
    }

    /**
     * Header (From, To, Cc, etc.) input object for templates
     */
    public function headers_output($attrib)
    {
        list($form_start,) = $this->form_tags($attrib);

        $out          = '';
        $part         = strtolower($attrib['part']);
        $fname        = null;
        $field_type   = null;
        $allow_attrib = [];
        $param        = $part;

        switch ($part) {
        case 'from':
            return $form_start . $this->compose_header_from($attrib);

        case 'to':
        case 'cc':
        case 'bcc':
            $fname  = '_' . $part;

            $allow_attrib = ['id', 'class', 'style', 'cols', 'rows', 'tabindex'];
            $field_type   = 'html_textarea';
            break;

        case 'replyto':
        case 'reply-to':
            $fname  = '_replyto';
            $param  = 'replyto';

        case 'followupto':
        case 'followup-to':
            if (!$fname) {
                $fname  = '_followupto';
                $param  = 'followupto';
            }

            $allow_attrib = ['id', 'class', 'style', 'size', 'tabindex'];
            $field_type   = 'html_inputfield';
            break;
        }

        if ($fname && $field_type) {
            // pass the following attributes to the form class
            $field_attrib = ['name' => $fname, 'spellcheck' => 'false'];
            foreach ($attrib as $attr => $value) {
                if (stripos($attr, 'data-') === 0 || in_array($attr, $allow_attrib)) {
                    $field_attrib[$attr] = $value;
                }
            }

            $mode = $this->data['mode'] ?? null;

            // create textarea object
            $input = new $field_type($field_attrib);
            $out   = $input->show($this->compose_header_value($param, $mode));
        }

        if ($form_start) {
            $out = $form_start . $out;
        }

        // configure autocompletion
        rcmail_action::autocomplete_init();

        return $out;
    }

    /**
     * Returns From header input element
     */
    protected function compose_header_from($attrib)
    {
        // pass the following attributes to the form class
        $field_attrib = ['name' => '_from'];
        foreach ($attrib as $attr => $value) {
            if (in_array($attr, ['id', 'class', 'style', 'size', 'tabindex'])) {
                $field_attrib[$attr] = $value;
            }
        }

        if (!empty($this->data['identities'])) {
            $a_signatures = [];
            $identities   = [];
            $top_posting  = intval($this->rcmail->config->get('reply_mode')) > 0
                && !$this->rcmail->config->get('sig_below')
                && ($this->data['mode'] == self::MODE_REPLY || $this->data['mode'] == self::MODE_FORWARD);

            $separator     = $top_posting ? '---' : '-- ';
            $add_separator = (bool) $this->rcmail->config->get('sig_separator');

            $field_attrib['onchange'] = rcmail_output::JS_OBJECT_NAME . ".change_identity(this)";
            $select_from = new html_select($field_attrib);

            // create SELECT element
            foreach ($this->data['identities'] as $sql_arr) {
                $identity_id = $sql_arr['identity_id'];
                $select_from->add(format_email_recipient($sql_arr['email'], $sql_arr['name']), $identity_id);

                // add signature to array
                if (!empty($sql_arr['signature']) && empty($this->data['param']['nosig'])) {
                    $text = $html = $sql_arr['signature'];

                    if ($sql_arr['html_signature']) {
                        $text = $this->rcmail->html2text($html, ['links' => false]);
                        $text = trim($text, "\r\n");
                    }
                    else {
                        $t2h  = new rcube_text2html($text, false);
                        $html = $t2h->get_html();
                    }

                    if ($add_separator && !preg_match('/^--[ -]\r?\n/m', $text)) {
                        $text = $separator . "\n" . ltrim($text, "\r\n");
                        $html = $separator . "<br>" . $html;
                    }

                    $a_signatures[$identity_id]['text'] = $text;
                    $a_signatures[$identity_id]['html'] = $html;
                }

                // add bcc and reply-to
                if (!empty($sql_arr['reply-to'])) {
                    $identities[$identity_id]['replyto'] = $sql_arr['reply-to'];
                }
                if (!empty($sql_arr['bcc'])) {
                    $identities[$identity_id]['bcc'] = $sql_arr['bcc'];
                }

                $identities[$identity_id]['email'] = $sql_arr['email'];
            }

            $out = $select_from->show($this->data['from'] ?? null);

            // add signatures to client
            $this->rcmail->output->set_env('signatures', $a_signatures);
            $this->rcmail->output->set_env('identities', $identities);
        }
        // no identities, display text input field
        else {
            $field_attrib['class'] = 'from_address';
            $input_from = new html_inputfield($field_attrib);
            $out = $input_from->show($this->data['from'] ?? null);
        }

        return $out;
    }

    /**
     * Set the value of specified header depending on compose mode
     */
    protected function compose_header_value($header, $mode)
    {
        $fvalue        = '';
        $decode_header = true;
        $message       = $this->options['message'];
        $charset       = !empty($message->headers) ? $message->headers->charset : RCUBE_CHARSET;
        $separator     = ', ';

        if (!isset($this->data['recipients'])) {
            $this->data['recipients'] = [];
        }

        // we have a set of recipients stored is session
        if (
            $header == 'to'
            && !empty($this->data['param']['mailto'])
            && ($mailto_id = $this->data['param']['mailto'])
            && !empty($_SESSION['mailto'][$mailto_id])
        ) {
            $fvalue        = urldecode($_SESSION['mailto'][$mailto_id]);
            $decode_header = false;
            $charset       = $this->rcmail->output->charset;

            // make session to not grow up too much
            $this->rcmail->session->remove("mailto.$mailto_id");
        }
        else if (!empty($_POST['_' . $header])) {
            $fvalue  = rcube_utils::get_input_string('_' . $header, rcube_utils::INPUT_POST, true);
            $charset = $this->rcmail->output->charset;
        }
        else if (!empty($this->data['param'][$header])) {
            $fvalue  = $this->data['param'][$header];
            $charset = $this->rcmail->output->charset;
        }
        else if ($mode == self::MODE_REPLY) {
            // get recipient address(es) out of the message headers
            if ($header == 'to') {
                $mailfollowup = $message->headers->others['mail-followup-to'] ?? [];
                $mailreplyto  = $message->headers->others['mail-reply-to'] ?? [];
                $reply_all    = $this->data['reply_all'] ?? null;

                // Reply to mailing list...
                if ($reply_all == 'list' && $mailfollowup) {
                    $fvalue = $mailfollowup;
                }
                else if ($reply_all == 'list'
                    && preg_match('/<mailto:([^>]+)>/i', $message->headers->others['list-post'], $m)
                ) {
                    $fvalue = $m[1];
                }
                // Reply to...
                else if ($reply_all && $mailfollowup) {
                    $fvalue = $mailfollowup;
                }
                else if ($mailreplyto) {
                    $fvalue = $mailreplyto;
                }
                else if (!empty($message->headers->replyto)) {
                    $fvalue  = $message->headers->replyto;
                    $replyto = true;
                }
                else if (!empty($message->headers->from)) {
                    $fvalue = $message->headers->from;
                }

                // Reply to message sent by yourself (#1487074, #1489230, #1490439)
                // Reply-To address need to be unset (#1490233)
                if (!empty($this->data['ident']) && empty($replyto)) {
                    foreach ([$fvalue, $message->get_header('from')] as $sender) {
                        $senders = rcube_mime::decode_address_list($sender, null, false, $charset, true);

                        if (in_array($this->data['ident']['email_ascii'], $senders)) {
                            $fvalue = $message->headers->to;
                            break;
                        }
                    }
                }
            }
            // add recipient of original message if reply to all
            else if ($header == 'cc' && !empty($this->data['reply_all']) && $this->data['reply_all'] != 'list') {
                if ($v = $message->headers->to) {
                    $fvalue .= $v;
                }
                if ($v = $message->headers->cc) {
                    $fvalue .= (!empty($fvalue) ? $separator : '') . $v;
                }

                // Deliberately ignore 'Sender' header (#6506)

                // When To: and Reply-To: are the same we add From: address to the list (#1489037)
                if ($v = $message->headers->from) {
                    $to      = $message->headers->to;
                    $replyto = $message->headers->replyto;
                    $from    = rcube_mime::decode_address_list($v, null, false, $charset, true);
                    $to      = rcube_mime::decode_address_list($to, null, false, $charset, true);
                    $replyto = rcube_mime::decode_address_list($replyto, null, false, $charset, true);

                    if (!empty($replyto) && !count(array_diff($to, $replyto)) && count(array_diff($from, $to))) {
                        $fvalue .= (!empty($fvalue) ? $separator : '') . $v;
                    }
                }
            }
        }
        else if (in_array($mode, [self::MODE_DRAFT, self::MODE_EDIT])) {
            // get drafted headers
            if ($header == 'to' && !empty($message->headers->to)) {
                $fvalue = $message->get_header('to', true);
            }
            else if ($header == 'cc' && !empty($message->headers->cc)) {
                $fvalue = $message->get_header('cc', true);
            }
            else if ($header == 'bcc' && !empty($message->headers->bcc)) {
                $fvalue = $message->get_header('bcc', true);
            }
            else if ($header == 'replyto' && !empty($message->headers->others['mail-reply-to'])) {
                $fvalue = $message->get_header('mail-reply-to');
            }
            else if ($header == 'replyto' && !empty($message->headers->replyto)) {
                $fvalue = $message->get_header('reply-to');
            }
            else if ($header == 'followupto' && !empty($message->headers->others['mail-followup-to'])) {
                $fvalue = $message->get_header('mail-followup-to');
            }
        }

        // split recipients and put them back together in a unique way
        if (!empty($fvalue) && in_array($header, ['to', 'cc', 'bcc'])) {
            $from_email   = !empty($this->data['ident']['email']) ? mb_strtolower($this->data['ident']['email']) : '';
            $to_addresses = rcube_mime::decode_address_list($fvalue, null, $decode_header, $charset);
            $fvalue       = [];

            foreach ($to_addresses as $addr_part) {
                if (empty($addr_part['mailto'])) {
                    continue;
                }

                // According to RFC5321 local part of email address is case-sensitive
                // however, here it is better to compare addresses in case-insensitive manner
                $mailto    = format_email(rcube_utils::idn_to_utf8($addr_part['mailto']));
                $mailto_lc = mb_strtolower($addr_part['mailto']);

                if (
                    ($header == 'to' || $mode != self::MODE_REPLY || $mailto_lc != $from_email)
                    && !in_array($mailto_lc, $this->data['recipients'])
                ) {
                    if ($addr_part['name'] && $mailto != $addr_part['name']) {
                        $mailto = format_email_recipient($mailto, $addr_part['name']);
                    }

                    $fvalue[] = $mailto;
                    $this->data['recipients'][] = $mailto_lc;
                }
            }

            $fvalue = implode($separator, $fvalue);
        }

        return $fvalue;
    }

    /**
     * Creates reply subject by removing common subject
     * prefixes/suffixes from the original message subject
     *
     * @param string $subject Subject string
     *
     * @return string Modified subject string
     */
    public static function reply_subject($subject)
    {
        $subject = trim($subject);

        //  Add config options for subject prefixes (#7929) 
        $subject = rcube_utils::remove_subject_prefix($subject, 'reply');
        $subject = rcmail::get_instance()->config->get('response_prefix', 'Re:') . ' ' . $subject;

        return trim($subject);
    }

    /**
     * Subject input object for templates
     *
     * @param array $attrib Object attributes
     *
     * @return string HTML content
     */
    public function compose_subject($attrib)
    {
        list($form_start, $form_end) = $this->form_tags($attrib);
        unset($attrib['form']);

        $attrib['name']       = '_subject';
        $attrib['spellcheck'] = 'true';

        $textfield = new html_inputfield($attrib);
        $subject   = '';

        // use subject from post
        if (isset($_POST['_subject'])) {
            $subject = rcube_utils::get_input_string('_subject', rcube_utils::INPUT_POST, TRUE);
        }
        else if (!empty($this->data['param']['subject'])) {
            $subject = $this->data['param']['subject'];
        }
        // create a reply-subject
        else if ($this->data['mode'] == self::MODE_REPLY) {
            $subject = self::reply_subject($this->options['message']->subject);
        }
        // create a forward-subject
        else if ($this->data['mode'] == self::MODE_FORWARD) {
            //  Add config options for subject prefixes (#7929) 
            $subject = rcube_utils::remove_subject_prefix($this->options['message']->subject, 'forward');
            $subject = trim($this->rcmail->config->get('forward_prefix', 'Fwd:') . ' ' . $subject);
        }
        // create a draft-subject
        else if ($this->data['mode'] == self::MODE_DRAFT || $this->data['mode'] == self::MODE_EDIT) {
            $subject = $this->options['message']->subject;
        }

        $out = $form_start ? "$form_start\n" : '';
        $out .= $textfield->show($subject);
        $out .= $form_end ? "\n$form_end" : '';

        return $out;
    }

    /**
     * Returns compose form tag (if not used already)
     *
     * @param array $attrib Form attributes
     */
    public function form_tags($attrib)
    {
        if (isset($attrib['noform']) && rcube_utils::get_boolean((string) $attrib['noform'])) {
            return ['', ''];
        }

        $form_start = '';
        if (!$this->message_form) {
            $hiddenfields = new html_hiddenfield(['name' => '_task', 'value' => $this->rcmail->task]);
            $hiddenfields->add(['name' => '_action', 'value' => 'send']);
            $hiddenfields->add(['name' => '_id', 'value' => $this->data['id'] ?? '']);
            $hiddenfields->add(['name' => '_attachments']);

            if (empty($attrib['form'])) {
                $form_attr  = [
                    'name'   => 'form',
                    'method' => 'post',
                    'class'  => !empty($attrib['class']) ? $attrib['class'] : '',
                ];
                $form_start = $this->rcmail->output->form_tag($form_attr);
            }

            $form_start .= $hiddenfields->show();
        }

        $form_end  = ($this->message_form && empty($attrib['form'])) ? '</form>' : '';
        $form_name = !empty($attrib['form']) ? $attrib['form'] : 'form';

        if (!$this->message_form) {
            $this->rcmail->output->add_gui_object('messageform', $form_name);
        }

        $this->message_form = $form_name;

        return [$form_start, $form_end];
    }

    /**
     * Returns compose form "head"
     */
    public function form_head($attrib)
    {
        list($form_start,) = $this->form_tags($attrib);

        return $form_start;
    }

    /**
     * Folder selector object for templates
     *
     * @param array $attrib Object attributes
     *
     * @return string HTML content
     */
    public function folder_selector($attrib)
    {
        if (isset($_POST['_store_target'])) {
            $mbox = $_POST['_store_target'];
        }
        else {
            $mbox = $this->data['param']['sent_mbox'] ?? null;
        }

        $params = [
            'noselection'   => '- ' . $this->rcmail->gettext('dontsave') . ' -',
            'folder_filter' => 'mail',
            'folder_rights' => 'w',
        ];

        $attrib['name'] = '_store_target';
        $select = rcmail_action::folder_selector(array_merge($attrib, $params));

        return $select->show($mbox, $attrib);
    }

    /**
     * Mail Disposition Notification checkbox object for templates
     *
     * @param array $attrib Object attributes
     *
     * @return string HTML content
     */
    public function mdn_checkbox($attrib)
    {
        list($form_start, $form_end) = $this->form_tags($attrib);
        unset($attrib['form']);

        if (empty($attrib['id'])) {
            $attrib['id'] = 'receipt';
        }

        $attrib['name']  = '_mdn';
        $attrib['value'] = '1';

        $checkbox = new html_checkbox($attrib);

        if (isset($_POST['_mdn'])) {
            $mdn_default = $_POST['_mdn'];
        }
        else if (in_array($this->data['mode'], [self::MODE_DRAFT, self::MODE_EDIT])) {
            $mdn_default = !empty($this->options['message']->headers->mdn_to);
        }
        else {
            $mdn_default = $this->rcmail->config->get('mdn_default');
        }

        $out = $form_start ? "$form_start\n" : '';
        $out .= $checkbox->show($mdn_default);
        $out .= $form_end ? "\n$form_end" : '';

        return $out;
    }

    /**
     * Delivery Status Notification checkbox object for templates
     *
     * @param array $attrib Object attributes
     *
     * @return string HTML content
     */
    public function dsn_checkbox($attrib)
    {
        list($form_start, $form_end) = $this->form_tags($attrib);
        unset($attrib['form']);

        if (empty($attrib['id'])) {
            $attrib['id'] = 'dsn';
        }

        $attrib['name']  = '_dsn';
        $attrib['value'] = '1';

        $checkbox = new html_checkbox($attrib);

        if (!empty($_POST['_dsn']) || !empty($this->options['dsn_enabled'])) {
            $dsn_value = 1;
        }
        else {
            $dsn_value = $this->rcmail->config->get('dsn_default');
        }

        $out = $form_start ? "$form_start\n" : '';
        $out .= $checkbox->show($dsn_value);
        $out .= $form_end ? "\n$form_end" : '';

        return $out;
    }

    /**
     * "Keep formatting" checkbox object for templates
     *
     * @param array $attrib Object attributes
     *
     * @return string HTML content
     */
    public function keep_formatting_checkbox($attrib)
    {
        list($form_start, $form_end) = $this->form_tags($attrib);
        unset($attrib['form']);

        if (empty($attrib['id'])) {
            $attrib['id'] = '_keepformatting';
        }

        $attrib['name']  = '_keepformatting';
        $attrib['value'] = '1';

        $checkbox = new html_checkbox($attrib);

        $out = $form_start ? "$form_start\n" : '';
        $out .= $checkbox->show();
        $out .= $form_end ? "\n$form_end" : '';

        return $out;
    }

    /**
     * Priority selector object for templates
     *
     * @param array $attrib Object attributes
     *
     * @return string HTML content
     */
    public function priority_selector($attrib)
    {
        list($form_start, $form_end) = $this->form_tags($attrib);
        unset($attrib['form']);

        $attrib['name'] = '_priority';
        $prio_list = [
            $this->rcmail->gettext('lowest')  => 5,
            $this->rcmail->gettext('low')     => 4,
            $this->rcmail->gettext('normal')  => 0,
            $this->rcmail->gettext('high')    => 2,
            $this->rcmail->gettext('highest') => 1,
        ];

        $selector = new html_select($attrib);
        $selector->add(array_keys($prio_list), array_values($prio_list));

        if (isset($_POST['_priority'])) {
            $sel = (int) $_POST['_priority'];
        }
        else if (isset($this->options['message']->headers->priority)
            && intval($this->options['message']->headers->priority) != 3
        ) {
            $sel = (int) $this->options['message']->headers->priority;
        }
        else {
            $sel = 0;
        }

        $out = $form_start ? "$form_start\n" : '';
        $out .= $selector->show((int) $sel);
        $out .= $form_end ? "\n$form_end" : '';

        return $out;
    }

    /**
     * Helper to create Sent folder if it does not exists
     *
     * @param string $folder Folder name to check
     * @param bool   $create Create if does not exist
     *
     * @return bool True if the folder exists, False otherwise
     */
    public static function check_sent_folder($folder, $create = false)
    {
        $rcmail = rcmail::get_instance();

        // we'll not save the message, so it doesn't matter
        if ($rcmail->config->get('no_save_sent_messages')) {
            return true;
        }

        if ($rcmail->storage->folder_exists($folder, true)) {
            return true;
        }

        // folder may exist but isn't subscribed (#1485241)
        if ($create) {
            if (!$rcmail->storage->folder_exists($folder)) {
                return $rcmail->storage->create_folder($folder, true);
            }
            else {
                return $rcmail->storage->subscribe($folder);
            }
        }

        return false;
    }

    /**
     * Initialize mail compose UI elements
     */
    protected function compose_init($message)
    {
        // get user's identities
        $this->data['identities'] = $this->rcmail->user->list_identities(null, true);

        // Set From field value
        if (!empty($_POST['_from'])) {
            $this->data['from'] = rcube_utils::get_input_string('_from', rcube_utils::INPUT_POST);
        }
        else if (!empty($this->data['param']['from'])) {
            $this->data['from'] = $this->data['param']['from'];
        }
        else if (!empty($this->data['identities'])) {
            $ident = self::identity_select($message, $this->data['identities'], $this->data['mode']);

            $this->data['from']  = $ident['identity_id'];
            $this->data['ident'] = $ident;
        }

        $this->rcmail->output->add_handlers([
                'storetarget'      => [$this, 'folder_selector'],
                'composeheaders'   => [$this, 'headers_output'],
                'composesubject'   => [$this, 'compose_subject'],
                'priorityselector' => [$this, 'priority_selector'],
                'mdncheckbox'      => [$this, 'mdn_checkbox'],
                'dsncheckbox'      => [$this, 'dsn_checkbox'],
                'keepformattingcheckbox' => [$this, 'keep_formatting_checkbox'],
                'composeformhead'  => [$this, 'form_head'],
        ]);

        // add some labels to client
        $this->rcmail->output->add_label('nosubject', 'nosenderwarning', 'norecipientwarning',
            'nosubjectwarning', 'cancel', 'nobodywarning', 'notsentwarning', 'savingmessage',
            'sendingmessage', 'searching', 'disclosedrecipwarning', 'disclosedreciptitle',
            'bccinstead', 'nosubjecttitle', 'sendmessage');

        $this->rcmail->output->set_env('max_disclosed_recipients', (int) $this->rcmail->config->get('max_disclosed_recipients', 5));
    }

    /**
     * Detect recipient identity from specified message
     *
     * @param rcube_message $message    Message object
     * @param array         $identities User identities (if NULL all user identities will be used)
     * @param string        $mode       Composing mode (see self::MODE_*)
     *
     * @return array Selected user identity (or the default identity) data
     */
    public static function identity_select($message, $identities = null, $mode = null)
    {
        $a_recipients = [];
        $a_names      = [];

        if ($identities === null) {
            $identities = rcmail::get_instance()->user->list_identities(null, true);
        }

        if (!$mode) {
            $mode = self::MODE_REPLY;
        }

        // extract all recipients of the reply-message
        if (!empty($message->headers)) {
            $charset = $message->headers->charset;

            if (in_array($mode, [self::MODE_REPLY, self::MODE_FORWARD])) {
                $a_to = rcube_mime::decode_address_list($message->headers->to, null, true, $charset);
                foreach ($a_to as $addr) {
                    if (!empty($addr['mailto'])) {
                        $a_recipients[] = strtolower($addr['mailto']);
                        $a_names[]      = $addr['name'];
                    }
                }

                if (!empty($message->headers->cc)) {
                    $a_cc = rcube_mime::decode_address_list($message->headers->cc, null, true, $charset);
                    foreach ($a_cc as $addr) {
                        if (!empty($addr['mailto'])) {
                            $a_recipients[] = strtolower($addr['mailto']);
                            $a_names[]      = $addr['name'];
                        }
                    }
                }
            }

            // decode From: address
            if (!empty($message->headers)) {
                $from = array_first(rcube_mime::decode_address_list($message->headers->from, null, true, $charset));
                $from['mailto'] = isset($from['mailto']) ? strtolower($from['mailto']) : '';
            }
        }

        if (empty($from)) {
            $from = ['mailto' => ''];
        }

        $from_idx   = null;
        $found_idx  = ['to' => null, 'from' => null];
        $check_from = in_array($mode, [self::MODE_DRAFT, self::MODE_EDIT, self::MODE_REPLY]);

        // Select identity
        foreach ($identities as $idx => $ident) {
            // use From: header when in edit/draft or reply-to-self
            if ($check_from && $from['mailto'] == strtolower($ident['email_ascii'])) {
                // remember first matching identity address
                if ($found_idx['from'] === null) {
                    $found_idx['from'] = $idx;
                }
                // match identity name
                if ($from['name'] && $ident['name'] && $from['name'] == $ident['name']) {
                    $from_idx = $idx;
                    break;
                }
            }

            // use replied/forwarded message recipients
            if (($found = array_search(strtolower($ident['email_ascii']), $a_recipients)) !== false) {
                // remember first matching identity address
                if ($found_idx['to'] === null) {
                    $found_idx['to'] = $idx;
                }
                // match identity name
                if ($a_names[$found] && $ident['name'] && $a_names[$found] == $ident['name']) {
                    $from_idx = $idx;
                    break;
                }
            }
        }

        // If matching by name+address didn't find any matches,
        // get first found identity (address) if any
        if ($from_idx === null) {
            $from_idx = $found_idx['to'] !== null ? $found_idx['to'] : $found_idx['from'];
        }

        // Try Return-Path
        if ($from_idx === null && !empty($message->headers->others['return-path'])) {
            $return_path = $message->headers->others['return-path'];
            $return_path = array_map('strtolower', (array) $return_path);

            foreach ($identities as $idx => $ident) {
                // Return-Path header contains an email address, but on some mailing list
                // it can be e.g. <pear-dev-return-55250-local=domain.tld@lists.php.net>
                // where local@domain.tld is the address we're looking for (#1489241)
                $ident1 = strtolower($ident['email_ascii']);
                $ident2 = str_replace('@', '=', $ident1);
                $ident1 = '<' . $ident1 . '>';
                $ident2 = '-' . $ident2 . '@';

                foreach ($return_path as $path) {
                    if ($path == $ident1 || stripos($path, $ident2)) {
                        $from_idx = $idx;
                        break 2;
                    }
                }
            }
        }

        // See identity_select plugin for example usage of this hook
        $plugin = rcmail::get_instance()->plugins->exec_hook('identity_select', [
                'message'    => $message,
                'identities' => $identities,
                'selected'   => $from_idx
        ]);

        $selected = $plugin['selected'];

        // default identity is always first on the list
        if ($selected === null) {
            $selected = 0;
        }

        return $identities[$selected] ?? null;
    }

    /**
     * Collect message recipients' addresses
     *
     * @param Mail_Mime $message The email message
     */
    public static function collect_recipients($message)
    {
        $rcmail = rcube::get_instance();

        // Find the addressbook source
        $collected_recipients = $rcmail->config->get('collected_recipients');

        if (!strlen($collected_recipients)) {
            return;
        }

        $source = $rcmail->get_address_book($collected_recipients);

        if (!$source) {
            return;
        }

        $headers = $message->headers();

        // extract recipients
        $recipients = (array) $headers['To'];

        if (!empty($headers['Cc'])) {
            $recipients[] = $headers['Cc'];
        }

        if (!empty($headers['Bcc'])) {
            $recipients[] = $headers['Bcc'];
        }

        $addresses = rcube_mime::decode_address_list($recipients);
        $type      = rcube_addressbook::TYPE_DEFAULT | rcube_addressbook::TYPE_RECIPIENT;

        foreach ($addresses as $address) {
            $contact = [
                'name'  => $address['name'],
                'email' => $address['mailto'],
            ];

            if (!$rcmail->contact_exists($contact['email'], $type)) {
                $rcmail->contact_create($contact, $source);
            }
        }
    }
}
