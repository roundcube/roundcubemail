<?php

/**
 +-----------------------------------------------------------------------+
 | program/steps/mail/attachments.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Upload, remove, display attachments in compose form                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

// Upload progress update
if (!empty($_GET['_progress'])) {
    $RCMAIL->upload_progress();
}

$COMPOSE_ID = rcube_utils::get_input_value('_id', rcube_utils::INPUT_GPC);
$COMPOSE    = null;

if ($COMPOSE_ID && $_SESSION['compose_data_' . $COMPOSE_ID]) {
    $SESSION_KEY = 'compose_data_' . $COMPOSE_ID;
    $COMPOSE =& $_SESSION[$SESSION_KEY];
}

if (!$COMPOSE) {
    die("Invalid session var!");
}


// remove an attachment
if ($RCMAIL->action == 'remove-attachment') {
    $id = 'undefined';

    if (preg_match('/^rcmfile(\w+)$/', $_POST['_file'], $regs)) {
        $id = $regs[1];
    }

    if ($attachment = $COMPOSE['attachments'][$id]) {
        $attachment = $RCMAIL->plugins->exec_hook('attachment_delete', $attachment);
    }

    if ($attachment['status']) {
        if (is_array($COMPOSE['attachments'][$id])) {
            $RCMAIL->session->remove($SESSION_KEY.'.attachments.'.$id);
            $OUTPUT->command('remove_from_attachment_list', "rcmfile$id");
        }
    }

    $OUTPUT->send();
    exit;
}

if ($RCMAIL->action == 'display-attachment') {
    $id = 'undefined';

    if (preg_match('/^rcmfile(\w+)$/', $_GET['_file'], $regs)) {
        $id = $regs[1];
    }

    $RCMAIL->display_uploaded_file($COMPOSE['attachments'][$id]);
    exit;
}

/*****  attachment upload action  *****/

// clear all stored output properties (like scripts and env vars)
$OUTPUT->reset();

$uploadid = rcube_utils::get_input_value('_uploadid', rcube_utils::INPUT_GPC);
$uri      = rcube_utils::get_input_value('_uri', rcube_utils::INPUT_POST);

// handle dropping a reference to an attachment part of some message
if ($uri) {
    $url = parse_url($uri);
    parse_str($url['query'], $params);

    if (strlen($params['_mbox']) && $params['_uid'] && $params['_part']) {
        // @TODO: at some point we might support drag-n-drop between
        // two different accounts on the same server, for now make sure
        // this is the same server and the same user
        list($host, $port) = explode(':', $_SERVER['HTTP_HOST']);
        if ($host == $url['host'] && $port == $url['port']
            && $RCMAIL->get_user_name() == rawurldecode($url['user'])
        ) {
            $message = new rcube_message($params['_uid'], $params['_mbox']);
        }
    }

    if ($message && !empty($message->headers)
        && ($attachment = rcmail_save_attachment($message, $params['_part'], $COMPOSE_ID))
    ) {
        rcmail_attachment_success($attachment, $uploadid);
    }
    else {
        $OUTPUT->command('display_message', $RCMAIL->gettext('filelinkerror'), 'error');
        $OUTPUT->command('remove_from_attachment_list', $uploadid);
    }

    $OUTPUT->send();
    return;
}

// handle file(s) upload
if (is_array($_FILES['_attachments']['tmp_name'])) {
    $multiple = count($_FILES['_attachments']['tmp_name']) > 1;

    foreach ($_FILES['_attachments']['tmp_name'] as $i => $filepath) {
        // Process uploaded attachment if there is no error
        $err = $_FILES['_attachments']['error'][$i];

        if (!$err) {
            $attachment = $RCMAIL->plugins->exec_hook('attachment_upload', array(
                'path' => $filepath,
                'size' => $_FILES['_attachments']['size'][$i],
                'name' => $_FILES['_attachments']['name'][$i],
                'mimetype' => rcube_mime::file_content_type($filepath, $_FILES['_attachments']['name'][$i], $_FILES['_attachments']['type'][$i]),
                'group' => $COMPOSE_ID,
            ));
        }

        if (!$err && $attachment['status'] && !$attachment['abort']) {
            // store new attachment in session
            unset($attachment['status'], $attachment['abort']);
            $RCMAIL->session->append($SESSION_KEY . '.attachments', $attachment['id'], $attachment);

            rcmail_attachment_success($attachment, $uploadid);
        }
        else {  // upload failed
            if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE) {
                $size = $RCMAIL->show_bytes(rcube_utils::max_upload_size());
                $msg  = $RCMAIL->gettext(array('name' => 'filesizeerror', 'vars' => array('size' => $size)));
            }
            else if ($attachment['error']) {
                $msg = $attachment['error'];
            }
            else {
                $msg = $RCMAIL->gettext('fileuploaderror');
            }

            if ($attachment['error'] || $err != UPLOAD_ERR_NO_FILE) {
                $OUTPUT->command('display_message', $msg, 'error');
                $OUTPUT->command('remove_from_attachment_list', $uploadid);
            }
        }
    }
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // if filesize exceeds post_max_size then $_FILES array is empty,
    // show filesizeerror instead of fileuploaderror
    if ($maxsize = ini_get('post_max_size')) {
        $msg = $RCMAIL->gettext(array(
            'name' => 'filesizeerror',
            'vars' => array('size' => $RCMAIL->show_bytes(parse_bytes($maxsize)))
        ));
    }
    else {
        $msg = $RCMAIL->gettext('fileuploaderror');
    }

    $OUTPUT->command('display_message', $msg, 'error');
    $OUTPUT->command('remove_from_attachment_list', $uploadid);
}

// send html page with JS calls as response
$OUTPUT->command('auto_save_start', false);
$OUTPUT->send('iframe');


function rcmail_attachment_success($attachment, $uploadid)
{
    global $RCMAIL, $COMPOSE;

    $id = $attachment['id'];

    if (($icon = $COMPOSE['deleteicon']) && is_file($icon)) {
        $button = html::img(array(
            'src' => $icon,
            'alt' => $RCMAIL->gettext('delete')
        ));
    }
    else if ($COMPOSE['textbuttons']) {
        $button = rcube::Q($RCMAIL->gettext('delete'));
    }
    else {
        $button = '';
    }

    $content = html::a(array(
        'href'    => "#delete",
        'onclick' => sprintf("return %s.command('remove-attachment','rcmfile%s', this)", rcmail_output::JS_OBJECT_NAME, $id),
        'title'   => $RCMAIL->gettext('delete'),
        'class'   => 'delete',
        'aria-label' => $RCMAIL->gettext('delete') . ' ' . $attachment['name'],
    ), $button);

    $content .= sprintf('%s <span class="attachment-size">(%s)</span>',
        rcube::Q($attachment['name']), $RCMAIL->show_bytes($attachment['size']));

    $RCMAIL->output->command('add2attachment_list', "rcmfile$id", array(
        'html'      => $content,
        'name'      => $attachment['name'],
        'mimetype'  => $attachment['mimetype'],
        'classname' => rcube_utils::file2class($attachment['mimetype'], $attachment['name']),
        'complete'  => true), $uploadid);
}
