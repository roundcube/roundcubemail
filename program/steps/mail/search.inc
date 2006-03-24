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
 +-----------------------------------------------------------------------+

*/

$REMOTE_REQUEST = TRUE;

// reset list_page
$IMAP->set_page(1);
$_SESSION['page'] = 1;

// get search string
$str = get_input_value('_search', RCUBE_INPUT_GET);
$mbox = get_input_value('_mbox', RCUBE_INPUT_GET);


// Check the search string for type of search
if (preg_match("/^from:/i", $str)) {
  list(,$srch) = explode(":", $str);
  $search = $IMAP->search($mbox, "FROM" ,trim($srch));
  finish_search($mbox, $search);
}
else if (preg_match("/^to:/i", $str)) {
  list(,$srch) = explode(":", $str);
  $search = $IMAP->search($mbox, "TO", trim($srch));
  finish_search($mbox, $search);
}
else if (preg_match("/^cc:/i", $str)) {
  list(,$srch) = explode(":", $str);
  $search = $IMAP->search($mbox, "CC", trim($srch));
  finish_search($mbox, $search);
}
else if (preg_match("/^subject:/i", $str)) {
  list(,$srch) = explode(":", $str);
  $search = $IMAP->search($mbox, "SUBJECT", trim($srch));
  finish_search($mbox, $search);
}
else if (preg_match("/^body:/i", $str)) {
  list(,$srch) = explode(":", $str);
  $search = $IMAP->search($mbox, "TEXT", trim($srch));
  finish_search($mbox, $search);
}
// search in subject and sender by default
else {
  $search = $IMAP->search($mbox, "SUBJECT", trim($str));
  $search2 = $IMAP->search($mbox, "FROM", trim($str));
  finish_search($mbox, array_unique(array_merge($search, $search2)));
}


// Complete the search display results or report error
function finish_search($mbox, $search)
  {
  global $IMAP, $JS_OBJECT_NAME, $OUTPUT;
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
      
    $search_request = md5(uniqid(rand()));
    $_SESSION['search'][$search_request] = join(',', $search);

    // Make sure we got the headers
    if ($result_h != NULL)
      {
      $count = count($result_h);
      $commands = rcmail_js_message_list($result_h);
      $commands .= show_message('searchsuccessful', 'confirmation', array('nr' => $count));
      $commands .= sprintf("\nthis.set_env('search_request', '%s')\n", $search_request);
      }
    }
  else
    $commands = show_message('searchnomatch', 'warning');
  
  // update message count display
  $pages = ceil($count/$IMAP->page_size);
  $commands .= sprintf("this.set_env('messagecount', %d);\n", $count);
  $commands .= sprintf("this.set_env('pagecount', %d);\n", $pages);
  $commands .= sprintf("this.set_rowcount('%s');\n", rcmail_get_messagecount_text($count, 1));
  rcube_remote_response($commands);
  }

?>