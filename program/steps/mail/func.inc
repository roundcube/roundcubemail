<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/func.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
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

$EMAIL_ADDRESS_PATTERN = '([a-z0-9][a-z0-9\-\.\+\_]*@[a-z0-9][a-z0-9\-\.]*\\.[a-z]{2,5})';

// actions that do not require imap connection
$NOIMAP_ACTIONS = array('spell', 'addcontact', 'autocomplete', 'upload', 'display-attachment', 'remove-attachment');

// Init IMAP object
$RCMAIL->imap_init();

// log in to imap server
if (!in_array($RCMAIL->action, $NOIMAP_ACTIONS) && !$RCMAIL->imap_connect()) {
  $RCMAIL->kill_session();

  if ($OUTPUT->ajax_call)
    $OUTPUT->redirect(array(), 2000);

  $OUTPUT->set_env('task', 'login');
  $OUTPUT->send('login');
}


// set imap properties and session vars
if ($mbox = get_input_value('_mbox', RCUBE_INPUT_GPC))
  $IMAP->set_mailbox(($_SESSION['mbox'] = $mbox));
else
  $_SESSION['mbox'] = $IMAP->get_mailbox_name();

if (!empty($_GET['_page']))
  $IMAP->set_page(($_SESSION['page'] = intval($_GET['_page'])));

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

