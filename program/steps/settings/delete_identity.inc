<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/delete_identity.inc                            |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Delete the submitted identities (IIDs) from the database            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

$iid = rcube_utils::get_input_value('_iid', rcube_utils::INPUT_GPC);

// check request token
if (!$OUTPUT->ajax_call && !$RCMAIL->check_request(rcube_utils::INPUT_GPC)) {
    $OUTPUT->show_message('invalidrequest', 'error');
    $RCMAIL->overwrite_action('identities');
    return;
}

if ($iid && preg_match('/^[0-9]+(,[0-9]+)*$/', $iid)) {
    $plugin = $RCMAIL->plugins->exec_hook('identity_delete', array('id' => $iid));

    $deleted = !$plugin['abort'] ? $RCMAIL->user->delete_identity($iid) : $plugin['result'];

    if ($deleted > 0 && $deleted !== false) {
        $OUTPUT->show_message('deletedsuccessfully', 'confirmation', null, false);
    }
    else {
        $msg = $plugin['message'] ? $plugin['message'] : ($deleted < 0 ? 'nodeletelastidentity' : 'errorsaving');
        $OUTPUT->show_message($msg, 'error', null, false);
    }

    // send response
    if ($OUTPUT->ajax_call) {
        $OUTPUT->send();
    }
}

if ($OUTPUT->ajax_call) {
    exit;
}

// go to identities page
$RCMAIL->overwrite_action('identities');
