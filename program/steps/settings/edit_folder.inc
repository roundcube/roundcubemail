<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/edit_folder.inc                                |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, Roundcube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide functionality to create/edit a folder                       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id$

*/

// WARNING: folder names in UI are encoded with RCMAIL_CHARSET

// init IMAP connection
$RCMAIL->imap_connect();

function rcube_folder_form($attrib)
{
    global $RCMAIL;

    // edited folder name (empty in create-folder mode)
    $mbox      = trim(get_input_value('_mbox', RCUBE_INPUT_GPC, true));
    $mbox_imap = rcube_charset_convert($mbox, RCMAIL_CHARSET, 'UTF7-IMAP');

    // predefined path for new folder
    $parent      = trim(get_input_value('_path', RCUBE_INPUT_GPC, true));
    $parent_imap = rcube_charset_convert($parent, RCMAIL_CHARSET, 'UTF7-IMAP');

    $delimiter = $RCMAIL->imap->get_hierarchy_delimiter();
    $special   = (strlen($mbox_imap) && in_array($mbox_imap, (array) $RCMAIL->config->get('default_imap_folders')));
    $protected = ($special && $RCMAIL->config->get('protect_default_folders'));
    $threading_supported = $RCMAIL->imap->get_capability('thread=references')
        || $RCMAIL->imap->get_capability('thread=orderedsubject')
        || $RCMAIL->imap->get_capability('thread=refs');

    // Get mailbox stats (messages count, etc.), mailbox name and parent
    if (strlen($mbox)) {
        $msgcount = $RCMAIL->imap->messagecount($mbox_imap, 'ALL', true, false);

        $path   = explode($delimiter, $mbox_imap);
        $folder = array_pop($path);
        $path   = implode($delimiter, $path);

        $folder = rcube_charset_convert($folder, 'UTF7-IMAP');

        $hidden_fields = array('name' => '_mbox', 'value' => $mbox);
    }
    else {
        $path = $parent_imap;
    }

    $form = array();

    // General tab
    $form['props'] = array(
        'name' => rcube_label('properties'),
    );

    // Location (name)
    if ($protected)
        $foldername = rcmail_localize_foldername($mbox_imap);
    else {
        if (isset($_POST['_name']))
            $folder = trim(get_input_value('_name', RCUBE_INPUT_POST, true));

        $foldername = new html_inputfield(array('name' => '_name', 'id' => '_name', 'size' => 30));
        $foldername = $foldername->show($folder);

        if ($special)
            $foldername .= '&nbsp;(' . rcmail_localize_foldername($mbox_imap) .')';
    }

    $form['props']['fieldsets']['location'] = array(
        'name'  => rcube_label('location'),
        'content' => array(
            'name' => array(
                'label' => rcube_label('foldername'),
                'value' => $foldername,
            ),
        ),
    );

    if (strlen($path)) {
        $radio1 = new html_radiobutton(array('name' => '_parent', 'value' => ''));
        $radio2 = new html_radiobutton(array('name' => '_parent', 'value' => $path));
        $selected  = isset($_POST['_parent']) ? $_POST['_parent'] : $path;

        $html_path = str_replace($delimiter, ' &raquo; ', rcmail_localize_folderpath($path));

        $folderpath = $radio1->show($selected) . Q(rcube_label('none')) . '&nbsp;'
            .$radio2->show($selected) . Q($html_path);

        $form['props']['fieldsets']['location']['content']['path'] = array(
            'label' => rcube_label('parentfolder'),
            'value' => $folderpath,
        );
    }

    // Settings
    $form['props']['fieldsets']['settings'] = array(
        'name'  => rcube_label('settings'),
    );

    // Settings: threading
    if ($threading_supported) {
        $select = new html_select(array('name' => '_viewmode', 'id' => '_listmode'));
        $select->add(rcube_label('list'), 0);
        $select->add(rcube_label('threads'), 1);

        if (isset($_POST['_viewmode'])) {
            $value = (int) $_POST['_viewmode'];
        }
        else if (strlen($mbox_imap)) {
            $a_threaded = $RCMAIL->config->get('message_threading', array());
            $value = (int) isset($a_threaded[$mbox_imap]);
        }

        $form['props']['fieldsets']['settings']['content']['viewmode'] = array(
            'label' => rcube_label('listmode'),
            'value' => $select->show($value),
        );
    }
/*
    // Settings: sorting column
    $select = new html_select(array('name' => '_sortcol', 'id' => '_sortcol'));
    $select->add(rcube_label('nonesort'), '');
    $select->add(rcube_label('arrival'), 'arrival');
    $select->add(rcube_label('sentdate'), 'date');
    $select->add(rcube_label('subject'), 'subject');
    $select->add(rcube_label('fromto'), 'from');
    $select->add(rcube_label('replyto'), 'replyto');
    $select->add(rcube_label('cc'), 'cc');
    $select->add(rcube_label('size'), 'size');

    $value = isset($_POST['_sortcol']) ? $_POST['_sortcol'] : '';

    $form['props']['fieldsets']['settings']['content']['sortcol'] = array(
        'label' => rcube_label('listsorting'),
        'value' => $select->show($value),
    );

    // Settings: sorting order
    $select = new html_select(array('name' => '_sortord', 'id' => '_sortord'));
    $select->add(rcube_label('asc'), 'ASC');
    $select->add(rcube_label('desc'), 'DESC');

    $value = isset($_POST['_sortord']) ? $_POST['_sortord'] : '';

    $form['props']['fieldsets']['settings']['content']['sortord'] = array(
        'label' => rcube_label('listorder'),
        'value' => $select->show(),
    );
*/
    // Information (count, size) - Edit mode
    if (strlen($mbox)) {
        // Number of messages
        $form['props']['fieldsets']['info'] = array(
            'name'  => rcube_label('info'),
            'content' => array(
                'count' => array(
                    'label' => rcube_label('messagecount'),
                    'value' => (int) $msgcount,
                ),
            ),
        );

        // Size
        if ($msgcount) {
            // create link with folder-size command
            $onclick = sprintf("return %s.command('folder-size', '%s', this)",
                JS_OBJECT_NAME, JQ($mbox_imap));
            $size = html::a(array('href' => '#', 'onclick' => $onclick, 'id' => 'folder-size'),
                rcube_label('getfoldersize'));
        }
        else {
            // no messages -> zero size
            $size = 0;
        }
        $form['props']['fieldsets']['info']['content']['size'] = array(
            'label' => rcube_label('size'),
            'value' => $size,
        );
    }

    // Allow plugins to modify folder form content
    $plugin = $RCMAIL->plugins->exec_hook('folder_form', array('form' => $form));

    $form = $plugin['form'];

    // Set form tags and hidden fields
    list($form_start, $form_end) = get_form_tags($attrib, 'save-folder', null, $hidden_fields);

    unset($attrib['form']);

    // return the complete edit form as table
    $out = "$form_start\n";

    // Create form output
    foreach ($form as $tab) {
        if (!empty($tab['fieldsets']) && is_array($tab['fieldsets'])) {
            $content = '';
            foreach ($tab['fieldsets'] as $fieldset) {
                $subcontent = rcmail_get_form_part($fieldset);
                if ($subcontent) {
                    $content .= html::tag('fieldset', null, html::tag('legend', null, Q($fieldset['name'])) . $subcontent) ."\n";
                }
            }
        }
        else {
            $content = rcmail_get_form_part($tab);
        }

        if ($content) {        
            $out .= html::tag('fieldset', null, html::tag('legend', null, Q($tab['name'])) . $content) ."\n";
        }
    }

    $out .= "\n$form_end";

    $RCMAIL->output->set_env('messagecount', (int) $msgcount);

    return $out;
}