// set main env variables, labels and page title
if (empty($RCMAIL->action) || $RCMAIL->action == 'list')
  {
  $mbox_name = $IMAP->get_mailbox_name();

  if (empty($RCMAIL->action))
    {
    // initialize searching result if search_filter is used
    if ($_SESSION['search_filter'] && $_SESSION['search_filter'] != 'ALL')
      {
      $search_request = md5($mbox_name.$_SESSION['search_filter']);
  
      $IMAP->search($mbox_name, $_SESSION['search_filter'], RCMAIL_CHARSET, $_SESSION['sort_col']);
      $_SESSION['search'][$search_request] = $IMAP->get_search_set();
      $OUTPUT->set_env('search_request', $search_request);
      }
    
      $OUTPUT->set_env('search_mods', $_SESSION['search_mods'] ? $_SESSION['search_mods'] : array('subject'=>'subject'));
      // make sure the message count is refreshed (for default view)
      $IMAP->messagecount($mbox_name, 'ALL', true);
    }
	
  // set current mailbox in client environment
  $OUTPUT->set_env('mailbox', $mbox_name);
  $OUTPUT->set_env('quota', $IMAP->get_capability('quota'));
  $OUTPUT->set_env('delimiter', $IMAP->get_hierarchy_delimiter());

  if ($CONFIG['flag_for_deletion'])
    $OUTPUT->set_env('flag_for_deletion', true);
  if ($CONFIG['read_when_deleted'])
    $OUTPUT->set_env('read_when_deleted', true);
  if ($CONFIG['skip_deleted'])
    $OUTPUT->set_env('skip_deleted', true);
  if ($CONFIG['display_next'])
    $OUTPUT->set_env('display_next', true);
	  
  if ($CONFIG['trash_mbox'])
    $OUTPUT->set_env('trash_mailbox', $CONFIG['trash_mbox']);
  if ($CONFIG['drafts_mbox'])
    $OUTPUT->set_env('drafts_mailbox', $CONFIG['drafts_mbox']);
  if ($CONFIG['junk_mbox'])
    $OUTPUT->set_env('junk_mailbox', $CONFIG['junk_mbox']);

  if (!$OUTPUT->ajax_call)
    $OUTPUT->add_label('checkingmail', 'deletemessage', 'movemessagetotrash', 'movingmessage');

  $OUTPUT->set_pagetitle(rcmail_localize_foldername($mbox_name));
  }


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
  $OUTPUT->add_label('from', 'to');

  // get message headers
  $a_headers = $IMAP->list_headers('', '', $sort_col, $sort_order);

  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcubemessagelist';

  // allow the following attributes to be added to the <table> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'cellpadding', 'cellspacing', 'border', 'summary'));

  $out = '<table' . $attrib_str . ">\n";

  // define list of cols to be displayed based on parameter or config
  if (empty($attrib['columns']))
      $a_show_cols = is_array($CONFIG['list_cols']) ? $CONFIG['list_cols'] : array('subject');
  else
      $a_show_cols = preg_split('/[\s,;]+/', strip_quotes($attrib['columns']));

  // store column list in a session-variable
  $_SESSION['list_columns'] = $a_show_cols;
  
  // define sortable columns
  $a_sort_cols = array('subject', 'date', 'from', 'to', 'size');

  $mbox = $IMAP->get_mailbox_name();
  $delim = $IMAP->get_hierarchy_delimiter();

  // show 'to' instead of 'from' in sent/draft messages
  if ((strpos($mbox.$delim, $CONFIG['sent_mbox'].$delim)===0 || strpos($mbox.$delim, $CONFIG['drafts_mbox'].$delim)===0)
      && ($f = array_search('from', $a_show_cols)) && !array_search('to', $a_show_cols))
    $a_show_cols[$f] = 'to';
  
  // add col definition
  $out .= '<colgroup>';
  $out .= '<col class="icon" />';

  foreach ($a_show_cols as $col)
    $out .= ($col!='attachment') ? sprintf('<col class="%s" />', $col) : '<col class="icon" />';

  $out .= "</colgroup>\n";

  // add table title
  $out .= "<thead><tr>\n<td class=\"icon\">&nbsp;</td>\n";

  $javascript = '';
  foreach ($a_show_cols as $col)
    {
    // get column name
    switch ($col)
      {
      case 'flag':
        $col_name = sprintf($image_tag, $skin_path, $attrib['unflaggedicon'], '');
        break;
      case 'attachment':
        $col_name = sprintf($image_tag, $skin_path, $attrib['attachmenticon'], '');
        break;
      default:
        $col_name = Q(rcube_label($col));
    }

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
    if ($col!='attachment')
      $out .= '<td class="'.$col.$sort_class.'" id="rcm'.$col.'">' . "$col_name$sort</td>\n";
    else    
      $out .= '<td class="icon" id="rcm'.$col.'">' . "$col_name$sort</td>\n";
    }

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
    $zebra_class = $i%2 ? ' even' : ' odd';

    // set messag attributes to javascript array
    if ($header->deleted)
      $js_row_arr['deleted'] = true;
    if (!$header->seen)
      $js_row_arr['unread'] = true;
    if ($header->answered)
      $js_row_arr['replied'] = true;
    if ($header->forwarded)
      $js_row_arr['forwarded'] = true;
    if ($header->flagged)
      $js_row_arr['flagged'] = true;

    // set message icon  
    if ($attrib['deletedicon'] && $header->deleted)
      $message_icon = $attrib['deletedicon'];
    else if ($attrib['repliedicon'] && $header->answered)
      {
      if ($attrib['forwardedrepliedicon'] && $header->forwarded)
        $message_icon = $attrib['forwardedrepliedicon'];
      else
        $message_icon = $attrib['repliedicon'];
      }
    else if ($attrib['forwardedicon'] && $header->forwarded)
      $message_icon = $attrib['forwardedicon'];
    else if ($attrib['unreadicon'] && !$header->seen)
      $message_icon = $attrib['unreadicon'];
    else if ($attrib['messageicon'])
      $message_icon = $attrib['messageicon'];

    if ($attrib['flaggedicon'] && $header->flagged)
      $flagged_icon = $attrib['flaggedicon'];
    else if ($attrib['unflaggedicon'] && !$header->flagged)
      $flagged_icon = $attrib['unflaggedicon'];
    
    // set attachment icon
    if ($attrib['attachmenticon'] && preg_match("/multipart\/m/i", $header->ctype))
      $attach_icon = $attrib['attachmenticon'];
        
    $out .= sprintf('<tr id="rcmrow%d" class="message%s%s%s%s">'."\n",
                    $header->uid,
                    $header->seen ? '' : ' unread',
                    $header->deleted ? ' deleted' : '',
                    $header->flagged ? ' flagged' : '',
                    $zebra_class);
    
    $out .= sprintf("<td class=\"icon\">%s</td>\n", $message_icon ? sprintf($image_tag, $skin_path, $message_icon, '') : '');

    $IMAP->set_charset(!empty($header->charset) ? $header->charset : $CONFIG['default_charset']);
  
    // format each col
    foreach ($a_show_cols as $col)
      {
      if (in_array($col, array('from', 'to', 'cc', 'replyto')))
        $cont = Q(rcmail_address_string($header->$col, 3, false, $attrib['addicon']), 'show');
      else if ($col=='subject')
        {
        $action = $mbox==$CONFIG['drafts_mbox'] ? 'compose' : 'show';
        $uid_param = $mbox==$CONFIG['drafts_mbox'] ? '_draft_uid' : '_uid';
        $cont = abbreviate_string(trim($IMAP->decode_header($header->$col)), 160);
        if (empty($cont)) $cont = rcube_label('nosubject');
        $cont = $OUTPUT->browser->ie ? Q($cont) : sprintf('<a href="%s" onclick="return rcube_event.cancel(event)">%s</a>', Q(rcmail_url($action, array($uid_param=>$header->uid, '_mbox'=>$mbox))), Q($cont));
        }
      else if ($col=='flag')
        $cont = $flagged_icon ? sprintf($image_tag, $skin_path, $flagged_icon, '') : '';
      else if ($col=='size')
        $cont = show_bytes($header->$col);
      else if ($col=='date')
        $cont = format_date($header->date);
      else
        $cont = Q($header->$col);
        
      if ($col!='attachment')
        $out .= '<td class="'.$col.'">' . $cont . "</td>\n";
      else
        $out .= sprintf("<td class=\"icon\">%s</td>\n", $attach_icon ? sprintf($image_tag, $skin_path, $attach_icon, '') : '&nbsp;');
      }

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
  if ($attrib['forwardedicon'])
    $OUTPUT->set_env('forwardedicon', $skin_path . $attrib['forwardedicon']);
  if ($attrib['forwardedrepliedicon'])
    $OUTPUT->set_env('forwardedrepliedicon', $skin_path . $attrib['forwardedrepliedicon']);
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
 * or to replace the whole list (IE only)
 */
