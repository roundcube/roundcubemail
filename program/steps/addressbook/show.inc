<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/show.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
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
if (($cid = get_input_value('_cid', RCUBE_INPUT_GPC)) && ($record = $CONTACTS->get_record($cid, true)))
  $OUTPUT->set_env('cid', $record['ID']);


function rcmail_contact_details($attrib)
  {
  global $CONTACTS, $OUTPUT;

  // check if we have a valid result
  if (!(($result = $CONTACTS->get_result()) && ($record = $result->first())))
  {
    $OUTPUT->show_message('contactnotfound');
    return false;
  }
  
  // a specific part is requested
  if ($attrib['part'])
    return Q($record[$attrib['part']]);

  // return the complete address record as table
  $out = "<table>\n\n";

  $a_show_cols = array('name', 'firstname', 'surname', 'email');
  foreach ($a_show_cols as $col)
    {
    if ($col=='email' && !empty($record[$col]))
      $value = sprintf(
        '<a href="#compose" onclick="%s.command(\'compose\', \'%s\')" title="%s">%s</a>',
        JS_OBJECT_NAME,
        JQ($record[$col]),
        rcube_label('composeto'),
        Q($record[$col]));
    else
      $value = Q($record[$col]);
    
    $out .= sprintf("<tr><td class=\"title\">%s</td><td>%s</td></tr>\n",
                    Q(rcube_label($col)),
                    $value);
    }
  
  $out .= "\n</table>";
  
  return $out;  
  }


//$OUTPUT->framed = $_framed;
$OUTPUT->add_handler('contactdetails', 'rcmail_contact_details');
$OUTPUT->send('showcontact');
?>