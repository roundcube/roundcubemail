<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/quotadisplay.inc                                   |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Remote call to return the quota image or text                       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Robin Elfrink <robin@15augustus.nl>                           |
 +-----------------------------------------------------------------------+

 $Id$

*/

$display = isset($_GET['_display']) ? $_GET['_display'] : 'text';
$id = isset($_GET['_id']) ? $_GET['_id'] : 'rcmquotadisplay';
$quota = rcmail_quota_content($display);
$command = sprintf("this.gui_objects.%s.innerHTML = '%s';\n", $id, $quota);
rcube_remote_response($command);

exit;
?>
