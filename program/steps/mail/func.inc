<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/func.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide webmail functionality and GUI objects                       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

require_once('lib/html2text.inc');
require_once('lib/enriched.inc');
require_once('lib/utf8.inc');
require_once('lib/utf7.inc');


$EMAIL_ADDRESS_PATTERN = '/([a-z0-9][a-z0-9\-\.\+\_]*@[a-z0-9]([a-z0-9\-][.]?)*[a-z0-9]\\.[a-z]{2,5})/i';

// set imap properties and session vars
if (strlen($_GET['_mbox']))
  {
  $IMAP->set_mailbox($_GET['_mbox']);
  $_SESSION['mbox'] = $_GET['_mbox'];
  }

if (strlen($_GET['_page']))
  {
  $IMAP->set_page($_GET['_page']);
  $_SESSION['page'] = $_GET['_page'];
  }


// set default sort col/order to session
if (!isset($_SESSION['sort_col']))
  $_SESSION['sort_col'] = $CONFIG['message_sort_col'];
if (!isset($_SESSION['sort_order']))
  $_SESSION['sort_order'] = $CONFIG['message_sort_order'];
  

// define url for getting message parts
if (strlen($_GET['_uid']))
  $GET_URL = sprintf('%s&_action=get&_mbox=%s&_uid=%d', $COMM_PATH, $IMAP->get_mailbox_name(), $_GET['_uid']);


// set current mailbox in client environment
$OUTPUT->add_script(sprintf("%s.set_env('mailbox', '%s');", $JS_OBJECT_NAME, $IMAP->get_mailbox_name()));


if ($CONFIG['trash_mbox'])
  $OUTPUT->add_script(sprintf("%s.set_env('trash_mailbox', '%s');", $JS_OBJECT_NAME, $CONFIG['trash_mbox']));



// return the mailboxlist in HTML
function rcmail_mailbox_list($attrib)
  {
  global $IMAP, $CONFIG, $OUTPUT, $JS_OBJECT_NAME, $COMM_PATH;
  static $s_added_script = FALSE;
  static $a_mailboxes;
  
  $type = $attrib['type'] ? $attrib['type'] : 'ul';
  $add_attrib = $type=='select' ? array('style', 'class', 'id', 'name', 'onchange') :
                                  array('style', 'class', 'id');
                                  
  if ($type=='ul' && !$attrib['id'])
    $attrib['id'] = 'rcmboxlist';

  // allow the following attributes to be added to the <ul> tag
  $attrib_str = create_attrib_string($attrib, $add_attrib);
 
  $out = '<' . $type . $attrib_str . ">\n";
  
  // add no-selection option
  if ($type=='select' && $attrib['noselection'])
    $out .= sprintf('<option value="0">%s</option>'."\n",
                    rcube_label($attrib['noselection']));
  
  // get mailbox list
  $mbox = $IMAP->get_mailbox_name();
  
  // for these mailboxes we have localized labels
  $special_mailboxes = array('inbox', 'sent', 'drafts', 'trash', 'junk');


  // build the folders tree
  if (empty($a_mailboxes))
    {
    // get mailbox list
    $a_folders = $IMAP->list_mailboxes();
    $delimiter = $IMAP->get_hierarchy_delimiter();
    $a_mailboxes = array();
    
    foreach ($a_folders as $folder)
      rcmail_build_folder_tree($a_mailboxes, $folder, $delimiter);
    }

// var_dump($a_mailboxes);

  if ($type=='select')
    $out .= rcmail_render_folder_tree_select($a_mailboxes, $special_mailboxes, $mbox, $attrib['maxlength']);
   else
    $out .= rcmail_render_folder_tree_html($a_mailboxes, $special_mailboxes, $mbox, $attrib['maxlength']);


  if ($type=='ul')
    $OUTPUT->add_script(sprintf("%s.gui_object('mailboxlist', '%s');", $JS_OBJECT_NAME, $attrib['id']));

  return $out . "</$type>";
  }




// create a hierarchical array of the mailbox list
function rcmail_build_folder_tree(&$arrFolders, $folder, $delm='/', $path='')
  {
  $pos = strpos($folder, $delm);
  if ($pos !== false)
    {
    $subFolders = substr($folder, $pos+1);
    $currentFolder = substr($folder, 0, $pos);
    }
  else
    {
    $subFolders = false;
    $currentFolder = $folder;
    }

  $path .= $currentFolder;

  if (!isset($arrFolders[$currentFolder]))
    {
    $arrFolders[$currentFolder] = array('id' => $path,
                                        'name' => $currentFolder,
                                        'folders' => array());
    }

  if (!empty($subFolders))
    rcmail_build_folder_tree($arrFolders[$currentFolder]['folders'], $subFolders, $delm, $path.$delm);
  }
  

// return html for a structured list <ul> for the mailbox tree
function rcmail_render_folder_tree_html(&$arrFolders, &$special, &$mbox, $maxlength, $nestLevel=0)
  {
  global $JS_OBJECT_NAME, $IMAP, $CONFIG;

  $idx = 0;
  $out = '';
  foreach ($arrFolders as $key => $folder)
    {
    $zebra_class = ($nestLevel*$idx)%2 ? 'even' : 'odd';

    $folder_lc = strtolower($folder['id']);
    if (in_array($folder_lc, $special))
      $foldername = rcube_label($folder_lc);
    else
      {
      $foldername = UTF7DecodeString($folder['name']);

      // shorten the folder name to a given length
      if ($maxlength && $maxlength>1)
        $foldername = abbrevate_string($foldername, $maxlength);
      }

    // add unread message count display
    if ($unread_count = $IMAP->messagecount($folder['id'], 'UNSEEN', ($folder['id']==$mbox)))
      $foldername .= sprintf(' (%d)', $unread_count);
      
    // make folder name safe for ids and class names
    $folder_css = $class_name = preg_replace('/[^a-z0-9\-_]/', '', $folder_lc);

    // set special class for Sent, Drafts, Trash and Junk
    if ($folder['id']==$CONFIG['sent_mbox'])
      $class_name = 'sent';
    else if ($folder['id']==$CONFIG['drafts_mbox'])
      $class_name = 'drafts';
    else if ($folder['id']==$CONFIG['trash_mbox'])
      $class_name = 'trash';
    else if ($folder['id']==$CONFIG['junk_mbox'])
      $class_name = 'junk';

    $out .= sprintf('<li id="rcmbx%s" class="mailbox %s %s%s%s"><a href="./#%s" onclick="return %s.command(\'list\',\'%s\')" onmouseup="return %s.mbox_mouse_up(\'%s\')">%s</a>',
                    $folder_css,
                    $class_name,
                    $zebra_class,
                    $unread_count ? ' unread' : '',
                    $folder['id']==$mbox ? ' selected' : '',
                    $folder['id'],
                    $JS_OBJECT_NAME,
                    $folder['id'],
                    $JS_OBJECT_NAME,
                    $folder['id'],
                    rep_specialchars_output($foldername, 'html', 'all'));

    if (!empty($folder['folders']))
      $out .= "\n<ul>\n" . rcmail_render_folder_tree_html($folder['folders'], $special, $mbox, $maxlength, $nestLevel+1) . "</ul>\n";

    $out .= "</li>\n";
    $idx++;
    }

  return $out;
  }


