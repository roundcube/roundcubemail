<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/get.inc                                            |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Delivering a specific part of a mail message                        |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


// show loading page
if (!empty($_GET['_preload'])) {
  $url = str_replace('&_preload=1', '', $_SERVER['REQUEST_URI']);
  $message = rcube_label('loadingdata');

  header('Content-Type: text/html; charset=' . RCMAIL_CHARSET);
  print "<html>\n<head>\n"
        . '<meta http-equiv="refresh" content="0; url='.Q($url).'">' . "\n"
        . '<meta http-equiv="content-type" content="text/html; charset='.RCMAIL_CHARSET.'">' . "\n"
        . "</head>\n<body>\n$message\n</body>\n</html>";
  exit;
}

ob_end_clean();

// similar code as in program/steps/mail/show.inc
if (!empty($_GET['_uid'])) {
  $RCMAIL->config->set('prefer_html', true);
  $MESSAGE = new rcube_message(get_input_value('_uid', RCUBE_INPUT_GET));
}


// show part page
if (!empty($_GET['_frame'])) {
  $OUTPUT->send('messagepart');
  exit;
}

else if ($pid = get_input_value('_part', RCUBE_INPUT_GET)) {
  // TNEF encoded attachment part
  if (preg_match('/^winmail\.([0-9.]+)\.([0-9]+)$/', $pid, $nt)) {
    $pid = $nt[1]; $i = $nt[2];
    if ($part = $MESSAGE->mime_parts[$pid]) {
      $tnef_arr = $IMAP->tnef_decode($part, $MESSAGE->uid);
      if (is_a($tnef_arr[$i], 'rcube_message_part'))
        $MESSAGE->mime_parts[$pid] = $tnef_arr[$i];
    }
  }
  
  if ($part = $MESSAGE->mime_parts[$pid]) {
    $ctype_primary = strtolower($part->ctype_primary);
    $ctype_secondary = strtolower($part->ctype_secondary);
    $mimetype = sprintf('%s/%s', $ctype_primary, $ctype_secondary);
    
    $browser = new rcube_browser;

    send_nocacheing_headers();
    
    // send download headers
    if ($_GET['_download']) {
      header("Content-Type: application/octet-stream");
      if ($browser->ie)
        header("Content-Type: application/force-download");
    }
    else if ($ctype_primary == 'text') {
      header("Content-Type: text/$ctype_secondary; charset=" . ($part->charset ? $part->charset : RCMAIL_CHARSET));
    }
    else {
      header("Content-Type: $mimetype");
      header("Content-Transfer-Encoding: binary");
    }

    // deliver part content
    if ($ctype_primary == 'text' && $ctype_secondary == 'html') {
      // get part body if not available
      if (!$part->body)
        $part->body = $MESSAGE->get_part_content($part->mime_id);

      $OUTPUT = new rcube_html_page();
      $OUTPUT->write(rcmail_print_body($part, array('safe' => $MESSAGE->is_safe, 'inline_html' => false)));
    }
    else {
      // don't kill the connection if download takes more than 30 sec.
      if (!ini_get('safe_mode')) {
          set_time_limit(0);
      }
      
      $filename = $part->filename ? $part->filename : ($MESSAGE->subject ? $MESSAGE->subject : 'roundcube') . '.'.$ctype_secondary;
      
      if ($browser->ie && $browser->ver < 7)
        $filename = rawurlencode(abbreviate_string($filename, 55));
      else if ($browser->ie)
        $filename = rawurlencode($filename);
      else
        $filename = addcslashes($filename, '"');
      
      $disposition = !empty($_GET['_download']) ? 'attachment' : 'inline';
      
      header("Content-Disposition: $disposition; filename=\"$filename\"");
      
      // turn off output buffering and print part content
      if ($part->body)
        echo $part->body;
      else if ($part->size)
        $IMAP->get_message_part($MESSAGE->uid, $part->mime_id, $part, true);
    }

    exit;
  }
}

// print message
else {
  // send correct headers for content type
  header("Content-Type: text/html");

  $cont = "<html>\n<head><title></title>\n</head>\n<body>";
  $cont .= rcmail_message_body(array());
  $cont .= "\n</body>\n</html>";

  $OUTPUT = new rcube_html_page();
  $OUTPUT->write($cont);

  exit;
}


// if we arrive here, the requested part was not found
header('HTTP/1.1 404 Not Found');
exit;

?>
