<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/func.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
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

// instantiate a contacts object according to the given source
$CONTACTS = $RCMAIL->get_address_book(($source = get_input_value('_source', RCUBE_INPUT_GPC)));

$CONTACTS->set_pagesize($CONFIG['pagesize']);

// set list properties and session vars
if (!empty($_GET['_page']))
  $CONTACTS->set_page(($_SESSION['page'] = intval($_GET['_page'])));
else
  $CONTACTS->set_page(isset($_SESSION['page']) ?$_SESSION['page'] : 1);

// set message set for search result
if (!empty($_REQUEST['_search']) && isset($_SESSION['search'][$_REQUEST['_search']]))
  $CONTACTS->set_search_set($_SESSION['search'][$_REQUEST['_search']]);

// set data source env
$OUTPUT->set_env('source', $source ? $source : '0');
$OUTPUT->set_env('readonly', $CONTACTS->readonly, false);

// add list of address sources to client env
$js_list = array();
if (strtolower($CONFIG['address_book_type']) != 'ldap') {
  // We are using the DB address book, add it.
  $js_list = array("0" => array('id' => 0, 'readonly' => false));
}
if (is_array($CONFIG['ldap_public'])) {
  foreach ($CONFIG['ldap_public'] as $id => $prop)
    $js_list[$id] = array('id' => $id, 'readonly' => !$prop['writable']);
}
$OUTPUT->set_env('address_sources', $js_list);


function rcmail_directory_list($attrib)
{
  global $CONFIG, $OUTPUT;
  
  if (!$attrib['id'])
    $attrib['id'] = 'rcmdirectorylist';

  $out = '';
  $local_id = '0';
  $current = get_input_value('_source', RCUBE_INPUT_GPC);
  $line_templ = html::tag('li', array('id' => 'rcmli%s', 'class' => '%s'),
    html::a(array('href' => '%s', 'onclick' => "return ".JS_OBJECT_NAME.".command('list','%s',this)"), '%s'));
    
  if (strtolower($CONFIG['address_book_type']) != 'ldap') {
    $out .= sprintf($line_templ, $local_id, (!$current ? 'selected' : ''),
      Q(rcmail_url(null, array('_source' => $local_id))), $local_id, rcube_label('personaladrbook'));
  } // end if
  else {
    // DB address book not used, see if a source is set, if not use the
    // first LDAP directory.
    if (!$current) {
      $current = key((array)$CONFIG['ldap_public']);
    } // end if
  } // end else
  
  foreach ((array)$CONFIG['ldap_public'] as $id => $prop) {
    $js_id = JQ($id);
    $dom_id = preg_replace('/[^a-z0-9\-_]/i', '', $id);
    $out .= sprintf($line_templ, $dom_id, ($current == $id ? 'selected' : ''),
      Q(rcmail_url(null, array('_source' => $id))), $js_id, (!empty($prop['name']) ? Q($prop['name']) : Q($id)));
  }
  
  $OUTPUT->add_gui_object('folderlist', $attrib['id']);
  
  return html::tag('ul', $attrib, $out, html::$common_attrib);
}


// return the message list as HTML table
function rcmail_contacts_list($attrib)
  {
  global $CONTACTS, $OUTPUT;
  
  // count contacts for this user
  $result = $CONTACTS->list_records();
  
  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcmAddressList';
  
  // define list of cols to be displayed
  $a_show_cols = array('name');

  // create XHTML table
  $out = rcube_table_output($attrib, $result->records, $a_show_cols, $CONTACTS->primary_key);
  
  // set client env
  $OUTPUT->add_gui_object('contactslist', $attrib['id']);
  $OUTPUT->set_env('current_page', (int)$CONTACTS->list_page);
  $OUTPUT->set_env('pagecount', ceil($result->count/$CONTACTS->page_size));
  $OUTPUT->include_script('list.js');
  
  // add some labels to client
  rcube_add_label('deletecontactconfirm');
  
  return $out;
  }


function rcmail_js_contacts_list($result, $prefix='')
  {
  global $OUTPUT;

  if (empty($result) || $result->count == 0)
    return;

  // define list of cols to be displayed
  $a_show_cols = array('name');
  
  while ($row = $result->next())
    {
    $a_row_cols = array();
    
    // format each col
    foreach ($a_show_cols as $col)
      $a_row_cols[$col] = Q($row[$col]);
    
    $OUTPUT->command($prefix.'add_contact_row', $row['ID'], $a_row_cols);
    }
  }


// similar function as /steps/settings/identities.inc::rcmail_identity_frame()
function rcmail_contact_frame($attrib)
  {
  global $OUTPUT;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmcontactframe';
    
  $attrib['name'] = $attrib['id'];

  $OUTPUT->set_env('contentframe', $attrib['name']);
  $OUTPUT->set_env('blankpage', $attrib['src'] ? $OUTPUT->abs_url($attrib['src']) : 'program/blank.gif');

  return html::iframe($attrib);
  }


function rcmail_rowcount_display($attrib)
  {
  global $OUTPUT;
  
  if (!$attrib['id'])
    $attrib['id'] = 'rcmcountdisplay';

  $OUTPUT->add_gui_object('countdisplay', $attrib['id']);

  return html::span($attrib, rcmail_get_rowcount_text());
  }



function rcmail_get_rowcount_text()
  {
  global $CONTACTS;
  
  // read nr of contacts
  $result = $CONTACTS->get_result();
  if (!$result)
    $result = $CONTACTS->count();
  
  if ($result->count == 0)
    $out = rcube_label('nocontactsfound');
  else
    $out = rcube_label(array(
      'name' => 'contactsfromto',
      'vars' => array(
        'from'  => $result->first + 1,
        'to'    => min($result->count, $result->first + $CONTACTS->page_size),
        'count' => $result->count)
      ));

  return $out;
  }


$OUTPUT->set_pagetitle(rcube_label('addressbook'));
  
// register UI objects
$OUTPUT->add_handlers(array(
  'directorylist' => 'rcmail_directory_list',
  'addresslist' => 'rcmail_contacts_list',
  'addressframe' => 'rcmail_contact_frame',
  'recordscountdisplay' => 'rcmail_rowcount_display',
  'searchform' => array($OUTPUT, 'search_form')
));

?>
