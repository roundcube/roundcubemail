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


// create temp dir for file uploads
$temp_dir = rcmail_create_compose_tempdir();


if (!is_array($_SESSION['compose']['attachments']))
  $_SESSION['compose']['attachments'] = array();


$response = '';

foreach ($_FILES['_attachments']['tmp_name'] as $i => $filepath)
  {
  $tmpfname = tempnam($temp_dir, 'rcmAttmnt');
  if (copy($filepath, $tmpfname))
    {
    $_SESSION['compose']['attachments'][] = array('name' => $_FILES['_attachments']['name'][$i],
                                                  'mimetype' => $_FILES['_attachments']['type'][$i],
                                                  'path' => $tmpfname);

    $response .= sprintf("parent.%s.add2attachment_list('%s');\n", $JS_OBJECT_NAME, $_FILES['_attachments']['name'][$i]);
    }
  }


// send html page with JS calls as response
print <<<EOF
<html>
<script type="text/javascript">
if (parent.$JS_OBJECT_NAME)
{
$response
parent.$JS_OBJECT_NAME.show_attachment_form(false);
}
</script>
</html>
EOF;
exit;

?>