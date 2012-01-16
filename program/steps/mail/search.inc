<?php
/*
 +-----------------------------------------------------------------------+
 | steps/mail/search.inc                                                 |
 |                                                                       |
 | Search functions for rc webmail                                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Benjamin Smith <defitro@gmail.com>                            |
 |         Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$REMOTE_REQUEST = TRUE;

// reset list_page and old search results
$RCMAIL->storage->set_page(1);
$RCMAIL->storage->set_search_set(NULL);
$_SESSION['page'] = 1;

// using encodeURI with javascript "should" give us
// a correctly encoded query string
$imap_charset = RCMAIL_CHARSET;

// get search string
$str     = get_input_value('_q', RCUBE_INPUT_GET, true);
$mbox    = get_input_value('_mbox', RCUBE_INPUT_GET, true);
$filter  = get_input_value('_filter', RCUBE_INPUT_GET);
$headers = get_input_value('_headers', RCUBE_INPUT_GET);
$subject = array();

$search_request = md5($mbox.$filter.$str);

// add list filter string
$search_str = $filter && $filter != 'ALL' ? $filter : '';

$_SESSION['search_filter'] = $filter;

// Check the search string for type of search
if (preg_match("/^from:.*/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject['from'] = "HEADER FROM";
}
else if (preg_match("/^to:.*/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject['to'] = "HEADER TO";
}
else if (preg_match("/^cc:.*/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject['cc'] = "HEADER CC";
}
else if (preg_match("/^bcc:.*/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject['bcc'] = "HEADER BCC";
}
else if (preg_match("/^subject:.*/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject['subject'] = "HEADER SUBJECT";
}
else if (preg_match("/^body:.*/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject['text'] = "TEXT";
}
else if (strlen(trim($str)))
{
  if ($headers) {
    foreach (explode(',', $headers) as $header) {
      if ($header == 'text') {
        // #1488208: get rid of other headers when searching by "TEXT"
        $subject = array('text' => 'TEXT');
        break;
      }
      else {
        $subject[$header] = 'HEADER '.strtoupper($header);
      }
    }

    // save search modifiers for the current folder to user prefs
    $search_mods = $RCMAIL->config->get('search_mods', $SEARCH_MODS_DEFAULT);
    $search_mods[$mbox] = array_fill_keys(array_keys($subject), 1);
    $RCMAIL->user->save_prefs(array('search_mods' => $search_mods));
  } else {
    // search in subject by default
    $subject['subject'] = 'HEADER SUBJECT';
  }
}

$search = isset($srch) ? trim($srch) : trim($str);

if (!empty($subject)) {
  $search_str .= str_repeat(' OR', count($subject)-1);
  foreach ($subject as $sub)
    $search_str .= sprintf(" %s {%d}\r\n%s", $sub, strlen($search), $search);
}

$search_str = trim($search_str);

// execute IMAP search
if ($search_str)
  $RCMAIL->storage->search($mbox, $search_str, $imap_charset, $_SESSION['sort_col']);

// save search results in session
if (!is_array($_SESSION['search']))
  $_SESSION['search'] = array();

if ($search_str) {
  $_SESSION['search'] = $RCMAIL->storage->get_search_set();
  $_SESSION['last_text_search'] = $str;
}
$_SESSION['search_request'] = $search_request;


// Get the headers
$result_h = $RCMAIL->storage->list_messages($mbox, 1, $_SESSION['sort_col'], $_SESSION['sort_order']);
$count = $RCMAIL->storage->count($mbox, $RCMAIL->storage->get_threading() ? 'THREADS' : 'ALL');


// Make sure we got the headers
if (!empty($result_h)) {
  rcmail_js_message_list($result_h);
  if ($search_str)
    $OUTPUT->show_message('searchsuccessful', 'confirmation', array('nr' => $RCMAIL->storage->count(NULL, 'ALL')));
}
// handle IMAP errors (e.g. #1486905)
else  if ($err_code = $RCMAIL->storage->get_error_code()) {
  rcmail_display_server_error();
}
else {
  $OUTPUT->show_message('searchnomatch', 'notice');
}

// update message count display
$OUTPUT->set_env('search_request', $search_str ? $search_request : '');
$OUTPUT->set_env('messagecount', $count);
$OUTPUT->set_env('pagecount', ceil($count/$RCMAIL->storage->get_pagesize()));
$OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($count, 1), $mbox);
$OUTPUT->send();
