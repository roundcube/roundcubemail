<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/get.inc                                            |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Delivering a specific part of a mail message                        |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/


// show loading page
if (!empty($_GET['_preload'])) {
  $url = preg_replace('/([&?]+)_preload=/', '\\1_embed=', $_SERVER['REQUEST_URI']);
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

// check connection status
check_storage_status();

// show part page
if (!empty($_GET['_frame'])) {
  if (($part_id = get_input_value('_part', RCUBE_INPUT_GPC)) && ($part = $MESSAGE->mime_parts[$part_id])) {
    $filename = $part->filename;
    if (empty($filename) && $part->mimetype == 'text/html') {
      $filename = rcube_label('htmlmessage');
    }
    if (!empty($filename)) {
      $OUTPUT->set_pagetitle($filename);
    }
  }

  $OUTPUT->send('messagepart');
  exit;
}

// render thumbnail of an image attachment
else if ($_GET['_thumb']) {
  $pid = get_input_value('_part', RCUBE_INPUT_GET);
  if ($part = $MESSAGE->mime_parts[$pid]) {
    $thumbnail_size = $RCMAIL->config->get('image_thumbnail_size', 240);
    $temp_dir = $RCMAIL->config->get('temp_dir');
    list(,$ext) = explode('/', $part->mimetype);
    $cache_basename = $temp_dir . '/' . md5($MESSAGE->headers->messageID . $part->mime_id . ':' . $RCMAIL->user->ID . ':' . $thumbnail_size);
    $cache_file = $cache_basename . '.' . $ext;
    $mimetype = $part->mimetype;

    // render thumbnail image if not done yet
    if (!is_file($cache_file)) {
      $fp = fopen(($orig_name = $cache_basename . '.orig.' . $ext), 'w');
      $MESSAGE->get_part_content($part->mime_id, $fp);
      fclose($fp);

      $image = new rcube_image($orig_name);
      if ($imgtype = $image->resize($RCMAIL->config->get('image_thumbnail_size', 240), $cache_file, true)) {
        $mimetype = 'image/' . $imgtype;
        unlink($orig_name);
      }
      else {
        rename($orig_name, $cache_file);
      }
    }

    if (is_file($cache_file)) {
      header('Content-Type: ' . $mimetype);
      readfile($cache_file);
    }
  }

  exit;
}

else if (strlen($pid = get_input_value('_part', RCUBE_INPUT_GET))) {

  if ($part = $MESSAGE->mime_parts[$pid]) {
    $ctype_primary = strtolower($part->ctype_primary);
    $ctype_secondary = strtolower($part->ctype_secondary);
    $mimetype = sprintf('%s/%s', $ctype_primary, $ctype_secondary);

    // allow post-processing of the message body
    $plugin = $RCMAIL->plugins->exec_hook('message_part_get',
      array('uid' => $MESSAGE->uid, 'id' => $part->mime_id, 'mimetype' => $mimetype, 'part' => $part, 'download' => !empty($_GET['_download'])));

    if ($plugin['abort'])
      exit;

    // overwrite modified vars from plugin
    $mimetype = $plugin['mimetype'];

    // TIFF to JPEG conversion, if needed
    $tiff_support = !empty($_SESSION['browser_caps']) && !empty($_SESSION['browser_caps']['tif']);
    if (!empty($_REQUEST['_embed']) && !$tiff_support
      && $RCMAIL->config->get('im_convert_path')
      && rcmail_part_image_type($part) == 'image/tiff'
    ) {
      $tiff2jpeg = true;
      $mimetype = 'image/jpeg';
    }

    list($ctype_primary, $ctype_secondary) = explode('/', $mimetype);
    if ($plugin['body'])
      $part->body = $plugin['body'];

    $browser = $RCMAIL->output->browser;

    // send download headers
    if ($plugin['download']) {
      header("Content-Type: application/octet-stream");
      if ($browser->ie)
        header("Content-Type: application/force-download");
    }
    else if ($ctype_primary == 'text') {
      header("Content-Type: text/$ctype_secondary; charset=" . ($part->charset ? $part->charset : RCMAIL_CHARSET));
    }
    else {
      $mimetype = rcmail_fix_mimetype($mimetype);
      header("Content-Type: $mimetype");
      header("Content-Transfer-Encoding: binary");
    }

    // deliver part content
    if ($ctype_primary == 'text' && $ctype_secondary == 'html' && empty($plugin['download'])) {
      // Check if we have enough memory to handle the message in it
      // #1487424: we need up to 10x more memory than the body
      if (!rcmail_mem_check($part->size * 10)) {
        $out = '<body>' . rcube_label('messagetoobig'). ' '
          . html::a('?_task=mail&_action=get&_download=1&_uid='.$MESSAGE->uid.'&_part='.$part->mime_id
            .'&_mbox='. urlencode($RCMAIL->storage->get_folder()), rcube_label('download')) . '</body></html>';
      }
      else {
        // get part body if not available
        if (!$part->body)
          $part->body = $MESSAGE->get_part_content($part->mime_id);

        $out = rcmail_print_body($part, array('safe' => $MESSAGE->is_safe, 'inline_html' => false));
      }

      // check connection status
      if ($part->size && empty($part->body)) {
        check_storage_status();
      }

      $OUTPUT = new rcube_output_html();
      $OUTPUT->write($out);
    }
    else {
      // don't kill the connection if download takes more than 30 sec.
      @set_time_limit(0);

      if ($part->filename) {
        $filename = $part->filename;
      }
      else if ($part->mimetype == 'text/html') {
        $filename = rcube_label('htmlmessage');
      }
      else {
        $ext = '.' . ($mimetype == 'text/plain' ? 'txt' : $ctype_secondary);
        $filename = ($MESSAGE->subject ? $MESSAGE->subject : 'roundcube') . $ext;
      }

      $filename = preg_replace('[\r\n]', '', $filename);

      if ($browser->ie && $browser->ver < 7)
        $filename = rawurlencode(abbreviate_string($filename, 55));
      else if ($browser->ie)
        $filename = rawurlencode($filename);
      else
        $filename = addcslashes($filename, '"');

      $disposition = !empty($plugin['download']) ? 'attachment' : 'inline';

      header("Content-Disposition: $disposition; filename=\"$filename\"");

      // handle tiff to jpeg conversion
      if (!empty($tiff2jpeg)) {
        $temp_dir  = unslashify($RCMAIL->config->get('temp_dir'));
        $file_path = tempnam($temp_dir, 'rcmAttmnt');

        // write content to temp file
        if ($part->body) {
          $saved = file_put_contents($file_path, $part->body);
        }
        else if ($part->size) {
          $fd = fopen($file_path, 'w');
          $saved = $RCMAIL->storage->get_message_part($MESSAGE->uid, $part->mime_id, $part, false, $fd);
          fclose($fd);
        }

        // convert image to jpeg and send it to the browser
        if ($saved) {
          $image = new rcube_image($file_path);
          if ($image->convert(rcube_image::TYPE_JPG, $file_path)) {
            header("Content-Length: " . filesize($file_path));
            readfile($file_path);
          }
          unlink($file_path);
        }
      }
      // do content filtering to avoid XSS through fake images
      else if (!empty($_REQUEST['_embed']) && $browser->ie && $browser->ver <= 8) {
        if ($part->body) {
          echo preg_match('/<(script|iframe|object)/i', $part->body) ? '' : $part->body;
          $sent = true;
        }
        else if ($part->size) {
          $stdout = fopen('php://output', 'w');
          stream_filter_register('rcube_content', 'rcube_content_filter') or die('Failed to register content filter');
          stream_filter_append($stdout, 'rcube_content');
          $sent = $RCMAIL->storage->get_message_part($MESSAGE->uid, $part->mime_id, $part, false, $stdout);
        }
      }
      // send part as-it-is
      else {
        if ($part->body) {
          header("Content-Length: " . sizeof($part->body));
          echo $part->body;
          $sent = true;
        }
        else if ($part->size) {
          if ($size = (int)$part->d_parameters['size']) {
            header("Content-Length: $size");
          }

          $sent = $RCMAIL->storage->get_message_part($MESSAGE->uid, $part->mime_id, $part, true);
        }
      }

      // check connection status
      if ($part->size && !$sent) {
        check_storage_status();
      }
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


function check_storage_status()
{
    $error = rcmail::get_instance()->storage->get_error_code();

    // Check if we have a connection error
    if ($error == rcube_imap_generic::ERROR_BAD) {
        ob_end_clean();

        // Get action is often executed simultanously.
        // Some servers have MAXPERIP or other limits.
        // To workaround this we'll wait for some time
        // and try again (once).
        // Note: Random sleep interval is used to minimize concurency
        // in getting message parts

        if (!isset($_GET['_redirected'])) {
            usleep(rand(10,30)*100000); // 1-3 sec.
            header('Location: ' . $_SERVER['REQUEST_URI'] . '&_redirected=1');
        }
        else {
            raise_error(array(
                'code' => 500, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => 'Unable to get/display message part. IMAP connection error'),
                true, true);
        }

        // Don't kill session, just quit (#1486995)
        exit;
    }
}
