<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/func.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide addressbook functionality and GUI objects                   |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$CONTACTS_LIST = array();

// set list properties and session vars
if (strlen($_GET['_page']))
  {
  $CONTACTS_LIST['page'] = $_GET['_page'];
  $_SESSION['page'] = $_GET['_page'];
  }
else
  $CONTACTS_LIST['page'] = $_SESSION['page'] ? $_SESSION['page'] : 1;

// disable the ldap public search button if there's no servers configured
$enable_ldap = 'true';
if (!$CONFIG['ldap_public'])
  $enable_ldap = 'false';
  
$OUTPUT->add_script("$JS_OBJECT_NAME.set_env('ldappublicsearch', $enable_ldap);");  

// return the message list as HTML table
function rcmail_contacts_list($attrib)
  {
  global $DB, $CONFIG, $OUTPUT, $CONTACTS_LIST, $JS_OBJECT_NAME;
  
  //$skin_path = $CONFIG['skin_path'];
  //$image_tag = '<img src="%s%s" alt="%s" border="0" />';
  
  // count contacts for this user
  $sql_result = $DB->query("SELECT COUNT(contact_id) AS rows
                            FROM ".get_table_name('contacts')."
                            WHERE  del<>1
                            AND    user_id=?",
                            $_SESSION['user_id']);

  $sql_arr = $DB->fetch_assoc($sql_result);
  $rowcount = $sql_arr['rows'];

  if ($rowcount)
    {
    $start_row = ($CONTACTS_LIST['page']-1) * $CONFIG['pagesize'];

    // get contacts from DB
    $sql_result = $DB->limitquery("SELECT * FROM ".get_table_name('contacts')."
                                   WHERE  del<>1
                                   AND    user_id= ?
                                   ORDER BY name",
                                   $start_row,
                                   $CONFIG['pagesize'],
                                   $_SESSION['user_id']);
    }
  else
    $sql_result = NULL;


  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcmAddressList';

  // define list of cols to be displayed
  $a_show_cols = array('name', 'email');

  // create XHTML table  
  $out = rcube_table_output($attrib, $sql_result, $a_show_cols, 'contact_id');

  // set client env
  $javascript = sprintf("%s.gui_object('contactslist', '%s');\n", $JS_OBJECT_NAME, $attrib['id']);
  $javascript .= sprintf("%s.set_env('current_page', %d);\n", $JS_OBJECT_NAME, $CONTACTS_LIST['page']);
  $javascript .= sprintf("%s.set_env('pagecount', %d);\n", $JS_OBJECT_NAME, ceil($rowcount/$CONFIG['pagesize']));
  $javascript .= "rcmail.set_env('newcontact', '" . rcube_label('newcontact') . "');";
  //$javascript .= sprintf("%s.set_env('contacts', %s);", $JS_OBJECT_NAME, array2js($a_js_message_arr));
  
  $OUTPUT->add_script($javascript);  

  // add some labels to client
  rcube_add_label('deletecontactconfirm');

  return $out;
  }



function rcmail_js_contacts_list($sql_result, $obj_name='this')
  {
  global $DB;

  $commands = '';
  
  if (!$sql_result)
    return '';

  // define list of cols to be displayed
  $a_show_cols = array('name', 'email');
    
  while ($sql_arr = $DB->fetch_assoc($sql_result))
    {
    $a_row_cols = array();
            
    // format each col
    foreach ($a_show_cols as $col)
      {
      $cont = rep_specialchars_output($sql_arr[$col]);
      $a_row_cols[$col] = $cont;
      }
  
    $commands .= sprintf("%s.add_contact_row(%s, %s);\n",
                         $obj_name,
                         $sql_arr['contact_id'],
                         array2js($a_row_cols));
    }
    
  return $commands;
  }


// similar function as /steps/settings/identities.inc::rcmail_identity_frame()
function rcmail_contact_frame($attrib)
  {
  global $OUTPUT, $JS_OBJECT_NAME;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmcontactframe';
    
  $attrib['name'] = $attrib['id'];

  $OUTPUT->add_script(sprintf("%s.set_env('contentframe', '%s');", $JS_OBJECT_NAME, $attrib['name']));

  $attrib_str = create_attrib_string($attrib, array('name', 'id', 'class', 'style', 'src', 'width', 'height', 'frameborder'));
  $out = '<iframe'. $attrib_str . '></iframe>';
    
  return $out;
  }


function rcmail_rowcount_display($attrib)
  {
  global $OUTPUT, $JS_OBJECT_NAME;
  
  if (!$attrib['id'])
    $attrib['id'] = 'rcmcountdisplay';

  $OUTPUT->add_script(sprintf("%s.gui_object('countdisplay', '%s');", $JS_OBJECT_NAME, $attrib['id']));

  // allow the following attributes to be added to the <span> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id'));

  
  $out = '<span' . $attrib_str . '>';
  $out .= rcmail_get_rowcount_text();
  $out .= '</span>';
  return $out;
  }



function rcmail_get_rowcount_text($max=NULL)
  {
  global $CONTACTS_LIST, $CONFIG, $DB;
  
  $start_row = ($CONTACTS_LIST['page']-1) * $CONFIG['pagesize'] + 1;

  // get nr of contacts
  if ($max===NULL)
    {
    $sql_result = $DB->query("SELECT 1 FROM ".get_table_name('contacts')."
                              WHERE  del<>1
                              AND    user_id=?",
                              $_SESSION['user_id']);

    $max = $DB->num_rows($sql_result);
    }

  if ($max==0)
    $out = rcube_label('nocontactsfound');
  else
    $out = rcube_label(array('name' => 'contactsfromto',
                             'vars' => array('from'  => $start_row,
                                             'to'    => min($max, $start_row + $CONFIG['pagesize'] - 1),
                                             'count' => $max)));

  return $out;
  }

?>
