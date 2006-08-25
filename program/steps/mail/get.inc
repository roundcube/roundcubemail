<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/get.inc                                            |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
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
if ($_GET['_preload'])
  {
  $url = str_replace('&_preload=1', '', $_SERVER['REQUEST_URI']);
  $message = rcube_label('loadingdata');

  print "<html>\n<head>\n" .
        '<meta http-equiv="refresh" content="0; url='.$url.'">' .
        "\n</head>\n<body>" .
        $message .
        "\n</body>\n</html>";
  exit;
  }


// similar code as in program/steps/mail/show.inc
if ($_GET['_uid'])
  {
  $MESSAGE = array('UID' => get_input_value('_uid', RCUBE_INPUT_GET));
  $MESSAGE['structure'] = $IMAP->get_structure($MESSAGE['UID']);
  $MESSAGE['parts'] = $IMAP->get_mime_numbers($MESSAGE['structure']);
  }


// show part page
if ($_GET['_frame'])
  {
  parse_template('messagepart');
  exit;
  }

else if ($pid = get_input_value('_part', RCUBE_INPUT_GET))
  {
  if ($part = $MESSAGE['parts'][$pid]);
    {
    $ctype_primary = strtolower($part->ctype_primary);
    $ctype_secondary = strtolower($part->ctype_secondary);

    $mimetype = sprintf('%s/%s', $ctype_primary, $ctype_secondary);
    $filename = $part->d_parameters['filename'] ? $part->d_parameters['filename'] : $part->ctype_parameters['name'];

    // send correct headers for content type and length
    if ($_GET['_download'])
      {
      // send download headers
      header("Content-Type: application/octet-stream");
      header(sprintf('Content-Disposition: attachment; filename="%s"',
                     $filename ? rcube_imap::decode_mime_string($filename) : "roundcube.$ctype_secondary"));
      }
    else
      {
      header("Content-Type: $mimetype");
      header(sprintf('Content-Disposition: inline; filename="%s"', rcube_imap::decode_mime_string($filename)));
      }

    // We need to set the following headers to make downloads work using IE in HTTPS mode.
    if (isset($_SERVER['HTTPS']))
      {
      header('Pragma: ');
      header('Cache-Control: ');
      }

    // deliver part content
    if ($ctype_primary=='text' && $ctype_secondary=='html')
      {
      // get part body if not available
      if (!$part->body)
        $part->body = $IMAP->get_message_part($MESSAGE['UID'], $part->mime_id, $part);      

      list($MESSAGE['parts']) = rcmail_parse_message($part,
                                                     array('safe' => (bool)$_GET['_safe'],
                                                           'prefer_html' => TRUE,
                                                           'get_url' => $GET_URL.'&_part=%s'));

      $OUTPUT = new rcube_html_page();
      $OUTPUT->write(rcmail_print_body($MESSAGE['parts'][0], (bool)$_GET['_safe']));
      }
    else
      {
      // turn off output buffering and print part content
      //@ob_end_clean();
      $IMAP->get_message_part($MESSAGE['UID'], $part->mime_id, $part->encoding, true);
      }

    exit;
    }
  }

// print message
else
  {
  $ctype_primary = strtolower($MESSAGE['structure']->ctype_primary);
  $ctype_secondary = strtolower($MESSAGE['structure']->ctype_secondary);
  $mimetype = sprintf('%s/%s', $ctype_primary, $ctype_secondary);

  // send correct headers for content type
  header("Content-Type: text/html");

  $cont = ''; 
  list($MESSAGE['parts']) = rcmail_parse_message($MESSAGE['structure'],
                                                 array('safe' => (bool)$_GET['_safe'],
                                                 'get_url' => $GET_URL.'&_part=%s'));

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