<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/upload.inc                                         |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Handle file-upload and make them available as attachments           |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


if (!$_SESSION['compose'])
  {
  exit;
  }


// use common temp dir for file uploads
$temp_dir = unslashify($CONFIG['temp_dir']);


if (!is_array($_SESSION['compose']['attachments']))
  $_SESSION['compose']['attachments'] = array();


$response = '';

foreach ($_FILES['_attachments']['tmp_name'] as $i => $filepath)
  {
  $tmpfname = tempnam($temp_dir, 'rcmAttmnt');
  if (move_uploaded_file($filepath, $tmpfname))
    {
    $id = count($_SESSION['compose']['attachments']);
    $_SESSION['compose']['attachments'][] = array('name' => $_FILES['_attachments']['name'][$i],
                                                  'mimetype' => $_FILES['_attachments']['type'][$i],
                                                  'path' => $tmpfname);

    if (is_file($CONFIG['skin_path'] . '/images/icons/remove-attachment.png'))
      $button = sprintf('<img src="%s/images/icons/remove-attachment.png" alt="%s" border="0" style="padding-right:2px;vertical-align:middle" />',
                        $CONFIG['skin_path'],
                        Q(rcube_label('delete')));
    else
      $button = Q(rcube_label('delete'));

    $content = sprintf('<a href="#delete" onclick="return %s.command(\\\'remove-attachment\\\', \\\'rcmfile%d\\\', this)" title="%s">%s</a>%s',
                       $JS_OBJECT_NAME,
                       $id,
                       Q(rcube_label('delete')),
                       $button,
                       Q($_FILES['_attachments']['name'][$i]));

    $response .= sprintf('parent.%s.add2attachment_list(\'rcmfile%d\',\'%s\');',
                         $JS_OBJECT_NAME,
                         $id,
                         $content);
    }
  else // upload failed
    {
    $err = $_FILES['_attachments']['error'][$i];
    if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE)
      $msg = rcube_label(array('name' => 'filesizeerror', 'vars' => array('size' => show_bytes(parse_bytes(ini_get('upload_max_filesize'))))));
    else
      $msg = rcube_label('fileuploaderror');
    
    $response = sprintf("parent.%s.display_message('%s', 'error');", $JS_OBJECT_NAME, JQ($msg));
    }
  }


// send html page with JS calls as response
$frameout = <<<EOF
$response
parent.$JS_OBJECT_NAME.show_attachment_form(false);
parent.$JS_OBJECT_NAME.auto_save_start();
EOF;

rcube_iframe_response($frameout);

?>
