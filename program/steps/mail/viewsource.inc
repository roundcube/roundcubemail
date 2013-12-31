<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/viewsource.inc                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Display a mail message similar as a usual mail application does     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

ob_end_clean();

// similar code as in program/steps/mail/get.inc
if ($uid = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_GET)) {
    $headers = $RCMAIL->storage->get_message_headers($uid);
    $charset = $headers->charset ? $headers->charset : $RCMAIL->config->get('default_charset');

    header("Content-Type: text/plain; charset={$charset}");

    if (!empty($_GET['_save'])) {
        $subject  = rcube_mime::decode_header($headers->subject, $headers->charset);
        $filename = ($subject ? $subject : $RCMAIL->config->get('product_name', 'email')) . '.eml';
        $browser  = $RCMAIL->output->browser;

        if ($browser->ie && $browser->ver < 7)
            $filename = rawurlencode(abbreviate_string($filename, 55));
        else if ($browser->ie)
            $filename = rawurlencode($filename);
        else
            $filename = addcslashes($filename, '"');

        header("Content-Length: {$headers->size}");
        header("Content-Disposition: attachment; filename=\"$filename\"");
    }

    $RCMAIL->storage->print_raw_body($uid, empty($_GET['_save']));
}
else {
    rcube::raise_error(array(
            'code'    => 500,
            'type'    => 'php',
            'file'    => __FILE__,
            'line'    => __LINE__,
            'message' => "Message UID $uid not found"
        ),
        true, true);
}

exit;
