<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/folders.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide functionality of folders management                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id$

*/

// WARNING: folder names in UI are encoded with RCMAIL_CHARSET

// init IMAP connection
$RCMAIL->imap_connect();

// subscribe mailbox
if ($RCMAIL->action == 'subscribe')
{
    $mbox = get_input_value('_mbox', RCUBE_INPUT_POST, true, 'UTF7-IMAP');
    if (strlen($mbox)) {
        $result = $IMAP->subscribe(array($mbox));

        // Handle virtual (non-existing) folders
        if (!$result && $IMAP->get_error_code() == -1 &&
            $IMAP->get_response_code() == rcube_imap::TRYCREATE
        ) {
            $result = $IMAP->create_mailbox($mbox, true);
            if ($result) {
                // @TODO: remove 'virtual' class of folder's row
            }
        }

        if ($result) {
            // Handle subscription of protected folder (#1487656)
            if ($CONFIG['protect_default_folders'] == true
                && in_array($mbox, $CONFIG['default_imap_folders'])
            ) {
                $OUTPUT->command('disable_subscription', $mbox);
            }

            $OUTPUT->show_message('foldersubscribed', 'confirmation');
        }
        else
            rcmail_display_server_error('errorsaving');
    }
}

// unsubscribe mailbox
else if ($RCMAIL->action == 'unsubscribe')
{
    $mbox = get_input_value('_mbox', RCUBE_INPUT_POST, true, 'UTF7-IMAP');
    if (strlen($mbox)) {
        $result = $IMAP->unsubscribe(array($mbox));
        if ($result)
            $OUTPUT->show_message('folderunsubscribed', 'confirmation');
        else
            rcmail_display_server_error('errorsaving');
    }
}

// delete an existing mailbox
else if ($RCMAIL->action == 'delete-folder')
{
    $mbox_utf8 = get_input_value('_mbox', RCUBE_INPUT_POST, true);
    $mbox      = rcube_charset_convert($mbox_utf8, RCMAIL_CHARSET, 'UTF7-IMAP');

    if (strlen($mbox)) {
        $plugin = $RCMAIL->plugins->exec_hook('folder_delete', array('name' => $mbox));

        if (!$plugin['abort']) {
            $deleted = $IMAP->delete_mailbox($plugin['name']);
        }
        else {
            $deleted = $plugin['result'];
        }
    }

    if ($OUTPUT->ajax_call && $deleted) {
        // Remove folder and subfolders rows
        $OUTPUT->command('remove_folder_row', $mbox_utf8, true);
        $OUTPUT->show_message('folderdeleted', 'confirmation');
        // Clear content frame
        $OUTPUT->command('subscription_select');
        $OUTPUT->command('set_quota', rcmail_quota_content());
    }
    else if (!$deleted) {
        rcmail_display_server_error('errorsaving');
    }
}

// rename an existing mailbox
else if ($RCMAIL->action == 'rename-folder')
{
    $name_utf8    = trim(get_input_value('_folder_newname', RCUBE_INPUT_POST, true));
    $oldname_utf8 = trim(get_input_value('_folder_oldname', RCUBE_INPUT_POST, true));

    if (strlen($name_utf8) && strlen($oldname_utf8)) {
        $name    = rcube_charset_convert($name_utf8, RCMAIL_CHARSET, 'UTF7-IMAP');
        $oldname = rcube_charset_convert($oldname_utf8, RCMAIL_CHARSET, 'UTF7-IMAP');

        $rename = rcmail_rename_folder($oldname, $name);
    }

    if ($rename && $OUTPUT->ajax_call) {
        rcmail_update_folder_row($name, $oldname);
    }
    else if (!$rename) {
        rcmail_display_server_error('errorsaving');
    }
}

// clear mailbox
else if ($RCMAIL->action == 'purge')
{
    $mbox_utf8 = get_input_value('_mbox', RCUBE_INPUT_POST, true);
    $mbox      = rcube_charset_convert($mbox_utf8, RCMAIL_CHARSET, 'UTF7-IMAP');
    $delimiter = $IMAP->get_hierarchy_delimiter();
    $trash_regexp = '/^' . preg_quote($CONFIG['trash_mbox'] . $delimiter, '/') . '/';

    // we should only be purging trash (or their subfolders)
    if (!strlen($CONFIG['trash_mbox']) || $mbox == $CONFIG['trash_mbox']
        || preg_match($trash_regexp, $mbox)
    ) {
        $success = $IMAP->clear_mailbox($mbox);
        $delete = true;
    }
    // copy to Trash
    else {
        $success = $IMAP->move_message('1:*', $CONFIG['trash_mbox'], $mbox);
        $delete = false;
    }

    if ($success) {
        $OUTPUT->set_env('messagecount', 0);
        if ($delete) {
            $OUTPUT->show_message('folderpurged', 'confirmation');
            $OUTPUT->command('set_quota', rcmail_quota_content());
        }
        else {
            $OUTPUT->show_message('messagemoved', 'confirmation');
        }
        $_SESSION['unseen_count'][$mbox] = 0;
        $OUTPUT->command('show_folder', $mbox_utf8, null, true);
    }
    else {
        rcmail_display_server_error('errorsaving');
    }
}