// return html for a flat list <select> for the mailbox tree
function rcmail_render_folder_tree_select(&$arrFolders, &$special, &$mbox, $maxlength, $nestLevel=0)
  {
  global $IMAP;

  $idx = 0;
  $out = '';
  foreach ($arrFolders as $key=>$folder)
    {
    $folder_lc = strtolower($folder['id']);
    if (in_array($folder_lc, $special))
      $foldername = rcube_label($folder_lc);
    else
      {
      $foldername = UTF7DecodeString($folder['name']);
      
      // shorten the folder name to a given length
      if ($maxlength && $maxlength>1)
        $foldername = abbrevate_string($foldername, $maxlength);
      }

    $out .= sprintf('<option value="%s">%s%s</option>'."\n",
                    $folder['id'],
                    str_repeat('&nbsp;', $nestLevel*4),
                    rep_specialchars_output($foldername, 'html', 'all'));

    if (!empty($folder['folders']))
      $out .= rcmail_render_folder_tree_select($folder['folders'], $special, $mbox, $maxlength, $nestLevel+1);

    $idx++;
    }

  return $out;
  }


// return the message list as HTML table
function rcmail_message_list($attrib)
  {
  global $IMAP, $CONFIG, $COMM_PATH, $OUTPUT, $JS_OBJECT_NAME;

  $skin_path = $CONFIG['skin_path'];
  $image_tag = '<img src="%s%s" alt="%s" border="0" />';

  // check to see if we have some settings for sorting
  $sort_col   = $_SESSION['sort_col'];
  $sort_order = $_SESSION['sort_order'];

  // get message headers
  $a_headers = $IMAP->list_headers('', '', $sort_col, $sort_order);

  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcubemessagelist';

  // allow the following attributes to be added to the <table> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'cellpadding', 'cellspacing', 'border', 'summary'));

  $out = '<table' . $attrib_str . ">\n";


  // define list of cols to be displayed
  $a_show_cols = is_array($CONFIG['list_cols']) ? $CONFIG['list_cols'] : array('subject');
  $a_sort_cols = array('subject', 'date', 'from', 'to');
  
  // show 'to' instead of from in sent messages
  if (strtolower($IMAP->get_mailbox_name())=='sent' && ($f = array_search('from', $a_show_cols)))
    $a_show_cols[$f] = 'to';

  // add col definition
  $out .= '<colgroup>';
  $out .= '<col class="icon">';

  foreach ($a_show_cols as $col)
    $out .= sprintf('<col class="%s">', $col);

  $out .= '<col class="icon">';
  $out .= "</colgroup>\n";

  // add table title
  $out .= "<thead><tr>\n<td class=\"icon\">&nbsp;</td>\n";

  $javascript = '';
  foreach ($a_show_cols as $col)
    {
    // get column name
    $col_name = rep_specialchars_output(rcube_label($col));

    // make sort links
    $sort = '';
    if (in_array($col, $a_sort_cols) && (!empty($attrib['sortdescbutton']) || !empty($attrib['sortascbutton'])))
      {
      $sort = '&nbsp;&nbsp;';

      // asc link
      if (!empty($attrib['sortascbutton']))
        {
        $sort .= rcube_button(array('command' => 'sort',
                                    'prop' => $col.'_ASC',
                                    'image' => $attrib['sortascbutton'],
                                    'title' => 'sortasc'));
        }        
        
      // desc link
      if (!empty($attrib['sortdescbutton']))
        {
        $sort .= rcube_button(array('command' => 'sort',
                                    'prop' => $col.'_DESC',
                                    'image' => $attrib['sortdescbutton'],
                                    'title' => 'sortdesc'));        
        }
      }
      
    $sort_class = $col==$sort_col ? " sorted$sort_order" : '';

    // put it all together
    $out .= '<td class="'.$col.$sort_class.'" id="rcmHead'.$col.'">' . "$col_name$sort</td>\n";    
    }

  $out .= '<td class="icon">'.($attrib['attachmenticon'] ? sprintf($image_tag, $skin_path, $attrib['attachmenticon'], '') : '')."</td>\n";
  $out .= "</tr></thead>\n<tbody>\n";


  // no messages in this mailbox
  if (!sizeof($a_headers))
    {
    $out .= rep_specialchars_output(
				sprintf('<tr><td colspan="%d">%s</td></tr>',
                   sizeof($a_show_cols)+2,
                   rcube_label('nomessagesfound')));
    }


  $a_js_message_arr = array();

  // create row for each message
  foreach ($a_headers as $i => $header)  //while (list($i, $header) = each($a_headers))
    {
    $message_icon = $attach_icon = '';
    $js_row_arr = array();
    $zebra_class = $i%2 ? 'even' : 'odd';

    // set messag attributes to javascript array
    if (!$header->seen)
      $js_row_arr['unread'] = true;
    if ($header->answered)
      $js_row_arr['replied'] = true;

	// set message icon    
    if ($attrib['unreadicon'] && !$header->seen)
      $message_icon = $attrib['unreadicon'];
    else if ($attrib['repliedicon'] && $header->answered)
      $message_icon = $attrib['repliedicon'];
    else if ($attrib['messageicon'])
      $message_icon = $attrib['messageicon'];
    
	// set attachment icon
    if ($attrib['attachmenticon'] && preg_match("/multipart\/m/i", $header->ctype))
      $attach_icon = $attrib['attachmenticon'];
        
    $out .= sprintf('<tr id="rcmrow%d" class="message'.($header->seen ? '' : ' unread').' '.$zebra_class.'">'."\n", $header->uid);
    $out .= sprintf("<td class=\"icon\">%s</td>\n", $message_icon ? sprintf($image_tag, $skin_path, $message_icon, '') : '');
        
    // format each col
    foreach ($a_show_cols as $col)
      {
      if ($col=='from' || $col=='to')
        $cont = rep_specialchars_output(rcmail_address_string($header->$col, 3, $attrib['addicon']));
      else if ($col=='subject')
        $cont = rep_specialchars_output($IMAP->decode_header($header->$col), 'html', 'all');
      else if ($col=='size')
        $cont = show_bytes($header->$col);
      else if ($col=='date')
        $cont = format_date($header->date); //date('m.d.Y G:i:s', strtotime($header->date));
      else
        $cont = rep_specialchars_output($header->$col, 'html', 'all');
        
	  $out .= '<td class="'.$col.'">' . $cont . "</td>\n";
      }

    $out .= sprintf("<td class=\"icon\">%s</td>\n", $attach_icon ? sprintf($image_tag, $skin_path, $attach_icon, '') : '');
    $out .= "</tr>\n";
    
    if (sizeof($js_row_arr))
      $a_js_message_arr[$header->uid] = $js_row_arr;
    }
  
  // complete message table
  $out .= "</tbody></table>\n";
  
  
  $message_count = $IMAP->messagecount();
  
  // set client env
  $javascript .= sprintf("%s.gui_object('messagelist', '%s');\n", $JS_OBJECT_NAME, $attrib['id']);
  $javascript .= sprintf("%s.set_env('messagecount', %d);\n", $JS_OBJECT_NAME, $message_count);
  $javascript .= sprintf("%s.set_env('current_page', %d);\n", $JS_OBJECT_NAME, $IMAP->list_page);
  $javascript .= sprintf("%s.set_env('pagecount', %d);\n", $JS_OBJECT_NAME, ceil($message_count/$IMAP->page_size));
  $javascript .= sprintf("%s.set_env('sort_col', '%s');\n", $JS_OBJECT_NAME, $sort_col);
  $javascript .= sprintf("%s.set_env('sort_order', '%s');\n", $JS_OBJECT_NAME, $sort_order);
  
  if ($attrib['messageicon'])
    $javascript .= sprintf("%s.set_env('messageicon', '%s%s');\n", $JS_OBJECT_NAME, $skin_path, $attrib['messageicon']);
  if ($attrib['unreadicon'])
    $javascript .= sprintf("%s.set_env('unreadicon', '%s%s');\n", $JS_OBJECT_NAME, $skin_path, $attrib['unreadicon']);
  if ($attrib['repliedicon'])
    $javascript .= sprintf("%s.set_env('repliedicon', '%s%s');\n", $JS_OBJECT_NAME, $skin_path, $attrib['repliedicon']);
  if ($attrib['attachmenticon'])
    $javascript .= sprintf("%s.set_env('attachmenticon', '%s%s');\n", $JS_OBJECT_NAME, $skin_path, $attrib['attachmenticon']);
    
  $javascript .= sprintf("%s.set_env('messages', %s);", $JS_OBJECT_NAME, array2js($a_js_message_arr));
  
  $OUTPUT->add_script($javascript);  
  
  return $out;
  }




