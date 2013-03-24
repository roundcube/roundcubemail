<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/export.inc                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2008-2011, The Roundcube Dev Team                       |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Export the selected address book as vCard file                      |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 +-----------------------------------------------------------------------+
*/

// Use search result
if (!empty($_REQUEST['_search']) && isset($_SESSION['search'][$_REQUEST['_search']]))
{
    $sort_col = $RCMAIL->config->get('addressbook_sort_col', 'name');
    $search  = (array)$_SESSION['search'][$_REQUEST['_search']];
    $records = array();

    // Get records from all sources
    foreach ($search as $s => $set) {
        $source = $RCMAIL->get_address_book($s);

        // reset page
        $source->set_page(1);
        $source->set_pagesize(99999);
        $source->set_search_set($set);

        // get records
        $result = $source->list_records();

        while ($row = $result->next()) {
            $row['sourceid'] = $s;
            $key = rcube_addressbook::compose_contact_key($row, $sort_col);
            $records[$key] = $row;
        }
        unset($result);
    }

    // sort the records
    ksort($records, SORT_LOCALE_STRING);

    // create resultset object
    $count  = count($records);
    $result = new rcube_result_set($count);
    $result->records = array_values($records);
}
// selected directory/group
else {
    $CONTACTS = rcmail_contact_source(null, true);

    // get contacts for this user
    $CONTACTS->set_page(1);
    $CONTACTS->set_pagesize(99999);
    $result = $CONTACTS->list_records(null, 0, true);
}

// send downlaod headers
header('Content-Type: text/x-vcard; charset='.RCMAIL_CHARSET);
header('Content-Disposition: attachment; filename="rcube_contacts.vcf"');

while ($result && ($row = $result->next())) {
    // we already have a vcard record
    if ($row['vcard'] && $row['name']) {
        // fix folding and end-of-line chars
        $row['vcard'] = preg_replace('/\r|\n\s+/', '', $row['vcard']);
        $row['vcard'] = preg_replace('/\n/', rcube_vcard::$eol, $row['vcard']);
        echo rcube_vcard::rfc2425_fold($row['vcard']) . rcube_vcard::$eol;
    }
    // copy values into vcard object
    else {
        $vcard = new rcube_vcard();
        $vcard->extend_fieldmap($CONTACTS->vcard_map);
        $vcard->load($row['vcard']);
        $vcard->reset();

        foreach ($row as $key => $values) {
            list($field, $section) = explode(':', $key);
            foreach ((array)$values as $value) {
                if (is_array($value) || @strlen($value))
                    $vcard->set($field, $value, strtoupper($section));
            }
        }

        echo $vcard->export(true) . rcube_vcard::$eol;
    }
}

exit;
