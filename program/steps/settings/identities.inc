<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/identities.inc                                 |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
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
  $PAGE_TITLE = sprintf('%s (%s@%s)', rcube_label('identities'), $USER_DATA['username'], $USER_DATA['mail_host']);



// similar function as /steps/addressbook/func.inc::rcmail_contact_frame()
function rcmail_identity_frame($attrib)
  {
  global $OUTPUT, $JS_OBJECT_NAME;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmIdentityFrame';

  $attrib['name'] = $attrib['id'];

  $OUTPUT->add_script(sprintf("%s.set_env('contentframe', '%s');", $JS_OBJECT_NAME, $attrib['name']));

  $attrib_str = create_attrib_string($attrib, array('name', 'id', 'class', 'style', 'src', 'width', 'height', 'frameborder'));
  $out = '<iframe'. $attrib_str . '></iframe>';

  return $out;
  }



parse_template('identities');
?>