// return javascript commands to add rows to the message list
function rcmail_js_message_list($a_headers, $insert_top=FALSE)
  {
  global $CONFIG, $IMAP;

  $commands = '';
  $a_show_cols = is_array($CONFIG['list_cols']) ? $CONFIG['list_cols'] : array('subject');

  // show 'to' instead of from in sent messages
  if (strtolower($IMAP->get_mailbox_name())=='sent' && ($f = array_search('from', $a_show_cols)))
    $a_show_cols[$f] = 'to';

  // loop through message headers
  for ($n=0; $a_headers[$n]; $n++)
    {
    $header = $a_headers[$n];
    $a_msg_cols = array();
    $a_msg_flags = array();
      
    // format each col; similar as in rcmail_message_list()
    foreach ($a_show_cols as $col)
      {
      if ($col=='from' || $col=='to')
        $cont = rep_specialchars_output(rcmail_address_string($header->$col, 3));
      else if ($col=='subject')
        $cont = rep_specialchars_output($IMAP->decode_header($header->$col), 'html', 'all');
      else if ($col=='size')
        $cont = show_bytes($header->$col);
      else if ($col=='date')
        $cont = format_date($header->date); //date('m.d.Y G:i:s', strtotime($header->date));
      else
        $cont = rep_specialchars_output($header->$col, 'html', 'all');
          
      $a_msg_cols[$col] = $cont;
      }

    $a_msg_flags['unread'] = $header->seen ? 0 : 1;
    $a_msg_flags['replied'] = $header->answered ? 1 : 0;
  
    $commands .= sprintf("this.add_message_row(%s, %s, %s, %b);\n",
                         $header->uid,
                         array2js($a_msg_cols),
                         array2js($a_msg_flags),
                         preg_match("/multipart\/m/i", $header->ctype));
    }

  return $commands;
  }



function rcmail_messagecount_display($attrib)
  {
  global $IMAP, $OUTPUT, $JS_OBJECT_NAME;
  
  if (!$attrib['id'])
    $attrib['id'] = 'rcmcountdisplay';

  $OUTPUT->add_script(sprintf("%s.gui_object('countdisplay', '%s');", $JS_OBJECT_NAME, $attrib['id']));

  // allow the following attributes to be added to the <span> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id'));

  
  $out = '<span' . $attrib_str . '>';
  $out .= rcmail_get_messagecount_text();
  $out .= '</span>';
  return $out;
  }



