<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/func.inc                                           |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, The Roundcube Dev Team                       |
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

// setup some global vars used by mail steps
$SENT_MBOX = $RCMAIL->config->get('sent_mbox');
$DRAFTS_MBOX = $RCMAIL->config->get('drafts_mbox');
$SEARCH_MODS_DEFAULT = array('*' => array('subject'=>1, 'from'=>1), $SENT_MBOX => array('subject'=>1, 'to'=>1), $DRAFTS_MBOX => array('subject'=>1, 'to'=>1));

// Simplified for IDN in Unicode
//$EMAIL_ADDRESS_PATTERN = '([a-z0-9][a-z0-9\-\.\+\_]*@[a-z0-9][a-z0-9\-\.]*\\.[a-z]{2,5})';
$EMAIL_ADDRESS_PATTERN = '([a-z0-9][a-z0-9\-\.\+\_]*@[^&@"\'.][^@&"\']*\\.[a-z]{2,5})';

// actions that do not require imap connection here
$NOIMAP_ACTIONS = array('addcontact', 'autocomplete', 'upload', 'display-attachment', 'remove-attachment', 'get');

// always instantiate imap object (but not yet connect to server)
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
if (strlen(trim($mbox = get_input_value('_mbox', RCUBE_INPUT_GPC, true))))
  $IMAP->set_mailbox(($_SESSION['mbox'] = $mbox));
else if ($IMAP)
  $_SESSION['mbox'] = $IMAP->get_mailbox_name();

if (!empty($_GET['_page']))
  $IMAP->set_page(($_SESSION['page'] = intval($_GET['_page'])));

// set default sort col/order to session
if (!isset($_SESSION['sort_col']))
  $_SESSION['sort_col'] = !empty($CONFIG['message_sort_col']) ? $CONFIG['message_sort_col'] : '';
if (!isset($_SESSION['sort_order']))
  $_SESSION['sort_order'] = strtoupper($CONFIG['message_sort_order']) == 'ASC' ? 'ASC' : 'DESC';

// set threads mode
$a_threading = $RCMAIL->config->get('message_threading', array());
if (isset($_GET['_threads'])) {
  if ($_GET['_threads'])
    $a_threading[$_SESSION['mbox']] = true;
  else
    unset($a_threading[$_SESSION['mbox']]);
  $RCMAIL->user->save_prefs(array('message_threading' => $a_threading));
}
$IMAP->set_threading($a_threading[$_SESSION['mbox']]);

// set message set for search result
if (!empty($_REQUEST['_search']) && isset($_SESSION['search'])
    && $_SESSION['search_request'] == $_REQUEST['_search']
) {
  $IMAP->set_search_set($_SESSION['search']);
  $OUTPUT->set_env('search_request', $_REQUEST['_search']);
  $OUTPUT->set_env('search_text', $_SESSION['last_text_search']);
}

// set main env variables, labels and page title
if (empty($RCMAIL->action) || $RCMAIL->action == 'list') {
  $mbox_name = $IMAP->get_mailbox_name();

  if (empty($RCMAIL->action)) {
    // initialize searching result if search_filter is used
    if ($_SESSION['search_filter'] && $_SESSION['search_filter'] != 'ALL') {
      $search_request = md5($mbox_name.$_SESSION['search_filter']);

      $IMAP->search($mbox_name, $_SESSION['search_filter'], RCMAIL_CHARSET, $_SESSION['sort_col']);
      $_SESSION['search'] = $IMAP->get_search_set();
      $_SESSION['search_request'] = $search_request;
      $OUTPUT->set_env('search_request', $search_request);
      }

      $search_mods = $RCMAIL->config->get('search_mods', $SEARCH_MODS_DEFAULT);
      $OUTPUT->set_env('search_mods', $search_mods);
  }

  // set current mailbox and some other vars in client environment
  $OUTPUT->set_env('mailbox', $mbox_name);
  $OUTPUT->set_env('pagesize', $IMAP->page_size);
  $OUTPUT->set_env('quota', $IMAP->get_capability('QUOTA'));
  $OUTPUT->set_env('delimiter', $IMAP->get_hierarchy_delimiter());
  $OUTPUT->set_env('threading', (bool) $IMAP->threading);
  $OUTPUT->set_env('threads', $IMAP->threading || $IMAP->get_capability('THREAD'));

  if ($CONFIG['flag_for_deletion'])
    $OUTPUT->set_env('flag_for_deletion', true);
  if ($CONFIG['read_when_deleted'])
    $OUTPUT->set_env('read_when_deleted', true);
  if ($CONFIG['skip_deleted'])
    $OUTPUT->set_env('skip_deleted', true);
  if ($CONFIG['display_next'])
    $OUTPUT->set_env('display_next', true);

  $OUTPUT->set_env('preview_pane_mark_read', $RCMAIL->config->get('preview_pane_mark_read', 0));

  if ($CONFIG['trash_mbox'])
    $OUTPUT->set_env('trash_mailbox', $CONFIG['trash_mbox']);
  if ($CONFIG['drafts_mbox'])
    $OUTPUT->set_env('drafts_mailbox', $CONFIG['drafts_mbox']);
  if ($CONFIG['junk_mbox'])
    $OUTPUT->set_env('junk_mailbox', $CONFIG['junk_mbox']);

  if (!$OUTPUT->ajax_call)
    $OUTPUT->add_label('checkingmail', 'deletemessage', 'movemessagetotrash',
      'movingmessage', 'copyingmessage', 'deletingmessage', 'markingmessage',
      'copy', 'move', 'quota');

  $OUTPUT->set_pagetitle(rcmail_localize_foldername($mbox_name));
}


/**
 * return the message list as HTML table
 */
