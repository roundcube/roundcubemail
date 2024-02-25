<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) Kolab Systems AG                                        |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Unified access to attachment properties and body                    |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Unified access to attachment properties and body
 * Unified for message parts as well as uploaded attachments
 *
 * @package Webmail
 */
class rcmail_attachment_handler
{
    public $filename;
    public $size;
    public $mimetype;
    public $ident;
    public $charset = RCUBE_CHARSET;

    private $message;
    private $part;
    private $upload;
    private $body;
    private $body_file;
    private $download = false;

    /**
     * Class constructor.
     * Reads request parameters and initializes attachment/part props.
     */
    public function __construct()
    {
        ob_end_clean();

        $part_id    = rcube_utils::get_input_string('_part', rcube_utils::INPUT_GET);
        $file_id    = rcube_utils::get_input_string('_file', rcube_utils::INPUT_GET);
        $compose_id = rcube_utils::get_input_string('_id', rcube_utils::INPUT_GET);
        $uid        = rcube_utils::get_input_string('_uid', rcube_utils::INPUT_GET);
        $rcube      = rcube::get_instance();

        $this->download = !empty($_GET['_download']);

        // similar code as in program/steps/mail/show.inc
        if (!empty($uid)) {
            $rcube->config->set('prefer_html', true);
            $this->message = new rcube_message($uid, null, !empty($_GET['_safe']));
            $this->part = $this->message->mime_parts[$part_id] ?? null;

            if ($this->part) {
                $this->filename = rcmail_action_mail_index::attachment_name($this->part);
                $this->mimetype = $this->part->mimetype;
                $this->size     = $this->part->size;
                $this->ident    = $this->message->headers->messageID . ':' . $this->part->mime_id . ':' . $this->size . ':' . $this->mimetype;
                $this->charset  = $this->part->charset ?: RCUBE_CHARSET;

                if (empty($_GET['_frame'])) {
                    // allow post-processing of the attachment body
                    $plugin = $rcube->plugins->exec_hook('message_part_get', [
                            'uid'      => $uid,
                            'id'       => $this->part->mime_id,
                            'mimetype' => $this->mimetype,
                            'part'     => $this->part,
                            'download' => $this->download,
                    ]);

                    if ($plugin['abort']) {
                        exit;
                    }

                    // overwrite modified vars from plugin
                    $this->mimetype = $plugin['mimetype'];

                    if (!empty($plugin['body'])) {
                        $this->body = $plugin['body'];
                        $this->size = strlen($this->body);
                    }
                }
            }
        }
        else if ($file_id && $compose_id) {
            $file_id = preg_replace('/^rcmfile/', '', $file_id);
            $compose = $_SESSION['compose_data_' . $compose_id] ?? null;

            if ($compose && ($this->upload = $compose['attachments'][$file_id])) {
                $this->filename = $this->upload['name'];
                $this->mimetype = $this->upload['mimetype'];
                $this->size     = $this->upload['size'];
                $this->ident    = sprintf('%s:%s%s', $compose_id, $file_id, $this->size);
                $this->charset  = !empty($this->upload['charset']) ? $this->upload['charset'] : RCUBE_CHARSET;
            }
        }

        if (empty($this->part) && empty($this->upload)) {
            header('HTTP/1.1 404 Not Found');
            exit;
        }

        // check connection status
        self::check_storage_status();

        $this->mimetype = rcube_mime::fix_mimetype($this->mimetype);
    }

    /**
     * Remove temp files, etc.
     */
    public function __destruct()
    {
        if ($this->body_file) {
            @unlink($this->body_file);
        }
    }

    /**
     * Check if the object is a message part not uploaded file
     *
     * @return bool True if the object is a message part
     */
    public function is_message_part()
    {
        return !empty($this->message);
    }

    /**
     * Object/request status
     *
     * @return bool Status
     */
    public function is_valid()
    {
        return !empty($this->part) || !empty($this->upload);
    }

    /**
     * Return attachment/part mimetype if this is an image
     * of supported type.
     *
     * @return string Image mimetype
     */
    public function image_type()
    {
        $part = (object) [
            'filename' => $this->filename,
            'mimetype' => $this->mimetype,
        ];

        return rcmail_action_mail_index::part_image_type($part);
    }

    /**
     * Formatted attachment/part size (with units)
     *
     * @return string Attachment/part size (with units)
     */
    public function size()
    {
        $part = $this->part ?: ((object) ['size' => $this->size, 'exact_size' => true]);
        return rcmail_action::message_part_size($part);
    }