function rcmail_get_messagecount_text()
  {
  global $IMAP, $MESSAGE;
  
  if (isset($MESSAGE['index']))
    {
    $a_msg_index = $IMAP->message_index();
    return rcube_label(array('name' => 'messagenrof',
                             'vars' => array('nr'  => $MESSAGE['index']+1,
                                             'count' => sizeof($a_msg_index))));
    }
  
  $start_msg = ($IMAP->list_page-1) * $IMAP->page_size + 1;
  $max = $IMAP->messagecount();

  if ($max==0)
    $out = rcube_label('mailboxempty');
  else
    $out = rcube_label(array('name' => 'messagesfromto',
                              'vars' => array('from'  => $start_msg,
                                              'to'    => min($max, $start_msg + $IMAP->page_size - 1),
                                              'count' => $max)));

  return rep_specialchars_output($out);
  }


function rcmail_print_body($part, $safe=FALSE, $plain=FALSE) // $body, $ctype_primary='text', $ctype_secondary='plain', $encoding='7bit', $safe=FALSE, $plain=FALSE)
  {
  global $IMAP, $REMOTE_OBJECTS, $JS_OBJECT_NAME;

  // extract part properties: body, ctype_primary, ctype_secondary, encoding, parameters
  extract($part);
  
  $block = $plain ? '%s' : '%s'; //'<div style="display:block;">%s</div>';
  $body = $IMAP->mime_decode($body, $encoding);
  $body = $IMAP->charset_decode($body, $parameters);


  // text/html
  if ($ctype_secondary=='html')
    {
    if (!$safe)  // remove remote images and scripts
      {
      $remote_patterns = array('/(src|background)=(["\']?)([hftps]{3,5}:\/{2}[^"\'\s]+)(\2|\s|>)/Ui',
                           //  '/(src|background)=(["\']?)([\.\/]+[^"\'\s]+)(\2|\s|>)/Ui',
                               '/(<base.*href=["\']?)([hftps]{3,5}:\/{2}[^"\'\s]+)([^<]*>)/i',
                               '/(<link.*href=["\']?)([hftps]{3,5}:\/{2}[^"\'\s]+)([^<]*>)/i',
                               '/url\s*\(["\']?([hftps]{3,5}:\/{2}[^"\'\s]+)["\']?\)/i',
                               '/url\s*\(["\']?([\.\/]+[^"\'\s]+)["\']?\)/i',
                               '/<script.+<\/script>/Umis');

      $remote_replaces = array('',  // '\\1=\\2#\\4',
                            // '\\1=\\2#\\4',
                               '',
                               '',  // '\\1#\\3',
                               'none',
                               'none',
                               '');
      
      // set flag if message containes remote obejcts that where blocked
      foreach ($remote_patterns as $pattern)
        {
        if (preg_match($pattern, $body))
          {
          $REMOTE_OBJECTS = TRUE;
          break;
          }
        }

      $body = preg_replace($remote_patterns, $remote_replaces, $body);
      }

    return sprintf($block, rep_specialchars_output($body, 'html', '', FALSE));
    }

  // text/enriched
  if ($ctype_secondary=='enriched')
    {
    $body = enriched_to_html($body);
    return sprintf($block, rep_specialchars_output($body, 'html'));
    }
  else
    {
    // make links and email-addresses clickable
    $convert_patterns = $convert_replaces = $replace_strings = array();
    
    $url_chars = 'a-z0-9_\-\+\*\$\/&%=@#:';
    $url_chars_within = '\?\.~,!';

    $convert_patterns[] = "/([\w]+):\/\/([a-z0-9\-\.]+[a-z]{2,4}([$url_chars$url_chars_within]*[$url_chars])?)/ie";
    $convert_replaces[] = "rcmail_str_replacement('<a href=\"\\1://\\2\" target=\"_blank\">\\1://\\2</a>', \$replace_strings)";

    $convert_patterns[] = "/([^\/:]|\s)(www\.)([a-z0-9\-]{2,}[a-z]{2,4}([$url_chars$url_chars_within]*[$url_chars])?)/ie";
    $convert_replaces[] = "rcmail_str_replacement('\\1<a href=\"http://\\2\\3\" target=\"_blank\">\\2\\3</a>', \$replace_strings)";
    
    $convert_patterns[] = '/([a-z0-9][a-z0-9\-\.\+\_]*@[a-z0-9]([a-z0-9\-][.]?)*[a-z0-9]\\.[a-z]{2,5})/ie';
    $convert_replaces[] = "rcmail_str_replacement('<a href=\"mailto:\\1\" onclick=\"return $JS_OBJECT_NAME.command(\'compose\',\'\\1\',this)\">\\1</a>', \$replace_strings)";

    $body = wordwrap(trim($body), 80);
    $body = preg_replace($convert_patterns, $convert_replaces, $body);

    // split body into single lines
    $a_lines = preg_split('/\r?\n/', $body);

    // colorize quoted parts
    for($n=0; $n<sizeof($a_lines); $n++)
      {
      $line = $a_lines[$n];

      if ($line{2}=='>')
        $color = 'red';
      else if ($line{1}=='>')
        $color = 'green';
      else if ($line{0}=='>')
        $color = 'blue';
      else
        $color = FALSE;

      $line = rep_specialchars_output($line, 'html', 'replace', FALSE);
        
      if ($color)
        $a_lines[$n] = sprintf('<font color="%s">%s</font>', $color, $line);
      else
        $a_lines[$n] = $line;
      }

    // insert the links for urls and mailtos
    $body = preg_replace("/##string_replacement\{([0-9]+)\}##/e", "\$replace_strings[\\1]", join("\n", $a_lines));
    
    return sprintf($block, "<pre>\n".$body."\n</pre>");
    }
  }



// add a string to the replacement array and return a replacement string
function rcmail_str_replacement($str, &$rep)
  {
  static $count = 0;
  $rep[$count] = stripslashes($str);
  return "##string_replacement{".($count++)."}##";
  }


