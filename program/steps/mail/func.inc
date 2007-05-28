<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/func.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
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


$EMAIL_ADDRESS_PATTERN = '/([a-z0-9][a-z0-9\-\.\+\_]*@[a-z0-9]([a-z0-9\-][.]?)*[a-z0-9]\\.[a-z]{2,5})/i';

if (empty($_SESSION['mbox']))
  $_SESSION['mbox'] = $IMAP->get_mailbox_name();

// set imap properties and session vars
if ($mbox = get_input_value('_mbox', RCUBE_INPUT_GPC))
  {
  $IMAP->set_mailbox($mbox);
  $_SESSION['mbox'] = $mbox;
  }

if (!empty($_GET['_page']))
  {
  $IMAP->set_page((int)$_GET['_page']);
  $_SESSION['page'] = (int)$_GET['_page'];
  }

// set mailbox to INBOX if not set
if (empty($_SESSION['mbox']))
  $_SESSION['mbox'] = $IMAP->get_mailbox_name();

// set default sort col/order to session
if (!isset($_SESSION['sort_col']))
  $_SESSION['sort_col'] = $CONFIG['message_sort_col'];
if (!isset($_SESSION['sort_order']))
  $_SESSION['sort_order'] = $CONFIG['message_sort_order'];

// set message set for search result
if (!empty($_REQUEST['_search']) && isset($_SESSION['search'][$_REQUEST['_search']]))
  $IMAP->set_search_set($_SESSION['search'][$_REQUEST['_search']]);


// define url for getting message parts
if (strlen($_GET['_uid']))
  $GET_URL = rcmail_url('get', array('_mbox'=>$IMAP->get_mailbox_name(), '_uid'=>get_input_value('_uid', RCUBE_INPUT_GET)));


// set current mailbox in client environment
$OUTPUT->set_env('mailbox', $IMAP->get_mailbox_name());
$OUTPUT->set_env('quota', $IMAP->get_capability('quota'));

if ($CONFIG['trash_mbox'])
  $OUTPUT->set_env('trash_mailbox', $CONFIG['trash_mbox']);
if ($CONFIG['drafts_mbox'])
  $OUTPUT->set_env('drafts_mailbox', $CONFIG['drafts_mbox']);
if ($CONFIG['junk_mbox'])
  $OUTPUT->set_env('junk_mailbox', $CONFIG['junk_mbox']);

if (!$OUTPUT->ajax_call)
  rcube_add_label('checkingmail');


// return the mailboxlist in HTML
function rcmail_mailbox_list($attrib)
  {
  global $IMAP, $CONFIG, $OUTPUT, $COMM_PATH;
  static $s_added_script = FALSE;
  static $a_mailboxes;

  // add some labels to client
  rcube_add_label('purgefolderconfirm');
  rcube_add_label('deletemessagesconfirm');
  
// $mboxlist_start = rcube_timer();
  
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
  $mbox_name = $IMAP->get_mailbox_name();
  
  // for these mailboxes we have localized labels
  $special_mailboxes = array('inbox', 'sent', 'drafts', 'trash', 'junk');


  // build the folders tree
  if (empty($a_mailboxes))
    {
    // get mailbox list
    $a_folders = $IMAP->list_mailboxes();
    $delimiter = $IMAP->get_hierarchy_delimiter();
    $a_mailboxes = array();

// rcube_print_time($mboxlist_start, 'list_mailboxes()');

    foreach ($a_folders as $folder)
      rcmail_build_folder_tree($a_mailboxes, $folder, $delimiter);
    }

// var_dump($a_mailboxes);

  if ($type=='select')
    $out .= rcmail_render_folder_tree_select($a_mailboxes, $special_mailboxes, $mbox_name, $attrib['maxlength']);
   else
    $out .= rcmail_render_folder_tree_html($a_mailboxes, $special_mailboxes, $mbox_name, $attrib['maxlength']);

// rcube_print_time($mboxlist_start, 'render_folder_tree()');


  if ($type=='ul')
    $OUTPUT->add_gui_object('mailboxlist', $attrib['id']);

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
                                        'name' => rcube_charset_convert($currentFolder, 'UTF-7'),
                                        'folders' => array());
    }

  if (!empty($subFolders))
    rcmail_build_folder_tree($arrFolders[$currentFolder]['folders'], $subFolders, $delm, $path.$delm);
  }
  

