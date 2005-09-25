<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/viewsource.inc                                     |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | All rights reserved.                                                  |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Display a mail message similar as a usual mail application does     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


// similar code as in program/steps/mail/get.inc
if ($_GET['_uid'])
  {
  header('Content-Type: text/plain');
  print rcmail_message_source($_GET['_uid']);
  }
else
  {
  raise_error(array('code' => 500,
                    'type' => 'php',
                    'message' => 'Message UID '.$_GET['_uid'].' not found'),
              TRUE,
              TRUE);
  }

exit;
?>