function rcmail_parse_message($structure, $arg=array(), $recursive=FALSE)
  {
  global $IMAP;
  static $sa_inline_objects = array();

  // arguments are: (bool)$prefer_html, (string)$get_url
  extract($arg);

  $a_attachments = array();
  $a_return_parts = array();
  $out = '';

  $message_ctype_primary = strtolower($structure->ctype_primary);
  $message_ctype_secondary = strtolower($structure->ctype_secondary);

  // show message headers
  if ($recursive && is_array($structure->headers) && isset($structure->headers['subject']))
    $a_return_parts[] = array('type' => 'headers',
                              'headers' => $structure->headers);

  // print body if message doesn't have multiple parts
  if ($message_ctype_primary=='text')
    {
    $a_return_parts[] = array('type' => 'content',
                              'body' => $structure->body,
                              'ctype_primary' => $message_ctype_primary,
                              'ctype_secondary' => $message_ctype_secondary,
                              'parameters' => $structure->ctype_parameters,
                              'encoding' => $structure->headers['content-transfer-encoding']);
    }

  // message contains alternative parts
  else if ($message_ctype_primary=='multipart' && $message_ctype_secondary=='alternative' && is_array($structure->parts))
    {
    // get html/plaintext parts
    $plain_part = $html_part = $print_part = $related_part = NULL;
    
    foreach ($structure->parts as $p => $sub_part)
      {
      $sub_ctype_primary = strtolower($sub_part->ctype_primary);
      $sub_ctype_secondary = strtolower($sub_part->ctype_secondary);

      // check if sub part is 
      if ($sub_ctype_primary=='text' && $sub_ctype_secondary=='plain')
        $plain_part = $p;
      else if ($sub_ctype_primary=='text' && $sub_ctype_secondary=='html')
        $html_part = $p;
      else if ($sub_ctype_primary=='text' && $sub_ctype_secondary=='enriched')
        $enriched_part = $p;
      else if ($sub_ctype_primary=='multipart' && $sub_ctype_secondary=='related')
        $related_part = $p;
      }

    // parse related part (alternative part could be in here)
    if ($related_part!==NULL && $prefer_html)
      {
      list($parts, $attachmnts) = rcmail_parse_message($structure->parts[$related_part], $arg, TRUE);
      $a_return_parts = array_merge($a_return_parts, $parts);
      $a_attachments = array_merge($a_attachments, $attachmnts);
      }

    // print html/plain part
    else if ($html_part!==NULL && $prefer_html)
      $print_part = $structure->parts[$html_part];
    else if ($enriched_part!==NULL)
      $print_part = $structure->parts[$enriched_part];
    else if ($plain_part!==NULL)
      $print_part = $structure->parts[$plain_part];

    // show message body
    if (is_object($print_part))
      $a_return_parts[] = array('type' => 'content',
                                'body' => $print_part->body,
                                'ctype_primary' => strtolower($print_part->ctype_primary),
                                'ctype_secondary' => strtolower($print_part->ctype_secondary),
                                'parameters' => $print_part->ctype_parameters,
                                'encoding' => $print_part->headers['content-transfer-encoding']);
    // show plaintext warning
    else if ($html_part!==NULL)
      $a_return_parts[] = array('type' => 'content',
                                'body' => rcube_label('htmlmessage'),
                                'ctype_primary' => 'text',
                                'ctype_secondary' => 'plain');
                                
    // add html part as attachment
    if ($html_part!==NULL && $structure->parts[$html_part]!==$print_part)
      {
      $html_part = $structure->parts[$html_part];
      $a_attachments[] = array('filename' => rcube_label('htmlmessage'),
                               'encoding' => $html_part->headers['content-transfer-encoding'],
                               'mimetype' => 'text/html',
                               'part_id'  => $html_part->mime_id,
                               'size'     => strlen($IMAP->mime_decode($html_part->body, $html_part->headers['content-transfer-encoding'])));
      }
    }

  // message contains multiple parts
  else if ($message_ctype_primary=='multipart' && is_array($structure->parts))
    {
    foreach ($structure->parts as $mail_part)
      {
      $primary_type = strtolower($mail_part->ctype_primary);
      $secondary_type = strtolower($mail_part->ctype_secondary);

      // multipart/alternative
      if ($primary_type=='multipart') // && ($secondary_type=='alternative' || $secondary_type=='mixed' || $secondary_type=='related'))
        {
        list($parts, $attachmnts) = rcmail_parse_message($mail_part, $arg, TRUE);

        $a_return_parts = array_merge($a_return_parts, $parts);
        $a_attachments = array_merge($a_attachments, $attachmnts);
        }

      // part text/[plain|html] OR message/delivery-status
      else if (($primary_type=='text' && ($secondary_type=='plain' || $secondary_type=='html')) ||
               ($primary_type=='message' && $secondary_type=='delivery-status'))
        {
        $a_return_parts[] = array('type' => 'content',
                                  'body' => $mail_part->body,
                                  'ctype_primary' => $primary_type,
                                  'ctype_secondary' => $secondary_type,
                                  'parameters' => $mail_part->ctype_parameters,
                                  'encoding' => $mail_part->headers['content-transfer-encoding']);
        }

      // part message/*
      else if ($primary_type=='message')
        {
        /* don't parse headers here; they're parsed within the recursive call to rcmail_parse_message()
        if ($mail_part->parts[0]->headers)
          $a_return_parts[] = array('type' => 'headers',
                                    'headers' => $mail_part->parts[0]->headers);
        */
                                      
        list($parts, $attachmnts) = rcmail_parse_message($mail_part->parts[0], $arg, TRUE);

        $a_return_parts = array_merge($a_return_parts, $parts);
        $a_attachments = array_merge($a_attachments, $attachmnts);
        }

      // part is file/attachment
      else if ($mail_part->disposition=='attachment' || $mail_part->disposition=='inline' || $mail_part->headers['content-id'] ||
               (empty($mail_part->disposition) && ($mail_part->d_parameters['filename'] || $mail_part->d_parameters['name'])))
        {
        if ($message_ctype_secondary=='related' && $mail_part->headers['content-id'])
          $sa_inline_objects[] = array('filename' => $mail_part->d_parameters['filename'],
                                       'mimetype' => strtolower("$primary_type/$secondary_type"),
                                       'part_id'  => $mail_part->mime_id,
                                       'content_id' => preg_replace(array('/^</', '/>$/'), '', $mail_part->headers['content-id']));

        else if ($mail_part->d_parameters['filename'])
          $a_attachments[] = array('filename' => $mail_part->d_parameters['filename'],
                                   'encoding' => strtolower($mail_part->headers['content-transfer-encoding']),
                                   'mimetype' => strtolower("$primary_type/$secondary_type"),
                                   'part_id'  => $mail_part->mime_id,
                                   'size'     => strlen($IMAP->mime_decode($mail_part->body, $mail_part->headers['content-transfer-encoding'])) /*,
                                   'content'  => $mail_part->body */);
                                   
        else if ($mail_part->ctype_parameters['name'])
          $a_attachments[] = array('filename' => $mail_part->ctype_parameters['name'],
                                   'encoding' => strtolower($mail_part->headers['content-transfer-encoding']),
                                   'mimetype' => strtolower("$primary_type/$secondary_type"),
                                   'part_id'  => $mail_part->mime_id,
                                   'size'     => strlen($IMAP->mime_decode($mail_part->body, $mail_part->headers['content-transfer-encoding'])) /*,
                                   'content'  => $mail_part->body */);
                                   
                                   
        }
      }


    // if this was a related part try to resolve references
    if ($message_ctype_secondary=='related' && sizeof($sa_inline_objects))
      {
      $a_replace_patters = array();
      $a_replace_strings = array();
        
      foreach ($sa_inline_objects as $inline_object)
        {
        $a_replace_patters[] = 'cid:'.$inline_object['content_id'];
        $a_replace_strings[] = sprintf($get_url, $inline_object['part_id']);
        }
      
      foreach ($a_return_parts as $i => $return_part)
        {
        if ($return_part['type']!='content')
          continue;

        // decode body and replace cid:...
        $a_return_parts[$i]['body'] = str_replace($a_replace_patters, $a_replace_strings, $IMAP->mime_decode($return_part['body'], $return_part['encoding']));
        $a_return_parts[$i]['encoding'] = '7bit';
        }
      }
    }
    

  // join all parts together
  //$out .= join($part_delimiter, $a_return_parts);

  return array($a_return_parts, $a_attachments);
  }




