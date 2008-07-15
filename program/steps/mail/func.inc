<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/func.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
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

require_once('lib/enriched.inc');
require_once('include/rcube_smtp.inc');


$EMAIL_ADDRESS_PATTERN = '/([a-z0-9][a-z0-9\-\.\+\_]*@[a-z0-9]([a-z0-9\-][.]?)*[a-z0-9]\\.[a-z]{2,5})/i';

if (empty($_SESSION['mbox']))
  $_SESSION['mbox'] = $IMAP->get_mailbox_name();

// set imap properties and session vars
if ($mbox = get_input_value('_mbox', RCUBE_INPUT_GPC))
  $IMAP->set_mailbox(($_SESSION['mbox'] = $mbox));

if (!empty($_GET['_page']))
  $IMAP->set_page(($_SESSION['page'] = intval($_GET['_page'])));

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
  {
  $IMAP->set_search_set($_SESSION['search'][$_REQUEST['_search']]);
  $OUTPUT->set_env('search_request', $_REQUEST['_search']);
  $OUTPUT->set_env('search_text', $_SESSION['last_text_search']);
  }


// set current mailbox in client environment
$OUTPUT->set_env('mailbox', $IMAP->get_mailbox_name());
$OUTPUT->set_env('quota', $IMAP->get_capability('quota'));
$OUTPUT->set_env('delimiter', $IMAP->get_hierarchy_delimiter());

if ($CONFIG['trash_mbox'])
  $OUTPUT->set_env('trash_mailbox', $CONFIG['trash_mbox']);
if ($CONFIG['drafts_mbox'])
  $OUTPUT->set_env('drafts_mailbox', $CONFIG['drafts_mbox']);
if ($CONFIG['junk_mbox'])
  $OUTPUT->set_env('junk_mailbox', $CONFIG['junk_mbox']);

if (!$OUTPUT->ajax_call)
  rcube_add_label('checkingmail', 'deletemessage', 'movemessagetotrash', 'movingmessage');

// set page title
if (empty($RCMAIL->action) || $RCMAIL->action == 'list')
  $OUTPUT->set_pagetitle(rcmail_localize_foldername($IMAP->get_mailbox_name()));


/**
 * return the message list as HTML table
 */
