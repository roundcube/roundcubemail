<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/ldapsearch.inc                              |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Show an ldap search form in the addressbook                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Justin Randell <justin.randell@gmail.com>                     |
 +-----------------------------------------------------------------------+

 $Id$

*/
require_once 'include/rcube_ldap.inc';

/**
 * draw the ldap public search form
 */
function rcmail_ldap_public_search_form($attrib)
  {
  global $CONFIG, $JS_OBJECT_NAME, $OUTPUT; 
  if (!$CONFIG['ldap_public'])
    {
    // no ldap servers to search
    show_message('noldapserver', 'warning');
    rcmail_overwrite_action('add');
    return false;
    }
  else
    {
    // store some information in the session
    $_SESSION['ldap_public']['server_count'] = $server_count = count($CONFIG['ldap_public']);
    $_SESSION['ldap_public']['server_names'] = $server_names = array_keys($CONFIG['ldap_public']);
    }
  
  list($form_start, $form_end) = get_form_tags($attrib);
  $out = "$form_start<table id=\"ldap_public_search_table\">\n\n";
  
  // search name field
  $search_name = new textfield(array('name' => '_ldap_public_search_name',
                                     'id'   => 'rcmfd_ldap_public_search_name'));
  $out .= "<tr><td class=\"title\"><label for=\"rcmfd_ldap_public_search_name\">" . 
          rep_specialchars_output(rcube_label('ldappublicsearchname')) . 
          "</label></td><td>" . $search_name->show() . "</td></tr>\n";


  // there's more than one server to search for, show a dropdown menu
  if ($server_count > 1)
    {
    $select_server = new select(array('name' => '_ldap_public_servers', 
                                      'id'   => 'rcfmd_ldap_public_servers'));
     
    $select_server->add($server_names, $server_names);

    $out .= '<tr><td class="title"><label for="rcfmd_ldap_public_servers">' .
            rep_specialchars_output(rcube_label('ldappublicserverselect')) .
            "</label></td><td>" . $select_server->show() . "</td></tr>\n";
    }
  
  // foreach configured ldap server, set up the search fields
  for ($i = 0; $i < $server_count; $i++)
    {
    $server = $CONFIG['ldap_public'][$server_names[$i]];
    
    // only display one search fields select - js takes care of the rest
    if (!$i)
      {
      $field_name = '_ldap_public_search_field';
      $field_id   = 'rcfmd_ldap_public_search_field';

      $search_fields = new select(array('name' => $field_name, 
                                        'id'   => $field_id));

      $search_fields->add(array_keys($server['search_fields']), array_values($server['search_fields']));
      $out .= '<tr><td class="title"><label for="' . $field_id . '">' .
              rep_specialchars_output(rcube_label('ldappublicsearchfield')) . 
              "</label></td><td>" . $search_fields->show() . "</td></tr>\n";
      
      $attributes = array('name'  => '_ldap_public_search_type', 
                          'id'    => 'rcmfd_ldap_public_search_type');

      // if there's only one server, and it doesn't accept fuzzy searches,
      // then check and disable the check box - thanks pieter
      if ($server_count == 1 && !$server['fuzzy_search'])
        {
        $attributes['CHECKED'] = 'CHECKED'; 
        $attributes['disabled'] = 'disabled'; 
        }

      $search_type = new checkbox($attributes);

      $out .= '<tr id="ldap_fuzzy_search"><td class="title"><label for="rcmfd_ldap_public_search_type">' .
              rep_specialchars_output(rcube_label('ldappublicsearchtype')) .
              "</label></td><td>" . $search_type->show() . "</td></tr>\n";
      }
    
    if ($server_count > 1)
      {
      // store the search fields in a js array for each server
      $js = '';
      foreach ($server['search_fields'] as $k => $search_field)
        $js .= "'$search_field', ";

      // store whether this server accepts fuzzy search as last item in array
      $js .= $server['fuzzy_search'] ? "'fuzzy'" : "'exact'";
      $OUTPUT->add_script("rcmail.set_env('{$server_names[$i]}_search_fields', new Array($js));");
      }
    }

  // add contact button label text
  $OUTPUT->add_script("rcmail.set_env('addcontact', '" . rcube_label('addcontact') . "');");

  $out .= "\n</table>$form_end";
  return $out;  
  }

/**
 * get search values and return ldap contacts
 */
