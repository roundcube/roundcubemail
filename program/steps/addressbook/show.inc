<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/show.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | All rights reserved.                                                  |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Show contact details                                                |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


if ($_GET['_cid'] || $_POST['_cid'])
  {
  $cid = $_POST['_cid'] ? $_POST['_cid'] : $_GET['_cid'];
  $DB->query(sprintf("SELECT * FROM %s
                      WHERE  contact_id=%d
                      AND    user_id=%d
                      AND    del!='1'",
                     get_table_name('contacts'),
                     $cid,
                     $_SESSION['user_id']));
  
  $CONTACT_RECORD = $DB->fetch_assoc();
  
  if (is_array($CONTACT_RECORD))
    $OUTPUT->add_script(sprintf("%s.set_env('cid', '%s');", $JS_OBJECT_NAME, $CONTACT_RECORD['contact_id']));
  }



function rcmail_contact_details($attrib)
  {
  global $CONTACT_RECORD, $JS_OBJECT_NAME;

  if (!$CONTACT_RECORD)
    return show_message('contactnotfound');
  
  // a specific part is requested
  if ($attrib['part'])
    return rep_specialchars_output($CONTACT_RECORD[$attrib['part']]);


  // return the complete address record as table
  $out = "<table>\n\n";

  $a_show_cols = array('name', 'firstname', 'surname', 'email');
  foreach ($a_show_cols as $col)
    {
    if ($col=='email' && $CONTACT_RECORD[$col])
      $value = sprintf('<a href="#compose" onclick="%s.command(\'compose\', %d)" title="%s">%s</a>',
                       $JS_OBJECT_NAME,
                       $CONTACT_RECORD['contact_id'],
                       rcube_label('composeto'),
                       $CONTACT_RECORD[$col]);
    else
      $value = rep_specialchars_output($CONTACT_RECORD[$col]);
    
    $title = rcube_label($col);
    $out .= sprintf("<tr><td class=\"title\">%s</td><td>%s</td></tr>\n", $title, $value);
    }


  $out .= "\n</table>";
  
  return $out;  
  }


parse_template('showcontact');
?>