    /**
     * Returns, prints or saves the attachment/part body
     */
    public function body($size = null, $fp = null)
    {
        // we may have the body in memory or file already
        if ($this->body !== null) {
            if ($fp == -1) {
                echo $size ? substr($this->body, 0, $size) : $this->body;
            }
            else if ($fp) {
                $result = fwrite($fp, $size ? substr($this->body, $size) : $this->body) !== false;
            }
            else {
                $result = $size ? substr($this->body, 0, $size) : $this->body;
            }
        }
        else if ($this->body_file) {
            if ($size) {
                $result = file_get_contents($this->body_file, false, null, 0, $size);
            }
            else {
                $result = file_get_contents($this->body_file);
            }

            if ($fp == -1) {
                echo $result;
            }
            else if ($fp) {
                $result = fwrite($fp, $result) !== false;
            }
        }
        else if ($this->message) {
            $result = $this->message->get_part_body($this->part->mime_id, false, 0, $fp);

            // check connection status
            if (!$fp && $this->size && empty($result)) {
                self::check_storage_status();
            }
        }
        else if ($this->upload) {
            // This hook retrieves the attachment contents from the file storage backend
            $attachment = rcube::get_instance()->plugins->exec_hook('attachment_get', $this->upload);

            if ($fp && $fp != -1) {
                if ($attachment['data']) {
                    $result = fwrite($fp, $size ? substr($attachment['data'], 0, $size) : $attachment['data']) !== false;
                }
                else if ($attachment['path']) {
                    if ($fh = fopen($attachment['path'], 'rb')) {
                        $result = stream_copy_to_stream($fh, $fp, $size ? $size : -1);
                    }
                }
            }
            else {
                $data = $attachment['data'] ?? '';
                if (!$data && $attachment['path']) {
                    $data = file_get_contents($attachment['path']);
                }

                if ($fp == -1) {
                    echo $size ? substr($data, 0, $size) : $data;
                }
                else {
                    $result = $size ? substr($data, 0, $size) : $data;
                }
            }
        }

        return $result ?? null;
    }

    /**
     * Save the body to a file
     *
     * @param string $filename File name with path
     *
     * @return bool True on success, False on failure
     */
    public function body_to_file($filename)
    {
        if ($filename && $this->size && ($fp = fopen($filename, 'w'))) {
            $this->body(0, $fp);
            $this->body_file = $filename;
            fclose($fp);
            @chmod($filename, 0600);

            return true;
        }

        return false;
    }

    /**
     * Output attachment body with content filtering
     */
    public function output($mimetype)
    {
        if (!$this->size) {
            return false;
        }

        $secure = stripos($mimetype, 'image/') === false || $this->download;

        // Remove <script> in SVG images
        if (!$secure && stripos($mimetype, 'image/svg') === 0) {
            if (!$this->body) {
                $this->body = $this->body();
                if (empty($this->body)) {
                    return false;
                }
            }

            echo self::svg_filter($this->body);
            return true;
        }

        if ($this->body !== null && !$this->download) {
            header("Content-Length: " . strlen($this->body));
            echo $this->body;
            return true;
        }

        // Don't be tempted to set Content-Length to $part->d_parameters['size'] (#1490482)
        // RFC2183 says "The size parameter indicates an approximate size"

        return $this->body(0, -1);
    }

    /**
     * Returns formatted HTML if the attachment is HTML
     */
    public function html()
    {
        list($type, $subtype) = explode('/', $this->mimetype);
        $part = (object) [
            'charset'         => $this->charset,
            'ctype_secondary' => $subtype,
        ];

        // get part body if not available
        // fix formatting and charset
        $body = rcube_message::format_part_body($this->body(), $part);

        // show images?
        $is_safe = $this->is_safe();

        return rcmail_action_mail_index::wash_html($body, ['safe' => $is_safe, 'inline_html' => false]);
    }

    /**
     * Remove <script> in SVG images
     */
    public static function svg_filter($body)
    {
        // clean SVG with washtml
        $wash_opts = [
            'show_washed'   => false,
            'allow_remote'  => false,
            'charset'       => RCUBE_CHARSET,
            'html_elements' => ['title'],
        ];

        // initialize HTML washer
        $washer = new rcube_washtml($wash_opts);

        // allow CSS styles, will be sanitized by rcmail_washtml_callback()
        $washer->add_callback('style', 'rcmail_action_mail_index::washtml_callback');

        return $washer->wash($body);
    }

    /**
     * Handles nicely storage connection errors
     */
    public static function check_storage_status()
    {
        $error = rcmail::get_instance()->storage->get_error_code();

        // Check if we have a connection error
        if ($error == rcube_imap_generic::ERROR_BAD) {
            ob_end_clean();

            // Get action is often executed simultaneously.
            // Some servers have MAXPERIP or other limits.
            // To workaround this we'll wait for some time
            // and try again (once).
            // Note: Random sleep interval is used to minimize concurrency
            // in getting message parts

            if (!isset($_GET['_redirected'])) {
                usleep(rand(10, 30) * 100000); // 1-3 sec.
                header('Location: ' . $_SERVER['REQUEST_URI'] . '&_redirected=1');
            }
            else {
                rcube::raise_error([
                        'code' => 500, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => 'Unable to get/display message part. IMAP connection error'
                    ],
                    true, true
                );
            }

            // Don't kill session, just quit (#1486995)
            exit;
        }
    }

    public function is_safe()
    {
        if ($this->message) {
            return rcmail_action_mail_index::check_safe($this->message);
        }

        return !empty($_GET['_safe']);
    }
}