// get mailbox size
else if ($RCMAIL->action == 'folder-size')
{
    $name = trim(get_input_value('_mbox', RCUBE_INPUT_POST, true));

    $size = $IMAP->get_mailbox_size($name);

    // @TODO: check quota and show percentage usage of specified mailbox?

    if ($size !== false) {
        $OUTPUT->command('folder_size_update', show_bytes($size));
    }
    else {
        rcmail_display_server_error();
    }
}

if ($OUTPUT->ajax_call)
    $OUTPUT->send();


// build table with all folders listed by server
function rcube_subscription_form($attrib)
{
    global $RCMAIL, $IMAP, $CONFIG, $OUTPUT;

    list($form_start, $form_end) = get_form_tags($attrib, 'folders');
    unset($attrib['form']);

    if (!$attrib['id'])
        $attrib['id'] = 'rcmSubscriptionlist';

    $table = new html_table();

    if ($attrib['noheader'] !== true && $attrib['noheader'] != "true") {
        // add table header
        $table->add_header('name', rcube_label('foldername'));
        $table->add_header('subscribed', '');
    }

    // get folders from server
    $IMAP->clear_cache('mailboxes', true);

    $a_unsubscribed = $IMAP->list_unsubscribed();
    $a_subscribed   = $IMAP->list_mailboxes();
    $delimiter      = $IMAP->get_hierarchy_delimiter();
    $namespace      = $IMAP->get_namespace();
    $a_js_folders   = array();
    $seen           = array();
    $list_folders   = array();

    // pre-process folders list
    foreach ($a_unsubscribed as $i => $folder) {
        $folder_id     = $folder;
        $folder        = $IMAP->mod_mailbox($folder);
        $foldersplit   = explode($delimiter, $folder);
        $name          = rcube_charset_convert(array_pop($foldersplit), 'UTF7-IMAP');
        $parent_folder = join($delimiter, $foldersplit);
        $level         = count($foldersplit);

        // add any necessary "virtual" parent folders
        if ($parent_folder && !isset($seen[$parent_folder])) {
            for ($i=1; $i<=$level; $i++) {
	            $ancestor_folder = join($delimiter, array_slice($foldersplit, 0, $i));
	            if ($ancestor_folder && !$seen[$ancestor_folder]++) {
	                $ancestor_name = rcube_charset_convert($foldersplit[$i-1], 'UTF7-IMAP');
	                $list_folders[] = array(
                        'id'      => $ancestor_folder,
                        'name'    => $ancestor_name,
                        'level'   => $i-1,
                        'virtual' => true,
                    );
	            }
            }
        }

        // Handle properly INBOX.INBOX situation
        if (isset($seen[$folder])) {
            continue;
        }

        $seen[$folder]++;

        $list_folders[] = array(
            'id'    => $folder_id,
            'name'  => $name,
            'level' => $level,
        );
    }

    unset($seen);

    // add drop-target representing 'root'
    $table->add_row(array('id' => 'mailboxroot', 'class' => 'virtual root'));
    $table->add('name', '&nbsp;');
    $table->add(null, '&nbsp;');

    $a_js_folders['mailboxroot'] = array('', '', true);

    $checkbox_subscribe = new html_checkbox(array(
        'name'    => '_subscribed[]',
        'title'   => rcube_label('changesubscription'),
        'onclick' => JS_OBJECT_NAME.".command(this.checked?'subscribe':'unsubscribe',this.value)",
    ));

    // create list of available folders
    foreach ($list_folders as $i => $folder) {
        $idx        = $i + 1;
        $sub_key    = array_search($folder['id'], $a_subscribed);
        $subscribed = $sub_key !== false;
        $protected  = ($CONFIG['protect_default_folders'] == true && in_array($folder['id'], $CONFIG['default_imap_folders']));
        $noselect   = false;
        $classes    = array($i%2 ? 'even' : 'odd');

        $folder_js      = Q($folder['id']);
        $folder_utf8    = rcube_charset_convert($folder['id'], 'UTF7-IMAP');
        $display_folder = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $folder['level'])
            . Q($protected ? rcmail_localize_foldername($folder['id']) : $folder['name']);

        if ($folder['virtual']) {
            $classes[] = 'virtual';
        }

        if (!$protected) {
            $opts = $IMAP->mailbox_options($folder['id']);
            $noselect = in_array('\\Noselect', $opts);
        }

        $disabled = (($protected && $subscribed) || $noselect);

        // check if the folder is a namespace prefix, then disable subscription option on it
        if (!$disabled && $folder['virtual'] && $folder['level'] == 0 && !empty($namespace)) {
            $fname = $folder['id'] . $delimiter;
            foreach ($namespace as $ns) {
                foreach ($ns as $item) {
                    if ($item[0] === $fname) {
                        $disabled = true;
                        break 2;
                    }
                }
            }
        }
        // check if the folder is an other users virtual-root folder, then disable subscription option on it
        if (!$disabled && $folder['virtual'] && $folder['level'] == 1
            && !empty($namespace) && !empty($namespace['other'])
        ) {
            $parts = explode($delimiter, $folder['id']);
            $fname = $parts[0] . $delimiter;
            foreach ($namespace['other'] as $item) {
                if ($item[0] === $fname) {
                    $disabled = true;
                    break;
                }
            }
        }
        // check if the folder is shared, then disable subscription option on it
        if (!$disabled && $folder['virtual'] && !empty($namespace)) {
            $tmp_ns = array_merge((array)$namespace['other'], (array)$namespace['shared']);
            foreach ($tmp_ns as $item) {
                if (strpos($folder['id'], $item[0]) === 0) {
                    $disabled = true;
                    break;
                }
            }
        }

        $table->add_row(array('id' => 'rcmrow'.$idx, 'class' => join(' ', $classes),
            'foldername' => $folder['id']));

        $table->add('name', $display_folder);
        $table->add('subscribed', $checkbox_subscribe->show(($subscribed ? $folder_utf8 : ''),
            array('value' => $folder_utf8, 'disabled' => $disabled ? 'disabled' : '')));

        $a_js_folders['rcmrow'.$idx] = array($folder_utf8,
            Q($display_folder), $protected || $folder['virtual']);
    }

    $RCMAIL->plugins->exec_hook('folders_list', array('table' => $table));

    $OUTPUT->add_gui_object('subscriptionlist', $attrib['id']);
    $OUTPUT->set_env('subscriptionrows', $a_js_folders);
    $OUTPUT->set_env('defaultfolders', $CONFIG['default_imap_folders']);
    $OUTPUT->set_env('delimiter', $delimiter);

    return $form_start . $table->show($attrib) . $form_end;
}

