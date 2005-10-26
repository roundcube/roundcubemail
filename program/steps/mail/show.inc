<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/show.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Display a mail message similar as a usual mail application does     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

require_once('Mail/mimeDecode.php');

$PRINT_MODE = $_action=='print' ? TRUE : FALSE;


// similar code as in program/steps/mail/get.inc
if ($_GET['_uid'])
  {
  $MESSAGE = array();
  $MESSAGE['headers'] = $IMAP->get_headers($_GET['_uid']);
  $MESSAGE['source'] = rcmail_message_source($_GET['_uid']);
  
  // go back to list if message not found (wrong UID)
  if (!$MESSAGE['headers'] || !$MESSAGE['source'])
    {
    $_action = 'list';
    return;
    }

  $mmd = new Mail_mimeDecode($MESSAGE['source']);
  $MESSAGE['structure'] = $mmd->decode(array('include_bodies' => TRUE,
                                             'decode_headers' => FALSE,
                                             'decode_bodies' => FALSE));
                                             
  $mmd->getMimeNumbers($MESSAGE['structure']);

  $MESSAGE['subject'] = $IMAP->decode_header($MESSAGE['structure']->headers['subject']);

  if ($MESSAGE['structure'])
    list($MESSAGE['parts'], $MESSAGE['attachments']) = rcmail_parse_message($MESSAGE['structure'],
                                                                           array('safe' => (bool)$_GET['_safe'],
                                                                                 'prefer_html' => $CONFIG['prefer_html'],
                                                                                 'get_url' => $GET_URL.'&_part=%s'));
  else
    $MESSAGE['body'] = $IMAP->get_body($_GET['_uid']);


  // mark message as read
  if (!$MESSAGE['headers']->seen)
    $IMAP->set_flag($_GET['_uid'], 'SEEN');

  // give message uid to the client
  $javascript = sprintf("%s.set_env('uid', '%s');\n", $JS_OBJECT_NAME, $_GET['_uid']);
  $javascript .= sprintf("%s.set_env('safemode', '%b');", $JS_OBJECT_NAME, $_GET['_safe']);

  // get previous and next message UID
  $a_msg_index = $IMAP->message_index(NULL, $_SESSION['sort_col'], $_SESSION['sort_order']);
  $MESSAGE['index'] = array_search((string)$_GET['_uid'], $a_msg_index, TRUE);
  
  if (isset($a_msg_index[$MESSAGE['index']-1]))
    $javascript .= sprintf("\n%s.set_env('prev_uid', '%s');", $JS_OBJECT_NAME, $a_msg_index[$MESSAGE['index']-1]);
  if (isset($a_msg_index[$MESSAGE['index']+1]))
    $javascript .= sprintf("\n%s.set_env('next_uid', '%s');", $JS_OBJECT_NAME, $a_msg_index[$MESSAGE['index']+1]);

  $OUTPUT->add_script($javascript);
  }



function rcmail_message_attachments($attrib)
  {
  global $CONFIG, $OUTPUT, $PRINT_MODE, $MESSAGE, $GET_URL, $JS_OBJECT_NAME;

  if (sizeof($MESSAGE['attachments']))
    {
    // allow the following attributes to be added to the <ul> tag
    $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id'));
    $out = '<ul' . $attrib_str . ">\n";

    foreach ($MESSAGE['attachments'] as $attach_prop)
      {
      if ($PRINT_MODE)
        $out .= sprintf('<li>%s (%s)</li>'."\n",
                        $attach_prop['filename'],
                        show_bytes($attach_prop['size']));
      else
        $out .= sprintf('<li><a href="#attachment" onclick="return %s.command(\'load-attachment\',{part:\'%s\', mimetype:\'%s\'},this)">%s</a></li>'."\n",
                        $JS_OBJECT_NAME,
                        $attach_prop['part_id'],
                        $attach_prop['mimetype'],
                        $attach_prop['filename']);
    /* direct link
      else
        $out .= sprintf('<li><a href="%s&_part=%s&_frame=1" target="rcubemailattachment">%s</a></li>'."\n",
                        $GET_URL,
                        $attach_prop['part_id'],
                        $attach_prop['filename']);
    */
      }

    $out .= "</ul>";
    return $out;
    }  
  }



// return an HTML iframe for loading mail content
function rcmail_messagecontent_frame($attrib)
  {
  global $COMM_PATH, $OUTPUT, $GET_URL, $JS_OBJECT_NAME;
  
  // allow the following attributes to be added to the <iframe> tag
  $attrib_str = create_attrib_string($attrib);
  $framename = 'rcmailcontentwindow';
  
  $out = sprintf('<iframe src="%s" name="%s"%s>%s</iframe>'."\n",
         $GET_URL,
         $framename,
         $attrib_str,
         rcube_label('loading'));


  $OUTPUT->add_script("$JS_OBJECT_NAME.set_env('contentframe', '$framename');");

  return $out;
  }


function rcmail_remote_objects_msg($attrib)
  {
  global $CONFIG, $OUTPUT, $JS_OBJECT_NAME;
  
  if (!$attrib['id'])
    $attrib['id'] = 'rcmremoteobjmsg';

  // allow the following attributes to be added to the <div> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id'));
  $out = '<div' . $attrib_str . ">";
  
  $out .= rep_specialchars_output(sprintf('%s&nbsp;<a href="#loadimages" onclick="%s.command(\'load-images\')" title="%s">%s</a>',
                                  rcube_label('blockedimages'),
                                  $JS_OBJECT_NAME,
                                  rcube_label('showimages'),
                                  rcube_label('showimages')));
  
  $out .= '</div>';
  
  $OUTPUT->add_script(sprintf("%s.gui_object('remoteobjectsmsg', '%s');", $JS_OBJECT_NAME, $attrib['id']));
  return $out;
  }


if ($_action=='print')
  parse_template('printmessage');
else
  parse_template('message');
?>