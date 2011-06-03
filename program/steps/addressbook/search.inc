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
    global $RCMAIL, $OUTPUT, $CONTACTS, $CONTACT_COLTYPES, $SEARCH_MODS_DEFAULT;

    $adv = isset($_POST['_adv']);

    // get fields/values from advanced search form
    if ($adv) {
        foreach ($CONTACT_COLTYPES as $col => $colprop) {
            $s = trim(get_input_value('_'.$col, RCUBE_INPUT_POST, true));
            if (strlen($s)) {
                $search[] = $s;
                $fields[] = $col;
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

    // search request ID
    $search_request = md5('addr'.implode($fields, ',')
        .(is_array($search) ? implode($search, ',') : $search));

    // reset page
    $CONTACTS->set_page(1);
    $_SESSION['page'] = 1;

    // get contacts for this user
    $result = $CONTACTS->search($fields, $search);

    // save search settings in session
    $_SESSION['search'][$search_request] = $CONTACTS->get_search_set();

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
    $OUTPUT->command('set_env', 'pagecount', ceil($result->count / $CONTACTS->page_size));
    $OUTPUT->command('set_rowcount', rcmail_get_rowcount_text());

    // send response
    $OUTPUT->send($adv ? 'iframe' : null);
}

function rcmail_contact_search_form($attrib)
{
    global $RCMAIL, $CONTACTS, $CONTACT_COLTYPES;

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

    foreach ($CONTACT_COLTYPES as $col => $colprop)
    {
        if ($colprop['type'] != 'image' && !$colprop['nosearch'])
        {
            $ftype    = $colprop['type'] == 'select' ? 'select' : 'text';
            $label    = isset($colprop['label']) ? $colprop['label'] : rcube_label($col);
            $category = $colprop['category'] ? $colprop['category'] : 'other';

            if ($ftype == 'text')
                $colprop['size'] = $i_size;

            $content  = html::div('row', html::div('contactfieldlabel label', Q($label))
                . html::div('contactfieldcontent', rcmail_get_edit_field($col, '', $colprop, $ftype)));

            $form[$category]['content'][] = $content;
        }
    }

    $hiddenfields = new html_hiddenfield(array(
        'name' => '_source', 'value' => get_input_value('_source', RCUBE_INPUT_GPC)));
    $hiddenfields->add(array('name' => '_gid', 'value' => $CONTACTS->group_id));
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
