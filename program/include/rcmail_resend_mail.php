<?php

/**
 +-----------------------------------------------------------------------+
 | program/include/rcmail_resend_mail.php                                |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2017, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Bounce/resend an email message                                      |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Mail_mime wrapper to handle mail resend/bounce
 *
 * @package Webmail
 */
class rcmail_resend_mail extends Mail_mime
{
    protected $orig_head;
    protected $orig_body;


    /**
     * Constructor function
     *
     * Added two parameters:
     *   'bounce_message' - rcube_message object of the original message
     *   'bounce_headers' - An array of headers to be added to the original message
     */
    public function __construct($params = array())
    {
        // To make the code simpler always use delay_file_io=true
        $params['delay_file_io'] = true;
        $params['eol']           = "\r\n";

        parent::__construct($params);
    }

    /**
     * Returns/Sets message headers
     */
    public function headers($headers = array(), $overwrite = false, $skip_content = false)
    {
        // headers() wrapper that returns Resent-Cc, Resent-Bcc instead of Cc,Bcc
        // it's also called to re-add Resent-Bcc after it has been sent (to store in Sent)

        if (array_key_exists('Bcc', $headers)) {
            $this->build_params['bounce_headers']['Resent-Bcc'] = $headers['Bcc'];
        }

        foreach ($this->build_params['bounce_headers'] as $key => $val) {
            $headers[str_replace('Resent-', '', $key)] = $val;
        }

        return $headers;
    }

    /**
     * Returns all message headers as string
     */
    public function txtHeaders($headers = array(), $overwrite = false, $skip_content = false)
    {
        // i.e. add Resent-* headers on top of the original message head
        $this->init_message();

        $result = array();

        foreach ($this->build_params['bounce_headers'] as $name => $value) {
            $key = str_replace('Resent-', '', $name);

            // txtHeaders() can be used to unset Bcc header
            if (array_key_exists($key, $headers)) {
                $value = $headers[$key];
                $this->build_params['bounce_headers']['Resent-'.$key] = $value;
            }

            if ($value) {
                $result[] = "$name: $value";
            }
        }

        $result = implode($this->build_params['eol'], $result);

        if (strlen($this->orig_head)) {
            $result .= $this->build_params['eol'] . $this->orig_head;
        }

        return $result;
    }

    /**
     * Save the message body to a file (if delay_file_io=true)
     */
    public function saveMessageBody($file, $params = null)
    {
        $this->init_message();

        // this will be called only once, so let just move the file
        rename($this->orig_body, $file);

        $this->orig_head = null;
    }

    protected function init_message()
    {
        if ($this->orig_head !== null) {
            return;
        }

        $rcmail   = rcmail::get_instance();
        $storage  = $rcmail->get_storage();
        $message  = $this->build_params['bounce_message'];
        $path     = rcube_utils::temp_filename('bounce');

        // We'll write the body to the file and the headers to a variable
        if ($fp = fopen($path, 'w')) {
            stream_filter_register('bounce_source', 'rcmail_bounce_stream_filter');
            stream_filter_append($fp, 'bounce_source');

            // message part
            if ($message->context) {
                $message->get_part_body($message->context, false, 0, $fp);
            }
            // complete message
            else {
                $storage->set_folder($message->folder);
                $storage->get_raw_body($message->uid, $fp);
            }

            fclose($fp);

            $this->orig_head = rcmail_bounce_stream_filter::$headers;
            $this->orig_body = $path;
        }
    }
}

/**
 * Stream filter to remove message headers from the streamed
 * message source (and store them in a variable)
 *
 * @package Webmail
 */
class rcmail_bounce_stream_filter extends php_user_filter
{
    public static $headers;

    protected $in_body = false;

    public function onCreate()
    {
        self::$headers = '';
    }

    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            if (!$this->in_body) {
                self::$headers .= $bucket->data;
                if (($pos = strpos(self::$headers, "\r\n\r\n")) === false) {
                    continue;
                }

                $bucket->data    = substr(self::$headers, $pos + 4);
                $bucket->datalen = strlen($bucket->data);

                self::$headers = substr(self::$headers, 0, $pos);
                $this->in_body = true;
            }

            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }
}
