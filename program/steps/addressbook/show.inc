<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/show.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, Roundcube Dev. - Switzerland                 |
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
    global $CONTACTS, $RCMAIL;

    // check if we have a valid result
    if (!(($result = $CONTACTS->get_result()) && ($record = $result->first()))) {
        $RCMAIL->output->show_message('contactnotfound');
        return false;
    }

    $i_size = !empty($attrib['size']) ? $attrib['size'] : 40;
    $t_rows = !empty($attrib['textarearows']) ? $attrib['textarearows'] : 6;
    $t_cols = !empty($attrib['textareacols']) ? $attrib['textareacols'] : 40;

    $microformats = array('name' => 'fn', 'email' => 'email');

    $form = array(
        'info' => array(
            'name'    => rcube_label('contactproperties'),
            'content' => array(
                'name' => array('type' => 'text', 'size' => $i_size),
                'firstname' => array('type' => 'text', 'size' => $i_size),
                'surname' => array('type' => 'text', 'size' => $i_size),
                'email' => array('type' => 'text', 'size' => $i_size),
            ),
        ),
        'groups' => array(
            'name'    => rcube_label('groups'),
            'content' => '',
        ),
    );

    // Get content of groups fieldset
    if ($groups = rcmail_contact_record_groups($record['ID'])) {
        $form['groups']['content'] = $groups;    
    }
    else {
        unset($form['groups']);
    }

    if (!empty($record['email'])) {
        $form['info']['content']['email']['value'] = html::a(array(
            'href' => 'mailto:' . $record['email'],
            'onclick' => sprintf("return %s.command('compose','%s',this)", JS_OBJECT_NAME, JQ($record['email'])),
            'title' => rcube_label('composeto'),
            'class' => $microformats['email'],
        ), Q($record['email']));
    }
    foreach (array('name', 'firstname', 'surname') as $col) {
        if ($record[$col]) {
            $form['info']['content'][$col]['value'] = html::span($microformats[$col], Q($record[$col]));
        }
    }

    return rcmail_contact_form($form, $record);
}


function rcmail_contact_record_groups($contact_id)
{
    global $RCMAIL, $CONTACTS, $GROUPS;

    $GROUPS = $CONTACTS->list_groups();

    if (empty($GROUPS)) {
        return '';
    }

    $table = new html_table(array('cols' => 2, 'cellspacing' => 0, 'border' => 0));

    $members = $CONTACTS->get_record_groups($contact_id);
    $checkbox = new html_checkbox(array('name' => '_gid[]',
        'class' => 'groupmember', 'disabled' => $CONTACTS->readonly));

    foreach ($GROUPS as $group) {
        $gid = $group['ID'];
        $table->add(null, $checkbox->show($members[$gid] ? $gid : null,
            array('value' => $gid, 'id' => 'ff_gid' . $gid)));
        $table->add(null, html::label('ff_gid' . $gid, Q($group['name'])));
    }

    $hiddenfields = new html_hiddenfield(array('name' => '_source', 'value' => get_input_value('_source', RCUBE_INPUT_GPC)));
    $hiddenfields->add(array('name' => '_cid', 'value' => $record['ID']));

    $form_start = $RCMAIL->output->request_form(array(
        'name' => "form", 'method' => "post",
        'task' => $RCMAIL->task, 'action' => 'save',
        'request' => 'save.'.intval($contact_id),
        'noclose' => true), $hiddenfields->show());
    $form_end = '</form>';

    $RCMAIL->output->add_gui_object('editform', 'form');
  
    return $form_start . $table->show() . $form_end;
}


//$OUTPUT->framed = $_framed;
$OUTPUT->add_handler('contactdetails', 'rcmail_contact_details');

$OUTPUT->send('contact');