function rcmail_js_message_list($a_headers, $insert_top=FALSE, $replace=TRUE)
  {
  global $CONFIG, $IMAP, $OUTPUT;

  if (empty($_SESSION['list_columns']))
    $a_show_cols = is_array($CONFIG['list_cols']) ? $CONFIG['list_cols'] : array('subject');
  else
    $a_show_cols = $_SESSION['list_columns'];

  $mbox = $IMAP->get_mailbox_name();
  $delim = $IMAP->get_hierarchy_delimiter();
  
  // show 'to' instead of 'from' in sent/draft messages
  if ((strpos($mbox.$delim, $CONFIG['sent_mbox'].$delim)===0 || strpos($mbox.$delim, $CONFIG['drafts_mbox'].$delim)===0)
      && (($f = array_search('from', $a_show_cols)) !== false) && array_search('to', $a_show_cols) === false)
    $a_show_cols[$f] = 'to';

  $browser = new rcube_browser;

  $OUTPUT->command('set_message_coltypes', $a_show_cols);

  // remove 'attachment' and 'flag' columns, we don't need them here
  if(($key = array_search('attachment', $a_show_cols)) !== FALSE)
    unset($a_show_cols[$key]);
  if(($key = array_search('flag', $a_show_cols)) !== FALSE)
    unset($a_show_cols[$key]);

  if ($browser->ie && $replace)
    $OUTPUT->command('offline_message_list', true);

  // loop through message headers
  foreach ($a_headers as $n => $header)
    {
    $a_msg_cols = array();
    $a_msg_flags = array();
    
    if (empty($header))
      continue;

    $IMAP->set_charset(!empty($header->charset) ? $header->charset : $CONFIG['default_charset']);

    // format each col; similar as in rcmail_message_list()
    foreach ($a_show_cols as $col)
      {
      if (in_array($col, array('from', 'to', 'cc', 'replyto')))
        $cont = Q(rcmail_address_string($header->$col, 3), 'show');
      else if ($col=='subject')
        {
        $action = $mbox==$CONFIG['drafts_mbox'] ? 'compose' : 'show';
        $uid_param = $mbox==$CONFIG['drafts_mbox'] ? '_draft_uid' : '_uid';
	$cont = abbreviate_string(trim($IMAP->decode_header($header->$col)), 160);
        if (!$cont) $cont = rcube_label('nosubject');
        $cont = $browser->ie ? Q($cont) : sprintf('<a href="%s" onclick="return rcube_event.cancel(event)">%s</a>', Q(rcmail_url($action, array($uid_param=>$header->uid, '_mbox'=>$mbox))), Q($cont));
        }
      else if ($col=='size')
        $cont = show_bytes($header->$col);
      else if ($col=='date')
        $cont = format_date($header->date);
      else
        $cont = Q($header->$col);
          
      $a_msg_cols[$col] = $cont;
      }

    if ($header->deleted)
      $a_msg_flags['deleted'] = 1;
    if (!$header->seen)
      $a_msg_flags['unread'] = 1;
    if ($header->answered)
      $a_msg_flags['replied'] = 1;
    if ($header->forwarded)
      $a_msg_flags['forwarded'] = 1;
    if ($header->flagged)
      $a_msg_flags['flagged'] = 1;
      
    $OUTPUT->command('add_message_row',
      $header->uid,
      $a_msg_cols,
      $a_msg_flags,
      preg_match("/multipart\/m/i", $header->ctype),
      $insert_top);
    }

  if ($browser->ie && $replace)
    $OUTPUT->command('offline_message_list', false);
  }


/**
 * return an HTML iframe for loading mail content
 */
function rcmail_messagecontent_frame($attrib)
  {
  global $OUTPUT;
  
  if (empty($attrib['id']))
    $attrib['id'] = 'rcmailcontentwindow';

  $attrib['name'] = $attrib['id'];

  $OUTPUT->set_env('contentframe', $attrib['id']);
  $OUTPUT->set_env('blankpage', $attrib['src'] ? $OUTPUT->abs_url($attrib['src']) : 'program/blank.gif');

  return html::iframe($attrib);
  }


function rcmail_messagecount_display($attrib)
  {
  global $IMAP, $OUTPUT;
  
  if (!$attrib['id'])
    $attrib['id'] = 'rcmcountdisplay';

  $OUTPUT->add_gui_object('countdisplay', $attrib['id']);

  return html::span($attrib, rcmail_get_messagecount_text());
  }


function rcmail_quota_display($attrib)
  {
  global $OUTPUT, $COMM_PATH;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmquotadisplay';

  if(isset($attrib['display']))
    $_SESSION['quota_display'] = $attrib['display'];

  $OUTPUT->add_gui_object('quotadisplay', $attrib['id']);
  
  $quota = rcmail_quota_content($attrib);
  
  if (is_array($quota)) {
    $OUTPUT->add_script('$(document).ready(function(){
	rcmail.set_quota('.json_serialize($quota).')});', 'foot');
    $quota = '';
    }
  
  return html::span($attrib, $quota);
  }


function rcmail_quota_content($attrib=NULL)
  {
  global $COMM_PATH, $RCMAIL;

  $display = isset($_SESSION['quota_display']) ? $_SESSION['quota_display'] : '';

  $quota = $RCMAIL->imap->get_quota();
  $quota = $RCMAIL->plugins->exec_hook('quota', $quota);

  if (!isset($quota['used']) || !isset($quota['total']))
    return rcube_label('unknown');

  if (!($quota['total']==0 && $RCMAIL->config->get('quota_zero_as_unlimited')))
    {
    if (!isset($quota['percent']))
      $quota['percent'] = min(100, round(($quota['used']/max(1,$quota['total']))*100));
    
    $quota_result = sprintf('%s / %s (%.0f%%)',
        show_bytes($quota['used'] * 1024), show_bytes($quota['total'] * 1024),
        $quota['percent']);

    if ($display == 'image') {
      $quota_result = array(
    	'percent' 	=> $quota['percent'],
        'title'		=> $quota_result,
	);

      if ($attrib['width'])
        $quota_result['width'] = $attrib['width'];
      if ($attrib['height'])
        $quota_result['height']	= $attrib['height'];
      }
    }
  else
    return rcube_label('unlimited');

  return $quota_result;
  }


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


