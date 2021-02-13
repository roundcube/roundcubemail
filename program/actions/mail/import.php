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
 |   Save the uploaded file(s) as messages to the current IMAP folder    |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_import extends rcmail_action
{
    // only process ajax requests
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        // clear all stored output properties (like scripts and env vars)
        $rcmail->output->reset();

        if (!empty($_FILES['_file']) && is_array($_FILES['_file'])) {
            $imported = 0;
            $folder   = $rcmail->storage->get_folder();

            foreach ((array) $_FILES['_file']['tmp_name'] as $i => $filepath) {
                // Process uploaded file if there is no error
                $err = $_FILES['_file']['error'][$i];

                if (!$err) {
                    // check file content type first
                    $ctype = rcube_mime::file_content_type($filepath, $_FILES['_file']['name'][$i], $_FILES['_file']['type'][$i]);
                    list($mtype_primary, $mtype_secondary) = explode('/', $ctype);

                    if (in_array($ctype, ['application/zip', 'application/x-zip'])) {
                        $filepath = self::zip_extract($filepath);
                        if (empty($filepath)) {
                            continue;
                        }
                    }
                    else if (!in_array($mtype_primary, ['text', 'message'])) {
                        continue;
                    }

                    foreach ((array) $filepath as $file) {
                        // read the first few lines to detect header-like structure
                        $fp = fopen($file, 'r');
                        do {
                            $line = fgets($fp);
                        }
                        while ($line !== false && trim($line) == '');

                        if (!preg_match('/^From .+/', $line) && !preg_match('/^[a-z-_]+:\s+.+/i', $line)) {
                            continue;
                        }

                        $message = $lastline = '';
                        fseek($fp, 0);

                        while (($line = fgets($fp)) !== false) {
                            // importing mbox file, split by From - lines
                            if ($lastline === '' && strncmp($line, 'From ', 5) === 0 && strlen($line) > 5) {
                                if (!empty($message)) {
                                    $imported += (int) self::save_message($folder, $message);
                                }

                                $message  = $line;
                                $lastline = '';
                                continue;
                            }

                            $message .= $line;
                            $lastline = rtrim($line);
                        }

                        if (!empty($message)) {
                            $imported += (int) self::save_message($folder, $message);
                        }

                        // remove temp files extracted from zip
                        if (is_array($filepath)) {
                            unlink($file);
                        }
                    }
                }
                else {
                    self::upload_error($err);
                }
            }

            if ($imported) {
                $rcmail->output->show_message($rcmail->gettext(['name' => 'importmessagesuccess', 'nr' => $imported, 'vars' => ['nr' => $imported]]), 'confirmation');
                $rcmail->output->command('command', 'list');
            }
            else {
                $rcmail->output->show_message('importmessageerror', 'error');
            }
        }
        else {
            self::upload_failure();
        }

        // send html page with JS calls as response
        $rcmail->output->send('iframe');
    }

    public static function zip_extract($path)
    {
        if (!class_exists('ZipArchive', false)) {
            return;
        }

        $zip   = new ZipArchive;
        $files = [];

        if ($zip->open($path)) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry    = $zip->getNameIndex($i);
                $tmpfname = rcube_utils::temp_filename('zipimport');

                if (copy("zip://$path#$entry", $tmpfname)) {
                    $ctype = rcube_mime::file_content_type($tmpfname, $entry);
                    list($mtype_primary, ) = explode('/', $ctype);

                    if (in_array($mtype_primary, ['text', 'message'])) {
                        $files[] = $tmpfname;
                    }
                    else {
                        unlink($tmpfname);
                    }
                }
            }

            $zip->close();
        }

        return $files;
    }

    public static function save_message($folder, &$message)
    {
        $date = null;

        if (strncmp($message, 'From ', 5) === 0) {
            // Extract the mbox from_line
            $pos     = strpos($message, "\n");
            $from    = substr($message, 0, $pos);
            $message = substr($message, $pos + 1);

            // Read the received date, support only known date formats

            // RFC4155: "Sat Jan  3 01:05:34 1996"
            $mboxdate_rx = '/^([a-z]{3} [a-z]{3} [0-9 ][0-9] [0-9]{2}:[0-9]{2}:[0-9]{2} [0-9]{4})/i';
            // Roundcube/Zipdownload: "12-Dec-2016 10:56:33 +0100"
            $imapdate_rx = '/^([0-9]{1,2}-[a-z]{3}-[0-9]{4} [0-9]{2}:[0-9]{2}:[0-9]{2} [0-9+-]{5})/i';

            if (
                ($pos = strpos($from, ' ', 6))
                && ($dt_str = substr($from, $pos + 1))
                && (preg_match($mboxdate_rx, $dt_str, $m) || preg_match($imapdate_rx, $dt_str, $m))
            ) {
                try {
                    $date = new DateTime($m[0], new DateTimeZone('UTC'));
                }
                catch (Exception $e) {
                    // ignore
                }
            }
        }

        // unquote ">From " lines in message body
        $message = preg_replace('/\n>([>]*)From /', "\n\\1From ", $message);
        $message = rtrim($message);
        $rcmail  = rcmail::get_instance();

        if ($rcmail->storage->save_message($folder, $message, '', false, [], $date)) {
            return true;
        }

        rcube::raise_error("Failed to import message to $folder", true, false);

        return false;
    }
}