function rcmail_folder_frame($attrib)
{
    global $OUTPUT;

    if (!$attrib['id'])
        $attrib['id'] = 'rcmfolderframe';

    $attrib['name'] = $attrib['id'];

    $OUTPUT->set_env('contentframe', $attrib['name']);
    $OUTPUT->set_env('blankpage', $attrib['src'] ? $OUTPUT->abs_url($attrib['src']) : 'program/blank.gif');

    return html::iframe($attrib);
}

function rcmail_rename_folder($oldname, $newname)
{
    global $RCMAIL;

    $delimiter = $RCMAIL->imap->get_hierarchy_delimiter();

    $plugin = $RCMAIL->plugins->exec_hook('folder_rename', array(
        'oldname' => $oldname, 'newname' => $newname));

    if (!$plugin['abort']) {
        $renamed =  $RCMAIL->imap->rename_mailbox($oldname, $newname);
    }
    else {
        $renamed = $plugin['result'];
    }

    // update per-folder options for modified folder and its subfolders
    if ($renamed) {
        $a_threaded = (array) $RCMAIL->config->get('message_threading', array());
        $oldprefix  = '/^' . preg_quote($oldname . $delimiter, '/') . '/';

        foreach ($a_threaded as $key => $val) {
            if ($key == $oldname) {
                unset($a_threaded[$key]);
    	        $a_threaded[$newname] = true;
            }
            else if (preg_match($oldprefix, $key)) {
                unset($a_threaded[$key]);
	            $a_threaded[preg_replace($oldprefix, $newname.$delimiter, $key)] = true;
            }
        }
        $RCMAIL->user->save_prefs(array('message_threading' => $a_threaded));

        return true;
    }

    return false;
}


$OUTPUT->set_pagetitle(rcube_label('folders'));
$OUTPUT->include_script('list.js');
$OUTPUT->set_env('quota', $IMAP->get_capability('QUOTA'));

// add some labels to client
$OUTPUT->add_label('deletefolderconfirm', 'purgefolderconfirm', 'folderdeleting',
    'foldermoving', 'foldersubscribing', 'folderunsubscribing', 'quota');

// register UI objects
$OUTPUT->add_handlers(array(
    'foldersubscription' => 'rcube_subscription_form',
    'folderframe' => 'rcmail_folder_frame',
    'quotadisplay' => 'rcmail_quota_display',
));

$OUTPUT->send('folders');

