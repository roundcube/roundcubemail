<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/attachments.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Upload, remove, display attachments in compose form                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: compose.inc 2081 2008-11-23 12:38:44Z thomasb $

*/


if (!$_SESSION['compose']) {
  die("Invalid session var!");
}


// remove an attachment
if ($RCMAIL->action=='remove-attachment')
{
  if (preg_match('/^rcmfile([0-9]+)$/', $_POST['_file'], $regs))
  {
    $id = $regs[1];
    if (is_array($_SESSION['compose']['attachments'][$id]))
    {
      @unlink($_SESSION['compose']['attachments'][$id]['path']);
      unset($_SESSION['compose']['attachments'][$id]);
      $OUTPUT->command('remove_from_attachment_list', "rcmfile$id");
      $OUTPUT->send();
    }
  }
  exit;
}

if ($RCMAIL->action=='display-attachment')
{
  if (preg_match('/^rcmfile([0-9]+)$/', $_GET['_file'], $regs))
  {
    $id = $regs[1];
    if (is_array($_SESSION['compose']['attachments'][$id]))
    {
      $apath = $_SESSION['compose']['attachments'][$id]['path'];
      header('Content-Type: ' . $_SESSION['compose']['attachments'][$id]['mimetype']);
      header('Content-Length: ' . filesize($apath));
      readfile($apath);
    }
  }
  exit;
}

// attachment upload action

// use common temp dir for file uploads
$temp_dir = unslashify($CONFIG['temp_dir']);

if (!is_array($_SESSION['compose']['attachments'])) {
  $_SESSION['compose']['attachments'] = array();
}

// clear all stored output properties (like scripts and env vars)
$OUTPUT->reset();

if (is_array($_FILES['_attachments']['tmp_name'])) {
  foreach ($_FILES['_attachments']['tmp_name'] as $i => $filepath) {
    $tmpfname = tempnam($temp_dir, 'rcmAttmnt');
    if (move_uploaded_file($filepath, $tmpfname)) {
      $id = count($_SESSION['compose']['attachments']);
      $_SESSION['compose']['attachments'][] = array(
        'name' => $_FILES['_attachments']['name'][$i],
        'mimetype' => rc_mime_content_type($tmpfname, $_FILES['_attachments']['name'][$i], $_FILES['_attachments']['type'][$i]),
        'path' => $tmpfname,
      );

      if (is_file($icon = $CONFIG['skin_path'] . '/images/icons/remove-attachment.png')) {
        $button = html::img(array(
          'src' => $icon,
          'alt' => rcube_label('delete'),
          'style' => "padding-right:2px;vertical-align:middle",
        ));
      }
      else {
        $button = Q(rcube_label('delete'));
      }

      $content = html::a(array(
        'href' => "#delete",
        'onclick' => sprintf("return %s.command('remove-attachment','rcmfile%d', this)", JS_OBJECT_NAME, $id),
        'title' => rcube_label('delete'),
      ), $button);
      
      $content .= Q($_FILES['_attachments']['name'][$i]);
      
      $OUTPUT->command('add2attachment_list', "rcmfile$id", $content);
    }
    else {  // upload failed
      $err = $_FILES['_attachments']['error'][$i];
      if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE) {
        $msg = rcube_label(array('name' => 'filesizeerror', 'vars' => array('size' => show_bytes(parse_bytes(ini_get('upload_max_filesize'))))));
      }
      else {
        $msg = rcube_label('fileuploaderror');
      }
    
      $OUTPUT->command('display_message', $msg, 'error');
    }
  }
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $OUTPUT->command('display_message', rcube_label('fileuploaderror'), 'error');
}

// send html page with JS calls as response
$OUTPUT->command('show_attachment_form', false);
$OUTPUT->command('auto_save_start', false);
$OUTPUT->send('iframe');

?>
