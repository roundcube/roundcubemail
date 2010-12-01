<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/folders.inc                                        |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, Roundcube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Implement folder operations line EXPUNGE and Clear                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$
*/

// only process ajax requests
if (!$OUTPUT->ajax_call)
  return;

$mbox = get_input_value('_mbox', RCUBE_INPUT_POST, true);

// send EXPUNGE command
if ($RCMAIL->action=='expunge')
{
  $success = $IMAP->expunge($mbox);

  // reload message list if current mailbox
  if ($success && !empty($_REQUEST['_reload']))
  {
    $OUTPUT->command('set_quota', rcmail_quota_content());
    $OUTPUT->command('message_list.clear');
    $RCMAIL->action = 'list';
    return;
  }
  else
    $commands = "// expunged: $success\n";
}

// clear mailbox
else if ($RCMAIL->action=='purge')
{
  $delimiter = $IMAP->get_hierarchy_delimiter();
  $trash_regexp = '/^' . preg_quote($CONFIG['trash_mbox'] . $delimiter, '/') . '/';
  $junk_regexp = '/^' . preg_quote($CONFIG['junk_mbox'] . $delimiter, '/') . '/';

  // we should only be purging trash and junk (or their subfolders)
  if ($mbox == $CONFIG['trash_mbox'] || $mbox == $CONFIG['junk_mbox']
    || preg_match($trash_regexp, $mbox) || preg_match($junk_regexp, $mbox))
  {
    $success = $IMAP->clear_mailbox($mbox);

    if ($success && !empty($_REQUEST['_reload']))
    {
      $OUTPUT->set_env('messagecount', 0);
      $OUTPUT->set_env('pagecount', 0);
      $OUTPUT->command('message_list.clear');
      $OUTPUT->command('set_rowcount', rcmail_get_messagecount_text());
      $OUTPUT->command('set_unread_count', $mbox, 0);
      $OUTPUT->command('set_quota', rcmail_quota_content());
      rcmail_set_unseen_count($mbox, 0);
    }
    else
      $commands = "// purged: $success";
  }
}

$OUTPUT->send($commands);


