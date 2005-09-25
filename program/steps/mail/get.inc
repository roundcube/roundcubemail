<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/get.inc                                            |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | All rights reserved.                                                  |
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
  $MESSAGE = array();
  $MESSAGE['source'] = rcmail_message_source($_GET['_uid']);

  $mmd = new Mail_mimeDecode($MESSAGE['source']);
  $MESSAGE['structure'] = $mmd->decode(array('include_bodies' => TRUE,
                                             'decode_headers' => FALSE,
                                             'decode_bodies' => FALSE));

  $MESSAGE['parts'] = $mmd->getMimeNumbers($MESSAGE['structure']);
  }



// show part page
if ($_GET['_frame'])
  {
  parse_template('messagepart');
  exit;
  }

else if ($_GET['_part'])
  {
  if ($part = $MESSAGE['parts'][$_GET['_part']]);
    {
    $ctype_primary = strtolower($part->ctype_primary);
    $ctype_secondary = strtolower($part->ctype_secondary);

    $mimetype = sprintf('%s/%s', $ctype_primary, $ctype_secondary);
    $filename = $part->d_parameters['filename'] ? $part->d_parameters['filename'] : $part->ctype_parameters['name'];
    
    if ($ctype_primary=='text')
      {
      list($MESSAGE['parts']) = rcmail_parse_message($MESSAGE['structure'],
                                                     array('safe' => (bool)$_GET['_safe'],
                                                           'prefer_html' => TRUE,
                                                           'get_url' => $GET_URL.'&_part=%s'));

      $cont = rcmail_print_body($MESSAGE['parts'][0], (bool)$_GET['_safe']);
      }
    else
      $cont = $IMAP->mime_decode($part->body, $part->headers['content-transfer-encoding']);

    // send correct headers for content type and length
    if ($_GET['_download'])
      {
      // send download headers
      header("Content-Type: application/octet-stream");
      header(sprintf('Content-Disposition: attachment; filename="%s"',
                     $filename ? $filename : "roundcube.$ctype_secondary"));
      }
    else
      {
      header("Content-Type: $mimetype");
      header(sprintf('Content-Disposition: inline; filename="%s"', $filename));
      }

    header(sprintf('Content-Length: %d', strlen($cont)));

    // deliver part content
    echo $cont;
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

  if ($MESSAGE['parts'] && $ctype_primary=='multipart')
    {
    // reset output page
    $OUTPUT = new rcube_html_page();
    parse_template('messagepart');
    exit;
    }
  else if ($MESSAGE['parts'][0])
    {
    $part = $MESSAGE['parts'][0];
    $cont = rcmail_print_body($part, (bool)$_GET['_safe']);
    }
  else
    $cont = $IMAP->get_body($_GET['_uid']);

  $OUTPUT = new rcube_html_page();
  $OUTPUT->write($cont);

/*
    if ($mimetype=='text/html')
      print $cont;
    else
      {
      print "<html>\n<body>\n";
      print $cont;
      print "\n</body>\n</html>";
      }
*/
  exit;
  }


// if we arrive here, the requested part was not found
header('HTTP/1.1 404 Not Found');
exit;

?>