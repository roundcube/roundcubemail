<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/list.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2007, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Send contacts list to client (as remote response)                   |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

// get contacts for this user
$result = $CONTACTS->list_records();

// update message count display
$OUTPUT->set_env('pagecount', ceil($result->count / $CONTACTS->page_size));
$OUTPUT->command('set_rowcount', rcmail_get_rowcount_text($rowcount));

// create javascript list
rcmail_js_contacts_list($result);
  
// send response
$OUTPUT->send();

