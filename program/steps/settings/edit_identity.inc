<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/edit_identity.inc                              |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Show edit form for a identity record or to add a new one            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

if (($_GET['_iid'] || $_POST['_iid']) && $_action=='edit-identity')
  {
  $id = $_POST['_iid'] ? $_POST['_iid'] : $_GET['_iid'];
  $DB->query(sprintf("SELECT * FROM %s
                      WHERE  identity_id=%d
                      AND    user_id=%d
                      AND    del!='1'",
                     get_table_name('identities'),
                     $id,
                     $_SESSION['user_id']));
  
  $IDENTITY_RECORD = $DB->fetch_assoc();
  
  if (is_array($IDENTITY_RECORD))
    $OUTPUT->add_script(sprintf("%s.set_env('iid', '%s');", $JS_OBJECT_NAME, $IDENTITY_RECORD['identity_id']));
    
  $PAGE_TITLE = rcube_label('edititem');
  }
else
  $PAGE_TITLE = rcube_label('newitem');



function rcube_identity_form($attrib)
  {
  global $IDENTITY_RECORD, $JS_OBJECT_NAME;

  if (!$IDENTITY_RECORD && $GLOBALS['_action']!='add-identity')
    return rcube_label('notfound');

  list($form_start, $form_end) = get_form_tags($attrib, 'save-identity', array('name' => '_iid', 'value' => $IDENTITY_RECORD['identity_id']));
  unset($attrib['form']);


  // list of available cols
  $a_show_cols = array('name'         => array('type' => 'text'),
                       'email'        => array('type' => 'text'),
                       'organization' => array('type' => 'text'),
                       'reply-to'     => array('type' => 'text', 'label' => 'replyto'),
                       'bcc'          => array('type' => 'text'),
                       'default'      => array('type' => 'checkbox', 'label' => 'setdefault'));


  // a specific part is requested
  if ($attrib['part'])
    {
    $colprop = $a_show_cols[$attrib['part']];
    if (is_array($colprop))
      {
      $out = $form_start;
      $out .= rcmail_get_edit_field($attrib['part'], $IDENTITY_RECORD[$attrib['part']], $attrib, $colprop['type']); 
      return $out;
      }
    else
      return '';
    }


  // return the complete edit form as table
  $out = "$form_start<table>\n\n";

  foreach ($a_show_cols as $col => $colprop)
    {
    $attrib['id'] = 'rcmfd_'.$col;
    $label = strlen($colprop['label']) ? $colprop['label'] : $col;
    $value = rcmail_get_edit_field($col, $IDENTITY_RECORD[$col], $attrib, $colprop['type']);

    $out .= sprintf("<tr><td class=\"title\"><label for=\"%s\">%s</label></td><td>%s</td></tr>\n",
                    $attrib['id'],
                    rep_specialchars_output(rcube_label($label)),
                    $value);
    }

  $out .= "\n</table>$form_end";

  return $out;  
  }



if ($_action=='add-identity' && template_exists('addidentity'))
  parse_template('addidentity');

parse_template('editidentity');
?>