// return table with message headers
function rcmail_message_headers($attrib, $headers=NULL)
  {
  global $IMAP, $OUTPUT, $MESSAGE;
  static $sa_attrib;
  
  // keep header table attrib
  if (is_array($attrib) && !$sa_attrib)
    $sa_attrib = $attrib;
  else if (!is_array($attrib) && is_array($sa_attrib))
    $attrib = $sa_attrib;
  
  
  if (!isset($MESSAGE))
    return FALSE;

  // get associative array of headers object
  if (!$headers)
    $headers = is_object($MESSAGE['headers']) ? get_object_vars($MESSAGE['headers']) : $MESSAGE['headers'];
    
  $header_count = 0;
  
  // allow the following attributes to be added to the <table> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'cellpadding', 'cellspacing', 'border', 'summary'));
  $out = '<table' . $attrib_str . ">\n";

  // show these headers
  $standard_headers = array('subject', 'from', 'organization', 'to', 'cc', 'reply-to', 'date');
  
  foreach ($standard_headers as $hkey)
    {
    if (!$headers[$hkey])
      continue;

    if ($hkey=='date' && !empty($headers[$hkey]))
      $header_value = format_date(strtotime($headers[$hkey]));
    else if (in_array($hkey, array('from', 'to', 'cc', 'reply-to')))
      $header_value = rep_specialchars_output(rcmail_address_string($IMAP->decode_header($headers[$hkey]), NULL, $attrib['addicon']));
    else
      $header_value = rep_specialchars_output($IMAP->decode_header($headers[$hkey]), '', 'all');

    $out .= "\n<tr>\n";
    $out .= '<td class="header-title">'.rep_specialchars_output(rcube_label($hkey)).":&nbsp;</td>\n";
    $out .= '<td class="'.$hkey.'" width="90%">'.$header_value."</td>\n</tr>";
    $header_count++;
    }

  $out .= "\n</table>\n\n";

  return $header_count ? $out : '';  
  }



function rcmail_message_body($attrib)
  {
  global $CONFIG, $OUTPUT, $MESSAGE, $GET_URL, $REMOTE_OBJECTS, $JS_OBJECT_NAME;
  
  if (!is_array($MESSAGE['parts']) && !$MESSAGE['body'])
    return '';
    
  if (!$attrib['id'])
    $attrib['id'] = 'rcmailMsgBody';

  $safe_mode = (bool)$_GET['_safe'];
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id'));
  $out = '<div '. $attrib_str . ">\n";
  
  $header_attrib = array();
  foreach ($attrib as $attr => $value)
    if (preg_match('/^headertable([a-z]+)$/i', $attr, $regs))
      $header_attrib[$regs[1]] = $value;


  // this is an ecrypted message
  // -> create a plaintext body with the according message
  if (!sizeof($MESSAGE['parts']) && $MESSAGE['headers']->ctype=='multipart/encrypted')
    {
    $MESSAGE['parts'][0] = array('type' => 'content',
                                 'ctype_primary' => 'text',
                                 'ctype_secondary' => 'plain',
                                 'body' => rcube_label('encryptedmessage'));
    }
  
  if ($MESSAGE['parts'])
    {
    foreach ($MESSAGE['parts'] as $i => $part)
      {
      if ($part['type']=='headers')
        $out .= rcmail_message_headers(sizeof($header_attrib) ? $header_attrib : NULL, $part['headers']);
      else if ($part['type']=='content')
        {
        if (empty($part['parameters']) || empty($part['parameters']['charset']))
          $part['parameters']['charset'] = $MESSAGE['headers']->charset;
        
        // $body = rcmail_print_body($part['body'], $part['ctype_primary'], $part['ctype_secondary'], $part['encoding'], $safe_mode);
        $body = rcmail_print_body($part, $safe_mode);
        $out .= '<div class="message-part">';
        $out .= rcmail_mod_html_body($body, $attrib['id']);
        $out .= "</div>\n";
        }
      }
    }
  else
    $out .= $MESSAGE['body'];


  $ctype_primary = strtolower($MESSAGE['structure']->ctype_primary);
  $ctype_secondary = strtolower($MESSAGE['structure']->ctype_secondary);
  
  // list images after mail body
  if (get_boolean($attrib['showimages']) && $ctype_primary=='multipart' && $ctype_secondary=='mixed' &&
      sizeof($MESSAGE['attachments']) && !strstr($message_body, '<html') && strlen($GET_URL))
    {
    foreach ($MESSAGE['attachments'] as $attach_prop)
      {
      if (strpos($attach_prop['mimetype'], 'image/')===0)
        $out .= sprintf("\n<hr />\n<p align=\"center\"><img src=\"%s&_part=%s\" alt=\"%s\" title=\"%s\" /></p>\n",
                        $GET_URL, $attach_prop['part_id'],
                        $attach_prop['filename'],
                        $attach_prop['filename']);
      }
    }
  
  // tell client that there are blocked remote objects
  if ($REMOTE_OBJECTS && !$safe_mode)
    $OUTPUT->add_script(sprintf("%s.set_env('blockedobjects', true);", $JS_OBJECT_NAME));

  $out .= "\n</div>";
  return $out;
  }



