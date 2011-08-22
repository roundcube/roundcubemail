<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/upload_photo.inc                            |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Handles contact photo uploads                                       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

// Supported image format types
// ImageMagick works with other non-image types (e.g.pdf) we don't want here
$IMAGE_TYPES = explode(',', 'jpeg,jpg,jp2,tiff,tif,bmp,eps,gif,png,png8,png24,png32,svg,ico'); 

// clear all stored output properties (like scripts and env vars)
$OUTPUT->reset();

if ($filepath = $_FILES['_photo']['tmp_name']) {
    // check file type and resize image
    $imageprop = rcmail::imageprops($_FILES['_photo']['tmp_name']);

    if (in_array(strtolower($imageprop['type']), $IMAGE_TYPES)
	&& $imageprop['width'] && $imageprop['height']
    ) {
        $maxsize = intval($RCMAIL->config->get('contact_photo_size', 160));
        $tmpfname = tempnam($RCMAIL->config->get('temp_dir'), 'rcmImgConvert');
        $save_hook = 'attachment_upload';

        // scale image to a maximum size
        if (($imageprop['width'] > $maxsize || $imageprop['height'] > $maxsize) &&
            (rcmail::imageconvert(array('in' => $filepath, 'out' => $tmpfname,
                'size' => $maxsize.'x'.$maxsize, 'type' => $imageprop['type'])) !== false)) {
            $filepath = $tmpfname;
            $save_hook = 'attachment_save';
        }

        // save uploaded file in storage backend
        $attachment = $RCMAIL->plugins->exec_hook($save_hook, array(
            'path' => $filepath,
            'size' => $_FILES['_photo']['size'],
            'name' => $_FILES['_photo']['name'],
            'mimetype' => 'image/' . $imageprop['type'],
            'group' => 'contact',
        ));
    }
    else
        $attachment['error'] = rcube_label('invalidimageformat');

    if ($attachment['status'] && !$attachment['abort']) {
        $file_id = $attachment['id'];
        $_SESSION['contacts']['files'][$file_id] = $attachment;
        $OUTPUT->command('replace_contact_photo', $file_id);
    }
    else {  // upload failed
        $err = $_FILES['_photo']['error'];
        if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE)
            $msg = rcube_label(array('name' => 'filesizeerror', 'vars' => array('size' => show_bytes(parse_bytes(ini_get('upload_max_filesize'))))));
        else if ($attachment['error'])
            $msg = $attachment['error'];
        else
            $msg = rcube_label('fileuploaderror');
            
        $OUTPUT->command('display_message', $msg, 'error');
    }
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // if filesize exceeds post_max_size then $_FILES array is empty,
    // show filesizeerror instead of fileuploaderror
    if ($maxsize = ini_get('post_max_size'))
        $msg = rcube_label(array('name' => 'filesizeerror', 'vars' => array('size' => show_bytes(parse_bytes($maxsize)))));
    else
        $msg = rcube_label('fileuploaderror');

    $OUTPUT->command('display_message', $msg, 'error');
}

$OUTPUT->command('photo_upload_end');
$OUTPUT->send('iframe');
