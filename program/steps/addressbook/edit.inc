<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/edit.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2007, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Show edit form for a contact entry or to add a new one              |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


if (($cid = get_input_value('_cid', RCUBE_INPUT_GPC)) && ($record = $CONTACTS->get_record($cid, true)))
    $OUTPUT->set_env('cid', $record['ID']);

// adding not allowed here
if ($CONTACTS->readonly) {
    $OUTPUT->show_message('sourceisreadonly');
    rcmail_overwrite_action('show');
    return;
}


function rcmail_contact_edithead($attrib)
{
   global $RCMAIL, $CONTACTS;

    // check if we have a valid result
    if ($RCMAIL->action != 'add'
        && !(($result = $CONTACTS->get_result()) && ($record = $result->first()))
    ) {
        $RCMAIL->output->show_message('contactnotfound');
        return false;
    }
    
    $i_size = !empty($attrib['size']) ? $attrib['size'] : 20;

    $form = array(
        'head' => array(
            'content' => array(
                'prefix' => array('size' => $i_size),
                'firstname' => array('size' => $i_size, 'visible' => true),
                'middlename' => array('size' => $i_size),
                'surname' => array('size' => $i_size, 'visible' => true),
                'suffix' => array('size' => $i_size),
                'name' => array('size' => 2*$i_size),
                'nickname' => array('size' => 2*$i_size),
                'company' => array('size' => $i_size),
                'department' => array('size' => $i_size),
                'jobtitle' => array('size' => $i_size),
            )
        )
    );

    list($form_start, $form_end) = get_form_tags($attrib);
    unset($attrib['form'], $attrib['name'], $attrib['size']);

    // return the address edit form
    $out = rcmail_contact_form($form, $record, $attrib);

    return $form_start . $out . $form_end;
}


function rcmail_contact_editform($attrib)
{
   global $RCMAIL, $CONTACTS, $CONTACT_COLTYPES;

    // check if we have a valid result
    if ($RCMAIL->action != 'add'
        && !(($result = $CONTACTS->get_result()) && ($record = $result->first()))
    ) {
        $RCMAIL->output->show_message('contactnotfound');
        return false;
    }

    // add some labels to client
    $RCMAIL->output->add_label('noemailwarning', 'nonamewarning');

    $i_size = !empty($attrib['size']) ? $attrib['size'] : 40;
    $t_rows = !empty($attrib['textarearows']) ? $attrib['textarearows'] : 10;
    $t_cols = !empty($attrib['textareacols']) ? $attrib['textareacols'] : 40;

    $form = array(
        'info' => array(
            'name'    => rcube_label('contactproperties'),
            'content' => array(
                'gender' => array('visible' => false),
                'maidenname' => array('size' => $i_size),
                'email' => array('size' => $i_size, 'visible' => true),
                'phone' => array('size' => $i_size, 'visible' => true),
                'address' => array('visible' => true),
                'birthday' => array('size' => 12),
                'anniversary' => array('size' => $i_size),
                'website' => array('size' => $i_size),
                'im' => array('size' => $i_size),
                'manager' => array('size' => $i_size),
                'assistant' => array('size' => $i_size),
                'spouse' => array('size' => $i_size),
            ),
        ),
    );
    
    if (isset($CONTACT_COLTYPES['notes'])) {
        $form['notes'] = array(
            'name'    => rcube_label('notes'),
            'content' => array(
                'notes' => array('size' => $t_cols, 'rows' => $t_rows, 'label' => false, 'visible' => true, 'limit' => 1),
            ),
            'single' => true,
        );
    }

    list($form_start, $form_end) = get_form_tags($attrib);
    unset($attrib['form']);

    // return the complete address edit form as table
    $out = rcmail_contact_form($form, $record, $attrib);

    return $form_start . $out . $form_end;
}


function rcmail_upload_photo_form($attrib)
{
  global $OUTPUT;

  // add ID if not given
  if (!$attrib['id'])
    $attrib['id'] = 'rcmUploadbox';

  // find max filesize value
  $max_filesize = parse_bytes(ini_get('upload_max_filesize'));
  $max_postsize = parse_bytes(ini_get('post_max_size'));
  if ($max_postsize && $max_postsize < $max_filesize)
    $max_filesize = $max_postsize;
  $max_filesize = show_bytes($max_filesize);
  
  $hidden = new html_hiddenfield(array('name' => '_cid', 'value' => $GLOBALS['cid']));
  $input = new html_inputfield(array('type' => 'file', 'name' => '_photo', 'size' => $attrib['size']));
  $button = new html_inputfield(array('type' => 'button'));
  
  $out = html::div($attrib,
    $OUTPUT->form_tag(array('name' => 'uploadform', 'method' => 'post', 'enctype' => 'multipart/form-data'),
      $hidden->show() .
      html::div(null, $input->show()) .
      html::div('hint', rcube_label(array('name' => 'maxuploadsize', 'vars' => array('size' => $max_filesize)))) .
      html::div('buttons',
        $button->show(rcube_label('close'), array('class' => 'button', 'onclick' => "$('#$attrib[id]').hide()")) . ' ' .
        $button->show(rcube_label('upload'), array('class' => 'button mainaction', 'onclick' => JS_OBJECT_NAME . ".command('upload-photo', this.form)"))
      )
    )
  );
  
  $OUTPUT->add_label('addphoto','replacephoto');
  $OUTPUT->add_gui_object('uploadbox', $attrib['id']);
  return $out;
}


// similar function as in /steps/settings/edit_identity.inc
function get_form_tags($attrib)
{
    global $CONTACTS, $EDIT_FORM, $RCMAIL;

    $form_start = $form_end = '';

    if (empty($EDIT_FORM)) {
        $hiddenfields = new html_hiddenfield(array(
            'name' => '_source', 'value' => get_input_value('_source', RCUBE_INPUT_GPC)));
        $hiddenfields->add(array('name' => '_gid', 'value' => $CONTACTS->group_id));

        if (($result = $CONTACTS->get_result()) && ($record = $result->first()))
            $hiddenfields->add(array('name' => '_cid', 'value' => $record['ID']));

        $form_start = $RCMAIL->output->request_form(array(
            'name' => "form", 'method' => "post",
            'task' => $RCMAIL->task, 'action' => 'save',
            'request' => 'save.'.intval($record['ID']),
            'noclose' => true) + $attrib, $hiddenfields->show());
        $form_end = !strlen($attrib['form']) ? '</form>' : '';

        $EDIT_FORM = !empty($attrib['form']) ? $attrib['form'] : 'form';
        $RCMAIL->output->add_gui_object('editform', $EDIT_FORM);
    }

    return array($form_start, $form_end); 
}


$OUTPUT->add_handler('contactedithead', 'rcmail_contact_edithead');
$OUTPUT->add_handler('contacteditform', 'rcmail_contact_editform');
$OUTPUT->add_handler('contactphoto',    'rcmail_contact_photo');
$OUTPUT->add_handler('photouploadform', 'rcmail_upload_photo_form');

if (!$CONTACTS->get_result() && $OUTPUT->template_exists('contactadd'))
    $OUTPUT->send('contactadd');

// this will be executed if no template for addcontact exists
$OUTPUT->send('contactedit');
