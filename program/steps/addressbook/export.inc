<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/export.inc                                  |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2008-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Export the selected address book as vCard file                      |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id:  $

*/

// get contacts for this user
$CONTACTS->set_page(1);
$CONTACTS->set_pagesize(99999);
$result = $CONTACTS->list_records(null, 0, true);

// send downlaod headers
send_nocacheing_headers();
header('Content-Type: text/x-vcard; charset='.RCMAIL_CHARSET);
header('Content-Disposition: attachment; filename="rcube_contacts.vcf"');

while ($result && ($row = $result->next())) {
  $vcard = new rcube_vcard($row['vcard']);
  $vcard->set('displayname', $row['name']);
  $vcard->set('firstname', $row['firstname']);
  $vcard->set('surname', $row['surname']);
  $vcard->set('email', $row['email']);
  
  echo $vcard->export();
}

exit;