function rcmail_message_list($attrib)
{
  global $IMAP, $CONFIG, $OUTPUT;

  // add some labels to client
  $OUTPUT->add_label('from', 'to');

  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcubemessagelist';

  // define list of cols to be displayed based on parameter or config
  if (empty($attrib['columns'])) {
    $a_show_cols = is_array($CONFIG['list_cols']) ? $CONFIG['list_cols'] : array('subject');
    $OUTPUT->set_env('col_movable', !in_array('list_cols', (array)$CONFIG['dont_override']));
  }
  else {
    $a_show_cols = preg_split('/[\s,;]+/', strip_quotes($attrib['columns']));
    $attrib['columns'] = $a_show_cols;
  }

  // save some variables for use in ajax list
  $_SESSION['list_attrib'] = $attrib;

  $mbox = $IMAP->get_mailbox_name();
  $delim = $IMAP->get_hierarchy_delimiter();

  // show 'to' instead of 'from' in sent/draft messages
  if ((strpos($mbox.$delim, $CONFIG['sent_mbox'].$delim)===0 || strpos($mbox.$delim, $CONFIG['drafts_mbox'].$delim)===0)
      && (($f = array_search('from', $a_show_cols)) !== false) && array_search('to', $a_show_cols) === false)
    $a_show_cols[$f] = 'to';

  // make sure 'threads' and 'subject' columns are present
  if (!in_array('subject', $a_show_cols))
    array_unshift($a_show_cols, 'subject');
  if (!in_array('threads', $a_show_cols))
    array_unshift($a_show_cols, 'threads');

  $skin_path = $_SESSION['skin_path'] = $CONFIG['skin_path'];

  // set client env
  $OUTPUT->add_gui_object('messagelist', $attrib['id']);
  $OUTPUT->set_env('autoexpand_threads', intval($CONFIG['autoexpand_threads']));
  $OUTPUT->set_env('sort_col', $_SESSION['sort_col']);
  $OUTPUT->set_env('sort_order', $_SESSION['sort_order']);
  $OUTPUT->set_env('messages', array());
  $OUTPUT->set_env('coltypes', $a_show_cols);

  $OUTPUT->include_script('list.js');

  $thead = '';
  foreach (rcmail_message_list_head($attrib, $a_show_cols) as $cell)
    $thead .= html::tag('td', array('class' => $cell['className'], 'id' => $cell['id']), $cell['html']);

  return html::tag('table',
    $attrib,
    html::tag('thead', null, html::tag('tr', null, $thead)) .
      html::tag('tbody', null, ''),
        array('style', 'class', 'id', 'cellpadding', 'cellspacing', 'border', 'summary'));
}


/**
 * return javascript commands to add rows to the message list
 */
function rcmail_js_message_list($a_headers, $insert_top=FALSE, $a_show_cols=null)
{
  global $CONFIG, $IMAP, $RCMAIL, $OUTPUT;

  if (empty($a_show_cols)) {
    if (!empty($_SESSION['list_attrib']['columns']))
      $a_show_cols = $_SESSION['list_attrib']['columns'];
    else
      $a_show_cols = is_array($CONFIG['list_cols']) ? $CONFIG['list_cols'] : array('subject');
  }
  else {
    if (!is_array($a_show_cols))
      $a_show_cols = preg_split('/[\s,;]+/', strip_quotes($a_show_cols));
    $head_replace = true;
  }

  $mbox = $IMAP->get_mailbox_name();
  $delim = $IMAP->get_hierarchy_delimiter();

  // make sure 'threads' and 'subject' columns are present
  if (!in_array('subject', $a_show_cols))
    array_unshift($a_show_cols, 'subject');
  if (!in_array('threads', $a_show_cols))
    array_unshift($a_show_cols, 'threads');

  $_SESSION['list_attrib']['columns'] = $a_show_cols;

  // show 'to' instead of 'from' in sent/draft messages
  if ((strpos($mbox.$delim, $CONFIG['sent_mbox'].$delim)===0 || strpos($mbox.$delim, $CONFIG['drafts_mbox'].$delim)===0)
      && (($f = array_search('from', $a_show_cols)) !== false) && array_search('to', $a_show_cols) === false)
    $a_show_cols[$f] = 'to';

  // Make sure there are no duplicated columns (#1486999)
  $a_show_cols = array_unique($a_show_cols);

  // Plugins may set header's list_cols/list_flags and other rcube_mail_header variables
  // and list columns
  $plugin = $RCMAIL->plugins->exec_hook('messages_list',
    array('messages' => $a_headers, 'cols' => $a_show_cols));

  $a_show_cols = $plugin['cols'];
  $a_headers   = $plugin['messages'];

  $thead = $head_replace ? rcmail_message_list_head($_SESSION['list_attrib'], $a_show_cols) : NULL;

  $OUTPUT->command('set_message_coltypes', $a_show_cols, $thead);

  if (empty($a_headers))
    return;

  // remove 'threads', 'attachment', 'flag', 'status' columns, we don't need them here
  foreach (array('threads', 'attachment', 'flag', 'status') as $col) {
    if (($key = array_search($col, $a_show_cols)) !== FALSE)
      unset($a_show_cols[$key]);
  }

  // loop through message headers
  foreach ($a_headers as $n => $header) {
    if (empty($header))
      continue;

    $a_msg_cols = array();
    $a_msg_flags = array();

    $IMAP->set_charset(!empty($header->charset) ? $header->charset : $CONFIG['default_charset']);

    // format each col; similar as in rcmail_message_list()
    foreach ($a_show_cols as $col) {
      if (in_array($col, array('from', 'to', 'cc', 'replyto')))
        $cont = Q(rcmail_address_string($header->$col, 3), 'show');
      else if ($col=='subject') {
        $cont = trim($IMAP->decode_header($header->$col));
        if (!$cont) $cont = rcube_label('nosubject');
        $cont = Q($cont);
      }
      else if ($col=='size')
        $cont = show_bytes($header->$col);
      else if ($col=='date')
        $cont = format_date($header->date);
      else
        $cont = Q($header->$col);

      $a_msg_cols[$col] = $cont;
    }

    if ($header->depth)
      $a_msg_flags['depth'] = $header->depth;
    else if ($header->has_children)
      $roots[] = $header->uid;
    if ($header->parent_uid)
      $a_msg_flags['parent_uid'] = $header->parent_uid;
    if ($header->has_children)
      $a_msg_flags['has_children'] = $header->has_children;
    if ($header->unread_children)
      $a_msg_flags['unread_children'] = $header->unread_children;
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
    if ($header->others['list-post'])
      $a_msg_flags['ml'] = 1;

    $a_msg_flags['ctype'] = Q($header->ctype);
    $a_msg_flags['mbox'] = $mbox;

    // merge with plugin result
    if (!empty($header->list_flags) && is_array($header->list_flags))
      $a_msg_flags = array_merge($a_msg_flags, $header->list_flags);
    if (!empty($header->list_cols) && is_array($header->list_cols))
      $a_msg_cols = array_merge($a_msg_cols, $header->list_cols);

    $OUTPUT->command('add_message_row',
      $header->uid,
      $a_msg_cols,
      $a_msg_flags,
      $insert_top);
  }

  if ($IMAP->threading) {
    $OUTPUT->command('init_threads', (array) $roots);
  }
}


