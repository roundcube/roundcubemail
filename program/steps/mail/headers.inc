<?php
/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/headers.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Fetch message headers in raw format for display                     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id: mark.inc 1580 2008-06-30 09:36:18Z alec $

*/

if ($uid = get_input_value('_uid', RCUBE_INPUT_POST))
{
  $source = $IMAP->get_raw_headers($uid);

  if ($source)
    {
    $browser = new rcube_browser;
    
    if ($browser->ie)
      $source = rc_utf8_clean($source);	  

    $source = htmlspecialchars(trim($source));
    $source = preg_replace('/\t/', '&nbsp;&nbsp;&nbsp;&nbsp;', $source);
    $source = preg_replace('/^([a-z0-9_:-]+)/im', '<font class="bold">'.'\1'.'</font>', $source);
    $source = preg_replace('/\r?\n/', '<br />', $source);
    
    $OUTPUT->command('set_headers', $source);
    $OUTPUT->send();
    }
}
  
exit;

?>