function rcmail_mailbox_name_display($attrib)
{
  global $RCMAIL;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmmailboxname';

  $RCMAIL->output->add_gui_object('mailboxname', $attrib['id']);

  return html::span($attrib, rcmail_get_mailbox_name_text());
}


function rcmail_get_mailbox_name_text()
{
  global $RCMAIL;
  return rcmail_localize_foldername($RCMAIL->imap->get_mailbox_name());
}


function rcmail_send_unread_count($mbox_name, $force=false)
{
  global $RCMAIL;
    
  $old_unseen = $_SESSION['unseen_count'][$mbox_name];
  $unseen = $RCMAIL->imap->messagecount($mbox_name, 'UNSEEN', $force);

  if ($unseen != $old_unseen || ($mbox_name == 'INBOX'))
    $RCMAIL->output->command('set_unread_count', $mbox_name, $unseen, ($mbox_name == 'INBOX'));

  // @TODO: this data is doubled (session and cache tables) if caching is enabled
  $_SESSION['unseen_count'][$mbox_name] = $unseen;
    
  return $unseen;
}


/**
 * Sets message is_safe flag according to 'show_images' option value
 *
 * @param object rcube_message Message
 */
function rcmail_check_safe(&$message)
{
  global $RCMAIL;

  $show_images = $RCMAIL->config->get('show_images');
  if (!$message->is_safe
    && !empty($show_images)
    && $message->has_html_part())
  {
    switch($show_images) {
      case '1': // known senders only
        $CONTACTS = new rcube_contacts($RCMAIL->db, $_SESSION['user_id']);
        if ($CONTACTS->search('email', $message->sender['mailto'], true, false)->count) {
          $message->set_safe(true);
        }
      break;
      case '2': // always
        $message->set_safe(true);
      break;
    }
  }
}


/**
 * Cleans up the given message HTML Body (for displaying)
 *
 * @param string HTML
 * @param array  Display parameters 
 * @param array  CID map replaces (inline images)
 * @return string Clean HTML
 */
function rcmail_wash_html($html, $p = array(), $cid_replaces)
{
  global $REMOTE_OBJECTS;
  
  $p += array('safe' => false, 'inline_html' => true);

  // special replacements (not properly handled by washtml class)
  $html_search = array(
    '/(<\/nobr>)(\s+)(<nobr>)/i',	// space(s) between <NOBR>
    '/<title[^>]*>.*<\/title>/i',		// PHP bug #32547 workaround: remove title tag
    '/^(\0\0\xFE\xFF|\xFF\xFE\0\0|\xFE\xFF|\xFF\xFE|\xEF\xBB\xBF)/',	// byte-order mark (only outlook?)
    '/<html\s[^>]+>/i',			// washtml/DOMDocument cannot handle xml namespaces
  );
  $html_replace = array(
    '\\1'.' &nbsp; '.'\\3',
    '',
    '',
    '<html>',
  );
  $html = preg_replace($html_search, $html_replace, $html);

  // fix (unknown/malformed) HTML tags before "wash"
  $html = preg_replace_callback('/(<[\/]*)([^\s>]+)/', 'rcmail_html_tag_callback', $html);

  // charset was converted to UTF-8 in rcube_imap::get_message_part(),
  // -> change charset specification in HTML accordingly
  $charset_pattern = '(<meta\s+[^>]*)(content=[\'"]?\w+\/\w+;\s*charset)=([a-z0-9-_]+)';
  if (preg_match("/$charset_pattern/Ui", $html)) {
    $html = preg_replace("/$charset_pattern/i", '\\1\\2='.RCMAIL_CHARSET, $html);
  }
  else {
    // add meta content-type to malformed messages, washtml cannot work without that
    if (!preg_match('/<head[^>]*>(.*)<\/head>/Uims', $html))
      $html = '<head></head>'. $html;
    $html = substr_replace($html, '<meta http-equiv="Content-Type" content="text/html; charset='.RCMAIL_CHARSET.'" />', intval(stripos($html, '<head>')+6), 0);
  }

  // turn relative into absolute urls
  $html = rcmail_resolve_base($html);

  // clean HTML with washhtml by Frederic Motte
  $wash_opts = array(
    'show_washed' => false,
    'allow_remote' => $p['safe'],
    'blocked_src' => "./program/blocked.gif",
    'charset' => RCMAIL_CHARSET,
    'cid_map' => $cid_replaces,
    'html_elements' => array('body'),
  );
    
  if (!$p['inline_html']) {
    $wash_opts['html_elements'] = array('html','head','title','body');
  }
  if ($p['safe']) {
    $wash_opts['html_elements'][] = 'link';
    $wash_opts['html_attribs'] = array('rel','type');
  }
    
  $washer = new washtml($wash_opts);
  $washer->add_callback('form', 'rcmail_washtml_callback');

  // allow CSS styles, will be sanitized by rcmail_washtml_callback()
  $washer->add_callback('style', 'rcmail_washtml_callback');

  $html = $washer->wash($html);
  $REMOTE_OBJECTS = $washer->extlinks;

  return $html;
}


/**
 * Convert the given message part to proper HTML
 * which can be displayed the message view
 *
 * @param object rcube_message_part Message part
 * @param array  Display parameters array 
 * @return string Formatted HTML string
 */