function rcmail_message_list($attrib)
  {
  global $IMAP, $CONFIG, $COMM_PATH, $OUTPUT;

  $skin_path = $CONFIG['skin_path'];
  $image_tag = '<img src="%s%s" alt="%s" />';

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
    $col_name = $col != 'flag' ? Q(rcube_label($col)) : sprintf($image_tag, $skin_path, $attrib['unflaggedicon'], '');

    // make sort links
    $sort = '';
    if (in_array($col, $a_sort_cols))
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
    $OUTPUT->show_message('nomessagesfound', 'notice');


  $a_js_message_arr = array();

  // create row for each message
  foreach ($a_headers as $i => $header)  //while (list($i, $header) = each($a_headers))
    {
    $message_icon = $attach_icon = $flagged_icon = '';
    $js_row_arr = array();
    $zebra_class = $i%2 ? 'even' : 'odd';

    // set messag attributes to javascript array
    if ($header->deleted)
      $js_row_arr['deleted'] = true;
    if (!$header->seen)
      $js_row_arr['unread'] = true;
    if ($header->answered)
      $js_row_arr['replied'] = true;
    if ($header->flagged)
      $js_row_arr['flagged'] = true;

    // set message icon  
    if ($attrib['deletedicon'] && $header->deleted)
      $message_icon = $attrib['deletedicon'];
    else if ($attrib['unreadicon'] && !$header->seen)
      $message_icon = $attrib['unreadicon'];
    else if ($attrib['repliedicon'] && $header->answered)
      $message_icon = $attrib['repliedicon'];
    else if ($attrib['messageicon'])
      $message_icon = $attrib['messageicon'];

    if ($attrib['flaggedicon'] && $header->flagged)
      $flagged_icon = $attrib['flaggedicon'];
    else if ($attrib['unflaggedicon'] && !$header->flagged)
      $flagged_icon = $attrib['unflaggedicon'];
    
    // set attachment icon
    if ($attrib['attachmenticon'] && preg_match("/multipart\/[mr]/i", $header->ctype))
      $attach_icon = $attrib['attachmenticon'];
        
    $out .= sprintf('<tr id="rcmrow%d" class="message%s%s %s">'."\n",
                    $header->uid,
                    $header->seen ? '' : ' unread',
                    $header->deleted ? ' deleted' : '',
                    $header->flagged ? ' flagged' : '',
                    $zebra_class);    
    
    $out .= sprintf("<td class=\"icon\">%s</td>\n", $message_icon ? sprintf($image_tag, $skin_path, $message_icon, '') : '');


    if (!empty($header->charset))
      $IMAP->set_charset($header->charset);
  
    // format each col
    foreach ($a_show_cols as $col)
      {
      if ($col=='from' || $col=='to')
        $cont = Q(rcmail_address_string($header->$col, 3, $attrib['addicon']), 'show');
      else if ($col=='subject')
        {
        $action = $mbox==$CONFIG['drafts_mbox'] ? 'compose' : 'show';
        $uid_param = $mbox==$CONFIG['drafts_mbox'] ? '_draft_uid' : '_uid';
        $cont = Q($IMAP->decode_header($header->$col));
        if (empty($cont)) $cont = Q(rcube_label('nosubject'));
        $cont = sprintf('<a href="%s" onclick="return rcube_event.cancel(event)">%s</a>', Q(rcmail_url($action, array($uid_param=>$header->uid, '_mbox'=>$mbox))), $cont);
        }
      else if ($col=='flag')
        $cont = $flagged_icon ? sprintf($image_tag, $skin_path, $flagged_icon, '') : '';
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
  if ($attrib['flaggedicon'])
    $OUTPUT->set_env('flaggedicon', $skin_path . $attrib['flaggedicon']);
  if ($attrib['unflaggedicon'])
    $OUTPUT->set_env('unflaggedicon', $skin_path . $attrib['unflaggedicon']);
  
  $OUTPUT->set_env('messages', $a_js_message_arr);
  $OUTPUT->set_env('coltypes', $a_show_cols);
  
  $OUTPUT->include_script('list.js');
  
  return $out;
  }


/**
 * return javascript commands to add rows to the message list
 */
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
  foreach ($a_headers as $n => $header)
    {
    $a_msg_cols = array();
    $a_msg_flags = array();
    
    if (empty($header))
      continue;

    if (!empty($header->charset))
      $IMAP->set_charset($header->charset);

    // format each col; similar as in rcmail_message_list()
    foreach ($a_show_cols as $col)
      {
      if ($col=='from' || $col=='to')
        $cont = Q(rcmail_address_string($header->$col, 3), 'show');
      else if ($col=='subject')
        {
        $action = $mbox==$CONFIG['drafts_mbox'] ? 'compose' : 'show';
        $uid_param = $mbox==$CONFIG['drafts_mbox'] ? '_draft_uid' : '_uid';
        $cont = Q($IMAP->decode_header($header->$col));
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
    $a_msg_flags['flagged'] = $header->flagged ? 1 : 0;
    
    $OUTPUT->command('add_message_row',
      $header->uid,
      $a_msg_cols,
      $a_msg_flags,
      preg_match("/multipart\/m/i", $header->ctype),
      $insert_top);
    }
  }


/**
 * return an HTML iframe for loading mail content
 */
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


/**
 *
 */
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


/**
 *
 */
function rcmail_quota_display($attrib)
  {
  global $OUTPUT, $COMM_PATH;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmquotadisplay';

  if(isset($attrib['display']))
    $_SESSION['quota_display'] = $attrib['display'];

  $OUTPUT->add_gui_object('quotadisplay', $attrib['id']);

  // allow the following attributes to be added to the <span> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'display'));

  $out = '<span' . $attrib_str . '>';
  $out .= rcmail_quota_content();
  $out .= '</span>';
  return $out;
  }


/**
 *
 */
function rcmail_quota_content($quota=NULL)
  {
  global $IMAP, $COMM_PATH;

  $display = isset($_SESSION['quota_display']) ? $_SESSION['quota_display'] : '';

  if (is_array($quota) && !empty($quota['used']) && !empty($quota['total']))
    {
      if (!isset($quota['percent']))
        $quota['percent'] = $quota['used'] / $quota['total'];
    }
  elseif (!$IMAP->get_capability('QUOTA'))
    return rcube_label('unknown');
  else
    $quota = $IMAP->get_quota();

  if ($quota)
    {
    $quota_text = sprintf('%s / %s (%.0f%%)',
                          show_bytes($quota['used'] * 1024),
                          show_bytes($quota['total'] * 1024),
                          $quota['percent']);

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


/**
 *
 */
function rcmail_get_messagecount_text($count=NULL, $page=NULL)
  {
  global $IMAP, $MESSAGE;
  
  if (isset($MESSAGE->index))
    {
    return rcube_label(array('name' => 'messagenrof',
                             'vars' => array('nr'  => $MESSAGE->index+1,
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


/**
 * Convert the given message part to proper HTML
 * which can be displayed the message view
 *
 * @param object rcube_message_part Message part
 * @param bool  True if external objects (ie. images ) are allowed
 * @param bool  True if part should be converted to plaintext
 * @return string Formatted HTML string
 */
function rcmail_print_body($part, $safe=false, $plain=false)
{
  global $REMOTE_OBJECTS;
  
  // convert html to text/plain
  if ($part->ctype_secondary == 'html' && $plain) {
    $txt = new html2text($part->body, false, true);
    $body = $txt->get_text();
    $part->ctype_secondary = 'plain';
  }
  // text/html
  else if ($part->ctype_secondary == 'html') {
    // charset was converted to UTF-8 in rcube_imap::get_message_part() -> change charset specification in HTML accordingly
    $html = $part->body; 
    if(preg_match('/(\s+content=[\'"]\w+\/\w+;\s+charset)=([a-z0-9-]+)/i', $html)) 
      $html = preg_replace('/(\s+content=[\'"]\w+\/\w+;\s+charset)=([a-z0-9-]+)/i', '\\1='.RCMAIL_CHARSET, $html); 
    else {
      // add <head> for malformed messages, washtml cannot work without that
      if (!preg_match('/<head>(.*)<\/head>/m', $html))
        $html = '<head></head>' . $html;
      $html = substr_replace($html, '<meta http-equiv="Content-Type" content="text/html; charset='.RCMAIL_CHARSET.'" />', intval(stripos($html, '</head>')), 0);
    }

    // clean HTML with washhtml by Frederic Motte
    $body = washtml::wash($html, array(
      'show_washed' => false,
      'allow_remote' => $safe,
      'blocked_src' => "./program/blocked.gif",
      'charset' => RCMAIL_CHARSET,
      'cid_map' => $part->replaces,
      ), $full_inline);

    $REMOTE_OBJECTS = !$full_inline;

    return $body;
  }
  // text/enriched
  else if ($part->ctype_secondary=='enriched') {
    $part->ctype_secondary = 'html';
    return Q(enriched_to_html($body), 'show');
  }
  else
    $body = $part->body;


  /**** assert plaintext ****/

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
  
//    if ($part->ctype_parameters['format'] != 'flowed')
//      $body = wordwrap(trim($body), 80);

  // search for patterns like links and e-mail addresses
  $body = preg_replace($convert_patterns, $convert_replaces, $body);

  // split body into single lines
  $a_lines = preg_split('/\r?\n/', $body);
  $quote_level = 0;

  // colorize quoted parts
  for ($n=0; $n < sizeof($a_lines); $n++) {
    $line = $a_lines[$n];
    $quotation = '';
    $q = 0;
    
    if (preg_match('/^(>+\s*)+/', $line, $regs)) {
      $q    = strlen(preg_replace('/\s/', '', $regs[0]));
      $line = substr($line, strlen($regs[0]));

      if ($q > $quote_level)
        $quotation = str_repeat('<blockquote>', $q - $quote_level);
      else if ($q < $quote_level)
        $quotation = str_repeat("</blockquote>", $quote_level - $q);
    }
    else if ($quote_level > 0)
      $quotation = str_repeat("</blockquote>", $quote_level);

    $quote_level = $q;
    $a_lines[$n] = $quotation . Q($line, 'replace', false);  // htmlquote plaintext
  }

  // insert the links for urls and mailtos
  $body = preg_replace("/##string_replacement\{([0-9]+)\}##/e", "\$replace_strings[\\1]", join("\n", $a_lines));
  
  return "<div class=\"pre\">".$body."\n</div>";
  }



/**
 * add a string to the replacement array and return a replacement string
 */
function rcmail_str_replacement($str, &$rep)
  {
  static $count = 0;
  $rep[$count] = stripslashes($str);
  return "##string_replacement{".($count++)."}##";
  }



/**
 * return table with message headers
 */
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
    $headers = is_object($MESSAGE->headers) ? get_object_vars($MESSAGE->headers) : $MESSAGE->headers;
    
  // add empty subject if none exsists
  if (empty($headers['subject']))
    $headers['subject'] = rcube_label('nosubject');
  
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
      $header_value = format_date($headers[$hkey]);
    else if (in_array($hkey, array('from', 'to', 'cc', 'bcc', 'reply-to')))
      $header_value = Q(rcmail_address_string($headers[$hkey], NULL, $attrib['addicon']), 'show');
    else
      $header_value = Q($IMAP->decode_header($headers[$hkey]));

    $out .= "\n<tr>\n";
    $out .= '<td class="header-title">'.Q(rcube_label($hkey)).":&nbsp;</td>\n";
    $out .= '<td class="'.$hkey.'" width="90%">'.$header_value."</td>\n</tr>";
    $header_count++;
    }

  $out .= "\n</table>\n\n";

  return $header_count ? $out : '';  
  }


/**
 * Handler for the 'messagebody' GUI object
 *
 * @param array Named parameters
 * @return string HTML content showing the message body
 */
function rcmail_message_body($attrib)
  {
  global $CONFIG, $OUTPUT, $MESSAGE, $IMAP, $REMOTE_OBJECTS;

  if (!is_array($MESSAGE->parts) && empty($MESSAGE->body))
    return '';
    
  if (!$attrib['id'])
    $attrib['id'] = 'rcmailMsgBody';

  $safe_mode = $MESSAGE->is_safe || intval($_GET['_safe']);
  $out = '';
  
  $header_attrib = array();
  foreach ($attrib as $attr => $value)
    if (preg_match('/^headertable([a-z]+)$/i', $attr, $regs))
      $header_attrib[$regs[1]] = $value;

  if (!empty($MESSAGE->parts))
    {
    foreach ($MESSAGE->parts as $i => $part)
      {
      if ($part->type == 'headers')
        $out .= rcmail_message_headers(sizeof($header_attrib) ? $header_attrib : NULL, $part->headers);
      else if ($part->type == 'content')
        {
        if (empty($part->ctype_parameters) || empty($part->ctype_parameters['charset']))
          $part->ctype_parameters['charset'] = $MESSAGE->headers->charset;

        // fetch part if not available
        if (!isset($part->body))
          $part->body = $MESSAGE->get_part_content($part->mime_id);

        $body = rcmail_print_body($part, $safe_mode, !$CONFIG['prefer_html']);

        if ($part->ctype_secondary == 'html')
          $out .= html::div('message-htmlpart', rcmail_html4inline($body, $attrib['id']));
        else
          $out .= html::div('message-part', $body);
        }
      }
    }
  else
    $out .= html::div('message-part', html::div('pre', Q($MESSAGE->body)));


  $ctype_primary = strtolower($MESSAGE->structure->ctype_primary);
  $ctype_secondary = strtolower($MESSAGE->structure->ctype_secondary);

  // list images after mail body
  if (get_boolean($attrib['showimages']) 
      && $CONFIG['inline_images']
      && $ctype_primary == 'multipart'
      && !empty($MESSAGE->attachments) 
      && !strstr($message_body, '<html'))
    {
    foreach ($MESSAGE->attachments as $attach_prop) {
      if (strpos($attach_prop->mimetype, 'image/') === 0) {
        $out .= html::tag('hr') . html::p(array('align' => "center"),
          html::img(array(
            'src' => $MESSAGE->get_part_url($attach_prop->mime_id),
            'title' => $attach_prop->filename,
            'alt' => $attach_prop->filename,
          )));
        }
    }
  }
  
  // tell client that there are blocked remote objects
  if ($REMOTE_OBJECTS && !$safe_mode)
    $OUTPUT->set_env('blockedobjects', true);

  return html::div($attrib, $out);
  }



/**
 * modify a HTML message that it can be displayed inside a HTML page
 */
function rcmail_html4inline($body, $container_id)
  {
  $base_url = "";
  $last_style_pos = 0;
  $body_lc = strtolower($body);
  
  // check for <base href>
  if (preg_match(($base_reg = '/(<base.*href=["\']?)([hftps]{3,5}:\/{2}[^"\'\s]+)([^<]*>)/i'), $body, $base_regs))
    $base_url = $base_regs[2];
  
  // find STYLE tags
  while (($pos = strpos($body_lc, '<style', $last_style_pos)) && ($pos2 = strpos($body_lc, '</style>', $pos)))
    {
    $pos = strpos($body_lc, '>', $pos)+1;

    // replace all css definitions with #container [def]
    $styles = rcmail_mod_css_styles(substr($body, $pos, $pos2-$pos), $container_id, $base_url);

    $body = substr($body, 0, $pos) . $styles . substr($body, $pos2);
    $body_lc = strtolower($body);
    $last_style_pos = $pos2;
    }

  // resolve <base href>
  if ($base_url)
    {
    $body = preg_replace('/(src|background|href)=(["\']?)([\.\/]+[^"\'\s]+)(\2|\s|>)/Uie', "'\\1=\"'.make_absolute_url('\\3', '$base_url').'\"'", $body);
    $body = preg_replace('/(url\s*\()(["\']?)([\.\/]+[^"\'\)\s]+)(\2)\)/Uie', "'\\1\''.make_absolute_url('\\3', '$base_url').'\')'", $body);
    $body = preg_replace($base_reg, '', $body);
    }
    
  // modify HTML links to open a new window if clicked
  $body = preg_replace('/<(a|link)\s+([^>]+)>/Uie', "rcmail_alter_html_link('\\1','\\2', '$container_id');", $body);

  // add comments arround html and other tags
  $out = preg_replace(array(
      '/(<!DOCTYPE[^>]*>)/i',
      '/(<\?xml[^>]*>)/i',
      '/(<\/?html[^>]*>)/i',
      '/(<\/?head[^>]*>)/i',
      '/(<title[^>]*>.*<\/title>)/Ui',
      '/(<\/?meta[^>]*>)/i'),
    '<!--\\1-->',
    $body);

  $out = preg_replace(
    array('/<body([^>]*)>/i', '/<\/body>/i'),
    array('<div class="rcmBody"\\1>', '</div>'),
    $out);

  // quote <? of php and xml files that are specified as text/html
  $out = preg_replace(array('/<\?/', '/\?>/'), array('&lt;?', '?&gt;'), $out);

  return $out;
  }


/**
 * parse link attributes and set correct target
 */
function rcmail_alter_html_link($tag, $attrs, $container_id)
  {
  $attrib = parse_attrib_string($attrs);

  if ($tag == 'link' && preg_match('/^https?:\/\//i', $attrib['href']))
    $attrib['href'] = "./bin/modcss.php?u=" . urlencode($attrib['href']) . "&amp;c=" . urlencode($container_id);

  else if (stristr((string)$attrib['href'], 'mailto:'))
    $attrib['onclick'] = sprintf(
      "return %s.command('compose','%s',this)",
      JS_OBJECT_NAME,
      JQ(substr($attrib['href'], 7)));

  else if (!empty($attrib['href']) && $attrib['href']{0}!='#')
    $attrib['target'] = '_blank';

  return "<$tag" . create_attrib_string($attrib, array('href','name','target','onclick','id','class','style','title','rel','type','media')) . ' />';
  }


/**
 * decode address string and re-format it as HTML links
 */
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
  global $MESSAGE;
  
  $part = asciiwords(get_input_value('_part', RCUBE_INPUT_GPC));
  if (!is_object($MESSAGE) || !is_array($MESSAGE->parts) || !($_GET['_uid'] && $_GET['_part']) || !$MESSAGE->mime_parts[$part])
    return '';
    
  $part = $MESSAGE->mime_parts[$part];
  $table = new html_table(array('cols' => 3));
  
  if (!empty($part->filename)) {
    $table->add('title', Q(rcube_label('filename')));
    $table->add(null, Q($part->filename));
    $table->add(null, '[' . html::a('?'.str_replace('_frame=', '_download=', $_SERVER['QUERY_STRING']), Q(rcube_label('download'))) . ']');
  }
  
  if (!empty($part->size)) {
    $table->add('title', Q(rcube_label('filesize')));
    $table->add(null, Q(show_bytes($part->size)));
  }
  
  return $table->show($attrib);
  }



function rcmail_message_part_frame($attrib)
  {
  global $MESSAGE;
  
  $part = $MESSAGE->mime_parts[asciiwords(get_input_value('_part', RCUBE_INPUT_GPC))];
  $ctype_primary = strtolower($part->ctype_primary);

  $attrib['src'] = Q('./?'.str_replace('_frame=', ($ctype_primary=='text' ? '_show=' : '_preload='), $_SERVER['QUERY_STRING']));

  $attrib_str = create_attrib_string($attrib, array('id', 'class', 'style', 'src', 'width', 'height'));
  $out = '<iframe '. $attrib_str . "></iframe>";
    
  return $out;
  }


/**
 * clear message composing settings
 */
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
  

/**
 * Send the given message compose object using the configured method
 */
function rcmail_deliver_message(&$message, $from, $mailto)
{
  global $CONFIG;

  $msg_body = $message->get();
  $headers = $message->headers();
  
  // send thru SMTP server using custom SMTP library
  if ($CONFIG['smtp_server'])
    {
    // generate list of recipients
    $a_recipients = array($mailto);
  
    if (strlen($headers['Cc']))
      $a_recipients[] = $headers['Cc'];
    if (strlen($headers['Bcc']))
      $a_recipients[] = $headers['Bcc'];
  
    // clean Bcc from header for recipients
    $send_headers = $headers;
    unset($send_headers['Bcc']);
    // here too, it because txtHeaders() below use $message->_headers not only $send_headers
    unset($message->_headers['Bcc']);

    // send message
    $smtp_response = array();
    $sent = smtp_mail($from, $a_recipients, ($foo = $message->txtHeaders($send_headers, true)), $msg_body, $smtp_response);

    // log error
    if (!$sent)
      raise_error(array('code' => 800, 'type' => 'smtp', 'line' => __LINE__, 'file' => __FILE__,
                        'message' => "SMTP error: ".join("\n", $smtp_response)), TRUE, FALSE);
    }
  
  // send mail using PHP's mail() function
  else
    {
    // unset some headers because they will be added by the mail() function
    $headers_enc = $message->headers($headers);
    $headers_php = $message->_headers;
    unset($headers_php['To'], $headers_php['Subject']);
    
    // reset stored headers and overwrite
    $message->_headers = array();
    $header_str = $message->txtHeaders($headers_php);
  
    if (ini_get('safe_mode'))
      $sent = mail($headers_enc['To'], $headers_enc['Subject'], $msg_body, $header_str);
    else
      $sent = mail($headers_enc['To'], $headers_enc['Subject'], $msg_body, $header_str, "-f$from");
    }
  
  if ($sent)  // remove MDN headers after sending
    unset($headers['Return-Receipt-To'], $headers['Disposition-Notification-To']);
  
  $message->_headers = array();
  $message->headers($headers);
  
  return $sent;
}


function rcmail_send_mdn($uid)
{
  global $RCMAIL, $IMAP;

  $message = new rcube_message($uid);
  
  if ($message->headers->mdn_to && !$message->headers->mdn_sent && $IMAP->check_permflag('MDNSENT'))
  {
    $identity = $RCMAIL->user->get_identity();
    $sender = format_email_recipient($identity['email'], $identity['name']);
    $recipient = array_shift($IMAP->decode_address_list($message->headers->mdn_to));
    $mailto = $recipient['mailto'];

    $compose = new rcube_mail_mime($RCMAIL->config->header_delimiter());
    $compose->setParam(array(
      'text_encoding' => 'quoted-printable',
      'html_encoding' => 'quoted-printable',
      'head_encoding' => 'quoted-printable',
      'head_charset'  => RCMAIL_CHARSET,
      'html_charset'  => RCMAIL_CHARSET,
      'text_charset'  => RCMAIL_CHARSET,
    ));
    
    // compose headers array
    $headers = array(
      'Date' => date('r'),
      'From' => $sender,
      'To'   => $message->headers->mdn_to,
      'Subject' => rcube_label('receiptread') . ': ' . $message->subject,
      'Message-ID' => sprintf('<%s@%s>', md5(uniqid('rcmail'.rand(),true)), $RCMAIL->config->mail_domain($_SESSION['imap_host'])),
      'X-Sender' => $identity['email'],
      'Content-Type' => 'multipart/report; report-type=disposition-notification',
    );
    
    if ($agent = $RCMAIL->config->get('useragent'))
      $headers['User-Agent'] = $agent;

    $body = rcube_label("yourmessage") . "\r\n\r\n" .
      "\t" . rcube_label("to") . ': ' . rcube_imap::decode_mime_string($message->headers->to, $message->headers->charset) . "\r\n" .
      "\t" . rcube_label("subject") . ': ' . $message->subject . "\r\n" .
      "\t" . rcube_label("sent") . ': ' . format_date($message->headers->date, $RCMAIL->config->get('date_long')) . "\r\n" .
      "\r\n" . rcube_label("receiptnote") . "\r\n";
    
    $ua = $RCMAIL->config->get('useragent', "RoundCube Webmail (Version ".RCMAIL_VERSION.")");
    $report = "Reporting-UA: $ua\r\n";
    
    if ($message->headers->to)
        $report .= "Original-Recipient: {$message->headers->to}\r\n";
    
    $report .= "Final-Recipient: rfc822; {$identity['email']}\r\n" .
               "Original-Message-ID: {$message->headers->messageID}\r\n" .
               "Disposition: manual-action/MDN-sent-manually; displayed\r\n";
    
    $compose->headers($headers);
    $compose->setTXTBody(wordwrap($body, 75, "\r\n"));
    $compose->addAttachment($report, 'message/disposition-notification', 'MDNPart2.txt', false, '7bit', 'inline');

    $sent = rcmail_deliver_message($compose, $identity['email'], $mailto);

    if ($sent)
    {
      $IMAP->set_flag($message->uid, 'MDNSENT');
      return true;
    }
  }
  
  return false;
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
  'searchform' => array($OUTPUT, 'search_form'),
));

?>
