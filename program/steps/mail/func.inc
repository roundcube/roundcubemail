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

require_once('lib/enriched.inc');
require_once('include/rcube_smtp.inc');

$EMAIL_ADDRESS_PATTERN = '/([a-z0-9][a-z0-9\-\.\+\_]*@[a-z0-9]([a-z0-9\-][.]?)*[a-z0-9]\\.[a-z]{2,5})/i';

// actions that do not require imap connection
$NOIMAP_ACTIONS = array('spell', 'addcontact', 'autocomplete', 'upload', 'display-attachment', 'remove-attachment');


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
    
      // make sure the message count is refreshed (for default view)
      $IMAP->messagecount($mbox_name, 'ALL', true);
    }
	
  // set current mailbox in client environment
  $OUTPUT->set_env('mailbox', $mbox_name);
  $OUTPUT->set_env('quota', $IMAP->get_capability('quota'));
  $OUTPUT->set_env('delimiter', $IMAP->get_hierarchy_delimiter());

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
  
  // show 'to' instead of from in sent messages
  if (($mbox==$CONFIG['sent_mbox'] || $mbox==$CONFIG['drafts_mbox']) && ($f = array_search('from', $a_show_cols))
      && !array_search('to', $a_show_cols))
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


    if (!empty($header->charset))
      $IMAP->set_charset($header->charset);
  
    // format each col
    foreach ($a_show_cols as $col)
      {
      if ($col=='from' || $col=='to')
        $cont = Q(rcmail_address_string($header->$col, 3, false, $attrib['addicon']), 'show');
      else if ($col=='subject')
        {
        $action = $mbox==$CONFIG['drafts_mbox'] ? 'compose' : 'show';
        $uid_param = $mbox==$CONFIG['drafts_mbox'] ? '_draft_uid' : '_uid';
        $cont = abbreviate_string(trim($IMAP->decode_header($header->$col)), 160);
        if (empty($cont)) $cont = rcube_label('nosubject');
        $cont = sprintf('<a href="%s" onclick="return rcube_event.cancel(event)">%s</a>', Q(rcmail_url($action, array($uid_param=>$header->uid, '_mbox'=>$mbox))), Q($cont));
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
 */
function rcmail_js_message_list($a_headers, $insert_top=FALSE)
  {
  global $CONFIG, $IMAP, $OUTPUT;

  if (empty($_SESSION['list_columns']))
    $a_show_cols = is_array($CONFIG['list_cols']) ? $CONFIG['list_cols'] : array('subject');
  else
    $a_show_cols = $_SESSION['list_columns'];

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

    // remove 'attachment' and 'flag' columns, we don't need them here
    if(($key = array_search('attachment', $a_show_cols)) !== FALSE)
      unset($a_show_cols[$key]);
    if(($key = array_search('flag', $a_show_cols)) !== FALSE)
      unset($a_show_cols[$key]);

    // format each col; similar as in rcmail_message_list()
    foreach ($a_show_cols as $col)
      {
      if ($col=='from' || $col=='to')
        $cont = Q(rcmail_address_string($header->$col, 3), 'show');
      else if ($col=='subject')
        {
        $action = $mbox==$CONFIG['drafts_mbox'] ? 'compose' : 'show';
        $uid_param = $mbox==$CONFIG['drafts_mbox'] ? '_draft_uid' : '_uid';
        $cont = abbreviate_string(trim($IMAP->decode_header($header->$col)), 160);
        if (!$cont) $cont = rcube_label('nosubject');
        $cont = sprintf('<a href="%s" onclick="return rcube_event.cancel(event)">%s</a>', Q(rcmail_url($action, array($uid_param=>$header->uid, '_mbox'=>$mbox))), Q($cont));
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
    $a_msg_flags['forwarded'] = $header->forwarded ? 1 : 0;
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

  $attrib['name'] = $attrib['id'];

  $OUTPUT->set_env('contentframe', $attrib['id']);
  $OUTPUT->set_env('blankpage', $attrib['src'] ? $OUTPUT->abs_url($attrib['src']) : 'program/blank.gif');

  return html::iframe($attrib);
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

  return html::span($attrib, rcmail_get_messagecount_text());
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

  return html::span($attrib, rcmail_quota_content(NULL, $attrib));
  }


/**
 *
 */
function rcmail_quota_content($quota=NULL, $attrib=NULL)
  {
  global $IMAP, $COMM_PATH, $RCMAIL;

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

  if ($quota && !($quota['total']==0 && $RCMAIL->config->get('quota_zero_as_unlimited')))
    {
    $quota_text = sprintf('%s / %s (%.0f%%)',
                          show_bytes($quota['used'] * 1024),
                          show_bytes($quota['total'] * 1024),
                          $quota['percent']);

    // show quota as image (by Brett Patterson)
    if ($display == 'image' && function_exists('imagegif'))
      {
      if (!$attrib['width'])
        $attrib['width'] = isset($_SESSION['quota_width']) ? $_SESSION['quota_width'] : 100;
      else
	$_SESSION['quota_width'] = $attrib['width'];

      if (!$attrib['height'])
        $attrib['height'] = isset($_SESSION['quota_height']) ? $_SESSION['quota_height'] : 14;
      else
	$_SESSION['quota_height'] = $attrib['height'];
	    
      $quota_text = sprintf('<img src="./bin/quotaimg.php?u=%s&amp;q=%d&amp;w=%d&amp;h=%d" width="%d" height="%d" alt="%s" title="%s / %s" />',
                            $quota['used'], $quota['total'],
                            $attrib['width'], $attrib['height'],
                            $attrib['width'], $attrib['height'],
                            $quota_text,
                            show_bytes($quota['used'] * 1024),
                            show_bytes($quota['total'] * 1024));
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
 *
 */
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


/**
 * Convert the given message part to proper HTML
 * which can be displayed the message view
 *
 * @param object rcube_message_part Message part
 * @param bool  True if external objects (ie. images ) are allowed
 * @param bool  True if part should be converted to plaintext
 * @return string Formatted HTML string
 */
function rcmail_print_body($part, $p = array())
{
  global $REMOTE_OBJECTS;
  
  $p += array('safe' => false, 'plain' => false, 'inline_html' => true);
  
  // convert html to text/plain
  if ($part->ctype_secondary == 'html' && $p['plain']) {
    $txt = new html2text($part->body, false, true);
    $body = $txt->get_text();
    $part->ctype_secondary = 'plain';
  }
  // text/html
  else if ($part->ctype_secondary == 'html') {
    $html = $part->body;

    // special replacements (not properly handled by washtml class)
    $html_search = array(
      '/(<\/nobr>)(\s+)(<nobr>)/i',	// space(s) between <NOBR>
      '/(<[\/]*st1:[^>]+>)/i',		// Microsoft's Smart Tags <ST1>
      '/<\/?rte_text>/i',		// Rich Text Editor tags (#1485647)
      '/<title>.*<\/title>/i',		// PHP bug #32547 workaround: remove title tag
      '/<html[^>]*>/im',		// malformed html: remove html tags (#1485139)
      '/<\/html>/i',			// malformed html: remove html tags (#1485139)
      '/^[\xFE\xFF\xBB\xBF\x00]+((?:<\!doctype|\<html))/im',	// remove byte-order mark (only outlook?)
    );
    $html_replace = array(
      '\\1'.' &nbsp; '.'\\3',
      '',
      '',
      '',
      '',
      '',
      '\\1',
    );
    $html = preg_replace($html_search, $html_replace, $html);

    // charset was converted to UTF-8 in rcube_imap::get_message_part() -> change charset specification in HTML accordingly
    $charset_pattern = '/(\s+content=[\'"]?\w+\/\w+;\s*charset)=([a-z0-9-_]+)/i';
    if (preg_match($charset_pattern, $html)) {
      $html = preg_replace($charset_pattern, '\\1='.RCMAIL_CHARSET, $html);
    }
    else {
      // add head for malformed messages, washtml cannot work without that
      if (!preg_match('/<head[^>]*>(.*)<\/head>/Uims', $html))
        $html = '<head></head>'. $html;
      $html = substr_replace($html, '<meta http-equiv="content-type" content="text/html; charset='.RCMAIL_CHARSET.'" />', intval(stripos($html, '<head>')+6), 0);
    }
    
    // turn relative into absolute urls
    $html = rcmail_resolve_base($html);

    // clean HTML with washhtml by Frederic Motte
    $wash_opts = array(
      'show_washed' => false,
      'allow_remote' => $p['safe'],
      'blocked_src' => "./program/blocked.gif",
      'charset' => RCMAIL_CHARSET,
      'cid_map' => $part->replaces,
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
    
    if ($p['safe']) {  // allow CSS styles, will be sanitized by rcmail_washtml_callback()
      $washer->add_callback('style', 'rcmail_washtml_callback');
    }
    
    $body = $washer->wash($html);
    $REMOTE_OBJECTS = $washer->extlinks;

    return $body;
  }
  // text/enriched
  else if ($part->ctype_secondary=='enriched') {
    $part->ctype_secondary = 'html';
    return Q(enriched_to_html($part->body), 'show');
  }
  else
    $body = $part->body;


  /**** assert plaintext ****/

  // make links and email-addresses clickable
  $replacements = new rcube_string_replacer;
  
  $url_chars = 'a-z0-9_\-\+\*\$\/&%=@#:;';
  $url_chars_within = '\?\.~,!';
  
  // search for patterns like links and e-mail addresses
  $body = preg_replace_callback("/([\w]+):\/\/([a-z0-9\-\.]+[a-z]{2,4}([$url_chars$url_chars_within]*[$url_chars])?)/i", array($replacements, 'link_callback'), $body);
  $body = preg_replace_callback("/([^\/:]|\s)(www\.)([a-z0-9\-]{2,}[a-z]{2,4}([$url_chars$url_chars_within]*[$url_chars])?)/i", array($replacements, 'link_callback'), $body);
  $body = preg_replace_callback('/([a-z0-9][a-z0-9\-\.\+\_]*@[a-z0-9]([a-z0-9\-][.]?)*[a-z0-9]\\.[a-z]{2,5})/i', array($replacements, 'mailto_callback'), $body);

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
  $body = $replacements->resolve(join("\n", $a_lines));

  return html::tag('pre', array(), $body);
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
      $stripped = preg_replace('/[^a-zA-Z\(:]/', '', rcmail_xss_entitiy_decode($content));
      
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
 * return table with message headers
 */
function rcmail_message_headers($attrib, $headers=NULL)
  {
  global $IMAP, $OUTPUT, $MESSAGE, $PRINT_MODE, $CONFIG;
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
    
  $header_count = 0;
  
  // allow the following attributes to be added to the <table> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'cellpadding', 'cellspacing', 'border', 'summary'));
  $out = '<table' . $attrib_str . ">\n";

  // show these headers
  $standard_headers = array('subject', 'from', 'to', 'cc', 'bcc', 'replyto', 'date');

  foreach ($standard_headers as $hkey)
    {
    if (!$headers[$hkey])
      continue;

    if ($hkey == 'date')
      {
      if ($PRINT_MODE)
        $header_value = format_date($headers[$hkey], $CONFIG['date_long'] ? $CONFIG['date_long'] : 'x');
      else
        $header_value = format_date($headers[$hkey]);
      }
    else if ($hkey == 'replyto')
      {
      if ($headers['replyto'] != $headers['from'])
        $header_value = Q(rcmail_address_string($headers['replyto'], null, true, $attrib['addicon']), 'show');
      else
        continue;
      }
    else if (in_array($hkey, array('from', 'to', 'cc', 'bcc')))
      $header_value = Q(rcmail_address_string($headers[$hkey], null, true, $attrib['addicon']), 'show');
    else if ($hkey == 'subject' && empty($headers[$hkey]))
      $header_value = Q(rcube_label('nosubject'));
    else
      $header_value = Q(trim($IMAP->decode_header($headers[$hkey])));

    $out .= "\n<tr>\n";
    $out .= '<td class="header-title">'.Q(rcube_label($hkey)).":&nbsp;</td>\n";
    $out .= '<td class="'.$hkey.'" width="90%">'.$header_value."</td>\n</tr>";
    $header_count++;
    }

  // all headers division
  $out .= "\n".'<tr><td colspan="2" class="more-headers show-headers"
	onclick="return '.JS_OBJECT_NAME.'.command(\'load-headers\', \'\', this)"></td></tr>';
  $out .= "\n".'<tr id="all-headers"><td colspan="2" class="all"><div id="headers-source"></div></td></tr>';

  $OUTPUT->add_gui_object('all_headers_row', 'all-headers');
  $OUTPUT->add_gui_object('all_headers_box', 'headers-source');

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

        $body = rcmail_print_body($part, array('safe' => $safe_mode, 'plain' => !$CONFIG['prefer_html']));

        if ($part->ctype_secondary == 'html')
          $out .= html::div('message-htmlpart', rcmail_html4inline($body, $attrib['id']));
        else
          $out .= html::div('message-part', $body);
        }
      }
    }
  else
    $out .= html::div('message-part', html::tag('pre', array(), Q($MESSAGE->body)));


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
  $end = '>';

  if ($tag == 'link' && preg_match('/^https?:\/\//i', $attrib['href'])) {
    $attrib['href'] = "./bin/modcss.php?u=" . urlencode($attrib['href']) . "&amp;c=" . urlencode($container_id);
    $end = ' />';
  }
  else if (stristr((string)$attrib['href'], 'mailto:')) {
    $attrib['onclick'] = sprintf(
      "return %s.command('compose','%s',this)",
      JS_OBJECT_NAME,
      JQ(substr($attrib['href'], 7)));
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
    else if (preg_match($EMAIL_ADDRESS_PATTERN, $part['mailto'])) {
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
        $line = wordwrap(substr($line, $length), $max - $length);

        // Rebuild the line with '> ' at the beginning of each 'subline'
        $newline = '';
        foreach (explode("\n", $line) as $l) {
          $newline .= $prefix . $l . "\n";
        }

        // Remove the righest newline char
        $line = rtrim($newline);
      }
      else {
        $line = wordwrap($line, $max);
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
  global $CONFIG, $RCMAIL;

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
  
  if ($sent)
  {
    // remove MDN headers after sending
    unset($headers['Return-Receipt-To'], $headers['Disposition-Notification-To']);
    
    if ($CONFIG['smtp_log'])
      write_log('sendmail', sprintf("User %s [%s]; Message for %s; %s",
		$RCMAIL->user->get_username(),
		$_SERVER['REMOTE_ADDR'],
		$mailto,
		!empty($smtp_response) ? join('; ', $smtp_response) : ''));
  }
  
  $message->_headers = array();
  $message->headers($headers);
  
  return $sent;
}


function rcmail_send_mdn($uid)
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


function rcmail_search_filter($attrib)
{
  global $OUTPUT;

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
