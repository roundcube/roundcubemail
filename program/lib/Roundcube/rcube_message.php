<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2008-2010, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Logical representation of a mail message with all its data          |
 |   and related functions                                               |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Logical representation of a mail message with all its data
 * and related functions
 *
 * @package    Framework
 * @subpackage Storage
 * @author     Thomas Bruederli <roundcube@gmail.com>
 */
class rcube_message
{
    /**
     * Instace of framework class.
     *
     * @var rcube
     */
    private $app;

    /**
     * Instance of storage class
     *
     * @var rcube_storage
     */
    private $storage;

    /**
     * Instance of mime class
     *
     * @var rcube_mime
     */
    private $mime;
    private $opt = array();
    private $parse_alternative = false;

    public $uid;
    public $folder;
    public $headers;
    public $parts = array();
    public $mime_parts = array();
    public $inline_parts = array();
    public $attachments = array();
    public $subject = '';
    public $sender = null;
    public $is_safe = false;


    /**
     * __construct
     *
     * Provide a uid, and parse message structure.
     *
     * @param string $uid    The message UID.
     * @param string $folder Folder name
     *
     * @see self::$app, self::$storage, self::$opt, self::$parts
     */
    function __construct($uid, $folder = null)
    {
        $this->uid  = $uid;
        $this->app  = rcube::get_instance();
        $this->storage = $this->app->get_storage();
        $this->folder  = strlen($folder) ? $folder : $this->storage->get_folder();
        $this->storage->set_options(array('all_headers' => true));

        // Set current folder
        $this->storage->set_folder($this->folder);

        $this->headers = $this->storage->get_message($uid);

        if (!$this->headers) {
            return;
        }

        $this->mime = new rcube_mime($this->headers->charset);

        $this->subject = $this->headers->get('subject');
        list(, $this->sender) = each($this->mime->decode_address_list($this->headers->from, 1));

        $this->set_safe((intval($_GET['_safe']) || $_SESSION['safe_messages'][$this->folder.':'.$uid]));
        $this->opt = array(
            'safe' => $this->is_safe,
            'prefer_html' => $this->app->config->get('prefer_html'),
            'get_url' => $this->app->url(array(
                'action' => 'get',
                'mbox'   => $this->storage->get_folder(),
                'uid'    => $uid))
        );

        if (!empty($this->headers->structure)) {
            $this->get_mime_numbers($this->headers->structure);
            $this->parse_structure($this->headers->structure);
        }
        else {
            $this->body = $this->storage->get_body($uid);
        }

        // notify plugins and let them analyze this structured message object
        $this->app->plugins->exec_hook('message_load', array('object' => $this));
    }


    /**
     * Return a (decoded) message header
     *
     * @param string $name Header name
     * @param bool   $row  Don't mime-decode the value
     * @return string Header value
     */
    public function get_header($name, $raw = false)
    {
        if (empty($this->headers)) {
            return null;
        }

        return $this->headers->get($name, !$raw);
    }


    /**
     * Set is_safe var and session data
     *
     * @param bool $safe enable/disable
     */
    public function set_safe($safe = true)
    {
        $_SESSION['safe_messages'][$this->folder.':'.$this->uid] = $this->is_safe = $safe;
    }


    /**
     * Compose a valid URL for getting a message part
     *
     * @param string $mime_id Part MIME-ID
     * @param mixed  $embed Mimetype class for parts to be embedded
     * @return string URL or false if part does not exist
     */
    public function get_part_url($mime_id, $embed = false)
    {
        if ($this->mime_parts[$mime_id])
            return $this->opt['get_url'] . '&_part=' . $mime_id . ($embed ? '&_embed=1&_mimeclass=' . $embed : '');
        else
            return false;
    }


