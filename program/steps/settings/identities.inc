<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/identities.inc                                 |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Manage identities of a user account                                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

if ($USER_DATA = $DB->fetch_assoc($sql_result))
  $OUTPUT->set_pagetitle(sprintf('%s (%s@%s)', rcube_label('identities'), $USER_DATA['username'], $USER_DATA['mail_host']));

$OUTPUT->include_script('list.js');


// similar function as /steps/addressbook/func.inc::rcmail_contact_frame()
function rcmail_identity_frame($attrib)
  {
  global $OUTPUT;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmIdentityFrame';

  $attrib['name'] = $attrib['id'];

  $OUTPUT->set_env('contentframe', $attrib['name']);

  $attrib_str = create_attrib_string($attrib, array('name', 'id', 'class', 'style', 'src', 'width', 'height', 'frameborder'));
  $out = '<iframe'. $attrib_str . '></iframe>';

  return $out;
  }

$OUTPUT->add_handler('identityframe', 'rcmail_identity_frame');

$OUTPUT->send('identities');
?>