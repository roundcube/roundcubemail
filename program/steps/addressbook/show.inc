<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/show.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Show contact details                                                |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Tobias 'tri' Richter  <tobias@datenwerkstatt-richter.de       |
 +-----------------------------------------------------------------------+

 $Id$

*/


if ($_GET['_cid'] || $_POST['_cid'])
  {
  $cid = $_POST['_cid'] ? $_POST['_cid'] : $_GET['_cid'];
  $DB->query("SELECT * FROM ".get_table_name('contacts')."
              WHERE  contact_id=?
              AND    user_id=?
              AND    del<>1",
              $cid,
              $_SESSION['user_id']);
  
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

  $a_show_cols = array('name', 'email', 'first_name', 'middle_name', 'last_name', 'edu_title', 'addon', 'nickname', 'company', 'organisation', 'department', 'job_title', 'note', 'tel_work1_voice', 'tel_work2_voice', 'tel_home1_voice', 'tel_home2_voice', 'tel_cell_voice', 'tel_car_voice', 'tel_pager_voice', 'tel_additional', 'tel_work_fax', 'tel_home_fax', 'tel_isdn', 'tel_preferred', 'tel_telex', 'work_street', 'work_zip', 'work_city', 'work_region', 'work_country', 'home_street', 'home_zip', 'home_city', 'home_region', 'home_country', 'postal_street', 'postal_zip', 'postal_city', 'postal_region', 'postal_country', 'url_work', 'role', 'birthday', 'rev', 'lang');
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
