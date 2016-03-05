<?php

/**
 +-----------------------------------------------------------------------+
 | program/steps/settings/aliases.inc                                 |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Manage aliases of a user account                                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Dilip Raj Baral <rajb.dilip@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

define('IDENTITIES_LEVEL', intval($RCMAIL->config->get('identities_level', 0)));    // TOOD: Remove

$OUTPUT->set_pagetitle($RCMAIL->gettext('aliases'));
$OUTPUT->include_script('list.js');

$OUTPUT->add_handler('aliasframe', 'rcmail_alias_frame');
// $OUTPUT->set_env('identities_level', IDENTITIES_LEVEL);
// $OUTPUT->add_label('deleteidentityconfirm');

$OUTPUT->send('aliases');


// similar function as /steps/addressbook/func.inc::rcmail_contact_frame()
function rcmail_alias_frame($attrib)
{
    global $OUTPUT;

    if (!$attrib['id']) {
        $attrib['id'] = 'rcmIdentityFrame';
    }

    return $OUTPUT->frame($attrib, true);
}