function rcmail_print_body($part, $p = array())
{
  global $RCMAIL;
  
  // trigger plugin hook
  $data = $RCMAIL->plugins->exec_hook('message_part_before',
    array('type' => $part->ctype_secondary, 'body' => $part->body) + $p + array('safe' => false, 'plain' => false, 'inline_html' => true));

  // convert html to text/plain
  if ($data['type'] == 'html' && $data['plain']) {
    $txt = new html2text($data['body'], false, true);
    $body = $txt->get_text();
    $part->ctype_secondary = 'plain';
  }
  // text/html
  else if ($data['type'] == 'html') {
    $body = rcmail_wash_html($data['body'], $data, $part->replaces);
    $part->ctype_secondary = $data['type'];
  }
  // text/enriched
  else if ($data['type'] == 'enriched') {
    $part->ctype_secondary = 'html';
    require_once('lib/enriched.inc');
    $body = Q(enriched_to_html($data['body']), 'show');
  }
  else {
    // assert plaintext
    $body = $part->body;
    $part->ctype_secondary = $data['type'] = 'plain';
  }
  
  // free some memory (hopefully)
  unset($data['body']);

  // plaintext postprocessing
  if ($part->ctype_secondary == 'plain')
    $body = rcmail_plain_body($body);

  // allow post-processing of the message body
  $data = $RCMAIL->plugins->exec_hook('message_part_after', array('type' => $part->ctype_secondary, 'body' => $body) + $data);

  return $data['type'] == 'html' ? $data['body'] : html::tag('pre', array(), $data['body']);
}


/**
 * Handle links and citation marks in plain text message
 *
 * @param string  Plain text string 
 * @return string Formatted HTML string
 */
