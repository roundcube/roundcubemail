<?php
/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/mark.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Mark the submitted messages with the specified flag                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

// only process ajax requests
if (!$OUTPUT->ajax_call)
  return;

$a_flags_map = array(
  'undelete' => 'UNDELETED',
  'delete' => 'DELETED',
  'read' => 'SEEN',
  'unread' => 'UNSEEN',
  'flagged' => 'FLAGGED',
  'unflagged' => 'UNFLAGGED');

if (($uids = get_input_value('_uid', RCUBE_INPUT_POST)) && ($flag = get_input_value('_flag', RCUBE_INPUT_POST)))
{
  $flag = $a_flags_map[$flag] ? $a_flags_map[$flag] : strtoupper($flag);

  if ($flag == 'DELETED' && $CONFIG['skip_deleted'] && $_POST['_from'] != 'show') {
    // count messages before changing anything
    $old_count = $IMAP->messagecount();
    $old_pages = ceil($old_count / $IMAP->page_size);
    $count = sizeof(explode(',', $uids));
  }

  $marked = $IMAP->set_flag($uids, $flag);
  
  if ($marked == -1) {
    // send error message
    if ($_POST['_from'] != 'show')
      $OUTPUT->command('list_mailbox');
    $OUTPUT->show_message('errormarking', 'error');
    $OUTPUT->send();
    exit;
  }

  if($flag == 'DELETED' && $CONFIG['read_when_deleted'] && !empty($_POST['_ruid'])) {
    $uids = get_input_value('_ruid', RCUBE_INPUT_POST);
    $read = $IMAP->set_flag($uids, 'SEEN');
    
    if ($read != -1 && !$CONFIG['skip_deleted'])
      $OUTPUT->command('flag_deleted_as_read', $uids);
  }
    
  if ($flag == 'SEEN' || $flag == 'UNSEEN' || ($flag == 'DELETED' && !$CONFIG['skip_deleted'])) {
    rcmail_send_unread_count($IMAP->get_mailbox_name());
  }
  else if ($flag == 'DELETED' && $CONFIG['skip_deleted']) {
    if ($_POST['_from'] == 'show') {
      if ($next = get_input_value('_next_uid', RCUBE_INPUT_GPC))
        $OUTPUT->command('show_message', $next);
      else
        $OUTPUT->command('command', 'list');
    } else {
      // refresh saved search set after moving some messages
      if (($search_request = get_input_value('_search', RCUBE_INPUT_GPC)) && $IMAP->search_set) {
        $_SESSION['search'][$search_request] = $IMAP->refresh_search();
      }

      $msg_count      = $IMAP->messagecount();
      $pages          = ceil($msg_count / $IMAP->page_size);
      $nextpage_count = $old_count - $IMAP->page_size * $IMAP->list_page;
      $remaining      = $msg_count - $IMAP->page_size * ($IMAP->list_page - 1);

      // jump back one page (user removed the whole last page)
      if ($IMAP->list_page > 1 && $nextpage_count <= 0 && $remaining == 0) {
        $IMAP->set_page($IMAP->list_page-1);
        $_SESSION['page'] = $IMAP->list_page;
        $jump_back = true;
      }

      // update message count display
      $OUTPUT->set_env('messagecount', $msg_count);
      $OUTPUT->set_env('current_page', $IMAP->list_page);
      $OUTPUT->set_env('pagecount', $pages);

      // update mailboxlist
      $mbox = $IMAP->get_mailbox_name();
      $unseen_count = $msg_count ? $IMAP->messagecount($mbox, 'UNSEEN') : 0;
      $old_unseen = $_SESSION['unseen_count'][$mbox];
      if ($old_unseen != $unseen_count) {
        $OUTPUT->command('set_unread_count', $mbox, $unseen_count, ($mbox == 'INBOX'));
	$_SESSION['unseen_count'][$mbox] = $unseen_count;
      }
      $OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($msg_count));

      // add new rows from next page (if any)
      if (($jump_back || $nextpage_count > 0)) {
        $sort_col   = isset($_SESSION['sort_col'])   ? $_SESSION['sort_col']   : $CONFIG['message_sort_col'];
        $sort_order = isset($_SESSION['sort_order']) ? $_SESSION['sort_order'] : $CONFIG['message_sort_order'];
  
        $a_headers = $IMAP->list_headers($mbox, NULL, $sort_col, $sort_order, $count);
      
        rcmail_js_message_list($a_headers, false, false);
      }
    }
  }
  
  $OUTPUT->send();
}
  
exit;
?>
