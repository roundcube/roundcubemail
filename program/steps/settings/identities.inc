<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/identities.inc                                 |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2007, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Manage identities of a user account                                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

define('IDENTITIES_LEVEL', intval($RCMAIL->config->get('identities_level', 0)));

$OUTPUT->set_pagetitle(rcube_label('identities'));
$OUTPUT->include_script('list.js');


// similar function as /steps/addressbook/func.inc::rcmail_contact_frame()
function rcmail_identity_frame($attrib)
  {
  global $OUTPUT;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmIdentityFrame';

  return $OUTPUT->frame($attrib, true);
  }

$OUTPUT->add_handler('identityframe', 'rcmail_identity_frame');
$OUTPUT->set_env('identities_level', IDENTITIES_LEVEL);
$OUTPUT->add_label('deleteidentityconfirm');

$OUTPUT->send('identities');
