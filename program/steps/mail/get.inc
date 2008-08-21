<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/get.inc                                            |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
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

require_once('Mail/mimeDecode.php');


// show loading page
if (!empty($_GET['_preload'])) {
  $url = str_replace('&_preload=1', '', $_SERVER['REQUEST_URI']);
  $message = rcube_label('loadingdata');

  print "<html>\n<head>\n" .
        '<meta http-equiv="refresh" content="0; url='.Q($url).'">' .
        "\n</head>\n<body>" .
        $message .
        "\n</body>\n</html>";
  exit;
}


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
  if ($part = $MESSAGE->mime_parts[$pid]) {
    $ctype_primary = strtolower($part->ctype_primary);
    $ctype_secondary = strtolower($part->ctype_secondary);
    $mimetype = sprintf('%s/%s', $ctype_primary, $ctype_secondary);

    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Content-Transfer-Encoding: binary");

    // send download headers
    if ($_GET['_download']) {
      header("Cache-Control: private", false);
      header("Content-Type: application/octet-stream");
    }
    else if ($ctype_primary == 'text')
      header("Content-Type: text/$ctype_secondary; charset=" . RCMAIL_CHARSET);
    else
      header("Content-Type: $mimetype");

    // We need to set the following headers to make downloads work using IE in HTTPS mode.
    if (isset($_SERVER['HTTPS'])) {
      header('Pragma: ');
      header('Cache-Control: ');
    }

    // deliver part content
    if ($ctype_primary == 'text' && $ctype_secondary == 'html') {
      // we have to analyze the whole structure again to find inline objects
      /* what was this good for again ?
      list($new_parts, $new_attachments) =
        rcmail_parse_message($MESSAGE['structure'],
                             array('safe' => intval($_GET['_safe']),
                                   'prefer_html' => TRUE,
                                   'get_url' => $GET_URL.'&_part=%s'));

      $all_parts = array_merge($new_parts, $new_attachments);
      for ($partix = 0; $partix < sizeof($all_parts); $partix++)
        if ($all_parts[$partix]->mime_id == $pid)
          $part = &$all_parts[$partix];
      */

      // get part body if not available
      if (!$part->body)
        $part->body = $MESSAGE->get_part_content($part->mime_id);

      $OUTPUT = new rcube_html_page();
      $OUTPUT->write(rcmail_print_body($part, array('safe' => $MESSAGE->is_safe, 'inline_html' => false)));
    }
    else {
      header(sprintf('Content-Length: %d', $part->size));
      header(sprintf('Content-Disposition: %s; filename="%s";',
                     $_GET['_download'] ? 'attachment' : 'inline',
                     $part->filename ? abbreviate_string($part->filename, 55) : "roundcube.$ctype_secondary"));

      // turn off output buffering and print part content
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
