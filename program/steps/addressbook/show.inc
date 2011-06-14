<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/show.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, The Roundcube Dev Team                       |
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

// Get contact ID and source ID from request
$cids   = rcmail_get_cids();
$source = key($cids);
$cid    = array_shift($cids[$source]);

// Initialize addressbook source
$CONTACTS = rcmail_contact_source($source, true);

// read contact record
if ($cid && ($record = $CONTACTS->get_record($cid, true))) {
    $OUTPUT->set_env('cid', $record['ID']);
}

// return raw photo of the given contact
if ($RCMAIL->action == 'photo') {
    if (($file_id = get_input_value('_photo', RCUBE_INPUT_GPC)) && ($tempfile = $_SESSION['contacts']['files'][$file_id])) {
        $tempfile = $RCMAIL->plugins->exec_hook('attachment_display', $tempfile);
        if ($tempfile['status']) {
            if ($tempfile['data'])
                $data = $tempfile['data'];
            else if ($tempfile['path'])
                $data = file_get_contents($tempfile['path']);
        }
    }
    else if ($record['photo']) {
        $data = is_array($record['photo']) ? $record['photo'][0] : $record['photo'];
        if (!preg_match('![^a-z0-9/=+-]!i', $data))
            $data = base64_decode($data, true);
    }

    header('Content-Type: ' . rc_image_content_type($data));
    echo $data ? $data : file_get_contents('program/blank.gif');
    exit;
}


function rcmail_contact_head($attrib)
{
    global $CONTACTS, $RCMAIL;

    // check if we have a valid result
    if (!(($result = $CONTACTS->get_result()) && ($record = $result->first()))) {
        $RCMAIL->output->show_message('contactnotfound');
        return false;
    }

    $microformats = array('name' => 'fn', 'email' => 'email');

    $form = array(
        'head' => array(  // section 'head' is magic!
            'content' => array(
                'prefix' => array('type' => 'text'),
                'firstname' => array('type' => 'text'),
                'middlename' => array('type' => 'text'),
                'surname' => array('type' => 'text'),
                'suffix' => array('type' => 'text'),
            ),
        ),
    );

    unset($attrib['name']);
    return rcmail_contact_form($form, $record, $attrib);
}


function rcmail_contact_details($attrib)
{
    global $CONTACTS, $RCMAIL, $CONTACT_COLTYPES;

    // check if we have a valid result
    if (!(($result = $CONTACTS->get_result()) && ($record = $result->first()))) {
        //$RCMAIL->output->show_message('contactnotfound');
        return false;
    }

    $i_size = !empty($attrib['size']) ? $attrib['size'] : 40;

    $form = array(
        'contact' => array(
            'name'    => rcube_label('contactproperties'),
            'content' => array(
              'email' => array('size' => $i_size, 'render_func' => 'rcmail_render_email_value'),
              'phone' => array('size' => $i_size),
              'address' => array(),
              'website' => array('size' => $i_size, 'render_func' => 'rcmail_render_url_value'),
              'im' => array('size' => $i_size),
            ),
        ),
        'personal' => array(
            'name'    => rcube_label('personalinfo'),
            'content' => array(
                'gender' => array('size' => $i_size),
                'maidenname' => array('size' => $i_size),
                'birthday' => array('size' => $i_size),
                'anniversary' => array('size' => $i_size),
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
                'notes' => array('type' => 'textarea', 'label' => false),
            ),
        );
    }
    
    if ($CONTACTS->groups) {
        $form['groups'] = array(
            'name'    => rcube_label('groups'),
            'content' => rcmail_contact_record_groups($record['ID']),
        );
    }

    return rcmail_contact_form($form, $record);
}


function rcmail_render_email_value($email, $col)
{
    return html::a(array(
        'href' => 'mailto:' . $email,
        'onclick' => sprintf("return %s.command('compose','%s',this)", JS_OBJECT_NAME, JQ($email)),
        'title' => rcube_label('composeto'),
        'class' => 'email',
    ), Q($email));
}


function rcmail_render_url_value($url, $col)
{
    $prefix = preg_match('!^(http|ftp)s?://!', $url) ? '' : 'http://';
    return html::a(array(
        'href' => $prefix . $url,
        'target' => '_blank',
        'class' => 'url',
    ), Q($url));
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


$OUTPUT->add_handlers(array(
    'contacthead'    => 'rcmail_contact_head',
    'contactdetails' => 'rcmail_contact_details',
    'contactphoto'   => 'rcmail_contact_photo',
));

$OUTPUT->send('contact');
