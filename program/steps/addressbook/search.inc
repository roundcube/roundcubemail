<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/search.inc                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Search action (and form) for address book contacts                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 +-----------------------------------------------------------------------+

 $Id: search.inc 456 2007-01-10 12:34:33Z thomasb $

*/

if (!isset($_GET['_form'])) {
    rcmail_contact_search();
}

$OUTPUT->add_handler('searchform', 'rcmail_contact_search_form');
$OUTPUT->send('contactsearch');


function rcmail_contact_search()
{
    global $RCMAIL, $OUTPUT, $CONFIG, $SEARCH_MODS_DEFAULT;

    $adv = isset($_POST['_adv']);

    // get fields/values from advanced search form
    if ($adv) {
        foreach (array_keys($_POST) as $key) {
            $s = trim(get_input_value($key, RCUBE_INPUT_POST, true));
            if (strlen($s) && preg_match('/^_search_([a-zA-Z0-9_-]+)$/', $key, $m)) {
                $search[] = $s;
                $fields[] = $m[1];
            }
        }

        if (empty($fields)) {
            // do nothing, show the form again
            return;
        }
    }
    // quick-search
    else {
        $search = trim(get_input_value('_q', RCUBE_INPUT_GET, true));
        $fields = explode(',', get_input_value('_headers', RCUBE_INPUT_GET));

        if (empty($fields)) {
            $fields = $SEARCH_MODS_DEFAULT;
        }

        // update search_mods setting
        $old_mods = $RCMAIL->config->get('addressbook_search_mods');
        $search_mods = array_fill_keys($fields, 1);
        if ($old_mods != $search_mods) {
            $RCMAIL->user->save_prefs(array('addressbook_search_mods' => $search_mods));
        }

        if ($fields['*'] || count($fields) == count($SEARCH_MODS_DEFAULT)) {
            $fields = '*';
        }
    }

    // get sources list
    $sources    = $RCMAIL->get_address_sources();
    $search_set = array();
    $records    = array();

    foreach ($sources as $s) {
        $source = $RCMAIL->get_address_book($s['id']);

        // check if all search fields are supported....
        if (is_array($fields)) {
            $cols = $source->coltypes[0] ? array_flip($source->coltypes) : $source->coltypes;
            $supported = 0;

            foreach ($fields as $f) {
                if (array_key_exists($f, $cols)) {
                    $supported ++;
                }
            }

            // ...if not, we can skip this source
            if ($supported < count($fields)) {
                continue;
            }
        }

        // reset page
        $source->set_page(1);
        $source->set_pagesize(9999);

        // get contacts count
        $result = $source->search($fields, $search, false, false);

        if (!$result->count) {
            continue;
        }

        // get records
        $result = $source->list_records(array('name', 'email'));

        while ($row = $result->next()) {
            $row['sourceid'] = $s['id'];
            $key = $row['name'] . ':' . $row['sourceid'];
            $records[$key] = $row;
        }

        unset($result);
        $search_set[$s['id']] = $source->get_search_set();
    }

    // sort the records
    ksort($records, SORT_LOCALE_STRING);

    // create resultset object
    $count  = count($records);
    $result = new rcube_result_set($count);

    // cut first-page records
    if ($CONFIG['pagesize'] < $count) {
        $records = array_slice($records, 0, $CONFIG['pagesize']);
    }

    $result->records = array_values($records);

    // search request ID
    $search_request = md5('addr'
        .(is_array($fields) ? implode($fields, ',') : $fields)
        .(is_array($search) ? implode($search, ',') : $search));

    // save search settings in session
    $_SESSION['search'][$search_request] = $search_set;
    $_SESSION['page'] = 1;

    if ($adv)
        $OUTPUT->command('list_contacts_clear');

    if ($result->count > 0) {
        // create javascript list
        rcmail_js_contacts_list($result);
    }
    else {
        $OUTPUT->show_message('nocontactsfound', 'notice');
    }

    // update message count display
    $OUTPUT->command('set_env', 'search_request', $search_request);
    $OUTPUT->command('set_env', 'pagecount', ceil($result->count / $CONFIG['pagesize']));
    $OUTPUT->command('set_rowcount', rcmail_get_rowcount_text($result));

    // unselect currently selected directory/group
    $OUTPUT->command('unselect_directory');
    $OUTPUT->command('update_group_commands');

    // send response
    $OUTPUT->send($adv ? 'iframe' : null);
}

function rcmail_contact_search_form($attrib)
{
    global $RCMAIL, $CONTACT_COLTYPES;

    $i_size = !empty($attrib['size']) ? $attrib['size'] : 30;

    $form = array(
        'main' => array(
            'name'    => rcube_label('contactproperties'),
            'content' => array(
            ),
        ),
        'personal' => array(
            'name'    => rcube_label('personalinfo'),
            'content' => array(
            ),
        ),
        'other' => array(
            'name'    => rcube_label('other'),
            'content' => array(
            ),
        ),
    );

    // get supported coltypes from all address sources
    $sources  = $RCMAIL->get_address_sources();
    $coltypes = array();

    foreach ($sources as $s) {
        $CONTACTS = $RCMAIL->get_address_book($s['id']);

        if (is_array($CONTACTS->coltypes)) {
            $contact_cols = $CONTACTS->coltypes[0] ? array_flip($CONTACTS->coltypes) : $CONTACTS->coltypes;
            $coltypes = array_merge($coltypes, $contact_cols);
        }
    }

    // merge supported coltypes with $CONTACT_COLTYPES
    foreach ($coltypes as $col => $colprop) {
        $coltypes[$col] = $CONTACT_COLTYPES[$col] ? array_merge($CONTACT_COLTYPES[$col], (array)$colprop) : (array)$colprop;
    }

    // build form fields list
    foreach ($coltypes as $col => $colprop)
    {
        if ($colprop['type'] != 'image' && !$colprop['nosearch'])
        {
            $ftype    = $colprop['type'] == 'select' ? 'select' : 'text';
            $label    = isset($colprop['label']) ? $colprop['label'] : rcube_label($col);
            $category = $colprop['category'] ? $colprop['category'] : 'other';

            if ($ftype == 'text')
                $colprop['size'] = $i_size;

            $content  = html::div('row', html::div('contactfieldlabel label', Q($label))
                . html::div('contactfieldcontent', rcmail_get_edit_field('search_'.$col, '', $colprop, $ftype)));

            $form[$category]['content'][] = $content;
        }
    }

    $hiddenfields = new html_hiddenfield();
    $hiddenfields->add(array('name' => '_adv', 'value' => 1));

    $out = $RCMAIL->output->request_form(array(
        'name' => 'form', 'method' => 'post',
        'task' => $RCMAIL->task, 'action' => 'search',
        'noclose' => true) + $attrib, $hiddenfields->show());

    $RCMAIL->output->add_gui_object('editform', $attrib['id']);

    unset($attrib['name']);
    unset($attrib['id']);

    foreach ($form as $f) {
        if (!empty($f['content'])) {
            $content = html::div('contactfieldgroup', join("\n", $f['content']));

            $out .= html::tag('fieldset', $attrib,
                html::tag('legend', null, Q($f['name']))
                . $content) . "\n";
        }
    }

    return $out . '</form>';
}
