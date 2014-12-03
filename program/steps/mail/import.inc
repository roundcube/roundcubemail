<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/import.inc                                         |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Save the uploaded file(s) as messages to the current IMAP folder    |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

// clear all stored output properties (like scripts and env vars)
$OUTPUT->reset();

if (is_array($_FILES['_file'])) {
    $imported = 0;

    foreach ((array)$_FILES['_file']['tmp_name'] as $i => $filepath) {
        // Process uploaded file if there is no error
        $err = $_FILES['_file']['error'][$i];

        if (!$err) {
            // check file content type first
            list($mtype_primary,) = explode('/', rcube_mime::file_content_type($filepath, $_FILES['_file']['name'][$i], $_FILES['_file']['type'][$i]));

            if (!in_array($mtype_primary, array('text','message'))) {
                continue;
            }

            // read the first few lines to detect header-like structure
            $fp = fopen($filepath, 'r');
            do {
                $line = fgets($fp);
            }
            while ($line !== false && trim($line) == '');

            if (!preg_match('/^From\s+-/', $line) && !preg_match('/^[a-z-_]+:\s+.+/i', $line)) {
                continue;
            }

            $message = $lastline = '';
            fseek($fp, 0);
            while (($line = fgets($fp)) !== false) {
                // importing mbox file, split by From - lines
                if (preg_match('/^From\s+-/', $line) && ($lastline == '' || substr($lastline, -2) == '--')) {
                    if (!empty($message)) {
                        if ($RCMAIL->storage->save_message(null, rtrim($message))) {
                            $imported++;
                        }
                        else {
                            rcube::raise_error("Failed to import message to " . $RCMAIL->storage->get_folder(), false, true);
                        }
                        $message = '';
                    }
                    continue;
                }

                $message .= $line;
                $lastline = rtrim($line);
            }

            if (!empty($message) && $RCMAIL->storage->save_message(null, rtrim($message))) {
                $imported++;
            }
        }

        if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE) {
            $size = $RCMAIL->show_bytes(parse_bytes(ini_get('upload_max_filesize')));
            $msg  = $RCMAIL->gettext(array('name' => 'filesizeerror', 'vars' => array('size' => $size)));
        }
        else if ($err) {
            $OUTPUT->show_message('fileuploaderror', 'error');
        }
    }  // end foreach

    if ($imported) {
        $OUTPUT->show_message($RCMAIL->gettext(array('name' => 'importmessagesuccess', 'nr' => $imported, 'vars' => array('nr' => $imported))), 'confirmation');
        $OUTPUT->command('command', 'list');
    }
    else {
        $OUTPUT->show_message('importmessageerror', 'error');
    }
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // if filesize exceeds post_max_size then $_FILES array is empty,
    // show filesizeerror instead of fileuploaderror
    if ($maxsize = ini_get('post_max_size'))
        $msg = $RCMAIL->gettext(array('name' => 'filesizeerror', 'vars' => array('size' => $RCMAIL->show_bytes(parse_bytes($maxsize)))));
    else
        $msg = $RCMAIL->gettext('fileuploaderror');

    $OUTPUT->command('display_message', $msg, 'error');
}

// send html page with JS calls as response
$OUTPUT->send('iframe');