function rcmail_plain_body($body)
{
  // make links and email-addresses clickable
  $replacements = new rcube_string_replacer;
    
  // search for patterns like links and e-mail addresses
  $body = preg_replace_callback($replacements->link_pattern, array($replacements, 'link_callback'), $body);
  $body = preg_replace_callback($replacements->mailto_pattern, array($replacements, 'mailto_callback'), $body);

  // split body into single lines
  $a_lines = preg_split('/\r?\n/', $body);
  $q_lines = array();
  $quote_level = 0;

  // find/mark quoted lines...
  for ($n=0, $cnt=count($a_lines); $n < $cnt; $n++) {
    $q = 0;

    if ($a_lines[$n][0] == '>' && preg_match('/^(>+\s*)+/', $a_lines[$n], $regs)) {
      $q = strlen(preg_replace('/\s/', '', $regs[0]));
        $a_lines[$n] = substr($a_lines[$n], strlen($regs[0]));

      if ($q > $quote_level)
        $q_lines[$n]['quote'] = $q - $quote_level;
      else if ($q < $quote_level)
        $q_lines[$n]['endquote'] = $quote_level - $q;
    }
    else if ($quote_level > 0)
      $q_lines[$n]['endquote'] = $quote_level;

    $quote_level = $q;
  }

  // quote plain text
  $body = Q(join("\n", $a_lines), 'replace', false);

  // colorize signature
  if (($sp = strrpos($body, '-- ')) !== false)
    if (($sp == 0 || $body[$sp-1] == "\n") && $body[$sp+3] == "\n") {
      $body = substr($body, 0, max(0, $sp))
	.'<span class="sig">'.substr($body, $sp).'</span>';
    }

  // colorize quoted lines
  $a_lines = preg_split('/\n/', $body);
  foreach ($q_lines as $i => $q)
    if ($q['quote'])
      $a_lines[$i] = str_repeat('<blockquote>', $q['quote']) . $a_lines[$i];
    else if ($q['endquote'])
      $a_lines[$i] = str_repeat('</blockquote>', $q['endquote']) . $a_lines[$i];

  // insert the links for urls and mailtos
  $body = $replacements->resolve(join("\n", $a_lines));
    
  return $body;
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
 * Callback function for washtml cleaning class
 */
function rcmail_washtml_callback($tagname, $attrib, $content)
{
  switch ($tagname) {
    case 'form':
      $out = html::div('form', $content);
      break;
      
    case 'style':
      // decode all escaped entities and reduce to ascii strings
      $stripped = preg_replace('/[^a-zA-Z\(:]/', '', rcmail_xss_entity_decode($content));
      
      // now check for evil strings like expression, behavior or url()
      if (!preg_match('/expression|behavior|url\(|import/', $stripped)) {
        $out = html::tag('style', array('type' => 'text/css'), $content);
        break;
      }
    
    default:
      $out = '';
  }
  
  return $out;
}


/**
 * Callback function for HTML tags fixing
 */
function rcmail_html_tag_callback($matches)
{
  $tagname = $matches[2];

  $tagname = preg_replace(array(
    '/:.*$/',			// Microsoft's Smart Tags <st1:xxxx>
    '/[^a-z0-9_\[\]\!-]/i',	// forbidden characters
    ), '', $tagname);

  return $matches[1].$tagname;
}


/**
 * return table with message headers
 */
function rcmail_message_headers($attrib, $headers=NULL)
  {
  global $IMAP, $OUTPUT, $MESSAGE, $PRINT_MODE, $RCMAIL;
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

  // show these headers
  $standard_headers = array('subject', 'from', 'to', 'cc', 'bcc', 'replyto', 'date');
  $output_headers = array();

  foreach ($standard_headers as $hkey) {
    if (!$headers[$hkey])
      continue;

    if ($hkey == 'date') {
      if ($PRINT_MODE)
        $header_value = format_date($headers[$hkey], $RCMAIL->config->get('date_long', 'x'));
      else
        $header_value = format_date($headers[$hkey]);
    }
    else if ($hkey == 'replyto') {
      if ($headers['replyto'] != $headers['from'])
        $header_value = rcmail_address_string($headers['replyto'], null, true, $attrib['addicon']);
      else
        continue;
    }
    else if (in_array($hkey, array('from', 'to', 'cc', 'bcc')))
      $header_value = rcmail_address_string($headers[$hkey], null, true, $attrib['addicon']);
    else if ($hkey == 'subject' && empty($headers[$hkey]))
      $header_value = rcube_label('nosubject');
    else
      $header_value = trim($IMAP->decode_header($headers[$hkey]));
      
    $output_headers[$hkey] = array('title' => rcube_label($hkey), 'value' => $header_value, 'raw' => $headers[$hkey]);
  }
    
  $plugin = $RCMAIL->plugins->exec_hook('message_headers_output', array('output' => $output_headers, 'headers' => $MESSAGE->headers));
  
  // compose html table
  $table = new html_table(array('cols' => 2));
  
  foreach ($plugin['output'] as $hkey => $row) {
    $table->add(array('class' => 'header-title'), Q($row['title']));
    $table->add(array('class' => $hkey, 'width' => "90%"), Q($row['value'], ($hkey == 'subject' ? 'strict' : 'show')));
  }

  // all headers division
  $table->add(array('colspan' => 2, 'class' => "more-headers show-headers", 'onclick' => "return ".JS_OBJECT_NAME.".command('load-headers','',this)"), '');
  $table->add_row(array('id' => "all-headers"));
  $table->add(array('colspan' => 2, 'class' => "all"), html::div(array('id' => 'headers-source'), ''));
  
  $OUTPUT->add_gui_object('all_headers_row', 'all-headers');
  $OUTPUT->add_gui_object('all_headers_box', 'headers-source');

  return $table->show($attrib);
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
      else if ($part->type == 'content' && $part->size)
        {
        if (empty($part->ctype_parameters) || empty($part->ctype_parameters['charset']))
          $part->ctype_parameters['charset'] = $MESSAGE->headers->charset;

        // fetch part if not available
        if (!isset($part->body))
          $part->body = $MESSAGE->get_part_content($part->mime_id);

        $body = rcmail_print_body($part, array('safe' => $safe_mode, 'plain' => !$CONFIG['prefer_html']));

        if ($part->ctype_secondary == 'html')
          $out .= html::div('message-htmlpart', rcmail_html4inline($body, $attrib['id']));
        else
          $out .= html::div('message-part', $body);
        }
      }
    }
  else
    $out .= html::div('message-part', html::tag('pre', array(),
      rcmail_plain_body(Q($MESSAGE->body, 'strict', false))));

  $ctype_primary = strtolower($MESSAGE->structure->ctype_primary);
  $ctype_secondary = strtolower($MESSAGE->structure->ctype_secondary);

  // list images after mail body
  if ($CONFIG['inline_images']
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
 * Convert all relative URLs according to a <base> in HTML
 */
function rcmail_resolve_base($body)
{
  // check for <base href=...>
  if (preg_match('!(<base.*href=["\']?)([hftps]{3,5}://[a-z0-9/.%-]+)!i', $body, $regs)) {
    $replacer = new rcube_base_replacer($regs[2]);

    // replace all relative paths
    $body = preg_replace_callback('/(src|background|href)=(["\']?)([\.\/]+[^"\'\s]+)(\2|\s|>)/Ui', array($replacer, 'callback'), $body);
    $body = preg_replace_callback('/(url\s*\()(["\']?)([\.\/]+[^"\'\)\s]+)(\2)\)/Ui', array($replacer, 'callback'), $body);
  }

  return $body;
}

/**
 * modify a HTML message that it can be displayed inside a HTML page
 */
function rcmail_html4inline($body, $container_id)
  {
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

  // modify HTML links to open a new window if clicked
  $GLOBALS['rcmail_html_container_id'] = $container_id;
  $body = preg_replace_callback('/<(a|link)\s+([^>]+)>/Ui', 'rcmail_alter_html_link', $body);
  unset($GLOBALS['rcmail_html_container_id']);

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
function rcmail_alter_html_link($matches)
{
  global $EMAIL_ADDRESS_PATTERN;
  
  $tag = $matches[1];
  $attrib = parse_attrib_string($matches[2]);
  $end = '>';

  if ($tag == 'link' && preg_match('/^https?:\/\//i', $attrib['href'])) {
    $attrib['href'] = "./bin/modcss.php?u=" . urlencode($attrib['href']) . "&amp;c=" . urlencode($GLOBALS['rcmail_html_container_id']);
    $end = ' />';
  }
  else if (preg_match('/^mailto:'.$EMAIL_ADDRESS_PATTERN.'(\?[^"\'>]+)?/i', $attrib['href'], $mailto)) {
    $attrib['href'] = $mailto[0];
    $attrib['onclick'] = sprintf(
      "return %s.command('compose','%s',this)",
      JS_OBJECT_NAME,
      JQ($mailto[1].$mailto[2]));
  }
  else if (!empty($attrib['href']) && $attrib['href'][0] != '#') {
    $attrib['target'] = '_blank';
  }

  return "<$tag" . html::attrib_string($attrib, array('href','name','target','onclick','id','class','style','title','rel','type','media')) . $end;
}


/**
 * decode address string and re-format it as HTML links
 */
function rcmail_address_string($input, $max=null, $linked=false, $addicon=null)
{
  global $IMAP, $PRINT_MODE, $CONFIG, $OUTPUT, $EMAIL_ADDRESS_PATTERN;

  $a_parts = $IMAP->decode_address_list($input);

  if (!sizeof($a_parts))
    return $input;

  $c = count($a_parts);
  $j = 0;
  $out = '';

  foreach ($a_parts as $part) {
    $j++;
    if ($PRINT_MODE) {
      $out .= sprintf('%s &lt;%s&gt;', Q($part['name']), $part['mailto']);
    }
    else if (preg_match("/$EMAIL_ADDRESS_PATTERN/i", $part['mailto'])) {
      if ($linked) {
        $out .= html::a(array(
            'href' => 'mailto:'.$part['mailto'],
            'onclick' => sprintf("return %s.command('compose','%s',this)", JS_OBJECT_NAME, JQ($part['mailto'])),
            'title' => $part['mailto'],
            'class' => "rcmContactAddress",
          ),
        Q($part['name']));
      }
      else {
        $out .= html::span(array('title' => $part['mailto'], 'class' => "rcmContactAddress"), Q($part['name']));
      }

      if ($addicon) {
        $out .= '&nbsp;' . html::a(array(
            'href' => "#add",
            'onclick' => sprintf("return %s.command('add-contact','%s',this)", JS_OBJECT_NAME, urlencode($part['string'])),
            'title' => rcube_label('addtoaddressbook'),
          ),
          html::img(array(
            'src' => $CONFIG['skin_path'] . $addicon,
            'alt' => "Add contact",
          )));
      }
    }
    else {
      if ($part['name'])
        $out .= Q($part['name']);
      if ($part['mailto'])
        $out .= (strlen($out) ? ' ' : '') . sprintf('&lt;%s&gt;', Q($part['mailto']));
    }
      
    if ($c>$j)
      $out .= ','.($max ? '&nbsp;' : ' ');
        
    if ($max && $j==$max && $c>$j) {
      $out .= '...';
      break;
    }
  }
    
  return $out;
}


/**
 * Wrap text to a given number of characters per line
 * but respect the mail quotation of replies messages (>)
 *
 * @param string Text to wrap
 * @param int The line width
 * @return string The wrapped text
 */
function rcmail_wrap_quoted($text, $max = 76)
{
  // Rebuild the message body with a maximum of $max chars, while keeping quoted message.
  $lines = preg_split('/\r?\n/', trim($text));
  $out = '';

  foreach ($lines as $line) {
    if (strlen($line) > $max) {
      if (preg_match('/^([>\s]+)/', $line, $regs)) {
        $length = strlen($regs[0]);
        $prefix = substr($line, 0, $length);

        // Remove '> ' from the line, then wordwrap() the line
        $line = rc_wordwrap(substr($line, $length), $max - $length);

        // Rebuild the line with '> ' at the beginning of each 'subline'
        $newline = '';
        foreach (explode("\n", $line) as $l) {
          $newline .= $prefix . $l . "\n";
        }

        // Remove the righest newline char
        $line = rtrim($newline);
      }
      else {
        $line = rc_wordwrap($line, $max);
      }
    }

    // Append the line
    $out .= $line . "\n";
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

  $attrib['src'] = './?' . str_replace('_frame=', ($ctype_primary=='text' ? '_show=' : '_preload='), $_SERVER['QUERY_STRING']);

  return html::iframe($attrib);
  }


/**
 * clear message composing settings
 */
function rcmail_compose_cleanup()
  {
  if (!isset($_SESSION['compose']))
    return;

  rcmail::get_instance()->plugins->exec_hook('cleanup_attachments',array());
  
  rcube_sess_unset('compose');
  }
  

/**
 * Send the given message using the configured method
 *
 * @param object $message    Reference to Mail_MIME object
 * @param string $from       Sender address string
 * @param array  $mailto     Array of recipient address strings
 * @param array  $smtp_error SMTP error array (reference)
 * @param string $body_file  Location of file with saved message body (reference)
 *
 * @return boolean Send status.
 */
function rcmail_deliver_message(&$message, $from, $mailto, &$smtp_error, &$body_file)
{
  global $CONFIG, $RCMAIL;

  $headers = $message->headers();

  // send thru SMTP server using custom SMTP library
  if ($CONFIG['smtp_server']) {
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

    $smtp_headers = $message->txtHeaders($send_headers, true);

    if ($message->getParam('delay_file_io')) {
      // use common temp dir
      $temp_dir = $RCMAIL->config->get('temp_dir');
      $body_file = tempnam($temp_dir, 'rcmMsg');
      if (PEAR::isError($mime_result = $message->saveMessageBody($body_file))) {
        raise_error(array('code' => 600, 'type' => 'php',
            'file' => __FILE__, 'line' => __LINE__,
            'message' => "Could not create message: ".$mime_result->getMessage()),
            TRUE, FALSE);
        return false;
      }
      $msg_body = fopen($body_file, 'r');
    } else {
      $msg_body = $message->get();
    }

    // send message
    if (!is_object($RCMAIL->smtp))
      $RCMAIL->smtp_init(true);
     
    $sent = $RCMAIL->smtp->send_mail($from, $a_recipients, $smtp_headers, $msg_body);
    $smtp_response = $RCMAIL->smtp->get_response();
    $smtp_error = $RCMAIL->smtp->get_error();

    if (is_resource($msg_body)) {
      fclose($msg_body);
    }

    // log error
    if (!$sent)
      raise_error(array('code' => 800, 'type' => 'smtp', 'line' => __LINE__, 'file' => __FILE__,
                        'message' => "SMTP error: ".join("\n", $smtp_response)), TRUE, FALSE);
  }
  // send mail using PHP's mail() function
  else {
    // unset some headers because they will be added by the mail() function
    $headers_enc = $message->headers($headers);
    $headers_php = $message->_headers;
    unset($headers_php['To'], $headers_php['Subject']);
    
    // reset stored headers and overwrite
    $message->_headers = array();
    $header_str = $message->txtHeaders($headers_php);
    
    // #1485779
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      if (preg_match_all('/<([^@]+@[^>]+)>/', $headers_enc['To'], $m)) {
        $headers_enc['To'] = implode(', ', $m[1]);
        }
      }
    
    $msg_body = $message->get();

    if (PEAR::isError($msg_body))
      raise_error(array('code' => 600, 'type' => 'php',
            'file' => __FILE__, 'line' => __LINE__,
            'message' => "Could not create message: ".$msg_body->getMessage()),
            TRUE, FALSE);
    else if (ini_get('safe_mode'))
      $sent = mail($headers_enc['To'], $headers_enc['Subject'], $msg_body, $header_str);
    else
      $sent = mail($headers_enc['To'], $headers_enc['Subject'], $msg_body, $header_str, "-f$from");
  }
  
  if ($sent) {
    $RCMAIL->plugins->exec_hook('message_sent', array('headers' => $headers, 'body' => $msg_body));
    
    // remove MDN headers after sending
    unset($headers['Return-Receipt-To'], $headers['Disposition-Notification-To']);
    
    if ($CONFIG['smtp_log']) {
      write_log('sendmail', sprintf("User %s [%s]; Message for %s; %s",
        $RCMAIL->user->get_username(),
        $_SERVER['REMOTE_ADDR'],
        $mailto,
        !empty($smtp_response) ? join('; ', $smtp_response) : ''));
    }
  }

  $message->_headers = array();
  $message->headers($headers);
  
  return $sent;
}


function rcmail_send_mdn($uid, &$smtp_error)
{
  global $RCMAIL, $IMAP;

  $message = new rcube_message($uid);
  
  if ($message->headers->mdn_to && !$message->headers->mdn_sent &&
    ($IMAP->check_permflag('MDNSENT') || $IMAP->check_permflag('*')))
  {
    $identity = $RCMAIL->user->get_identity();
    $sender = format_email_recipient($identity['email'], $identity['name']);
    $recipient = array_shift($IMAP->decode_address_list($message->headers->mdn_to));
    $mailto = $recipient['mailto'];

    $compose = new Mail_mime($RCMAIL->config->header_delimiter());

    $compose->setParam('text_encoding', 'quoted-printable');
    $compose->setParam('html_encoding', 'quoted-printable');
    $compose->setParam('head_encoding', 'quoted-printable');
    $compose->setParam('head_charset', RCMAIL_CHARSET);
    $compose->setParam('html_charset', RCMAIL_CHARSET);
    $compose->setParam('text_charset', RCMAIL_CHARSET);
    
    // compose headers array
    $headers = array(
      'Date' => date('r'),
      'From' => $sender,
      'To'   => $message->headers->mdn_to,
      'Subject' => rcube_label('receiptread') . ': ' . $message->subject,
      'Message-ID' => sprintf('<%s@%s>', md5(uniqid('rcmail'.mt_rand(),true)), $RCMAIL->config->mail_domain($_SESSION['imap_host'])),
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
    $compose->setTXTBody(rc_wordwrap($body, 75, "\r\n"));
    $compose->addAttachment($report, 'message/disposition-notification', 'MDNPart2.txt', false, '7bit', 'inline');

    $sent = rcmail_deliver_message($compose, $identity['email'], $mailto, $smtp_error, $body_file);

    if ($sent)
    {
      $IMAP->set_flag($message->uid, 'MDNSENT');
      return true;
    }
  }
  
  return false;
}


function rcmail_search_filter($attrib)
{
  global $OUTPUT, $CONFIG;

  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcmlistfilter';

  $attrib['onchange'] = JS_OBJECT_NAME.'.filter_mailbox(this.value)';
  
  /*
    RFC3501 (6.4.4): 'ALL', 'RECENT', 
    'ANSWERED', 'DELETED', 'FLAGGED', 'SEEN',
    'UNANSWERED', 'UNDELETED', 'UNFLAGGED', 'UNSEEN',
    'NEW', // = (RECENT UNSEEN)
    'OLD' // = NOT RECENT
  */

  $select_filter = new html_select($attrib);
  $select_filter->add(rcube_label('all'), 'ALL');
  $select_filter->add(rcube_label('unread'), 'UNSEEN');
  $select_filter->add(rcube_label('flagged'), 'FLAGGED');
  $select_filter->add(rcube_label('unanswered'), 'UNANSWERED');
  if (!$CONFIG['skip_deleted'])
    $select_filter->add(rcube_label('deleted'), 'DELETED');

  $out = $select_filter->show($_SESSION['search_filter']);

  $OUTPUT->add_gui_object('search_filter', $attrib['id']);

  return $out;										
}

// register UI objects
$OUTPUT->add_handlers(array(
  'mailboxlist' => 'rcmail_mailbox_list',
  'messages' => 'rcmail_message_list',
  'messagecountdisplay' => 'rcmail_messagecount_display',
  'quotadisplay' => 'rcmail_quota_display',
  'mailboxname' => 'rcmail_mailbox_name_display',
  'messageheaders' => 'rcmail_message_headers',
  'messagebody' => 'rcmail_message_body',
  'messagecontentframe' => 'rcmail_messagecontent_frame',
  'messagepartframe' => 'rcmail_message_part_frame',
  'messagepartcontrols' => 'rcmail_message_part_controls',
  'searchfilter' => 'rcmail_search_filter',
  'searchform' => array($OUTPUT, 'search_form'),
));

?>
