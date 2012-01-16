<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/save_folder.inc                                |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, The Roundcube Dev Team                       |
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
$STORAGE = $RCMAIL->get_storage();


$name = trim(get_input_value('_name', RCUBE_INPUT_POST, true));
$old  = trim(get_input_value('_mbox', RCUBE_INPUT_POST, true));
$path = trim(get_input_value('_parent', RCUBE_INPUT_POST, true));

$name_imap = rcube_charset_convert($name, RCMAIL_CHARSET, 'UTF7-IMAP');
$old_imap  = rcube_charset_convert($old, RCMAIL_CHARSET, 'UTF7-IMAP');
// $path is in UTF7-IMAP already

$delimiter = $STORAGE->get_hierarchy_delimiter();
$options = strlen($old_imap) ? rcmail_folder_options($old_imap) : array();

// Folder name checks
if ($options['protected'] || $options['norename']) {
}
else if (!strlen($name)) {
    $error = rcube_label('cannotbeempty');
}
else if (mb_strlen($name) > 128) {
    $error = rcube_label('nametoolong');
}
else {
    // these characters are problematic e.g. when used in LIST/LSUB
    foreach (array($delimiter, '%', '*') as $char) {
        if (strpos($name, $delimiter) !== false) {
            $error = rcube_label('forbiddencharacter') . " ($char)";
            break;
        }
    }
}

if ($error) {
    $OUTPUT->command('display_message', $error, 'error');
}
else {
    if ($options['protected'] || $options['norename']) {
        $name_imap = $old_imap;
    }
    else if (strlen($path)) {
        $name_imap = $path . $delimiter . $name_imap;
    }
    else {
        $name_imap = $STORAGE->mod_folder($name_imap, 'in');
    }
}

// Check access rights to the parent folder
if (!$error && strlen($path) && (!strlen($old_imap) || $old_imap != $name_imap)) {
    $parent_opts = $STORAGE->folder_info($path);
    if ($parent_opts['namespace'] != 'personal'
        && (empty($parent_opts['rights']) || !preg_match('/[ck]/', implode($parent_opts)))
    ) {
        $error = rcube_label('parentnotwritable');
    }
}

if (!$error) {
    $folder['name']     = $name_imap;
    $folder['oldname']  = $old_imap;
    $folder['class']    = '';
    $folder['options']  = $options;
    $folder['settings'] = array(
        // List view mode: 0-list, 1-threads
        'view_mode'   => (int) get_input_value('_viewmode', RCUBE_INPUT_POST),
        'sort_column' => get_input_value('_sortcol', RCUBE_INPUT_POST),
        'sort_order'  => get_input_value('_sortord', RCUBE_INPUT_POST),
    );
}

// create a new mailbox
if (!$error && !strlen($old)) {

    $folder['subscribe'] = true;

    $plugin = $RCMAIL->plugins->exec_hook('folder_create', array('record' => $folder));

    $folder = $plugin['record'];

    if (!$plugin['abort']) {
        $created = $STORAGE->create_folder($folder['name'], $folder['subscribe']);
    }
    else {
        $created = $plugin['result'];
    }

    if ($created) {
        // Save folder settings
        if (isset($_POST['_viewmode'])) {
            $a_threaded = (array) $RCMAIL->config->get('message_threading', array());

            if ($_POST['_viewmode'])
                $a_threaded[$folder['name']] = true;
            else
                unset($a_threaded[$folder['name']]);

            $RCMAIL->user->save_prefs(array('message_threading' => $a_threaded));
        }

        rcmail_update_folder_row($folder['name'], null, $folder['subscribe'], $folder['class']);
        $OUTPUT->show_message('foldercreated', 'confirmation');
        // reset folder preview frame
        $OUTPUT->command('subscription_select');
        $OUTPUT->send('iframe');
    }
    else {
        // show error message
        $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'errorsaving', 'error', null, false);
    }
}

// update a mailbox
else if (!$error) {
    $plugin = $RCMAIL->plugins->exec_hook('folder_update', array('record' => $folder));

    $folder = $plugin['record'];
    $rename = ($folder['oldname'] != $folder['name']);

    if (!$plugin['abort']) {
        if ($rename) {
            $updated = $STORAGE->rename_folder($folder['oldname'], $folder['name']);
        }
        else {
            $updated = true;
        }
    }
    else {
        $updated = $plugin['result'];
    }

    if ($updated) {
        // Update folder settings,
        if (isset($_POST['_viewmode'])) {
            $a_threaded = (array) $RCMAIL->config->get('message_threading', array());

            // In case of name change update names of childrens in settings
            if ($rename) {
                $oldprefix  = '/^' . preg_quote($folder['oldname'] . $delimiter, '/') . '/';
                foreach ($a_threaded as $key => $val) {
                    if ($key == $folder['oldname']) {
                        unset($a_threaded[$key]);
                    }
                    else if (preg_match($oldprefix, $key)) {
                        unset($a_threaded[$key]);
  	                    $a_threaded[preg_replace($oldprefix, $folder['name'].$delimiter, $key)] = true;
                    }
                }
            }
            if ($_POST['_viewmode'])
                $a_threaded[$folder['name']] = true;
            else
                unset($a_threaded[$folder['name']]);

            $RCMAIL->user->save_prefs(array('message_threading' => $a_threaded));
        }

        $OUTPUT->show_message('folderupdated', 'confirmation');
        if ($rename) {
            rcmail_update_folder_row($folder['name'], $folder['oldname'], $folder['subscribe'], $folder['class']);
            $OUTPUT->send('iframe');
        }
    }
    else {
        // show error message
        $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'errorsaving', 'error', null, false);
    }
}

rcmail_overwrite_action('edit-folder');
