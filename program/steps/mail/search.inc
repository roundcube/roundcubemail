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
$mbox = get_input_value('_mbox', RCUBE_INPUT_GET);
$search_request = md5($mbox.$str);


// Check the search string for type of search
if (preg_match("/^from:/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject =  "HEADER FROM";
  $search = trim($srch);
}
else if (preg_match("/^to:/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject = "HEADER TO";
  $search = trim($srch);
}
else if (preg_match("/^cc:/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject = "HEADER CC";
  $search = trim($srch);
}
else if (preg_match("/^subject:/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject = "HEADER SUBJECT";
  $search = trim($srch);
}
else if (preg_match("/^body:/i", $str))
{
  list(,$srch) = explode(":", $str);
  $subject = "TEXT";
  $search = trim($srch);
}
// search in subject and sender by default
else
{
  $from = ($mbox == $CONFIG['sent_mbox'] || $mbox == $CONFIG['drafts_mbox']) ? "TO" : "FROM";
  $subject = array("HEADER SUBJECT", "HEADER $from");
  $search = trim($str);
}


// execute IMAP search
$result = $IMAP->search($mbox, $subject, $search, $imap_charset);
$count = 0;
  
// Make sure our $result is legit..
if (is_array($result) && $result[0] != '')
{
  // Get the headers
  $result_h = $IMAP->list_header_set($mbox, $result, 1, $_SESSION['sort_col'], $_SESSION['sort_order']);
  $count = $IMAP->messagecount();

  // save search results in session
  if (!is_array($_SESSION['search']))
    $_SESSION['search'] = array();

  // Make sure we got the headers
  if ($result_h != NULL)
  {
    $_SESSION['search'][$search_request] = $IMAP->get_search_set();
    $_SESSION['last_text_search'] = $str;
    rcmail_js_message_list($result_h);
    $OUTPUT->show_message('searchsuccessful', 'confirmation', array('nr' => $count));
  }
}
else
{
  $OUTPUT->show_message('searchnomatch', 'notice');
  $search_request = -1;
}

// update message count display
$pages = ceil($count/$IMAP->page_size);
$OUTPUT->set_env('search_request', $search_request);
$OUTPUT->set_env('messagecount', $count);
$OUTPUT->set_env('pagecount', $pages);
$OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($count, 1));
$OUTPUT->send();

?>