    /**
     * Get content of a specific part of this message
     *
     * @param string   $mime_id           Part MIME-ID
     * @param resource $fp File           pointer to save the message part
     * @param boolean  $skip_charset_conv Disables charset conversion
     * @param int      $max_bytes         Only read this number of bytes
     *
     * @return string Part content
     */
    public function get_part_content($mime_id, $fp = null, $skip_charset_conv = false, $max_bytes = 0)
    {
        if ($part = $this->mime_parts[$mime_id]) {
            // stored in message structure (winmail/inline-uuencode)
            if (!empty($part->body) || $part->encoding == 'stream') {
                if ($fp) {
                    fwrite($fp, $part->body);
                }
                return $fp ? true : $part->body;
            }

            // get from IMAP
            $this->storage->set_folder($this->folder);

            return $this->storage->get_message_part($this->uid, $mime_id, $part, NULL, $fp, $skip_charset_conv, $max_bytes);
        }
    }


    /**
     * Determine if the message contains a HTML part
     *
     * @param bool $recursive Enables checking in all levels of the structure
     * @param bool $enriched  Enables checking for text/enriched parts too
     *
     * @return bool True if a HTML is available, False if not
     */
    function has_html_part($recursive = true, $enriched = false)
    {
        // check all message parts
        foreach ($this->parts as $part) {
            if ($part->mimetype == 'text/html' || ($enriched && $part->mimetype == 'text/enriched')) {
                // Level check, we'll skip e.g. HTML attachments
                if (!$recursive) {
                    $level = explode('.', $part->mime_id);

                    // Skip if part is an attachment
                    if ($this->is_attachment($part)) {
                        continue;
                    }

                    // Check if the part belongs to higher-level's alternative/related
                    while (array_pop($level) !== null) {
                        if (!count($level)) {
                            return true;
                        }

                        $parent = $this->mime_parts[join('.', $level)];
                        if ($parent->mimetype != 'multipart/alternative' && $parent->mimetype != 'multipart/related') {
                            continue 2;
                        }
                    }
                }

                return true;
            }
        }

        return false;
    }


    /**
     * Return the first HTML part of this message
     *
     * @return string HTML message part content
     */
    function first_html_part()
    {
        // check all message parts
        foreach ($this->mime_parts as $pid => $part) {
            if ($part->mimetype == 'text/html') {
                return $this->get_part_content($pid);
            }
        }
    }


    /**
     * Return the first text part of this message
     *
     * @param rcube_message_part $part Reference to the part if found
     * @return string Plain text message/part content
     */
    function first_text_part(&$part=null)
    {
        // no message structure, return complete body
        if (empty($this->parts))
            return $this->body;

        // check all message parts
        foreach ($this->mime_parts as $mime_id => $part) {
            if ($part->mimetype == 'text/plain') {
                return $this->get_part_content($mime_id);
            }
            else if ($part->mimetype == 'text/html') {
                $out = $this->get_part_content($mime_id);

                // create instance of html2text class
                $txt = new rcube_html2text($out);
                return $txt->get_text();
            }
        }

        $part = null;
        return null;
    }


