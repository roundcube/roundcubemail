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

// reset list_page
$IMAP->set_page(1);
$_SESSION['page'] = 1;

// using encodeURI with javascript "should" give us
// a correctly UTF-8 encoded query string
$imap_charset = 'UTF-8';

// get search string
$str = get_input_value('_search', RCUBE_INPUT_GET);
$mbox = get_input_value('_mbox', RCUBE_INPUT_GET);
$search_request = md5($str);


// Check the search string for type of search
if (preg_match("/^from:/i", $str)) {
  list(,$srch) = explode(":", $str);
  $search = $IMAP->search($mbox, "HEADER FROM" ,trim($srch), $imap_charset);
  finish_search($mbox, $search);
}
else if (preg_match("/^to:/i", $str)) {
  list(,$srch) = explode(":", $str);
  $search = $IMAP->search($mbox, "HEADER TO", trim($srch), $imap_charset);
  finish_search($mbox, $search);
}
else if (preg_match("/^cc:/i", $str)) {
  list(,$srch) = explode(":", $str);
  $search = $IMAP->search($mbox, "HEADER CC", trim($srch), $imap_charset);
  finish_search($mbox, $search);
}
else if (preg_match("/^subject:/i", $str)) {
  list(,$srch) = explode(":", $str);
  $search = $IMAP->search($mbox, "HEADER SUBJECT", trim($srch), $imap_charset);
  finish_search($mbox, $search);
}
else if (preg_match("/^body:/i", $str)) {
  list(,$srch) = explode(":", $str);
  $search = $IMAP->search($mbox, "TEXT", trim($srch), $imap_charset);
  finish_search($mbox, $search);
}
// search in subject and sender by default
else {
  $search = $IMAP->search($mbox, "HEADER SUBJECT", trim($str), $imap_charset);
  $search2 = $IMAP->search($mbox, "HEADER FROM", trim($str), $imap_charset);
  finish_search($mbox, array_unique(array_merge($search, $search2)));
}


// Complete the search display results or report error
function finish_search($mbox, $search)
  {
  global $IMAP, $JS_OBJECT_NAME, $OUTPUT, $search_request;
  $commands = '';
  $count = 0;
    
  // Make sure our $search is legit..
  if (is_array($search) && $search[0] != '')
    {
    // Get the headers
    $result_h = $IMAP->list_header_set($mbox, $search, 1, $_SESSION['sort_col'], $_SESSION['sort_order']);
    $count = count($search);

    // save search results in session
    if (!is_array($_SESSION['search']))
      $_SESSION['search'] = array();

    // Make sure we got the headers
    if ($result_h != NULL)
      {
      $_SESSION['search'][$search_request] = join(',', $search);
      $commands = rcmail_js_message_list($result_h);
      $commands .= show_message('searchsuccessful', 'confirmation', array('nr' => $count));
      }
    }
  else
    {
    $commands = show_message('searchnomatch', 'warning');
    $search_request = -1;
    }
  
  // update message count display
  $pages = ceil($count/$IMAP->page_size);
  $commands .= sprintf("\nthis.set_env('search_request', '%s')\n", $search_request);
  $commands .= sprintf("this.set_env('messagecount', %d);\n", $count);
  $commands .= sprintf("this.set_env('pagecount', %d);\n", $pages);
  $commands .= sprintf("this.set_rowcount('%s');\n", rcmail_get_messagecount_text($count, 1));
  rcube_remote_response($commands);
  }

?>