function rcmail_get_form_part($form)
{
    $content = '';

    if (is_array($form['content']) && !empty($form['content'])) {
        $table = new html_table(array('cols' => 2));
        foreach ($form['content'] as $col => $colprop) {
            $colprop['id'] = '_'.$col;
            $label = !empty($colprop['label']) ? $colprop['label'] : rcube_label($col);

            $table->add('title', sprintf('<label for="%s">%s</label>', $colprop['id'], Q($label)));
            $table->add(null, $colprop['value']);
        }
        $content = $table->show();
    }
    else {
        $content = $tag['content'];
    }

    return $content;
}

function rcmail_localize_folderpath($path)
{
    global $RCMAIL;

    $protect_folders = $RCMAIL->config->get('protect_default_folders');
    $default_folders = (array) $RCMAIL->config->get('default_imap_folders');
    $delimiter       = $RCMAIL->imap->get_hierarchy_delimiter();
    $path            = explode($delimiter, $path);
    $result          = array();

    foreach ($path as $idx => $dir) {
        $directory = implode($delimiter, array_slice($path, 0, $idx+1));
        if ($protect_folders && in_array($directory, $default_folders)) {
            unset($result);
            $result[] = rcmail_localize_foldername($directory);
        }
        else if ($protect_folders && in_array($dir, $default_folders)) {
            $result[] = rcmail_localize_foldername($dir);
        }
        else {
            $result[] = rcube_charset_convert($dir, 'UTF7-IMAP');
        }
    }

    return implode($delimiter, $result);
}


//$OUTPUT->set_pagetitle(rcube_label('folders'));

// register UI objects
$OUTPUT->add_handlers(array(
    'folderdetails' => 'rcube_folder_form',
));

$OUTPUT->add_label('nonamewarning');

$OUTPUT->send('folderedit');