/*
 * Creates <THEAD> for message list table
 */
function rcmail_message_list_head($attrib, $a_show_cols)
{
  global $CONFIG;

  $skin_path = $_SESSION['skin_path'];
  $image_tag = html::img(array('src' => "%s%s", 'alt' => "%s"));

  // check to see if we have some settings for sorting
  $sort_col   = $_SESSION['sort_col'];
  $sort_order = $_SESSION['sort_order'];

  // define sortable columns
  $a_sort_cols = array('subject', 'date', 'from', 'to', 'size', 'cc');

  if (!empty($attrib['optionsmenuicon'])) {
    $onclick = 'return ' . JS_OBJECT_NAME . ".command('menu-open', 'messagelistmenu')";
    if ($attrib['optionsmenuicon'] === true || $attrib['optionsmenuicon'] == 'true')
      $list_menu = html::div(array('onclick' => $onclick, 'class' => 'listmenu',
        'id' => 'listmenulink', 'title' => rcube_label('listoptions')));
    else
      $list_menu = html::a(array('href' => '#', 'onclick' => $onclick),
        html::img(array('src' => $skin_path . $attrib['optionsmenuicon'],
          'id' => 'listmenulink', 'title' => rcube_label('listoptions')))
      );
  }
  else
    $list_menu = '';

  $cells = array();

  foreach ($a_show_cols as $col) {
    // get column name
    switch ($col) {
      case 'flag':
        $col_name = '<span class="flagged">&nbsp;</span>';
        break;
      case 'attachment':
      case 'status':
        $col_name = '<span class="' . $col .'">&nbsp;</span>';
        break;
      case 'threads':
        $col_name = $list_menu;
        break;
      default:
        $col_name = Q(rcube_label($col));
    }

    // make sort links
    if (in_array($col, $a_sort_cols))
      $col_name = html::a(array('href'=>"./#sort", 'onclick' => 'return '.JS_OBJECT_NAME.".command('sort','".$col."',this)", 'title' => rcube_label('sortby')), $col_name);

    $sort_class = $col == $sort_col ? " sorted$sort_order" : '';
    $class_name = $col.$sort_class;

    // put it all together
    $cells[] = array('className' => $class_name, 'id' => "rcm$col", 'html' => $col_name);
  }

  return $cells;
}


/**
 * return an HTML iframe for loading mail content
 */
function rcmail_messagecontent_frame($attrib)
  {
  global $OUTPUT, $RCMAIL;

  if (empty($attrib['id']))
    $attrib['id'] = 'rcmailcontentwindow';

  $attrib['name'] = $attrib['id'];

  if ($RCMAIL->config->get('preview_pane'))
    $OUTPUT->set_env('contentframe', $attrib['id']);
  $OUTPUT->set_env('blankpage', $attrib['src'] ? $OUTPUT->abs_url($attrib['src']) : 'program/blank.gif');

  return html::iframe($attrib);
  }


function rcmail_messagecount_display($attrib)
  {
  global $RCMAIL;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmcountdisplay';

  $RCMAIL->output->add_gui_object('countdisplay', $attrib['id']);

  $content =  $RCMAIL->action != 'show' ? rcmail_get_messagecount_text() : rcube_label('loading');

  return html::span($attrib, $content);
  }