    /**
     * Checks if part of the message is an attachment (or part of it)
     *
     * @param rcube_message_part $part Message part
     *
     * @return bool True if the part is an attachment part
     */
    public function is_attachment($part)
    {
        foreach ($this->attachments as $att_part) {
            if ($att_part->mime_id == $part->mime_id) {
                return true;
            }

            // check if the part is a subpart of another attachment part (message/rfc822)
            if ($att_part->mimetype == 'message/rfc822') {
                if (in_array($part, (array)$att_part->parts)) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Read the message structure returend by the IMAP server
     * and build flat lists of content parts and attachments
     *
     * @param rcube_message_part $structure Message structure node
     * @param bool               $recursive True when called recursively
     */
    private function parse_structure($structure, $recursive = false)
    {
        // real content-type of message/rfc822 part
        if ($structure->mimetype == 'message/rfc822' && $structure->real_mimetype) {
            $mimetype = $structure->real_mimetype;

            // parse headers from message/rfc822 part
            if (!isset($structure->headers['subject'])) {
                list($headers, $dump) = explode("\r\n\r\n", $this->get_part_content($structure->mime_id, null, true, 32768));
                $structure->headers = rcube_mime::parse_headers($headers);
            }
        }
        else
            $mimetype = $structure->mimetype;

        // show message headers
        if ($recursive && is_array($structure->headers) &&
                ($structure->headers['subject'] || $structure->headers['from'] || $structure->headers['to'])) {
            $c = new stdClass;
            $c->type = 'headers';
            $c->headers = $structure->headers;
            $this->parts[] = $c;
        }

        // Allow plugins to handle message parts
        $plugin = $this->app->plugins->exec_hook('message_part_structure',
            array('object' => $this, 'structure' => $structure,
                'mimetype' => $mimetype, 'recursive' => $recursive));

        if ($plugin['abort'])
            return;

        $structure = $plugin['structure'];
        list($message_ctype_primary, $message_ctype_secondary) = explode('/', $plugin['mimetype']);

        // print body if message doesn't have multiple parts
        if ($message_ctype_primary == 'text' && !$recursive) {
            // parts with unsupported type add to attachments list
            if (!in_array($message_ctype_secondary, array('plain', 'html', 'enriched'))) {
                $this->attachments[] = $structure;
                return;
            }

            $structure->type = 'content';
            $this->parts[] = $structure;

            // Parse simple (plain text) message body
            if ($message_ctype_secondary == 'plain') {
                foreach ((array)$this->uu_decode($structure) as $uupart) {
                    $this->mime_parts[$uupart->mime_id] = $uupart;
                    $this->attachments[] = $uupart;
                }
            }
        }
        // the same for pgp signed messages
        else if ($mimetype == 'application/pgp' && !$recursive) {
            $structure->type = 'content';
            $this->parts[] = $structure;
        }
        // message contains (more than one!) alternative parts
        else if ($mimetype == 'multipart/alternative'
            && is_array($structure->parts) && count($structure->parts) > 1
        ) {
            $plain_part   = null;
            $html_part    = null;
            $print_part   = null;
            $related_part = null;
            $attach_part  = null;

            // get html/plaintext parts, other add to attachments list
            foreach ($structure->parts as $p => $sub_part) {
                $sub_mimetype = $sub_part->mimetype;
                $is_multipart = preg_match('/^multipart\/(related|relative|mixed|alternative)/', $sub_mimetype);

                // skip empty text parts
                if (!$sub_part->size && !$is_multipart) {
                    continue;
                }

                // check if sub part is
                if ($is_multipart)
                    $related_part = $p;
                else if ($sub_mimetype == 'text/plain')
                    $plain_part = $p;
                else if ($sub_mimetype == 'text/html')
                    $html_part = $p;
                else if ($sub_mimetype == 'text/enriched')
                    $enriched_part = $p;
                else
                    $attach_part = $p;
            }

            // parse related part (alternative part could be in here)
            if ($related_part !== null && !$this->parse_alternative) {
                $this->parse_alternative = true;
                $this->parse_structure($structure->parts[$related_part], true);
                $this->parse_alternative = false;

                // if plain part was found, we should unset it if html is preferred
                if ($this->opt['prefer_html'] && count($this->parts))
                    $plain_part = null;
            }

            // choose html/plain part to print
            if ($html_part !== null && $this->opt['prefer_html']) {
                $print_part = $structure->parts[$html_part];
            }
            else if ($enriched_part !== null) {
                $print_part = $structure->parts[$enriched_part];
            }
            else if ($plain_part !== null) {
                $print_part = $structure->parts[$plain_part];
            }

            // add the right message body
            if (is_object($print_part)) {
                $print_part->type = 'content';
                $this->parts[] = $print_part;
            }
            // show plaintext warning
            else if ($html_part !== null && empty($this->parts)) {
                $c = new stdClass;
                $c->type            = 'content';
                $c->ctype_primary   = 'text';
                $c->ctype_secondary = 'plain';
                $c->mimetype        = 'text/plain';
                $c->realtype        = 'text/html';

                $this->parts[] = $c;
            }

            // add html part as attachment
            if ($html_part !== null && $structure->parts[$html_part] !== $print_part) {
                $html_part = $structure->parts[$html_part];
                $html_part->mimetype = 'text/html';

                $this->attachments[] = $html_part;
            }

            // add unsupported/unrecognized parts to attachments list
            if ($attach_part) {
                $this->attachments[] = $structure->parts[$attach_part];
            }
        }
        // this is an ecrypted message -> create a plaintext body with the according message
        else if ($mimetype == 'multipart/encrypted') {
            $p = new stdClass;
            $p->type            = 'content';
            $p->ctype_primary   = 'text';
            $p->ctype_secondary = 'plain';
            $p->mimetype        = 'text/plain';
            $p->realtype        = 'multipart/encrypted';

            $this->parts[] = $p;
        }
        // this is an S/MIME ecrypted message -> create a plaintext body with the according message
        else if ($mimetype == 'application/pkcs7-mime') {
            $p = new stdClass;
            $p->type            = 'content';
            $p->ctype_primary   = 'text';
            $p->ctype_secondary = 'plain';
            $p->mimetype        = 'text/plain';
            $p->realtype        = 'application/pkcs7-mime';

            $this->parts[] = $p;
        }
        // message contains multiple parts
        else if (is_array($structure->parts) && !empty($structure->parts)) {
            // iterate over parts
            for ($i=0; $i < count($structure->parts); $i++) {
                $mail_part      = &$structure->parts[$i];
                $primary_type   = $mail_part->ctype_primary;
                $secondary_type = $mail_part->ctype_secondary;

                // real content-type of message/rfc822
                if ($mail_part->real_mimetype) {
                    $part_orig_mimetype = $mail_part->mimetype;
                    $part_mimetype = $mail_part->real_mimetype;
                    list($primary_type, $secondary_type) = explode('/', $part_mimetype);
                }
                else
                    $part_mimetype = $mail_part->mimetype;

                // multipart/alternative
                if ($primary_type == 'multipart') {
                    $this->parse_structure($mail_part, true);

                    // list message/rfc822 as attachment as well (mostly .eml)
                    if ($part_orig_mimetype == 'message/rfc822' && !empty($mail_part->filename))
                        $this->attachments[] = $mail_part;
                }
                // part text/[plain|html] or delivery status
                else if ((($part_mimetype == 'text/plain' || $part_mimetype == 'text/html') && $mail_part->disposition != 'attachment') ||
                    in_array($part_mimetype, array('message/delivery-status', 'text/rfc822-headers', 'message/disposition-notification'))
                ) {
                    // Allow plugins to handle also this part
                    $plugin = $this->app->plugins->exec_hook('message_part_structure',
                        array('object' => $this, 'structure' => $mail_part,
                            'mimetype' => $part_mimetype, 'recursive' => true));

                    if ($plugin['abort'])
                        continue;

                    if ($part_mimetype == 'text/html' && $mail_part->size) {
                        $got_html_part = true;
                    }

                    $mail_part = $plugin['structure'];
                    list($primary_type, $secondary_type) = explode('/', $plugin['mimetype']);

                    // add text part if it matches the prefs
                    if (!$this->parse_alternative ||
                        ($secondary_type == 'html' && $this->opt['prefer_html']) ||
                        ($secondary_type == 'plain' && !$this->opt['prefer_html'])
                    ) {
                        $mail_part->type = 'content';
                        $this->parts[] = $mail_part;
                    }

                    // list as attachment as well
                    if (!empty($mail_part->filename)) {
                        $this->attachments[] = $mail_part;
                    }
                    // list html part as attachment (here the part is most likely inside a multipart/related part)
                    else if ($this->parse_alternative && ($secondary_type == 'html' && !$this->opt['prefer_html'])) {
                        $this->attachments[] = $mail_part;
                    }
                }
                // part message/*
                else if ($primary_type == 'message') {
                    $this->parse_structure($mail_part, true);

                    // list as attachment as well (mostly .eml)
                    if (!empty($mail_part->filename))
                        $this->attachments[] = $mail_part;
                }
                // ignore "virtual" protocol parts
                else if ($primary_type == 'protocol') {
                    continue;
                }
                // part is Microsoft Outlook TNEF (winmail.dat)
                else if ($part_mimetype == 'application/ms-tnef') {
                    foreach ((array)$this->tnef_decode($mail_part) as $tpart) {
                        $this->mime_parts[$tpart->mime_id] = $tpart;
                        $this->attachments[] = $tpart;
                    }
                }
                // part is a file/attachment
                else if (preg_match('/^(inline|attach)/', $mail_part->disposition) ||
                    $mail_part->headers['content-id'] ||
                    ($mail_part->filename &&
                        (empty($mail_part->disposition) || preg_match('/^[a-z0-9!#$&.+^_-]+$/i', $mail_part->disposition)))
                ) {
                    // skip apple resource forks
                    if ($message_ctype_secondary == 'appledouble' && $secondary_type == 'applefile')
                        continue;

                    // part belongs to a related message and is linked
                    if (preg_match('/^multipart\/(related|relative)/', $mimetype)
                        && ($mail_part->headers['content-id'] || $mail_part->headers['content-location'])) {
                        if ($mail_part->headers['content-id'])
                            $mail_part->content_id = preg_replace(array('/^</', '/>$/'), '', $mail_part->headers['content-id']);
                        if ($mail_part->headers['content-location'])
                            $mail_part->content_location = $mail_part->headers['content-base'] . $mail_part->headers['content-location'];

                        $this->inline_parts[] = $mail_part;
                    }
                    // attachment encapsulated within message/rfc822 part needs further decoding (#1486743)
                    else if ($part_orig_mimetype == 'message/rfc822') {
                        $this->parse_structure($mail_part, true);

                        // list as attachment as well (mostly .eml)
                        if (!empty($mail_part->filename))
                            $this->attachments[] = $mail_part;
                    }
                    // regular attachment with valid content type
                    // (content-type name regexp according to RFC4288.4.2)
                    else if (preg_match('/^[a-z0-9!#$&.+^_-]+\/[a-z0-9!#$&.+^_-]+$/i', $part_mimetype)) {
                        $this->attachments[] = $mail_part;
                    }
                    // attachment with invalid content type
                    // replace malformed content type with application/octet-stream (#1487767)
                    else if ($mail_part->filename) {
                        $mail_part->ctype_primary   = 'application';
                        $mail_part->ctype_secondary = 'octet-stream';
                        $mail_part->mimetype        = 'application/octet-stream';

                        $this->attachments[] = $mail_part;
                    }
                }
                // attachment part as message/rfc822 (#1488026)
                else if ($mail_part->mimetype == 'message/rfc822') {
                    $this->parse_structure($mail_part);
                }
            }

            // if this was a related part try to resolve references
            if (preg_match('/^multipart\/(related|relative)/', $mimetype) && sizeof($this->inline_parts)) {
                $a_replaces = array();
                $img_regexp = '/^image\/(gif|jpe?g|png|tiff|bmp|svg)/';

                foreach ($this->inline_parts as $inline_object) {
                    $part_url = $this->get_part_url($inline_object->mime_id, $inline_object->ctype_primary);
                    if (isset($inline_object->content_id))
                        $a_replaces['cid:'.$inline_object->content_id] = $part_url;
                    if ($inline_object->content_location) {
                        $a_replaces[$inline_object->content_location] = $part_url;
                    }

                    if (!empty($inline_object->filename)) {
                        // MS Outlook sends sometimes non-related attachments as related
                        // In this case multipart/related message has only one text part
                        // We'll add all such attachments to the attachments list
                        if (!isset($got_html_part) && empty($inline_object->content_id)) {
                            $this->attachments[] = $inline_object;
                        }
                        // MS Outlook sometimes also adds non-image attachments as related
                        // We'll add all such attachments to the attachments list
                        // Warning: some browsers support pdf in <img/>
                        else if (!preg_match($img_regexp, $inline_object->mimetype)) {
                            $this->attachments[] = $inline_object;
                        }
                        // @TODO: we should fetch HTML body and find attachment's content-id
                        // to handle also image attachments without reference in the body
                        // @TODO: should we list all image attachments in text mode?
                    }
                }

                // add replace array to each content part
                // (will be applied later when part body is available)
                foreach ($this->parts as $i => $part) {
                    if ($part->type == 'content')
                        $this->parts[$i]->replaces = $a_replaces;
                }
            }
        }
        // message is a single part non-text
        else if ($structure->filename) {
            $this->attachments[] = $structure;
        }
        // message is a single part non-text (without filename)
        else if (preg_match('/application\//i', $mimetype)) {
            $this->attachments[] = $structure;
        }
    }


    /**
     * Fill aflat array with references to all parts, indexed by part numbers
     *
     * @param rcube_message_part $part Message body structure
     */
    private function get_mime_numbers(&$part)
    {
        if (strlen($part->mime_id))
            $this->mime_parts[$part->mime_id] = &$part;

        if (is_array($part->parts))
            for ($i=0; $i<count($part->parts); $i++)
                $this->get_mime_numbers($part->parts[$i]);
    }


    /**
     * Decode a Microsoft Outlook TNEF part (winmail.dat)
     *
     * @param rcube_message_part $part Message part to decode
     * @return array
     */
    function tnef_decode(&$part)
    {
        // @TODO: attachment may be huge, hadle it via file
        if (!isset($part->body)) {
            $this->storage->set_folder($this->folder);
            $part->body = $this->storage->get_message_part($this->uid, $part->mime_id, $part);
        }

        $parts = array();
        $tnef = new tnef_decoder;
        $tnef_arr = $tnef->decompress($part->body);

        foreach ($tnef_arr as $pid => $winatt) {
            $tpart = new rcube_message_part;

            $tpart->filename        = trim($winatt['name']);
            $tpart->encoding        = 'stream';
            $tpart->ctype_primary   = trim(strtolower($winatt['type']));
            $tpart->ctype_secondary = trim(strtolower($winatt['subtype']));
            $tpart->mimetype        = $tpart->ctype_primary . '/' . $tpart->ctype_secondary;
            $tpart->mime_id         = 'winmail.' . $part->mime_id . '.' . $pid;
            $tpart->size            = $winatt['size'];
            $tpart->body            = $winatt['stream'];

            $parts[] = $tpart;
            unset($tnef_arr[$pid]);
        }

        return $parts;
    }


    /**
     * Parse message body for UUencoded attachments bodies
     *
     * @param rcube_message_part $part Message part to decode
     * @return array
     */
    function uu_decode(&$part)
    {
        // @TODO: messages may be huge, hadle body via file
        if (!isset($part->body)) {
            $this->storage->set_folder($this->folder);
            $part->body = $this->storage->get_message_part($this->uid, $part->mime_id, $part);
        }

        $parts = array();
        // FIXME: line length is max.65?
        $uu_regexp = '/begin [0-7]{3,4} ([^\n]+)\n/s';

        if (preg_match_all($uu_regexp, $part->body, $matches, PREG_SET_ORDER)) {
            // update message content-type
            $part->ctype_primary   = 'multipart';
            $part->ctype_secondary = 'mixed';
            $part->mimetype        = $part->ctype_primary . '/' . $part->ctype_secondary;
            $uu_endstring = "`\nend\n";

            // add attachments to the structure
            foreach ($matches as $pid => $att) {
                $startpos = strpos($part->body, $att[1]) + strlen($att[1]) + 1; // "\n"
                $endpos = strpos($part->body, $uu_endstring);
                $filebody = substr($part->body, $startpos, $endpos-$startpos);

                // remove attachments bodies from the message body
                $part->body = substr_replace($part->body, "", $startpos, $endpos+strlen($uu_endstring)-$startpos);

                $uupart = new rcube_message_part;

                $uupart->filename = trim($att[1]);
                $uupart->encoding = 'stream';
                $uupart->body     = convert_uudecode($filebody);
                $uupart->size     = strlen($uupart->body);
                $uupart->mime_id  = 'uu.' . $part->mime_id . '.' . $pid;

                $ctype = rcube_mime::content_type($uupart->body, $uupart->filename, 'application/octet-stream', true);
                $uupart->mimetype = $ctype;
                list($uupart->ctype_primary, $uupart->ctype_secondary) = explode('/', $ctype);

                $parts[] = $uupart;
                unset($matches[$pid]);
            }

            // remove attachments bodies from the message body
            $part->body = preg_replace($uu_regexp, '', $part->body);
        }

        return $parts;
    }


    /**
     * Deprecated methods (to be removed)
     */

    public static function unfold_flowed($text)
    {
        return rcube_mime::unfold_flowed($text);
    }

    public static function format_flowed($text, $length = 72)
    {
        return rcube_mime::format_flowed($text, $length);
    }

}
