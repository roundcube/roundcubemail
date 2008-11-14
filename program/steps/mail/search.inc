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

*/

$REMOTE_REQUEST = TRUE;

// reset list_page and old search results
$IMAP->set_page(1);
$IMAP->set_search_set(NULL);
$_SESSION['page'] = 1;

// using encodeURI with javascript "should" give us
// a correctly UTF-8 encoded query string
$imap_charset = 'UTF-8';

// get search string
$str = get_input_value('_q', RCUBE_INPUT_GET);
$filter = get_input_value('_filter', RCUBE_INPUT_GET);
$mbox = get_input_value('_mbox', RCUBE_INPUT_GET);
$search_request = md5($mbox.$filter.$str);

// add list filter string
$search_str = $filter && $filter != 'ALL' ? $filter : '';

$_SESSION['search_filter'] = $filter;

// Check the search string for type of search
if (preg_match("/^from:.*/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject = "HEADER FROM";
  $search = trim($srch);
}
else if (preg_match("/^to.*:/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject = "HEADER TO";
  $search = trim($srch);
}
else if (preg_match("/^cc:.*/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject = "HEADER CC";
  $search = trim($srch);
}
else if (preg_match("/^subject:.*/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject = "HEADER SUBJECT";
  $search = trim($srch);
}
else if (preg_match("/^body:.*/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject = "TEXT";
  $search = trim($srch);
}
// search in subject and sender by default
else if(trim($str))
{
  $from = ($mbox == $CONFIG['sent_mbox'] || $mbox == $CONFIG['drafts_mbox']) ? "TO" : "FROM";
  $subject = array("HEADER SUBJECT", "HEADER $from");
  $search = trim($str);
}

if ($subject && !is_array($subject))
  $search_str .= sprintf(" %s {%d}\r\n%s", $subject, strlen($search), $search);
else if ($subject) {
  $search_str .= ' OR';
  foreach($subject as $sub)
    $search_str .= sprintf(" (%s {%d}\r\n%s)", $sub, strlen($search), $search);
}

$search_str = trim($search_str);

// execute IMAP search
if ($search_str)
  $result = $IMAP->search($mbox, $search_str, $imap_charset, $_SESSION['sort_col']);

// Get the headers
$result_h = $IMAP->list_headers($mbox, 1, $_SESSION['sort_col'], $_SESSION['sort_order']);
$count = $IMAP->messagecount();

// save search results in session
if (!is_array($_SESSION['search']))
  $_SESSION['search'] = array();

// Make sure we got the headers
if (!empty($result_h))
{
  if ($search_str) {
    $_SESSION['search'][$search_request] = $IMAP->get_search_set();
    $_SESSION['last_text_search'] = $str;
  }
  rcmail_js_message_list($result_h);
  $OUTPUT->show_message('searchsuccessful', 'confirmation', array('nr' => $count));
}
else
{
  $OUTPUT->show_message('searchnomatch', 'notice');
}

// update message count display
$OUTPUT->set_env('search_request', $search_str ? $search_request : -1);
$OUTPUT->set_env('messagecount', $count);
$OUTPUT->set_env('pagecount', ceil($count/$IMAP->page_size));
$OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($count, 1));
$OUTPUT->send();

?>