// modify a HTML message that it can be displayed inside a HTML page
function rcmail_mod_html_body($body, $container_id)
  {
  $last_style_pos = 0;
  $body_lc = strtolower($body);
  
  // find STYLE tags
  while (($pos = strpos($body_lc, '<style', $last_style_pos)) && ($pos2 = strpos($body_lc, '</style>', $pos)))
    {
    $pos2 += 8;
    $body_pre = substr($body, 0, $pos);
    $styles = substr($body, $pos, $pos2-$pos);
    $body_post = substr($body, $pos2, strlen($body)-$pos2);
    
    // replace all css definitions with #container [def]
    $styles = rcmail_mod_css_styles($styles, $container_id);
    
    $body = $body_pre . $styles . $body_post;
    $last_style_pos = $pos2;
    }


  // remove SCRIPT tags
  foreach (array('script', 'applet', 'object', 'embed', 'iframe') as $tag)
    {
    while (($pos = strpos($body_lc, '<'.$tag)) && ($pos2 = strpos($body_lc, '</'.$tag.'>', $pos)))
      {
      $pos2 += 8;
      $body = substr($body, 0, $pos) . substr($body, $pos2, strlen($body)-$pos2);
      $body_lc = strtolower($body);
      }
    }

  // replace event handlers on any object
  $body = preg_replace('/\s(on[a-z]+)=/im', ' __removed=', $body);  

  // resolve <base href>
  $base_reg = '/(<base.*href=["\']?)([hftps]{3,5}:\/{2}[^"\'\s]+)([^<]*>)/i';
  if (preg_match($base_reg, $body, $regs))
    {
    $base_url = $regs[2];
    $body = preg_replace('/(src|background|href)=(["\']?)([\.\/]+[^"\'\s]+)(\2|\s|>)/Uie', "'\\1=\"'.make_absolute_url('\\3', '$base_url').'\"'", $body);
    $body = preg_replace('/(url\s*\()(["\']?)([\.\/]+[^"\'\)\s]+)(\2)\)/Uie', "'\\1\''.make_absolute_url('\\3', '$base_url').'\')'", $body);
    $body = preg_replace($base_reg, '', $body);
    }

  // add comments arround html and other tags
  $out = preg_replace(array('/(<\/?html[^>]*>)/i',
                            '/(<\/?head[^>]*>)/i',
                            '/(<title[^>]*>.+<\/title>)/ui',
                            '/(<\/?meta[^>]*>)/i'),
                      '<!--\\1-->',
                      $body);
                      
  $out = preg_replace(array('/(<body[^>]*>)/i',
                            '/(<\/body>)/i'),
                      array('<div class="rcmBody">',
                            '</div>'),
                      $out);

  
  return $out;
  }



// replace all css definitions with #container [def]
function rcmail_mod_css_styles($source, $container_id)
  {
  $a_css_values = array();
  $last_pos = 0;
  
  // cut out all contents between { and }
  while (($pos = strpos($source, '{', $last_pos)) && ($pos2 = strpos($source, '}', $pos)))
    {
    $key = sizeof($a_css_values);
    $a_css_values[$key] = substr($source, $pos+1, $pos2-($pos+1));
    $source = substr($source, 0, $pos+1) . "<<str_replacement[$key]>>" . substr($source, $pos2, strlen($source)-$pos2);
    $last_pos = $pos+2;
    }
  
  $styles = preg_replace('/(^\s*|,\s*)([a-z0-9\._][a-z0-9\.\-_]*)/im', "\\1#$container_id \\2", $source);
  $styles = preg_replace('/<<str_replacement\[([0-9]+)\]>>/e', "\$a_css_values[\\1]", $styles);
  
  // replace body definition because we also stripped off the <body> tag
  $styles = preg_replace("/$container_id\s+body/i", "$container_id div.rcmBody", $styles);
  
  return $styles;
  }



// return first text part of a message
function rcmail_first_text_part($message_parts)
  {
  if (!is_array($message_parts))
    return FALSE;
    
  $html_part = NULL;
      
  // check all message parts
  foreach ($message_parts as $pid => $part)
    {
    $mimetype = strtolower($part->ctype_primary.'/'.$part->ctype_secondary);
    if ($mimetype=='text/plain')
      {
      $body = rcube_imap::mime_decode($part->body, $part->headers['content-transfer-encoding']);
      $body = rcube_imap::charset_decode($body, $part->ctype_parameters);
      return $body;
      }
    else if ($mimetype=='text/html')
      {
      $html_part = rcube_imap::mime_decode($part->body, $part->headers['content-transfer-encoding']);
      $html_part = rcube_imap::charset_decode($html_part, $part->ctype_parameters);
      }
    }
    

  // convert HTML to plain text
  if ($html_part)
    {    
    // remove special chars encoding
    $trans = array_flip(get_html_translation_table(HTML_ENTITIES));
    $html_part = strtr($html_part, $trans);

    // create instance of html2text class
    $txt = new html2text($html_part);
    return $txt->get_text();
    }

  return FALSE;
  }