function rcmail_ldap_public_list()
  {
  // just return if we are not being called from a search form
  if (!isset($_POST['_action']))
    return null;

  global $CONFIG, $OUTPUT, $JS_OBJECT_NAME;
  
  // show no search name warning and exit
  if (empty($_POST['_ldap_public_search_name']) || trim($_POST['_ldap_public_search_name']) == '')
    {
    show_message('nosearchname', 'warning');
    return false;
    }
  
  // set up ldap server(s) array or bail
  if ($_SESSION['ldap_public']['server_count'] > 1)
    // show no ldap server warning and exit
    if (empty($_POST['_ldap_public_servers']))
      {
      show_message('noldappublicserver', 'warning');
      return false;
      }
    else
      $server_name = $_POST['_ldap_public_servers'];
  else if ($_SESSION['ldap_public']['server_count'] == 1)
    $server_name = $_SESSION['ldap_public']['server_names'][0];
  else
    return false;

  // get search parameters
  $search_value = $_POST['_ldap_public_search_name'];
  $search_field = $_POST['_ldap_public_search_field'];

  // only use the post var for search type if the ldap server allows 'like'
  $exact = true;
  if ($CONFIG['ldap_public'][$server_name]['fuzzy_search'])
    $exact = isset($_POST['_ldap_public_search_type']) ? true : false; 
  
  // perform an ldap search
  $contacts = rcmail_ldap_contact_search($search_value, 
                                         $search_field, 
                                         $CONFIG['ldap_public'][$server_name], 
                                         $exact);
  
  // if no results, show a warning and return
  if (!$contacts)
    {
    show_message('nocontactsreturned', 'warning');
    return false;
    }

  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'ldapAddressList';
  
  // define table class
  $attrib['class'] = 'records-table';
  $attrib['cellspacing'] = 0;

  // define list of cols to be displayed
  $a_show_cols = array('name', 'email');

  // create XHTML table  
  $out = rcube_table_output($attrib, $contacts, $a_show_cols, 'row_id');

  // set client env
  $javascript = "$JS_OBJECT_NAME.gui_object('ldapcontactslist', '{$attrib['id']}');\n";
  $OUTPUT->add_script($javascript);  
  
  return $out;  
  }

/**
 * perform search for contacts from given public ldap server
 */
function rcmail_ldap_contact_search($search_value, $search_field, $server, $exact=true)
  {
  global $CONFIG;
  
  $attributes = array($server['name_field'], $server['mail_field']); 

  $LDAP = new rcube_ldap();
  if ($LDAP->connect($server['hosts'], $server['port'], $server['protocol']))
    {
    $filter = "$search_field=" . ($exact ? $search_value : "*$search_value*"); 
    $result = $LDAP->search($server['base_dn'],
                            $filter, 
                            $attributes, 
                            $server['scope'], 
                            $sort=null);
         
    // add any results to contact array
    if ($result['count'])
      {
      for ($n = 0; $n < $result['count']; $n++)
        {
        $contacts[$n]['name']   = $result[$n][$server['name_field']][0];
        $contacts[$n]['email']  = $result[$n][$server['mail_field']][0];
        $contacts[$n]['row_id'] = $n + 1;
        }
      }
    }
  else
    return false;

  // cleanup
  $LDAP->close();

  if (!$result['count'])
    return false;
 
  // weed out duplicate emails
  for ($n = 0; $n < $result['count']; $n++)
    for ($i = 0; $i < $result['count']; $i++)
      if ($contacts[$i]['email'] == $contacts[$n]['email'] && $i != $n)
        unset($contacts[$n]);

  return $contacts;
  }

function get_form_tags($attrib)
  {
  global $OUTPUT, $JS_OBJECT_NAME, $EDIT_FORM, $SESS_HIDDEN_FIELD;  

  $form_start = '';
  if (!strlen($EDIT_FORM))
    {
    $hiddenfields = new hiddenfield(array('name' => '_task', 'value' => $GLOBALS['_task']));
    $hiddenfields->add(array('name' => '_action', 'value' => 'ldappublicsearch'));
    
    if ($_GET['_framed'] || $_POST['_framed'])
      $hiddenfields->add(array('name' => '_framed', 'value' => 1));
    
    $form_start .= !strlen($attrib['form']) ? '<form name="form" action="./" method="post">' : '';
    $form_start .= "\n$SESS_HIDDEN_FIELD\n";
    $form_start .= $hiddenfields->show();
    }
    
  $form_end = (strlen($EDIT_FORM) && !strlen($attrib['form'])) ? '</form>' : '';
  $form_name = strlen($attrib['form']) ? $attrib['form'] : 'form';
  
  $OUTPUT->add_script("$JS_OBJECT_NAME.gui_object('ldappublicsearchform', '$form_name');");
  
  $EDIT_FORM = $form_name;

  return array($form_start, $form_end);  
  }

parse_template('ldappublicsearch');
?>
