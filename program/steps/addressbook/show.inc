<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/show.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Show contact details                                                |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


// read contact record
if (($cid = get_input_value('_cid', RCUBE_INPUT_GPC)) && ($record = $CONTACTS->get_record($cid, true))) {
  $OUTPUT->set_env('cid', $record['ID']);
}

function rcmail_contact_details($attrib)
{
  global $CONTACTS, $OUTPUT;

  // check if we have a valid result
  if (!(($result = $CONTACTS->get_result()) && ($record = $result->first()))) {
    $OUTPUT->show_message('contactnotfound');
    return false;
  }
  
  // a specific part is requested
  if ($attrib['part']) {
    return Q($record[$attrib['part']]);
  }

  // return the complete address record as table
  $table = new html_table(array('cols' => 2));

  $a_show_cols = array('name', 'firstname', 'surname', 'email');
  $microformats = array('name' => 'fn', 'email' => 'email');

  foreach ($a_show_cols as $col) {
    if ($col == 'email' && !empty($record[$col])) {
      $value = html::a(array(
        'href' => 'mailto:' . $record[$col],
        'onclick' => sprintf("return %s.command('compose','%s',this)", JS_OBJECT_NAME, JQ($record[$col])),
        'title' => rcube_label('composeto'),
        'class' => $microformats[$col],
      ), Q($record[$col]));
    }
    else if (!empty($record[$col])) {
      $value = html::span($microformats[$col], Q($record[$col]));
    }
    else
      $value = '';
    
    $table->add('title', Q(rcube_label($col)));
    $table->add(null, $value);
  }
  
  return $table->show($attrib + array('class' => 'vcard'));
}


//$OUTPUT->framed = $_framed;
$OUTPUT->add_handler('contactdetails', 'rcmail_contact_details');
$OUTPUT->send('showcontact');
?>