// get source code of a specific message and cache it
function rcmail_message_source($uid)
  {
  global $IMAP, $DB;

  // get message ID if uid is given  
  $headers = $IMAP->get_headers($uid);
  $message_id = $headers->messageID;
  
  // get cached message source
  $msg_source = rcube_read_cache($message_id);

  // get message from server and cache it
  if (!$msg_source)
    {
    $msg_source = $IMAP->get_raw_body($uid);
    rcube_write_cache($message_id, $msg_source, TRUE);
    }

  return $msg_source;
  }


// decode address string and re-format it as HTML links
function rcmail_address_string($input, $max=NULL, $addicon=NULL)
  {
  global $IMAP, $PRINT_MODE, $CONFIG, $OUTPUT, $JS_OBJECT_NAME, $EMAIL_ADDRESS_PATTERN;
  
  $a_parts = $IMAP->decode_address_list($input);

  if (!sizeof($a_parts))
    return $input;

  $c = count($a_parts);
  $j = 0;
  $out = '';

  foreach ($a_parts as $part)
    {
    $j++;
    if ($PRINT_MODE)
      $out .= sprintf('%s &lt;%s&gt;', rep_specialchars_output($part['name']), $part['mailto']);
    else if (preg_match($EMAIL_ADDRESS_PATTERN, $part['mailto']))
      {
      $out .= sprintf('<a href="mailto:%s" onclick="return %s.command(\'compose\',\'%s\',this)" class="rcmContactAddress" title="%s">%s</a>',
                      $part['mailto'],
                      $JS_OBJECT_NAME,
                      $part['mailto'],
                      $part['mailto'],
                      rep_specialchars_output($part['name']));
                      
      if ($addicon)
        $out .= sprintf('&nbsp;<a href="#add" onclick="return %s.command(\'add-contact\',\'%s\',this)" title="%s"><img src="%s%s" alt="add" border="0" /></a>',
                        $JS_OBJECT_NAME,
                        urlencode($part['string']),
                        rcube_label('addtoaddressbook'),
                        $CONFIG['skin_path'],
                        $addicon);
      }
    else
      {
      if ($part['name'])
        $out .= rep_specialchars_output($part['name']);
      if ($part['mailto'])
        $out .= (strlen($out) ? ' ' : '') . sprintf('&lt;%s&gt;', $part['mailto']);
      }
      
    if ($c>$j)
      $out .= ','.($max ? '&nbsp;' : ' ');
        
    if ($max && $j==$max && $c>$j)
      {
      $out .= '...';
      break;
      }        
    }
    
  return $out;
  }


function rcmail_message_part_controls()
  {
  global $CONFIG, $IMAP, $MESSAGE;
  
  if (!is_array($MESSAGE) || !is_array($MESSAGE['parts']) || !($_GET['_uid'] && $_GET['_part']) || !$MESSAGE['parts'][$_GET['_part']])
    return '';
    
  $part = $MESSAGE['parts'][$_GET['_part']];
  
  $attrib_str = create_attrib_string($attrib, array('id', 'class', 'style', 'cellspacing', 'cellpadding', 'border', 'summary'));
  $out = '<table '. $attrib_str . ">\n";
  
  $filename = $part->d_parameters['filename'] ? $part->d_parameters['filename'] : $part->ctype_parameters['name'];
  $filesize = strlen($IMAP->mime_decode($part->body, $part->headers['content-transfer-encoding']));
  
  if ($filename)
    {
    $out .= sprintf('<tr><td class="title">%s</td><td>%s</td><td>[<a href="./?%s">%s</a>]</tr>'."\n",
                    rcube_label('filename'),
                    rep_specialchars_output($filename),
                    str_replace('_frame=', '_download=', $_SERVER['QUERY_STRING']),
                    rcube_label('download'));
    }
    
  if ($filesize)
    $out .= sprintf('<tr><td class="title">%s</td><td>%s</td></tr>'."\n",
                    rcube_label('filesize'),
                    show_bytes($filesize));
  
  $out .= "\n</table>";
  
  return $out;
  }



function rcmail_message_part_frame($attrib)
  {
  global $MESSAGE;
  
  $part = $MESSAGE['parts'][$_GET['_part']];
  $ctype_primary = strtolower($part->ctype_primary);

  $attrib['src'] = './?'.str_replace('_frame=', ($ctype_primary=='text' ? '_show=' : '_preload='), $_SERVER['QUERY_STRING']);

  $attrib_str = create_attrib_string($attrib, array('id', 'class', 'style', 'src', 'width', 'height'));
  $out = '<iframe '. $attrib_str . "></ifame>";
    
  return $out;
  }


// create temp dir for attachments
function rcmail_create_compose_tempdir()
  {
  global $CONFIG;
  
  if ($_SESSION['compose']['temp_dir'])
    return $_SESSION['compose']['temp_dir'];
  
  if (!empty($CONFIG['temp_dir']))
    $temp_dir = $CONFIG['temp_dir'].(!eregi('\/$', $CONFIG['temp_dir']) ? '/' : '').$_SESSION['compose']['id'];

  // create temp-dir for uploaded attachments
  if (!empty($CONFIG['temp_dir']) && is_writeable($CONFIG['temp_dir']))
    {
    mkdir($temp_dir);
    $_SESSION['compose']['temp_dir'] = $temp_dir;
    }

  return $_SESSION['compose']['temp_dir'];
  }


// clear message composing settings
function rcmail_compose_cleanup()
  {
  if (!isset($_SESSION['compose']))
    return;
  
  // remove attachment files from temp dir
  if (is_array($_SESSION['compose']['attachments']))
    foreach ($_SESSION['compose']['attachments'] as $attachment)
      unlink($attachment['path']);

  // kill temp dir
  if ($_SESSION['compose']['temp_dir'])
    rmdir($_SESSION['compose']['temp_dir']);
  
  unset($_SESSION['compose']);
  }
  
  
?>