// return html for a structured list <ul> for the mailbox tree
function rcmail_render_folder_tree_html(&$arrFolders, &$special, &$mbox_name, $maxlength, $nestLevel=0)
  {
  global $COMM_PATH, $IMAP, $CONFIG, $OUTPUT;

  $idx = 0;
  $out = '';
  foreach ($arrFolders as $key => $folder)
    {
    $zebra_class = ($nestLevel*$idx)%2 ? 'even' : 'odd';
    $title = '';

    $folder_lc = strtolower($folder['id']);
    if (in_array($folder_lc, $special))
      $foldername = rcube_label($folder_lc);
    else
      {
      $foldername = $folder['name'];

      // shorten the folder name to a given length
      if ($maxlength && $maxlength>1)
        {
        $fname = abbrevate_string($foldername, $maxlength);
        if ($fname != $foldername)
          $title = ' title="'.Q($foldername).'"';
        $foldername = $fname;
        }
      }

    // add unread message count display
    if ($unread_count = $IMAP->messagecount($folder['id'], 'RECENT', ($folder['id']==$mbox_name)))
      $foldername .= sprintf(' (%d)', $unread_count);

    // make folder name safe for ids and class names
    $folder_id = preg_replace('/[^A-Za-z0-9\-_]/', '', $folder['id']);
    $class_name = preg_replace('/[^a-z0-9\-_]/', '', $folder_lc);

    // set special class for Sent, Drafts, Trash and Junk
    if ($folder['id']==$CONFIG['sent_mbox'])
      $class_name = 'sent';
    else if ($folder['id']==$CONFIG['drafts_mbox'])
      $class_name = 'drafts';
    else if ($folder['id']==$CONFIG['trash_mbox'])
      $class_name = 'trash';
    else if ($folder['id']==$CONFIG['junk_mbox'])
      $class_name = 'junk';

    $js_name = htmlspecialchars(JQ($folder['id']));
    $out .= sprintf('<li id="rcmli%s" class="mailbox %s %s%s%s"><a href="%s"'.
                    ' onclick="return %s.command(\'list\',\'%s\',this)"'.
                    ' onmouseover="return %s.focus_folder(\'%s\')"' .
                    ' onmouseout="return %s.unfocus_folder(\'%s\')"' .
                    ' onmouseup="return %s.folder_mouse_up(\'%s\')"%s>%s</a>',
                    $folder_id,
                    $class_name,
                    $zebra_class,
                    $unread_count ? ' unread' : '',
                    $folder['id']==$mbox_name ? ' selected' : '',
                    Q(rcmail_url('', array('_mbox' => $folder['id']))),
                    JS_OBJECT_NAME,
                    $js_name,
                    JS_OBJECT_NAME,
                    $js_name,
                    JS_OBJECT_NAME,
                    $js_name,
                    JS_OBJECT_NAME,
                    $js_name,
                    $title,
                    Q($foldername));

    if (!empty($folder['folders']))
      $out .= "\n<ul>\n" . rcmail_render_folder_tree_html($folder['folders'], $special, $mbox_name, $maxlength, $nestLevel+1) . "</ul>\n";

    $out .= "</li>\n";
    $idx++;
    }

  return $out;
  }


// return html for a flat list <select> for the mailbox tree
function rcmail_render_folder_tree_select(&$arrFolders, &$special, &$mbox_name, $maxlength, $nestLevel=0)
  {
  global $IMAP, $OUTPUT;

  $idx = 0;
  $out = '';
  foreach ($arrFolders as $key=>$folder)
    {
    $folder_lc = strtolower($folder['id']);
    if (in_array($folder_lc, $special))
      $foldername = rcube_label($folder_lc);
    else
      {
      $foldername = $folder['name'];
      
      // shorten the folder name to a given length
      if ($maxlength && $maxlength>1)
        $foldername = abbrevate_string($foldername, $maxlength);
      }

    $out .= sprintf('<option value="%s">%s%s</option>'."\n",
                    htmlspecialchars($folder['id']),
                    str_repeat('&nbsp;', $nestLevel*4),
                    Q($foldername));

    if (!empty($folder['folders']))
      $out .= rcmail_render_folder_tree_select($folder['folders'], $special, $mbox_name, $maxlength, $nestLevel+1);

    $idx++;
    }

  return $out;
  }