function rcmail_get_messagecount_text($count=NULL, $page=NULL)
  {
  global $RCMAIL, $IMAP;

  if ($page===NULL)
    $page = $IMAP->list_page;

  $start_msg = ($page-1) * $IMAP->page_size + 1;

  if ($count!==NULL)
    $max = $count;
  else if ($RCMAIL->action)
    $max = $IMAP->messagecount(NULL, $IMAP->threading ? 'THREADS' : 'ALL');

  if ($max==0)
    $out = rcube_label('mailboxempty');
  else
    $out = rcube_label(array('name' => $IMAP->threading ? 'threadsfromto' : 'messagesfromto',
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


function rcmail_send_unread_count($mbox_name, $force=false, $count=null)
{
  global $RCMAIL;

  $old_unseen = rcmail_get_unseen_count($mbox_name);

  if ($count === null)
    $unseen = $RCMAIL->imap->messagecount($mbox_name, 'UNSEEN', $force);
  else
    $unseen = $count;

  if ($unseen != $old_unseen || ($mbox_name == 'INBOX'))
    $RCMAIL->output->command('set_unread_count', $mbox_name, $unseen, ($mbox_name == 'INBOX'));

  rcmail_set_unseen_count($mbox_name, $unseen);

  return $unseen;
}


function rcmail_set_unseen_count($mbox_name, $count)
{
  // @TODO: this data is doubled (session and cache tables) if caching is enabled

  // Make sure we have an array here (#1487066)
  if (!is_array($_SESSION['unseen_count']))
    $_SESSION['unseen_count'] = array();

  $_SESSION['unseen_count'][$mbox_name] = $count;
}


function rcmail_get_unseen_count($mbox_name)
{
  if (is_array($_SESSION['unseen_count']) && array_key_exists($mbox_name, $_SESSION['unseen_count']))
    return $_SESSION['unseen_count'][$mbox_name];
  else
    return null;
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
    '/<title[^>]*>.*<\/title>/i',	// PHP bug #32547 workaround: remove title tag
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

  // PCRE errors handling (#1486856), should we use something like for every preg_* use?
  if ($html === null && ($preg_error = preg_last_error()) != PREG_NO_ERROR) {
    $errstr = "Could not clean up HTML message! PCRE Error: $preg_error.";

    if ($preg_error == PREG_BACKTRACK_LIMIT_ERROR)
      $errstr .= " Consider raising pcre.backtrack_limit!";
    if ($preg_error == PREG_RECURSION_LIMIT_ERROR)
      $errstr .= " Consider raising pcre.recursion_limit!";

    raise_error(array('code' => 600, 'type' => 'php',
        'line' => __LINE__, 'file' => __FILE__,
        'message' => $errstr), true, false);
    return '';
  }

  // fix (unknown/malformed) HTML tags before "wash"
  $html = preg_replace_callback('/(<[\/]*)([^\s>]+)/', 'rcmail_html_tag_callback', $html);

  // charset was converted to UTF-8 in rcube_imap::get_message_part(),
  // -> change charset specification in HTML accordingly
  $charset_pattern = '(<meta\s+[^>]*content=)[\'"]?(\w+\/\w+;\s*charset=)([a-z0-9-_]+[\'"]?)';
  if (preg_match("/$charset_pattern/Ui", $html)) {
    $html = preg_replace("/$charset_pattern/i", '\\1"\\2'.RCMAIL_CHARSET.'"', $html);
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

  // overwrite washer options with options from plugins
  if (isset($p['html_elements']))
    $wash_opts['html_elements'] = $p['html_elements'];
  if (isset($p['html_attribs']))
    $wash_opts['html_attribs'] = $p['html_attribs'];

  // initialize HTML washer
  $washer = new washtml($wash_opts);

  if (!$p['skip_washer_form_callback'])
    $washer->add_callback('form', 'rcmail_washtml_callback');

  // allow CSS styles, will be sanitized by rcmail_washtml_callback()
  if (!$p['skip_washer_style_callback'])
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
    array('type' => $part->ctype_secondary, 'body' => $part->body, 'id' => $part->mime_id)
        + $p + array('safe' => false, 'plain' => false, 'inline_html' => true));

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
    require_once(INSTALL_PATH . 'program/lib/enriched.inc');
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
    $body = rcmail_plain_body($body, $part->ctype_parameters['format'] == 'flowed');

  // allow post-processing of the message body
  $data = $RCMAIL->plugins->exec_hook('message_part_after',
    array('type' => $part->ctype_secondary, 'body' => $body, 'id' => $part->mime_id) + $data);

  return $data['type'] == 'html' ? $data['body'] : html::tag('pre', array(), $data['body']);
}


/**
 * Handle links and citation marks in plain text message
 *
 * @param string  Plain text string
 * @param boolean Text uses format=flowed
 *
 * @return string Formatted HTML string
 */
function rcmail_plain_body($body, $flowed=false)
{
  global $RCMAIL;

  // make links and email-addresses clickable
  $replacer = new rcube_string_replacer;

  // search for patterns like links and e-mail addresses
  $body = preg_replace_callback($replacer->link_pattern, array($replacer, 'link_callback'), $body);
  $body = preg_replace_callback($replacer->mailto_pattern, array($replacer, 'mailto_callback'), $body);

  // split body into single lines
  $body = preg_split('/\r?\n/', $body);
  $quote_level = 0;
  $last = -1;

  // find/mark quoted lines...
  for ($n=0, $cnt=count($body); $n < $cnt; $n++) {
    if ($body[$n][0] == '>' && preg_match('/^(>+\s*)+/', $body[$n], $regs)) {
      $q = strlen(preg_replace('/\s/', '', $regs[0]));
      $body[$n] = substr($body[$n], strlen($regs[0]));

      if ($q > $quote_level) {
        $body[$n] = $replacer->get_replacement($replacer->add(
          str_repeat('<blockquote>', $q - $quote_level))) . $body[$n];
      }
      else if ($q < $quote_level) {
        $body[$n] = $replacer->get_replacement($replacer->add(
          str_repeat('</blockquote>', $quote_level - $q))) . $body[$n];
      }
      else if ($flowed) {
        // previous line is flowed
        if (isset($body[$last]) && $body[$n]
          && $body[$last][strlen($body[$last])-1] == ' ') {
          // merge lines
          $body[$last] .= $body[$n];
          unset($body[$n]);
        }
        else {
          $last = $n;
        }
      }
    }
    else {
      $q = 0;
      if ($flowed) {
        // sig separator - line is fixed
        if ($body[$n] == '-- ') {
          $last = $last_sig = $n;
        }
        else {
          // remove space-stuffing
          if ($body[$n][0] == ' ')
            $body[$n] = substr($body[$n], 1);

          // previous line is flowed?
          if (isset($body[$last]) && $body[$n]
            && $last != $last_sig
            && $body[$last][strlen($body[$last])-1] == ' '
          ) {
            $body[$last] .= $body[$n];
            unset($body[$n]);
          }
          else {
            $last = $n;
          }
        }
        if ($quote_level > 0)
          $body[$last] = $replacer->get_replacement($replacer->add(
            str_repeat('</blockquote>', $quote_level))) . $body[$last];
      }
      else if ($quote_level > 0)
        $body[$n] = $replacer->get_replacement($replacer->add(
          str_repeat('</blockquote>', $quote_level))) . $body[$n];
    }

    $quote_level = $q;
  }

  $body = join("\n", $body);

  // quote plain text (don't use Q() here, to display entities "as is")
  $table = get_html_translation_table(HTML_SPECIALCHARS);
  unset($table['?']);
  $body = strtr($body, $table);

  // colorize signature (up to <sig_max_lines> lines)
  $len = strlen($body);
  $sig_max_lines = $RCMAIL->config->get('sig_max_lines', 15);
  while (($sp = strrpos($body, "-- \n", $sp ? -$len+$sp-1 : 0)) !== false) {
    if ($sp == 0 || $body[$sp-1] == "\n") {
      // do not touch blocks with more that X lines
      if (substr_count($body, "\n", $sp) < $sig_max_lines)
        $body = substr($body, 0, max(0, $sp))
          .'<span class="sig">'.substr($body, $sp).'</span>';
      break;
    }
  }

  // insert url/mailto links and citation tags
  $body = $replacer->resolve($body);

  return $body;
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
      $stripped = preg_replace('/[^a-zA-Z\(:;]/', '', rcmail_xss_entity_decode($content));

      // now check for evil strings like expression, behavior or url()
      if (!preg_match('/expression|behavior|url\(|import[^a]/', $stripped)) {
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
  $standard_headers = array('subject', 'from', 'to', 'cc', 'bcc', 'replyto',
    'mail-reply-to', 'mail-followup-to', 'date');
  $output_headers = array();

  foreach ($standard_headers as $hkey) {
    if ($headers[$hkey])
      $value = $headers[$hkey];
    else if ($headers['others'][$hkey])
      $value = $headers['others'][$hkey];
    else
      continue;

    if ($hkey == 'date') {
      if ($PRINT_MODE)
        $header_value = format_date($value, $RCMAIL->config->get('date_long', 'x'));
      else
        $header_value = format_date($value);
    }
    else if ($hkey == 'replyto') {
      if ($headers['replyto'] != $headers['from'])
        $header_value = rcmail_address_string($value, null, true, $attrib['addicon']);
      else
        continue;
    }
    else if ($hkey == 'mail-reply-to') {
      if ($headers['mail-replyto'] != $headers['reply-to']
        && $headers['reply-to'] != $headers['from']
      )
        $header_value = rcmail_address_string($value, null, true, $attrib['addicon']);
      else
        continue;
    }
    else if ($hkey == 'mail-followup-to') {
      $header_value = rcmail_address_string($value, null, true, $attrib['addicon']);
    }
    else if (in_array($hkey, array('from', 'to', 'cc', 'bcc')))
      $header_value = rcmail_address_string($value, null, true, $attrib['addicon']);
    else if ($hkey == 'subject' && empty($value))
      $header_value = rcube_label('nosubject');
    else
      $header_value = trim($IMAP->decode_header($value));

    $output_headers[$hkey] = array(
        'title' => rcube_label(preg_replace('/(^mail-|-)/', '', $hkey)),
        'value' => $header_value, 'raw' => $value
    );
  }

  $plugin = $RCMAIL->plugins->exec_hook('message_headers_output',
    array('output' => $output_headers, 'headers' => $MESSAGE->headers));

  // compose html table
  $table = new html_table(array('cols' => 2));

  foreach ($plugin['output'] as $hkey => $row) {
    $table->add(array('class' => 'header-title'), Q($row['title']));
    $table->add(array('class' => 'header '.$hkey), Q($row['value'], ($hkey == 'subject' ? 'strict' : 'show')));
  }

  return $table->show($attrib);
}


/**
 * return block to show full message headers
 */
function rcmail_message_full_headers($attrib, $headers=NULL)
{
  global $OUTPUT;
  
  $html = html::div(array('class' => "more-headers show-headers", 'onclick' => "return ".JS_OBJECT_NAME.".command('load-headers','',this)"), '');
  $html .= html::div(array('id' => "all-headers", 'class' => "all", 'style' => 'display:none'), html::div(array('id' => 'headers-source'), ''));
  
  $OUTPUT->add_gui_object('all_headers_row', 'all-headers');
  $OUTPUT->add_gui_object('all_headers_box', 'headers-source');
  
  return html::div($attrib, $html);
}


/**
 * Handler for the 'messagebody' GUI object
 *
 * @param array Named parameters
 * @return string HTML content showing the message body
 */
function rcmail_message_body($attrib)
  {
  global $CONFIG, $OUTPUT, $MESSAGE, $IMAP, $RCMAIL, $REMOTE_OBJECTS;

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

        // message is cached but not exists (#1485443), or other error
        if ($part->body === false) {
          rcmail_message_error($MESSAGE->uid);
        }

        $plugin = $RCMAIL->plugins->exec_hook('message_body_prefix', array(
          'part' => $part, 'prefix' => ''));

        $body = rcmail_print_body($part, array('safe' => $safe_mode, 'plain' => !$CONFIG['prefer_html']));

        if ($part->ctype_secondary == 'html') {
          $body = rcmail_html4inline($body, $attrib['id'], 'rcmBody', $attrs);
          $div_attr = array('class' => 'message-htmlpart');
          $style = array();

          if (!empty($attrs)) {
            foreach ($attrs as $a_idx => $a_val)
              $style[] = $a_idx . ': ' . $a_val;
            if (!empty($style))
              $div_attr['style'] = implode('; ', $style);
          }

          $out .= html::div($div_attr, $plugin['prefix'] . $body);
        }
        else
          $out .= html::div('message-part', $plugin['prefix'] . $body);
        }
      }
    }
  else {
    $plugin = $RCMAIL->plugins->exec_hook('message_body_prefix', array(
      'part' => $MESSAGE, 'prefix' => ''));

    $out .= html::div('message-part', $plugin['prefix'] . html::tag('pre', array(),
      rcmail_plain_body(Q($MESSAGE->body, 'strict', false))));
    }

  $ctype_primary = strtolower($MESSAGE->structure->ctype_primary);
  $ctype_secondary = strtolower($MESSAGE->structure->ctype_secondary);

  // list images after mail body
  if ($CONFIG['inline_images']
      && $ctype_primary == 'multipart'
      && !empty($MESSAGE->attachments))
    {
    foreach ($MESSAGE->attachments as $attach_prop) {
      // Content-Type: image/*...
      if (preg_match('/^image\//i', $attach_prop->mimetype) ||
        // ...or known file extension: many clients are using application/octet-stream
        ($attach_prop->filename &&
          preg_match('/^application\/octet-stream$/i', $attach_prop->mimetype) &&
          preg_match('/\.(jpg|jpeg|png|gif|bmp)$/i', $attach_prop->filename))
      ) {
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
function rcmail_html4inline($body, $container_id, $body_id='', &$attributes=null)
{
  $last_style_pos = 0;
  $body_lc = strtolower($body);
  $cont_id = $container_id.($body_id ? ' div.'.$body_id : '');

  // find STYLE tags
  while (($pos = strpos($body_lc, '<style', $last_style_pos)) && ($pos2 = strpos($body_lc, '</style>', $pos)))
  {
    $pos = strpos($body_lc, '>', $pos)+1;

    // replace all css definitions with #container [def]
    $styles = rcmail_mod_css_styles(
      substr($body, $pos, $pos2-$pos), $cont_id);

    $body = substr($body, 0, $pos) . $styles . substr($body, $pos2);
    $body_lc = strtolower($body);
    $last_style_pos = $pos2;
  }

  // modify HTML links to open a new window if clicked
  $GLOBALS['rcmail_html_container_id'] = $container_id;
  $body = preg_replace_callback('/<(a|link)\s+([^>]+)>/Ui', 'rcmail_alter_html_link', $body);
  unset($GLOBALS['rcmail_html_container_id']);

  $body = preg_replace(array(
      // add comments arround html and other tags
      '/(<!DOCTYPE[^>]*>)/i',
      '/(<\?xml[^>]*>)/i',
      '/(<\/?html[^>]*>)/i',
      '/(<\/?head[^>]*>)/i',
      '/(<title[^>]*>.*<\/title>)/Ui',
      '/(<\/?meta[^>]*>)/i',
      // quote <? of php and xml files that are specified as text/html
      '/<\?/',
      '/\?>/',
      // replace <body> with <div>
      '/<body([^>]*)>/i',
      '/<\/body>/i',
      ),
    array(
      '<!--\\1-->',
      '<!--\\1-->',
      '<!--\\1-->',
      '<!--\\1-->',
      '<!--\\1-->',
      '<!--\\1-->',
      '&lt;?',
      '?&gt;',
      '<div class="'.$body_id.'"\\1>',
      '</div>',
      ),
    $body);

  $attributes = array();

  // Handle body attributes that doesn't play nicely with div elements
  $regexp = '/<div class="' . preg_quote($body_id, '/') . '"([^>]*)/';
  if (preg_match($regexp, $body, $m)) {
    $attrs = $m[0];
    // Get bgcolor, we'll set it as background-color of the message container
    if ($m[1] && preg_match('/bgcolor=["\']*([a-z0-9#]+)["\']*/', $attrs, $mb)) {
      $attributes['background-color'] = $mb[1];
      $attrs = preg_replace('/bgcolor=["\']*([a-z0-9#]+)["\']*/', '', $attrs);
    }
    // Get background, we'll set it as background-image of the message container
    if ($m[1] && preg_match('/background=["\']*([^"\'>\s]+)["\']*/', $attrs, $mb)) {
      $attributes['background-image'] = 'url('.$mb[1].')';
      $attrs = preg_replace('/background=["\']*([^"\'>\s]+)["\']*/', '', $attrs);
    }
    if (!empty($attributes)) {
      $body = preg_replace($regexp, rtrim($attrs), $body, 1);
    }

    // handle body styles related to background image
    if ($attributes['background-image']) {
      // get body style
      if (preg_match('/#'.preg_quote($cont_id, '/').'\s+\{([^}]+)}/i', $body, $m)) {
        // get background related style
        if (preg_match_all('/(background-position|background-repeat)\s*:\s*([^;]+);/i', $m[1], $ma, PREG_SET_ORDER)) {
          foreach ($ma as $style)
            $attributes[$style[1]] = $style[2];
        }
      }
    }
  }
  // make sure there's 'rcmBody' div, we need it for proper css modification
  // its name is hardcoded in rcmail_message_body() also
  else {
    $body = '<div class="' . $body_id . '">' . $body . '</div>';
  }

  return $body;
}


/**
 * parse link attributes and set correct target
 */
function rcmail_alter_html_link($matches)
{
  global $RCMAIL, $EMAIL_ADDRESS_PATTERN;

  $tag = $matches[1];
  $attrib = parse_attrib_string($matches[2]);
  $end = '>';

  // Remove non-printable characters in URL (#1487805)
  $attrib['href'] = preg_replace('/[\x00-\x1F]/', '', $attrib['href']);

  if ($tag == 'link' && preg_match('/^https?:\/\//i', $attrib['href'])) {
    $tempurl = 'tmp-' . md5($attrib['href']) . '.css';
    $_SESSION['modcssurls'][$tempurl] = $attrib['href'];
    $attrib['href'] = $RCMAIL->url(array('task' => 'utils', 'action' => 'modcss', 'u' => $tempurl, 'c' => $GLOBALS['rcmail_html_container_id']));
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
  global $IMAP, $RCMAIL, $PRINT_MODE, $CONFIG;
  static $got_writable_abook = null;

  $a_parts = $IMAP->decode_address_list($input);

  if (!sizeof($a_parts))
    return $input;

  $c = count($a_parts);
  $j = 0;
  $out = '';

  if ($got_writable_abook === null && $books = $RCMAIL->get_address_sources(true)) {
    $got_writable_abook = true;
  }

  foreach ($a_parts as $part) {
    $j++;

    $name   = $part['name'];
    $mailto = $part['mailto'];
    $string = $part['string'];

    // IDNA ASCII to Unicode
    if ($name == $mailto)
      $name = rcube_idn_to_utf8($name);
    if ($string == $mailto)
      $string = rcube_idn_to_utf8($string);
    $mailto = rcube_idn_to_utf8($mailto);

    if ($PRINT_MODE) {
      $out .= sprintf('%s &lt;%s&gt;', Q($name), $mailto);
    }
    else if (check_email($part['mailto'], false)) {
      if ($linked) {
        $address = html::a(array(
            'href' => 'mailto:'.$mailto,
            'onclick' => sprintf("return %s.command('compose','%s',this)", JS_OBJECT_NAME, JQ($mailto)),
            'title' => $mailto,
            'class' => "rcmContactAddress",
          ),
        Q($name ? $name : $mailto));
      }
      else {
        $address = html::span(array('title' => $mailto, 'class' => "rcmContactAddress"),
          Q($name ? $name : $mailto));
      }

      if ($addicon && $got_writable_abook) {
        $address = html::span(null, $address . html::a(array(
            'href' => "#add",
            'onclick' => sprintf("return %s.command('add-contact','%s',this)", JS_OBJECT_NAME, urlencode($string)),
            'title' => rcube_label('addtoaddressbook'),
          ),
          html::img(array(
            'src' => $CONFIG['skin_path'] . $addicon,
            'alt' => "Add contact",
          ))));
      }
      $out .= $address;
    }
    else {
      if ($name)
        $out .= Q($name);
      if ($mailto)
        $out .= (strlen($out) ? ' ' : '') . sprintf('&lt;%s&gt;', Q($mailto));
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
 * but respect the mail quotation of replies messages (>).
 * Finally add another quotation level by prpending the lines
 * with >
 *
 * @param string Text to wrap
 * @param int The line width
 * @return string The wrapped text
 */
function rcmail_wrap_and_quote($text, $length = 72)
{
  // Rebuild the message body with a maximum of $max chars, while keeping quoted message.
  $max = min(77, $length + 8);
  $lines = preg_split('/\r?\n/', trim($text));
  $out = '';

  foreach ($lines as $line) {
    // don't wrap already quoted lines
    if ($line[0] == '>')
      $line = '>' . rtrim($line);
    else if (mb_strlen($line) > $max) {
      $newline = '';
      foreach(explode("\n", rc_wordwrap($line, $length - 2)) as $l) {
        if (strlen($l))
          $newline .= '> ' . $l . "\n";
        else
          $newline .= ">\n";
      }
      $line = rtrim($newline);
    }
    else
      $line = '> ' . $line;

    // Append the line
    $out .= $line . "\n";
  }

  return $out;
}


function rcmail_draftinfo_encode($p)
{
  $parts = array();
  foreach ($p as $key => $val)
    $parts[] = $key . '=' . ($key == 'folder' ? base64_encode($val) : $val);

  return join('; ', $parts);
}


function rcmail_draftinfo_decode($str)
{
  $info = array();
  foreach (preg_split('/;\s+/', $str) as $part) {
    list($key, $val) = explode('=', $part, 2);
    if ($key == 'folder')
      $val = base64_decode($val);
    $info[$key] = $val;
  }

  return $info;
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
function rcmail_compose_cleanup($id)
{
  if (!isset($_SESSION['compose_data'][$id]))
    return;

  $rcmail = rcmail::get_instance();
  $rcmail->plugins->exec_hook('attachments_cleanup', array('group' => $id));
  unset($_SESSION['compose_data'][$id]);
}


/**
 * Send the given message using the configured method
 *
 * @param object $message    Reference to Mail_MIME object
 * @param string $from       Sender address string
 * @param array  $mailto     Array of recipient address strings
 * @param array  $smtp_error SMTP error array (reference)
 * @param string $body_file  Location of file with saved message body (reference)
 * @param array  $smtp_opts  SMTP options (e.g. DSN request)
 *
 * @return boolean Send status.
 */
function rcmail_deliver_message(&$message, $from, $mailto, &$smtp_error, &$body_file, $smtp_opts=null)
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

    $sent = $RCMAIL->smtp->send_mail($from, $a_recipients, $smtp_headers, $msg_body, $smtp_opts);
    $smtp_response = $RCMAIL->smtp->get_response();
    $smtp_error = $RCMAIL->smtp->get_error();

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
    else {
      $delim   = $RCMAIL->config->header_delimiter();
      $to      = $headers_enc['To'];
      $subject = $headers_enc['Subject'];
      $header_str = rtrim($header_str);

      if ($delim != "\r\n") {
        $header_str = str_replace("\r\n", $delim, $header_str);
        $msg_body   = str_replace("\r\n", $delim, $msg_body);
        $to         = str_replace("\r\n", $delim, $to);
        $subject    = str_replace("\r\n", $delim, $subject);
      }

      if (ini_get('safe_mode'))
        $sent = mail($to, $subject, $msg_body, $header_str);
      else
        $sent = mail($to, $subject, $msg_body, $header_str, "-f$from");
    }
  }

  if ($sent) {
    $RCMAIL->plugins->exec_hook('message_sent', array('headers' => $headers, 'body' => $msg_body));

    // remove MDN headers after sending
    unset($headers['Return-Receipt-To'], $headers['Disposition-Notification-To']);

    // get all recipients
    if ($headers['Cc'])
      $mailto .= $headers['Cc'];
    if ($headers['Bcc'])
      $mailto .= $headers['Bcc'];
    if (preg_match_all('/<([^@]+@[^>]+)>/', $mailto, $m))
      $mailto = implode(', ', array_unique($m[1]));

    if ($CONFIG['smtp_log']) {
      write_log('sendmail', sprintf("User %s [%s]; Message for %s; %s",
        $RCMAIL->user->get_username(),
        $_SERVER['REMOTE_ADDR'],
        $mailto,
        !empty($smtp_response) ? join('; ', $smtp_response) : ''));
    }
  }

  if (is_resource($msg_body)) {
    fclose($msg_body);
  }

  $message->_headers = array();
  $message->headers($headers);

  return $sent;
}

/**
 * Send the MDN response
 *
 * @param mixed $message    Original message object (rcube_message) or UID
 * @param array $smtp_error SMTP error array (reference)
 *
 * @return boolean Send status
 */
function rcmail_send_mdn($message, &$smtp_error)
{
  global $RCMAIL, $IMAP;

  if (!is_a($message, rcube_message))
    $message = new rcube_message($message);

  if ($message->headers->mdn_to && !$message->headers->mdn_sent &&
    ($IMAP->check_permflag('MDNSENT') || $IMAP->check_permflag('*')))
  {
    $identity = $RCMAIL->user->get_identity();
    $sender = format_email_recipient($identity['email'], $identity['name']);
    $recipient = array_shift($IMAP->decode_address_list($message->headers->mdn_to));
    $mailto = $recipient['mailto'];

    $compose = new Mail_mime("\r\n");

    $compose->setParam('text_encoding', 'quoted-printable');
    $compose->setParam('html_encoding', 'quoted-printable');
    $compose->setParam('head_encoding', 'quoted-printable');
    $compose->setParam('head_charset', RCMAIL_CHARSET);
    $compose->setParam('html_charset', RCMAIL_CHARSET);
    $compose->setParam('text_charset', RCMAIL_CHARSET);

    // compose headers array
    $headers = array(
      'Date' => rcmail_user_date(),
      'From' => $sender,
      'To'   => $message->headers->mdn_to,
      'Subject' => rcube_label('receiptread') . ': ' . $message->subject,
      'Message-ID' => rcmail_gen_message_id(),
      'X-Sender' => $identity['email'],
      'References' => trim($message->headers->references . ' ' . $message->headers->messageID),
    );

    if ($agent = $RCMAIL->config->get('useragent'))
      $headers['User-Agent'] = $agent;

    $body = rcube_label("yourmessage") . "\r\n\r\n" .
      "\t" . rcube_label("to") . ': ' . rcube_imap::decode_mime_string($message->headers->to, $message->headers->charset) . "\r\n" .
      "\t" . rcube_label("subject") . ': ' . $message->subject . "\r\n" .
      "\t" . rcube_label("sent") . ': ' . format_date($message->headers->date, $RCMAIL->config->get('date_long')) . "\r\n" .
      "\r\n" . rcube_label("receiptnote") . "\r\n";

    $ua = $RCMAIL->config->get('useragent', "Roundcube Webmail (Version ".RCMAIL_VERSION.")");
    $report = "Reporting-UA: $ua\r\n";

    if ($message->headers->to)
        $report .= "Original-Recipient: {$message->headers->to}\r\n";

    $report .= "Final-Recipient: rfc822; {$identity['email']}\r\n" .
               "Original-Message-ID: {$message->headers->messageID}\r\n" .
               "Disposition: manual-action/MDN-sent-manually; displayed\r\n";

    $compose->headers($headers);
    $compose->setContentType('multipart/report', array('report-type'=> 'disposition-notification'));
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

// Returns unique Message-ID
function rcmail_gen_message_id()
{
  global $RCMAIL;

  $local_part  = md5(uniqid('rcmail'.mt_rand(),true));
  $domain_part = $RCMAIL->user->get_username('domain');

  // Try to find FQDN, some spamfilters doesn't like 'localhost' (#1486924)
  if (!preg_match('/\.[a-z]+$/i', $domain_part)) {
    if (($host = preg_replace('/:[0-9]+$/', '', $_SERVER['HTTP_HOST']))
      && preg_match('/\.[a-z]+$/i', $host)) {
        $domain_part = $host;
    }
    else if (($host = preg_replace('/:[0-9]+$/', '', $_SERVER['SERVER_NAME']))
      && preg_match('/\.[a-z]+$/i', $host)) {
        $domain_part = $host;
    }
  }

  return sprintf('<%s@%s>', $local_part, $domain_part);
}

// Returns RFC2822 formatted current date in user's timezone
function rcmail_user_date()
{
  global $CONFIG;

  // get user's timezone
  if ($CONFIG['timezone'] === 'auto') {
    $tz = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : date('Z')/3600;
  }
  else {
    $tz = $CONFIG['timezone'];
    if ($CONFIG['dst_active'])
      $tz++;
  }

  $date = time() + $tz * 60 * 60;
  $date = gmdate('r', $date);
  $tz   = sprintf('%+05d', intval($tz) * 100 + ($tz - intval($tz)) * 60);
  $date = preg_replace('/[+-][0-9]{4}$/', $tz, $date);

  return $date;
}

// Fixes some content-type names
function rcmail_fix_mimetype($name)
{
  // Some versions of Outlook create garbage Content-Type:
  // application/pdf.A520491B_3BF7_494D_8855_7FAC2C6C0608
  if (preg_match('/^application\/pdf.+/', $name))
    $name = 'application/pdf';

  return $name;
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

function rcmail_message_error($uid=null)
{
  global $RCMAIL;

  // Set env variables for messageerror.html template
  if ($RCMAIL->action == 'show') {
    $mbox_name = $RCMAIL->imap->get_mailbox_name();
    $RCMAIL->output->set_env('mailbox', $mbox_name);
    $RCMAIL->output->set_env('uid', null);
  }
  // display error message
  $RCMAIL->output->show_message('messageopenerror', 'error');
  // ... display message error page
  $RCMAIL->output->send('messageerror');
}

// register UI objects
$OUTPUT->add_handlers(array(
  'mailboxlist' => 'rcmail_mailbox_list',
  'messages' => 'rcmail_message_list',
  'messagecountdisplay' => 'rcmail_messagecount_display',
  'quotadisplay' => 'rcmail_quota_display',
  'mailboxname' => 'rcmail_mailbox_name_display',
  'messageheaders' => 'rcmail_message_headers',
  'messagefullheaders' => 'rcmail_message_full_headers',
  'messagebody' => 'rcmail_message_body',
  'messagecontentframe' => 'rcmail_messagecontent_frame',
  'messagepartframe' => 'rcmail_message_part_frame',
  'messagepartcontrols' => 'rcmail_message_part_controls',
  'searchfilter' => 'rcmail_search_filter',
  'searchform' => array($OUTPUT, 'search_form'),
));

// register action aliases
$RCMAIL->register_action_map(array(
    'preview' => 'show.inc',
    'print'   => 'show.inc',
    'moveto'  => 'move_del.inc',
    'delete'  => 'move_del.inc',
    'send'    => 'sendmail.inc',
    'expunge' => 'folders.inc',
    'purge'   => 'folders.inc',
    'remove-attachment'  => 'attachments.inc',
    'display-attachment' => 'attachments.inc',
    'upload'             => 'attachments.inc',
    'group-expand'       => 'autocomplete.inc',
));
