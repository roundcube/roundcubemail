<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/export.inc                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2008-2009, The Roundcube Dev Team                       |
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
  // we already have a vcard record
  if ($row['vcard']) {
    echo $row['vcard'];
  }
  // copy values into vcard object
  else {
    $vcard = new rcube_vcard($row['vcard']);
    $vcard->reset();
    foreach ($row as $key => $values) {
      list($field, $section) = explode(':', $key);
      foreach ((array)$values as $value) {
        if (is_array($value) || strlen($value))
          $vcard->set($field, $value, strtoupper($section));
      }
    }
    
    echo $vcard->export();
  }
}

exit;