// return the message list as HTML table
function rcmail_message_list($attrib)
  {
  global $IMAP, $CONFIG, $COMM_PATH, $OUTPUT;

  $skin_path = $CONFIG['skin_path'];
  $image_tag = '<img src="%s%s" alt="%s" border="0" />';

  // check to see if we have some settings for sorting
  $sort_col   = $_SESSION['sort_col'];
  $sort_order = $_SESSION['sort_order'];
  
  // add some labels to client
  rcube_add_label('from', 'to');

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
  $a_sort_cols = array('subject', 'date', 'from', 'to', 'size');

  $mbox = $IMAP->get_mailbox_name();
  
  // show 'to' instead of from in sent messages
  if (($mbox==$CONFIG['sent_mbox'] || $mbox==$CONFIG['drafts_mbox']) && ($f = array_search('from', $a_show_cols))
      && !array_search('to', $a_show_cols))
    $a_show_cols[$f] = 'to';
  
  // add col definition
  $out .= '<colgroup>';
  $out .= '<col class="icon" />';

  foreach ($a_show_cols as $col)
    $out .= sprintf('<col class="%s" />', $col);

  $out .= '<col class="icon" />';
  $out .= "</colgroup>\n";

  // add table title
  $out .= "<thead><tr>\n<td class=\"icon\">&nbsp;</td>\n";

  $javascript = '';
  foreach ($a_show_cols as $col)
    {
    // get column name
    $col_name = Q(rcube_label($col));

    // make sort links
    $sort = '';
    if ($IMAP->get_capability('sort') && in_array($col, $a_sort_cols))
      {
      // have buttons configured
      if (!empty($attrib['sortdescbutton']) || !empty($attrib['sortascbutton']))
        {
        $sort = '&nbsp;&nbsp;';

        // asc link
        if (!empty($attrib['sortascbutton']))
          {
          $sort .= $OUTPUT->button(array(
            'command' => 'sort',
            'prop' => $col.'_ASC',
            'image' => $attrib['sortascbutton'],
            'align' => 'absmiddle',
            'title' => 'sortasc'));
          }       
        
        // desc link
        if (!empty($attrib['sortdescbutton']))
          {
          $sort .= $OUTPUT->button(array(
            'command' => 'sort',
            'prop' => $col.'_DESC',
            'image' => $attrib['sortdescbutton'],
            'align' => 'absmiddle',
            'title' => 'sortdesc'));
          }
        }
      // just add a link tag to the header
      else
        {
        $col_name = sprintf(
          '<a href="./#sort" onclick="return %s.command(\'sort\',\'%s\',this)" title="%s">%s</a>',
          JS_OBJECT_NAME,
          $col,
          rcube_label('sortby'),
          $col_name);
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
    $out .= sprintf('<tr><td colspan="%d">%s</td></tr>',
                    sizeof($a_show_cols)+2,
                    Q(rcube_label('nomessagesfound')));
    }


  $a_js_message_arr = array();

  // create row for each message
  foreach ($a_headers as $i => $header)  //while (list($i, $header) = each($a_headers))
    {
    $message_icon = $attach_icon = '';
    $js_row_arr = array();
    $zebra_class = $i%2 ? 'even' : 'odd';

    // set messag attributes to javascript array
    if ($header->deleted)
      $js_row_arr['deleted'] = true;
    if (!$header->seen)
      $js_row_arr['unread'] = true;
    if ($header->answered)
      $js_row_arr['replied'] = true;
    // set message icon  
    if ($attrib['deletedicon'] && $header->deleted)
      $message_icon = $attrib['deletedicon'];
    else if ($attrib['unreadicon'] && !$header->seen)
      $message_icon = $attrib['unreadicon'];
    else if ($attrib['repliedicon'] && $header->answered)
      $message_icon = $attrib['repliedicon'];
    else if ($attrib['messageicon'])
      $message_icon = $attrib['messageicon'];
    
    // set attachment icon
    if ($attrib['attachmenticon'] && preg_match("/multipart\/[mr]/i", $header->ctype))
      $attach_icon = $attrib['attachmenticon'];
        
    $out .= sprintf('<tr id="rcmrow%d" class="message%s%s %s">'."\n",
                    $header->uid,
                    $header->seen ? '' : ' unread',
                    $header->deleted ? ' deleted' : '',
                    $zebra_class);    
    
    $out .= sprintf("<td class=\"icon\">%s</td>\n", $message_icon ? sprintf($image_tag, $skin_path, $message_icon, '') : '');
    
    // format each col
    foreach ($a_show_cols as $col)
      {
      if ($col=='from' || $col=='to')
        $cont = Q(rcmail_address_string($header->$col, 3, $attrib['addicon']), 'show');
      else if ($col=='subject')
        {
        $action = $mbox==$CONFIG['drafts_mbox'] ? 'compose' : 'show';
        $uid_param = $mbox==$CONFIG['drafts_mbox'] ? '_draf_uid' : '_uid';
        $cont = Q(rcube_imap::decode_mime_string($header->$col, $header->charset));
        if (empty($cont)) $cont = Q(rcube_label('nosubject'));
        $cont = sprintf('<a href="%s" onclick="return rcube_event.cancel(event)">%s</a>', Q(rcmail_url($action, array($uid_param=>$header->uid, '_mbox'=>$mbox))), $cont);
        }
      else if ($col=='size')
        $cont = show_bytes($header->$col);
      else if ($col=='date')
        $cont = format_date($header->date);
      else
        $cont = Q($header->$col);
        
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
  $OUTPUT->add_gui_object('mailcontframe', 'mailcontframe');
  $OUTPUT->add_gui_object('messagelist', $attrib['id']);
  $OUTPUT->set_env('messagecount', $message_count);
  $OUTPUT->set_env('current_page', $IMAP->list_page);
  $OUTPUT->set_env('pagecount', ceil($message_count/$IMAP->page_size));
  $OUTPUT->set_env('sort_col', $sort_col);
  $OUTPUT->set_env('sort_order', $sort_order);
  
  if ($attrib['messageicon'])
    $OUTPUT->set_env('messageicon', $skin_path . $attrib['messageicon']);
  if ($attrib['deletedicon'])
    $OUTPUT->set_env('deletedicon', $skin_path . $attrib['deletedicon']);
  if ($attrib['unreadicon'])
    $OUTPUT->set_env('unreadicon', $skin_path . $attrib['unreadicon']);
  if ($attrib['repliedicon'])
    $OUTPUT->set_env('repliedicon', $skin_path . $attrib['repliedicon']);
  if ($attrib['attachmenticon'])
    $OUTPUT->set_env('attachmenticon', $skin_path . $attrib['attachmenticon']);
  
  $OUTPUT->set_env('messages', $a_js_message_arr);
  
  $OUTPUT->include_script('list.js');
  
  return $out;
  }


// return javascript commands to add rows to the message list
function rcmail_js_message_list($a_headers, $insert_top=FALSE)
  {
  global $CONFIG, $IMAP, $OUTPUT;

  $a_show_cols = is_array($CONFIG['list_cols']) ? $CONFIG['list_cols'] : array('subject');
  $mbox = $IMAP->get_mailbox_name();

  // show 'to' instead of from in sent messages
  if (($mbox == $CONFIG['sent_mbox'] || $mbox == $CONFIG['drafts_mbox'])
      && (($f = array_search('from', $a_show_cols)) !== false) && array_search('to', $a_show_cols) === false)
    $a_show_cols[$f] = 'to';

  $OUTPUT->command('set_message_coltypes', $a_show_cols);

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
        $cont = Q(rcmail_address_string($header->$col, 3), 'show');
      else if ($col=='subject')
        {
        $action = $mbox==$CONFIG['drafts_mbox'] ? 'compose' : 'show';
        $uid_param = $mbox==$CONFIG['drafts_mbox'] ? '_draf_uid' : '_uid';
        $cont = Q(rcube_imap::decode_mime_string($header->$col, $header->charset));
        if (!$cont) $cont = Q(rcube_label('nosubject'));
        $cont = sprintf('<a href="%s" onclick="return rcube_event.cancel(event)">%s</a>', Q(rcmail_url($action, array($uid_param=>$header->uid, '_mbox'=>$mbox))), $cont);
        }
      else if ($col=='size')
        $cont = show_bytes($header->$col);
      else if ($col=='date')
        $cont = format_date($header->date);
      else
        $cont = Q($header->$col);
          
      $a_msg_cols[$col] = $cont;
      }

    $a_msg_flags['deleted'] = $header->deleted ? 1 : 0;
    $a_msg_flags['unread'] = $header->seen ? 0 : 1;
    $a_msg_flags['replied'] = $header->answered ? 1 : 0;
    $OUTPUT->command('add_message_row',
      $header->uid,
      $a_msg_cols,
      $a_msg_flags,
      preg_match("/multipart\/m/i", $header->ctype),
      $insert_top);
    }
  }


// return an HTML iframe for loading mail content
function rcmail_messagecontent_frame($attrib)
  {
  global $OUTPUT;
  
  if (empty($attrib['id']))
    $attrib['id'] = 'rcmailcontentwindow';

  // allow the following attributes to be added to the <iframe> tag
  $attrib_str = create_attrib_string($attrib, array('id', 'class', 'style', 'src', 'width', 'height', 'frameborder'));
  $framename = $attrib['id'];

  $out = sprintf('<iframe name="%s"%s></iframe>'."\n",
         $framename,
         $attrib_str);

  $OUTPUT->set_env('contentframe', $framename);
  $OUTPUT->set_env('blankpage', $attrib['src'] ? $OUTPUT->abs_url($attrib['src']) : 'program/blank.gif');

  return $out;
  }


function rcmail_messagecount_display($attrib)
  {
  global $IMAP, $OUTPUT;
  
  if (!$attrib['id'])
    $attrib['id'] = 'rcmcountdisplay';

  $OUTPUT->add_gui_object('countdisplay', $attrib['id']);

  // allow the following attributes to be added to the <span> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id'));

  
  $out = '<span' . $attrib_str . '>';
  $out .= rcmail_get_messagecount_text();
  $out .= '</span>';
  return $out;
  }


function rcmail_quota_display($attrib)
  {
  global $OUTPUT, $COMM_PATH;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmquotadisplay';

  $OUTPUT->add_gui_object('quotadisplay', $attrib['id']);

  // allow the following attributes to be added to the <span> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id'));

  $out = '<span' . $attrib_str . '>';
  $out .= rcmail_quota_content($attrib['display']);
  $out .= '</span>';
  return $out;
  }


function rcmail_quota_content($display)
  {
  global $IMAP, $COMM_PATH;

  if (!$IMAP->get_capability('QUOTA'))
    $quota_text = rcube_label('unknown');
  else if ($quota = $IMAP->get_quota())
    {
    $quota_text = sprintf("%s / %s (%.0f%%)",
                          show_bytes($quota["used"] * 1024),
                          show_bytes($quota["total"] * 1024),
                          $quota["percent"]);

    // show quota as image (by Brett Patterson)
    if ($display == 'image' && function_exists('imagegif'))
      {
      $attrib = array('width' => 100, 'height' => 14);
      $quota_text = sprintf('<img src="./bin/quotaimg.php?u=%s&amp;q=%d&amp;w=%d&amp;h=%d" width="%d" height="%d" alt="%s" title="%s / %s" />',
                            $quota['used'], $quota['total'],
                            $attrib['width'], $attrib['height'],
                            $attrib['width'], $attrib['height'],
                            $quota_text,
                            show_bytes($quota["used"] * 1024),
                            show_bytes($quota["total"] * 1024));
      }
    }
  else
    $quota_text = rcube_label('unlimited');

  return $quota_text;
  }


function rcmail_get_messagecount_text($count=NULL, $page=NULL)
  {
  global $IMAP, $MESSAGE;
  
  if (isset($MESSAGE['index']))
    {
    return rcube_label(array('name' => 'messagenrof',
                             'vars' => array('nr'  => $MESSAGE['index']+1,
                                             'count' => $count!==NULL ? $count : $IMAP->messagecount())));
    }

  if ($page===NULL)
    $page = $IMAP->list_page;
    
  $start_msg = ($page-1) * $IMAP->page_size + 1;
  $max = $count!==NULL ? $count : $IMAP->messagecount();

  if ($max==0)
    $out = rcube_label('mailboxempty');
  else
    $out = rcube_label(array('name' => 'messagesfromto',
                              'vars' => array('from'  => $start_msg,
                                              'to'    => min($max, $start_msg + $IMAP->page_size - 1),
                                              'count' => $max)));

  return Q($out);
  }


function rcmail_print_body($part, $safe=FALSE, $plain=FALSE)
  {
  global $IMAP, $REMOTE_OBJECTS;
  
  $body = is_array($part->replaces) ? strtr($part->body, $part->replaces) : $part->body;

  // convert html to text/plain
  if ($part->ctype_secondary=='html' && $plain)
    {
    $txt = new html2text($body, false, true);
    $body = $txt->get_text();
    $part->ctype_secondary = 'plain';
    }
    
  // text/html
  if ($part->ctype_secondary=='html')
    {
    // remove charset specification in HTML message
    $body = preg_replace('/charset=[a-z0-9\-]+/i', '', $body);

    if (!$safe)  // remove remote images and scripts
      {
      $remote_patterns = array('/<img\s+(.*)src=(["\']?)([hftps]{3,5}:\/{2}[^"\'\s]+)(\2|\s|>)/Ui',
                               '/(src|background)=(["\']?)([hftps]{3,5}:\/{2}[^"\'\s]+)(\2|\s|>)/Ui',
                               '/(<base.*href=["\']?)([hftps]{3,5}:\/{2}[^"\'\s]+)([^<]*>)/i',
                               '/(<link.*href=["\']?)([hftps]{3,5}:\/{2}[^"\'\s]+)([^<]*>)/i',
                               '/url\s*\(["\']?([hftps]{3,5}:\/{2}[^"\'\s]+)["\']?\)/i',
                               '/url\s*\(["\']?([\.\/]+[^"\'\s]+)["\']?\)/i',
                               '/<script.+<\/script>/Umis');

      $remote_replaces = array('<img \\1src=\\2./program/blocked.gif\\4',
                               '',
                               '',
                               '',
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

    return Q($body, 'show', FALSE);
    }

  // text/enriched
  if ($part->ctype_secondary=='enriched')
    {
    return Q(enriched_to_html($body), 'show');
    }
  else
    {
    // make links and email-addresses clickable
    $convert_patterns = $convert_replaces = $replace_strings = array();
    
    $url_chars = 'a-z0-9_\-\+\*\$\/&%=@#:;';
    $url_chars_within = '\?\.~,!';

    $convert_patterns[] = "/([\w]+):\/\/([a-z0-9\-\.]+[a-z]{2,4}([$url_chars$url_chars_within]*[$url_chars])?)/ie";
    $convert_replaces[] = "rcmail_str_replacement('<a href=\"\\1://\\2\" target=\"_blank\">\\1://\\2</a>', \$replace_strings)";

    $convert_patterns[] = "/([^\/:]|\s)(www\.)([a-z0-9\-]{2,}[a-z]{2,4}([$url_chars$url_chars_within]*[$url_chars])?)/ie";
    $convert_replaces[] = "rcmail_str_replacement('\\1<a href=\"http://\\2\\3\" target=\"_blank\">\\2\\3</a>', \$replace_strings)";
    
    $convert_patterns[] = '/([a-z0-9][a-z0-9\-\.\+\_]*@[a-z0-9]([a-z0-9\-][.]?)*[a-z0-9]\\.[a-z]{2,5})/ie';
    $convert_replaces[] = "rcmail_str_replacement('<a href=\"mailto:\\1\" onclick=\"return ".JS_OBJECT_NAME.".command(\'compose\',\'\\1\',this)\">\\1</a>', \$replace_strings)";
    
    if ($part->ctype_parameters['format'] != 'flowed')
      $body = wordwrap(trim($body), 80);

    $body = preg_replace($convert_patterns, $convert_replaces, $body);

    // split body into single lines
    $a_lines = preg_split('/\r?\n/', $body);
    $quote_level = 0;

    // colorize quoted parts
    for($n=0; $n<sizeof($a_lines); $n++)
      {
      $line = $a_lines[$n];
      $quotation = '';
      $q = 0;
      
      if (preg_match('/^(>+\s*)/', $line, $regs))
        {
        $q = strlen(preg_replace('/\s/', '', $regs[1]));
        $line = substr($line, strlen($regs[1]));

        if ($q > $quote_level)
          $quotation = str_repeat('<blockquote>', $q - $quote_level);
        else if ($q < $quote_level)
          $quotation = str_repeat("</blockquote>", $quote_level - $q);
        }
      else if ($quote_level > 0)
        $quotation = str_repeat("</blockquote>", $quote_level);

      $quote_level = $q;
      $a_lines[$n] = $quotation . Q($line, 'replace', FALSE);
      }

    // insert the links for urls and mailtos
    $body = preg_replace("/##string_replacement\{([0-9]+)\}##/e", "\$replace_strings[\\1]", join("\n", $a_lines));
    
    return "<div class=\"pre\">".$body."\n</div>";
    }
  }



// add a string to the replacement array and return a replacement string
function rcmail_str_replacement($str, &$rep)
  {
  static $count = 0;
  $rep[$count] = stripslashes($str);
  return "##string_replacement{".($count++)."}##";
  }


function rcmail_parse_message(&$structure, $arg=array(), $recursive=FALSE)
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
    {
    $c = new stdClass;
    $c->type = 'headers';
    $c->headers = &$structure->headers;
    $a_return_parts[] = $c;
    }

  // print body if message doesn't have multiple parts
  if ($message_ctype_primary=='text')
    {
    $structure->type = 'content';
    $a_return_parts[] = &$structure;
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
      $print_part = &$structure->parts[$html_part];
    else if ($enriched_part!==NULL)
      $print_part = &$structure->parts[$enriched_part];
    else if ($plain_part!==NULL)
      $print_part = &$structure->parts[$plain_part];

    // show message body
    if (is_object($print_part))
      {
      $print_part->type = 'content';
      $a_return_parts[] = $print_part;
      }
    // show plaintext warning
    else if ($html_part!==NULL)
      {
      $c = new stdClass;
      $c->type = 'content';
      $c->body = rcube_label('htmlmessage');
      $c->ctype_primary = 'text';
      $c->ctype_secondary = 'plain';
      
      $a_return_parts[] = $c;
      }
                                
    // add html part as attachment
    if ($html_part!==NULL && $structure->parts[$html_part]!==$print_part)
      {
      $html_part = &$structure->parts[$html_part];
      $html_part->filename = rcube_label('htmlmessage');
      $html_part->mimetype = 'text/html';
      
      $a_attachments[] = $html_part;
      }
    }

  // message contains multiple parts
  else if (is_array($structure->parts) && !empty($structure->parts))
    {
    for ($i=0; $i<count($structure->parts); $i++)
      {
      $mail_part = &$structure->parts[$i];
      $primary_type = strtolower($mail_part->ctype_primary);
      $secondary_type = strtolower($mail_part->ctype_secondary);

      // multipart/alternative
      if ($primary_type=='multipart')
        {
        list($parts, $attachmnts) = rcmail_parse_message($mail_part, $arg, TRUE);

        $a_return_parts = array_merge($a_return_parts, $parts);
        $a_attachments = array_merge($a_attachments, $attachmnts);
        }

      // part text/[plain|html] OR message/delivery-status
      else if (($primary_type=='text' && ($secondary_type=='plain' || $secondary_type=='html') && $mail_part->disposition!='attachment') ||
               ($primary_type=='message' && $secondary_type=='delivery-status'))
        {
        $mail_part->type = 'content';
        $a_return_parts[] = $mail_part;
        }

      // part message/*
      else if ($primary_type=='message')
        {
        list($parts, $attachmnts) = rcmail_parse_message($mail_part, $arg, TRUE);
          
        $a_return_parts = array_merge($a_return_parts, $parts);
        $a_attachments = array_merge($a_attachments, $attachmnts);
        }

      // part is file/attachment
      else if ($mail_part->disposition=='attachment' || $mail_part->disposition=='inline' || $mail_part->headers['content-id'] ||
               (empty($mail_part->disposition) && $mail_part->filename))
        {
        // skip apple resource forks
        if ($message_ctype_secondary=='appledouble' && $secondary_type=='applefile')
          continue;

        // part belongs to a related message
        if ($message_ctype_secondary=='related' && $mail_part->headers['content-id'])
          {
          $mail_part->content_id = preg_replace(array('/^</', '/>$/'), '', $mail_part->headers['content-id']);
          $sa_inline_objects[] = $mail_part;
          }
        // is regular attachment
        else
          {
          if (!$mail_part->filename)
            $mail_part->filename = 'file_'.$mail_part->mime_id;
          $a_attachments[] = $mail_part;
          }
        }
      }

    // if this was a related part try to resolve references
    if ($message_ctype_secondary=='related' && sizeof($sa_inline_objects))
      {
      $a_replaces = array();
        
      foreach ($sa_inline_objects as $inline_object)
        $a_replaces['cid:'.$inline_object->content_id] = htmlspecialchars(sprintf($get_url, $inline_object->mime_id));
      
      // add replace array to each content part
      // (will be applied later when part body is available)
      for ($i=0; $i<count($a_return_parts); $i++)
        {
        if ($a_return_parts[$i]->type=='content')
          $a_return_parts[$i]->replaces = $a_replaces;
        }
      }
    }

  // message is single part non-text
  else if ($structure->filename)
    $a_attachments[] = $structure;

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
  $standard_headers = array('subject', 'from', 'organization', 'to', 'cc', 'bcc', 'reply-to', 'date');
  
  foreach ($standard_headers as $hkey)
    {
    if (!$headers[$hkey])
      continue;

    if ($hkey=='date' && !empty($headers[$hkey]))
      $header_value = format_date(strtotime($headers[$hkey]));
    else if (in_array($hkey, array('from', 'to', 'cc', 'bcc', 'reply-to')))
      $header_value = Q(rcmail_address_string($headers[$hkey], NULL, $attrib['addicon']), 'show');
    else
      $header_value = Q(rcube_imap::decode_mime_string($headers[$hkey], $headers['charset']));

    $out .= "\n<tr>\n";
    $out .= '<td class="header-title">'.Q(rcube_label($hkey)).":&nbsp;</td>\n";
    $out .= '<td class="'.$hkey.'" width="90%">'.$header_value."</td>\n</tr>";
    $header_count++;
    }

  $out .= "\n</table>\n\n";

  return $header_count ? $out : '';  
  }



function rcmail_message_body($attrib)
  {
  global $CONFIG, $OUTPUT, $MESSAGE, $IMAP, $GET_URL, $REMOTE_OBJECTS;
  
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
    $p = new stdClass;
    $p->type = 'content';
    $p->ctype_primary = 'text';
    $p->ctype_secondary = 'plain';
    $p->body = rcube_label('encryptedmessage');
    $MESSAGE['parts'][0] = $p;
    }
  
  if ($MESSAGE['parts'])
    {
    foreach ($MESSAGE['parts'] as $i => $part)
      {
      if ($part->type=='headers')
        $out .= rcmail_message_headers(sizeof($header_attrib) ? $header_attrib : NULL, $part->headers);
      else if ($part->type=='content')
        {
        if (empty($part->ctype_parameters) || empty($part->ctype_parameters['charset']))
          $part->ctype_parameters['charset'] = $MESSAGE['headers']->charset;

        // fetch part if not available
        if (!isset($part->body))
          $part->body = $IMAP->get_message_part($MESSAGE['UID'], $part->mime_id, $part);

        $body = rcmail_print_body($part, $safe_mode, !$CONFIG['prefer_html']);
        $out .= '<div class="message-part">';
        
        if ($part->ctype_secondary != 'plain')
          $out .= rcmail_sanitize_html($body, $attrib['id']);
        else
          $out .= $body;

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
      if (strpos($attach_prop->mimetype, 'image/')===0)
        $out .= sprintf("\n<hr />\n<p align=\"center\"><img src=\"%s&amp;_part=%s\" alt=\"%s\" title=\"%s\" /></p>\n",
                        htmlspecialchars($GET_URL), $attach_prop->mime_id,
                        $attach_prop->filename,
                        $attach_prop->filename);
      }
    }
  
  // tell client that there are blocked remote objects
  if ($REMOTE_OBJECTS && !$safe_mode)
    $OUTPUT->set_env('blockedobjects', true);

  $out .= "\n</div>";
  return $out;
  }



// modify a HTML message that it can be displayed inside a HTML page
function rcmail_sanitize_html($body, $container_id)
  {
  // remove any null-byte characters before parsing
  $body = preg_replace('/\x00/', '', $body);
  
  $last_style_pos = 0;
  $body_lc = strtolower($body);
  
  // find STYLE tags
  while (($pos = strpos($body_lc, '<style', $last_style_pos)) && ($pos2 = strpos($body_lc, '</style>', $pos)))
    {
    $pos = strpos($body_lc, '>', $pos)+1;

    // replace all css definitions with #container [def]
    $styles = rcmail_mod_css_styles(substr($body, $pos, $pos2-$pos), $container_id);

    $body = substr($body, 0, $pos) . $styles . substr($body, $pos2);
    $body_lc = strtolower($body);
    $last_style_pos = $pos2;
    }


  // remove SCRIPT tags
  foreach (array('script', 'applet', 'object', 'embed', 'iframe') as $tag)
    {
    while (($pos = strpos($body_lc, '<'.$tag)) && ($pos2 = strpos($body_lc, '</'.$tag.'>', $pos)))
      {
      $pos2 += strlen('</'.$tag.'>');
      $body = substr($body, 0, $pos) . substr($body, $pos2, strlen($body)-$pos2);
      $body_lc = strtolower($body);
      }
    }

  // replace event handlers on any object
  while ($body != $prev_body)
    {
    $prev_body = $body;
    $body = preg_replace('/(<[^!][^>]*\s)(on[^=>]+)=([^>]+>)/im', '$1__removed=$3', $body);
    $body = preg_replace('/(<[^!][^>]*\shref=["\']?)(javascript:)([^>]*?>)/im', '$1null:$3', $body);
    }

  // resolve <base href>
  $base_reg = '/(<base.*href=["\']?)([hftps]{3,5}:\/{2}[^"\'\s]+)([^<]*>)/i';
  if (preg_match($base_reg, $body, $regs))
    {
    $base_url = $regs[2];
    $body = preg_replace('/(src|background|href)=(["\']?)([\.\/]+[^"\'\s]+)(\2|\s|>)/Uie', "'\\1=\"'.make_absolute_url('\\3', '$base_url').'\"'", $body);
    $body = preg_replace('/(url\s*\()(["\']?)([\.\/]+[^"\'\)\s]+)(\2)\)/Uie', "'\\1\''.make_absolute_url('\\3', '$base_url').'\')'", $body);
    $body = preg_replace($base_reg, '', $body);
    }
    
  // modify HTML links to open a new window if clicked
  $body = preg_replace('/<a\s+([^>]+)>/Uie', "rcmail_alter_html_link('\\1');", $body);

  // add comments arround html and other tags
  $out = preg_replace(array(
      '/(<!DOCTYPE.+)/i',
      '/(<\/?html[^>]*>)/i',
      '/(<\/?head[^>]*>)/i',
      '/(<title[^>]*>.*<\/title>)/Ui',
      '/(<\/?meta[^>]*>)/i'),
    '<!--\\1-->',
    $body);

  $out = preg_replace(array('/<body([^>]*)>/i',
                            '/<\/body>/i'),
                      array('<div class="rcmBody"\\1>',
                            '</div>'),
                      $out);

  // quote <? of php and xml files that are specified as text/html
  $out = preg_replace(array('/<\?/', '/\?>/'), array('&lt;?', '?&gt;'), $out);

  return $out;
  }


// parse link attributes and set correct target
function rcmail_alter_html_link($in)
  {
  $in = preg_replace('/=([^("|\'|\s)]+)(\s|$)/', '="\1"', $in);
  $attrib = parse_attrib_string($in);

  if (stristr((string)$attrib['href'], 'mailto:'))
    $attrib['onclick'] = sprintf("return %s.command('compose','%s',this)",
                                 JS_OBJECT_NAME,
                                 JQ(substr($attrib['href'], 7)));
  else if (!empty($attrib['href']) && $attrib['href']{0}!='#')
    $attrib['target'] = '_blank';
  
  return '<a' . create_attrib_string($attrib, array('href', 'name', 'target', 'onclick', 'id', 'class', 'style', 'title')) . '>';
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

  // remove html commends and add #container to each tag selector.
  // also replace body definition because we also stripped off the <body> tag
  $styles = preg_replace(array('/(^\s*<!--)|(-->\s*$)/', '/(^\s*|,\s*|\}\s*)([a-z0-9\._][a-z0-9\.\-_]*)/im', '/<<str_replacement\[([0-9]+)\]>>/e', "/$container_id\s+body/i"),
                         array('', "\\1#$container_id \\2", "\$a_css_values[\\1]", "$container_id div.rcmBody"),
                         $source);

  return $styles;
  }


function rcmail_has_html_part($message_parts)
{
   if (!is_array($message_parts))
      return FALSE;

   // check all message parts
   foreach ($message_parts as $pid => $part)
   {
      $mimetype = strtolower($part->ctype_primary.'/'.$part->ctype_secondary);
      if ($mimetype=='text/html')
      {
         return TRUE;
      }
   }
    
   return FALSE;
}

// return first HTML part of a message
function rcmail_first_html_part($message_struct)
  {
  global $IMAP;

  if (!is_array($message_struct['parts']))
    return FALSE;
    
  $html_part = NULL;

  // check all message parts
  foreach ($message_struct['parts'] as $pid => $part)
    {
    $mimetype = strtolower($part->ctype_primary.'/'.$part->ctype_secondary);
    if ($mimetype=='text/html')
      {
      $html_part = $IMAP->get_message_part($message_struct['UID'], $pid, $part);
      }
    }

  if ($html_part)
    {
    // remove special chars encoding
    //$trans = array_flip(get_html_translation_table(HTML_ENTITIES));
    //$html_part = strtr($html_part, $trans);

    return $html_part;
    }

  return FALSE;
}


// return first text part of a message
function rcmail_first_text_part($message_struct)
  {
  global $IMAP;

  if (empty($message_struct['parts']))
    return $message_struct['UID'] ? $IMAP->get_body($message_struct['UID']) : false;

  // check all message parts
  foreach ($message_struct['parts'] as $pid => $part)
    {
    $mimetype = strtolower($part->ctype_primary.'/'.$part->ctype_secondary);

    if ($mimetype=='text/plain')
      return $IMAP->get_message_part($message_struct['UID'], $pid, $part);

    else if ($mimetype=='text/html')
      {
      $html_part = $IMAP->get_message_part($message_struct['UID'], $pid, $part);
      
      // remove special chars encoding
      $trans = array_flip(get_html_translation_table(HTML_ENTITIES));
      $html_part = strtr($html_part, $trans);

      // create instance of html2text class
      $txt = new html2text($html_part);
      return $txt->get_text();
      }
    }

  return FALSE;
  }


// decode address string and re-format it as HTML links
function rcmail_address_string($input, $max=NULL, $addicon=NULL)
  {
  global $IMAP, $PRINT_MODE, $CONFIG, $OUTPUT, $EMAIL_ADDRESS_PATTERN;
  
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
      $out .= sprintf('%s &lt;%s&gt;', Q($part['name']), $part['mailto']);
    else if (preg_match($EMAIL_ADDRESS_PATTERN, $part['mailto']))
      {
      $out .= sprintf('<a href="mailto:%s" onclick="return %s.command(\'compose\',\'%s\',this)" class="rcmContactAddress" title="%s">%s</a>',
                      Q($part['mailto']),
                      JS_OBJECT_NAME,
                      JQ($part['mailto']),
                      Q($part['mailto']),
                      Q($part['name']));
                      
      if ($addicon)
        $out .= sprintf('&nbsp;<a href="#add" onclick="return %s.command(\'add-contact\',\'%s\',this)" title="%s"><img src="%s%s" alt="add" border="0" /></a>',
                        JS_OBJECT_NAME,
                        urlencode($part['string']),
                        rcube_label('addtoaddressbook'),
                        $CONFIG['skin_path'],
                        $addicon);
      }
    else
      {
      if ($part['name'])
        $out .= Q($part['name']);
      if ($part['mailto'])
        $out .= (strlen($out) ? ' ' : '') . sprintf('&lt;%s&gt;', Q($part['mailto']));
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
    
  $part = &$MESSAGE['parts'][$_GET['_part']];
  
  $attrib_str = create_attrib_string($attrib, array('id', 'class', 'style', 'cellspacing', 'cellpadding', 'border', 'summary'));
  $out = '<table '. $attrib_str . ">\n";
  
  if ($filename)
    {
    $out .= sprintf('<tr><td class="title">%s</td><td>%s</td><td>[<a href="./?%s">%s</a>]</tr>'."\n",
                    Q(rcube_label('filename')),
                    Q($part->filename),
                    str_replace('_frame=', '_download=', $_SERVER['QUERY_STRING']),
                    Q(rcube_label('download')));
    }
    
  if ($part->size)
    $out .= sprintf('<tr><td class="title">%s</td><td>%s</td></tr>'."\n",
                    Q(rcube_label('filesize')),
                    show_bytes($part->size));
  
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
  $out = '<iframe '. $attrib_str . "></iframe>";
    
  return $out;
  }


// clear message composing settings
function rcmail_compose_cleanup()
  {
  if (!isset($_SESSION['compose']))
    return;

  // remove attachment files from temp dir
  if (is_array($_SESSION['compose']['attachments']))
    foreach ($_SESSION['compose']['attachments'] as $attachment)
      @unlink($attachment['path']);
  
  unset($_SESSION['compose']);
  }


// register UI objects
$OUTPUT->add_handlers(array(
  'mailboxlist' => 'rcmail_mailbox_list',
  'messages' => 'rcmail_message_list',
  'messagecountdisplay' => 'rcmail_messagecount_display',
  'quotadisplay' => 'rcmail_quota_display',
  'messageheaders' => 'rcmail_message_headers',
  'messagebody' => 'rcmail_message_body',
  'messagecontentframe' => 'rcmail_messagecontent_frame',
  'messagepartframe' => 'rcmail_message_part_frame',
  'messagepartcontrols' => 'rcmail_message_part_controls',
  'searchform' => 'rcmail_search_form'
));

?>