<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/func.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2007, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide addressbook functionality and GUI objects                   |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$SEARCH_MODS_DEFAULT = array('name'=>1, 'firstname'=>1, 'surname'=>1, 'email'=>1, '*'=>1);

// general definition of contact coltypes
$CONTACT_COLTYPES = array(
  'name'         => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'limit' => 1, 'label' => rcube_label('name'), 'category' => 'main'),
  'firstname'    => array('type' => 'text', 'size' => 19, 'maxlength' => 50, 'limit' => 1, 'label' => rcube_label('firstname'), 'category' => 'main'),
  'surname'      => array('type' => 'text', 'size' => 19, 'maxlength' => 50, 'limit' => 1, 'label' => rcube_label('surname'), 'category' => 'main'),
  'email'        => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'label' => rcube_label('email'), 'subtypes' => array('home','work','other'), 'category' => 'main'),
  'middlename'   => array('type' => 'text', 'size' => 19, 'maxlength' => 50, 'limit' => 1, 'label' => rcube_label('middlename'), 'category' => 'main'),
  'prefix'       => array('type' => 'text', 'size' => 8,  'maxlength' => 20, 'limit' => 1, 'label' => rcube_label('nameprefix'), 'category' => 'main'),
  'suffix'       => array('type' => 'text', 'size' => 8,  'maxlength' => 20, 'limit' => 1, 'label' => rcube_label('namesuffix'), 'category' => 'main'),
  'nickname'     => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'limit' => 1, 'label' => rcube_label('nickname'), 'category' => 'main'),
  'jobtitle'     => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'limit' => 1, 'label' => rcube_label('jobtitle'), 'category' => 'main'),
  'organization' => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'limit' => 1, 'label' => rcube_label('organization'), 'category' => 'main'),
  'department'   => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'limit' => 1, 'label' => rcube_label('department'), 'category' => 'main'),
  'gender'       => array('type' => 'select', 'limit' => 1, 'label' => rcube_label('gender'), 'options' => array('male' => rcube_label('male'), 'female' => rcube_label('female')), 'category' => 'personal'),
  'maidenname'   => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'limit' => 1, 'label' => rcube_label('maidenname'), 'category' => 'personal'),
  'phone'        => array('type' => 'text', 'size' => 40, 'maxlength' => 20, 'label' => rcube_label('phone'), 'subtypes' => array('home','home2','work','work2','mobile','main','homefax','workfax','car','pager','video','assistant','other'), 'category' => 'main'),
  'address'      => array('type' => 'composite', 'label' => rcube_label('address'), 'subtypes' => array('home','work','other'), 'childs' => array(
    'street'     => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'label' => rcube_label('street'), 'category' => 'main'),
    'locality'   => array('type' => 'text', 'size' => 28, 'maxlength' => 50, 'label' => rcube_label('locality'), 'category' => 'main'),
    'zipcode'    => array('type' => 'text', 'size' => 8,  'maxlength' => 15, 'label' => rcube_label('zipcode'), 'category' => 'main'),
    'region'     => array('type' => 'text', 'size' => 12, 'maxlength' => 50, 'label' => rcube_label('region'), 'category' => 'main'),
    'country'    => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'label' => rcube_label('country'), 'category' => 'main'),
  ), 'category' => 'main'),
  'birthday'     => array('type' => 'date', 'size' => 12, 'maxlength' => 16, 'label' => rcube_label('birthday'), 'limit' => 1, 'render_func' => 'rcmail_format_date_col', 'category' => 'personal'),
  'anniversary'  => array('type' => 'date', 'size' => 12, 'maxlength' => 16, 'label' => rcube_label('anniversary'), 'limit' => 1, 'render_func' => 'rcmail_format_date_col', 'category' => 'personal'),
  'website'      => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'label' => rcube_label('website'), 'subtypes' => array('homepage','work','blog','profile','other'), 'category' => 'main'),
  'im'           => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'label' => rcube_label('instantmessenger'), 'subtypes' => array('aim','icq','msn','yahoo','jabber','skype','other'), 'category' => 'main'),
  'notes'        => array('type' => 'textarea', 'size' => 40, 'rows' => 15, 'maxlength' => 500, 'label' => rcube_label('notes'), 'limit' => 1),
  'photo'        => array('type' => 'image', 'limit' => 1, 'category' => 'main'),
  'assistant'    => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'limit' => 1, 'label' => rcube_label('assistant'), 'category' => 'personal'),
  'manager'      => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'limit' => 1, 'label' => rcube_label('manager'), 'category' => 'personal'),
  'spouse'       => array('type' => 'text', 'size' => 40, 'maxlength' => 50, 'limit' => 1, 'label' => rcube_label('spouse'), 'category' => 'personal'),
  // TODO: define fields for vcards like GEO, KEY
);


// Addressbook UI
if (!$RCMAIL->action && !$OUTPUT->ajax_call) {
    // add list of address sources to client env
    $js_list = $RCMAIL->get_address_sources();

    $source = get_input_value('_source', RCUBE_INPUT_GPC);

    // use first directory by default
    if (!strlen($source) || !isset($js_list[$source]))
        $source = $js_list[key($js_list)]['id'];

    // count all/writeable sources
    $writeable = 0;
    $count = 0;
    foreach ($js_list as $sid => $s) {
        $count++;
        if (!$s['readonly']) {
            $writeable++;
        }
        // unset hidden sources
        if ($s['hidden']) {
            unset($js_list[$sid]);
        }
    }

    $search_mods = $RCMAIL->config->get('addressbook_search_mods', $SEARCH_MODS_DEFAULT);
    $OUTPUT->set_env('search_mods', $search_mods);
    $OUTPUT->set_env('address_sources', $js_list);
    $OUTPUT->set_env('writable_source', $writeable);

    $OUTPUT->set_pagetitle(rcube_label('addressbook'));
    $_SESSION['addressbooks_count'] = $count;
    $_SESSION['addressbooks_count_writeable'] = $writeable;

    if (!strlen($source))
      $source = strval(key($js_list));

    $CONTACTS = rcmail_contact_source($source, true);
}

// remove undo information...
if ($undo = $_SESSION['contact_undo']) {
    // ...after timeout
    $undo_time = $RCMAIL->config->get('undo_timeout', 0);
    if ($undo['ts'] < time() - $undo_time)
        $RCMAIL->session->remove('contact_undo');
}

// instantiate a contacts object according to the given source
function rcmail_contact_source($source=null, $init_env=false, $writable=false)
{
    global $RCMAIL, $OUTPUT, $CONFIG, $CONTACT_COLTYPES;

    if (!strlen($source)) {
        $source = get_input_value('_source', RCUBE_INPUT_GPC);
    }

    // Get object
    $CONTACTS = $RCMAIL->get_address_book($source, $writable);
    $CONTACTS->set_pagesize($CONFIG['pagesize']);

    // set list properties and session vars
    if (!empty($_GET['_page']))
        $CONTACTS->set_page(($_SESSION['page'] = intval($_GET['_page'])));
    else
        $CONTACTS->set_page(isset($_SESSION['page']) ? $_SESSION['page'] : 1);

    if (!empty($_REQUEST['_gid']))
        $CONTACTS->set_group(get_input_value('_gid', RCUBE_INPUT_GPC));

    if (!$init_env)
        return $CONTACTS;

    $OUTPUT->set_env('readonly', $CONTACTS->readonly);
    $OUTPUT->set_env('source', $source);

    // reduce/extend $CONTACT_COLTYPES with specification from the current $CONTACT object
    if (is_array($CONTACTS->coltypes)) {
        // remove cols not listed by the backend class
        $contact_cols = $CONTACTS->coltypes[0] ? array_flip($CONTACTS->coltypes) : $CONTACTS->coltypes;
        $CONTACT_COLTYPES = array_intersect_key($CONTACT_COLTYPES, $contact_cols);
        // add associative coltypes definition
        if (!$CONTACTS->coltypes[0]) {
            foreach ($CONTACTS->coltypes as $col => $colprop) {
                if (is_array($colprop['childs'])) {
                    foreach ($colprop['childs'] as $childcol => $childprop)
                        $colprop['childs'][$childcol] = array_merge((array)$CONTACT_COLTYPES[$col]['childs'][$childcol], $childprop);
                }
                $CONTACT_COLTYPES[$col] = $CONTACT_COLTYPES[$col] ? array_merge($CONTACT_COLTYPES[$col], $colprop) : $colprop;
            }
        }
    }

    $OUTPUT->set_env('photocol', is_array($CONTACT_COLTYPES['photo']));

    return $CONTACTS;
}


function rcmail_default_source($writable=false)
{
    global $RCMAIL;

    // get list of address sources
    $first = reset($RCMAIL->get_address_sources($writable));

    // use first directory by default
    return $first['id'];
}

function rcmail_set_sourcename($abook)
{
    global $OUTPUT;

    // get address book name (for display)
    if ($abook && $_SESSION['addressbooks_count'] > 1) {
        $name = $abook->get_name();
        if (!$name && $source == 0) {
            $name = rcube_label('personaladrbook');
        }
        $OUTPUT->set_env('sourcename', $name);
    }
}


function rcmail_directory_list($attrib)
{
    global $RCMAIL, $OUTPUT;

    if (!$attrib['id'])
        $attrib['id'] = 'rcmdirectorylist';

    $out = '';
    $local_id = '0';
    $jsdata = array();

    $line_templ = html::tag('li', array(
        'id' => 'rcmli%s', 'class' => '%s'),
        html::a(array('href' => '%s',
            'rel' => '%s',
            'onclick' => "return ".JS_OBJECT_NAME.".command('list','%s',this)"), '%s'));

    $sources = (array) $OUTPUT->env['address_sources'];
    reset($sources);

    // currently selected source
    $current = get_input_value('_source', RCUBE_INPUT_GPC);

    foreach ($sources as $j => $source) {
        $id = strval(strlen($source['id']) ? $source['id'] : $j);
        $js_id = JQ($id);

        // set class name(s)
        $class_name = 'addressbook';
        if ($current === $id)
            $class_name .= ' selected';
        if ($source['readonly'])
            $class_name .= ' readonly';
        if ($source['class_name'])
            $class_name .= ' ' . $source['class_name'];

        $out .= sprintf($line_templ,
            html_identifier($id),
            $class_name,
            Q(rcmail_url(null, array('_source' => $id))),
            $source['id'],
            $js_id, (!empty($source['name']) ? Q($source['name']) : Q($id)));

        $groupdata = array('out' => $out, 'jsdata' => $jsdata, 'source' => $id);
        if ($source['groups'])
            $groupdata = rcmail_contact_groups($groupdata);
        $jsdata = $groupdata['jsdata'];
        $out = $groupdata['out'];
    }

    $line_templ = html::tag('li', array(
        'id' => 'rcmliS%s', 'class' => '%s'),
        html::a(array('href' => '#', 'rel' => 'S%s',
            'onclick' => "return ".JS_OBJECT_NAME.".command('listsearch', '%s', this)"), '%s'));

    // Saved searches
    $sources = $RCMAIL->user->list_searches(rcube_user::SEARCH_ADDRESSBOOK);
    foreach ($sources as $j => $source) {
        $id = $source['id'];
        $js_id = JQ($id);

        // set class name(s)
        $class_name = 'contactsearch';
        if ($current === $id)
            $class_name .= ' selected';
        if ($source['class_name'])
            $class_name .= ' ' . $source['class_name'];

        $out .= sprintf($line_templ,
            html_identifier($id),
            $class_name,
            $id,
            $js_id, (!empty($source['name']) ? Q($source['name']) : Q($id)));
    }

    $OUTPUT->set_env('contactgroups', $jsdata);
    $OUTPUT->add_gui_object('folderlist', $attrib['id']);
    // add some labels to client
    $OUTPUT->add_label('deletegroupconfirm', 'groupdeleting', 'addingmember', 'removingmember');

    return html::tag('ul', $attrib, $out, html::$common_attrib);
}


function rcmail_contact_groups($args)
{
    global $RCMAIL;

    $groups = $RCMAIL->get_address_book($args['source'])->list_groups();

    if (!empty($groups)) {
        $line_templ = html::tag('li', array(
            'id' => 'rcmliG%s', 'class' => 'contactgroup'),
            html::a(array('href' => '#',
                'rel' => '%s:%s',
                'onclick' => "return ".JS_OBJECT_NAME.".command('listgroup',{'source':'%s','id':'%s'},this)"), '%s'));

        $jsdata = array();
        foreach ($groups as $group) {
            $args['out'] .= sprintf($line_templ,
                html_identifier($args['source'] . $group['ID']),
                $args['source'], $group['ID'],
                $args['source'], $group['ID'], Q($group['name'])
            );
            $args['jsdata']['G'.$args['source'].$group['ID']] = array(
                'source' => $args['source'], 'id' => $group['ID'],
                'name' => $group['name'], 'type' => 'group');
        }
    }

    return $args;
}


// return the contacts list as HTML table
function rcmail_contacts_list($attrib)
{
    global $CONTACTS, $OUTPUT;

    // define list of cols to be displayed
    $a_show_cols = array('name');

    // add id to message list table if not specified
    if (!strlen($attrib['id']))
        $attrib['id'] = 'rcmAddressList';

    // create XHTML table
    $out = rcube_table_output($attrib, array(), $a_show_cols, $CONTACTS->primary_key);

    // set client env
    $OUTPUT->add_gui_object('contactslist', $attrib['id']);
    $OUTPUT->set_env('current_page', (int)$CONTACTS->list_page);
    $OUTPUT->include_script('list.js');

    // add some labels to client
    $OUTPUT->add_label('deletecontactconfirm', 'copyingcontact', 'contactdeleting');

    return $out;
}


function rcmail_js_contacts_list($result, $prefix='')
{
    global $OUTPUT;

    if (empty($result) || $result->count == 0)
        return;

    // define list of cols to be displayed
    $a_show_cols = array('name');

    while ($row = $result->next()) {
        $a_row_cols = array();

        // build contact ID with source ID
        if (isset($row['sourceid'])) {
            $row['ID'] = $row['ID'].'-'.$row['sourceid'];
        }

        // format each col
        foreach ($a_show_cols as $col) {
            $val = $row[$col];
            if ($val == '' && $col == 'name') {
                $val = rcube_addressbook::compose_display_name($row, true);
            }

            $a_row_cols[$col] = Q($val);
	}

        $OUTPUT->command($prefix.'add_contact_row', $row['ID'], $a_row_cols);
    }
}


// similar function as /steps/settings/identities.inc::rcmail_identity_frame()
function rcmail_contact_frame($attrib)
{
    global $OUTPUT;

    if (!$attrib['id'])
        $attrib['id'] = 'rcmcontactframe';

    $attrib['name'] = $attrib['id'];

    $OUTPUT->set_env('contentframe', $attrib['name']);
    $OUTPUT->set_env('blankpage', $attrib['src'] ? $OUTPUT->abs_url($attrib['src']) : 'program/blank.gif');

    return html::iframe($attrib);
}


function rcmail_rowcount_display($attrib)
{
    global $OUTPUT;

    if (!$attrib['id'])
        $attrib['id'] = 'rcmcountdisplay';

    $OUTPUT->add_gui_object('countdisplay', $attrib['id']);

    return html::span($attrib, rcube_label('loading'));
}


function rcmail_get_rowcount_text($result=null)
{
    global $CONTACTS, $CONFIG;

    // read nr of contacts
    if (!$result) {
        $result = $CONTACTS->get_result();
    }

    if ($result->count == 0)
        $out = rcube_label('nocontactsfound');
    else
        $out = rcube_label(array(
            'name'  => 'contactsfromto',
            'vars'  => array(
            'from'  => $result->first + 1,
            'to'    => min($result->count, $result->first + $CONFIG['pagesize']),
            'count' => $result->count)
        ));

    return $out;
}


function rcmail_get_type_label($type)
{
    $label = 'type'.$type;
    if (rcube_label_exists($label, '*', $domain))
        return rcube_label($label, $domain);
    else if (preg_match('/\w+(\d+)$/', $label, $m)
            && ($label = preg_replace('/(\d+)$/', '', $label))
            && rcube_label_exists($label, '*', $domain))
        return rcube_label($label, $domain) . ' ' . $m[1];

    return ucfirst($type);
}


function rcmail_contact_form($form, $record, $attrib = null)
{
    global $RCMAIL, $CONFIG;
    static $jqueryui_loaded = 0;

    // Allow plugins to modify contact form content
    $plugin = $RCMAIL->plugins->exec_hook('contact_form', array(
        'form' => $form, 'record' => $record));

    $form = $plugin['form'];
    $record = $plugin['record'];
    $edit_mode = $RCMAIL->action != 'show';
    $del_button = $attrib['deleteicon'] ? html::img(array('src' => $CONFIG['skin_path'] . $attrib['deleteicon'], 'alt' => rcube_label('delete'))) : rcube_label('delete');
    unset($attrib['deleteicon']);
    $out = '';

    // get default coltypes
    $coltypes = $GLOBALS['CONTACT_COLTYPES'];
    $coltype_labels = array();

    foreach ($coltypes as $col => $prop) {
        if ($prop['subtypes']) {
            $subtype_names = array_map('rcmail_get_type_label', $prop['subtypes']);
            $select_subtype = new html_select(array('name' => '_subtype_'.$col.'[]', 'class' => 'contactselectsubtype'));
            $select_subtype->add($subtype_names, $prop['subtypes']);
            $coltypes[$col]['subtypes_select'] = $select_subtype->show();
        }
        if ($prop['childs']) {
            foreach ($prop['childs'] as $childcol => $cp)
                $coltype_labels[$childcol] = array('label' => $cp['label']);
        }
    }

    foreach ($form as $section => $fieldset) {
        // skip empty sections
        if (empty($fieldset['content']))
            continue;

        $select_add = new html_select(array('class' => 'addfieldmenu', 'rel' => $section));
        $select_add->add(rcube_label('addfield'), '');

        // render head section with name fields (not a regular list of rows)
        if ($section == 'head') {
            $content = '';

            // unset display name if it is composed from name parts
            if ($record['name'] == rcube_addressbook::compose_display_name(array('name' => '') + (array)$record))
              unset($record['name']);

            // group fields
            $field_blocks = array(
                'names'    => array('prefix','firstname','middlename','surname','suffix'),
                'displayname' => array('name'),
                'nickname' => array('nickname'),
                'organization' => array('organization'),
                'department' => array('department'),
                'jobtitle' => array('jobtitle'),
            );
            foreach ($field_blocks as $blockname => $colnames) {
                $fields = '';
                foreach ($colnames as $col) {
                    // skip cols unknown to the backend
                    if (!$coltypes[$col])
                        continue;

                    // only string values are expected here
                    if (is_array($record[$col]))
                        $record[$col] = join(' ', $record[$col]);

                    if ($RCMAIL->action == 'show') {
                        if (!empty($record[$col]))
                            $fields .= html::span('namefield ' . $col, Q($record[$col])) . " ";
                    }
                    else {
                        $colprop = (array)$fieldset['content'][$col] + (array)$coltypes[$col];
                        $colprop['id'] = 'ff_'.$col;
                        if (empty($record[$col]) && !$colprop['visible']) {
                            $colprop['style'] = 'display:none';
                            $select_add->add($colprop['label'], $col);
                        }
                        $fields .= rcmail_get_edit_field($col, $record[$col], $colprop, $colprop['type']);
                    }
                }
                $content .= html::div($blockname, $fields);
            }

            if ($edit_mode)
                $content .= html::p('addfield', $select_add->show(null));

            $out .= html::tag('fieldset', $attrib, (!empty($fieldset['name']) ? html::tag('legend', null, Q($fieldset['name'])) : '') . $content) ."\n";
            continue;
        }

        $content = '';
        if (is_array($fieldset['content'])) {
            foreach ($fieldset['content'] as $col => $colprop) {
                // remove subtype part of col name
                list($field, $subtype) = explode(':', $col);
                if (!$subtype) $subtype = 'home';
                $fullkey = $col.':'.$subtype;

                // skip cols unknown to the backend
                if (!$coltypes[$field])
                    continue;

                // merge colprop with global coltype configuration
                $colprop += $coltypes[$field];
                $label = isset($colprop['label']) ? $colprop['label'] : rcube_label($col);

                // prepare subtype selector in edit mode
                if ($edit_mode && is_array($colprop['subtypes'])) {
                    $subtype_names = array_map('rcmail_get_type_label', $colprop['subtypes']);
                    $select_subtype = new html_select(array('name' => '_subtype_'.$col.'[]', 'class' => 'contactselectsubtype'));
                    $select_subtype->add($subtype_names, $colprop['subtypes']);
                }
                else
                    $select_subtype = null;

                if (!empty($colprop['value'])) {
                    $values = (array)$colprop['value'];
                }
                else {
                    // iterate over possible subtypes and collect values with their subtype
                    if (is_array($colprop['subtypes'])) {
                        $values = $subtypes = array();
                        foreach ($colprop['subtypes'] as $i => $st) {
                            $newval = false;
                            if ($record[$field.':'.$st]) {
                                $subtypes[count($values)] = $st;
                                $newval = $record[$field.':'.$st];
                            }
                            else if ($i == 0 && $record[$field]) {
                                $subtypes[count($values)] = $st;
                                $newval = $record[$field];
                            }
                            if ($newval !== false) {
                                if (is_array($newval) && isset($newval[0]))
                                    $values = array_merge($values, $newval);
                                else
                                    $values[] = $newval;
                            }
                        }
                    }
                    else {
                        $values = $record[$fullkey] ? $record[$fullkey] : $record[$field];
                        $subtypes = null;
                    }
                }

                // hack: create empty values array to force this field to be displayed
                if (empty($values) && $colprop['visible'])
                    $values[] = '';

                $rows = '';
                foreach ((array)$values as $i => $val) {
                    if ($subtypes[$i])
                        $subtype = $subtypes[$i];

                    // render composite field
                    if ($colprop['type'] == 'composite') {
                        $composite = array(); $j = 0;
                        $template = $RCMAIL->config->get($col . '_template', '{'.join('} {', array_keys($colprop['childs'])).'}');
                        foreach ($colprop['childs'] as $childcol => $cp) {
                            $childvalue = $val[$childcol] ? $val[$childcol] : $val[$j];

                            if ($edit_mode) {
                                if ($colprop['subtypes'] || $colprop['limit'] != 1) $cp['array'] = true;
                                $composite['{'.$childcol.'}'] = rcmail_get_edit_field($childcol, $childvalue, $cp, $cp['type']) . " ";
                            }
                            else {
                                $childval = $cp['render_func'] ? call_user_func($cp['render_func'], $childvalue, $childcol) : Q($childvalue);
                                $composite['{'.$childcol.'}'] = html::span('data ' . $childcol, $childval) . " ";
                            }
                            $j++;
                        }

                        $coltypes[$field] += (array)$colprop;
                        $coltypes[$field]['count']++;
                        $val = preg_replace('/\{\w+\}/', '', strtr($template, $composite));
                    }
                    else if ($edit_mode) {
                        // call callback to render/format value
                        if ($colprop['render_func'])
                            $val = call_user_func($colprop['render_func'], $val, $col);

                        $coltypes[$field] = (array)$colprop + $coltypes[$field];

                        if ($colprop['subtypes'] || $colprop['limit'] != 1)
                            $colprop['array'] = true;

                        // load jquery UI datepickert for date fields
                        if ($colprop['type'] == 'date') {
                            if (!$jqueryui_loaded++) {
                                $RCMAIL->plugins->load_plugin('jqueryui');
                                $RCMAIL->output->set_env('date_format', strtr($RCMAIL->config->get('date_format', 'Y-m-d'), array('y'=>'y', 'Y'=>'yy', 'm'=>'mm', 'n'=>'m', 'd'=>'dd', 'j'=>'d')));
                                foreach (array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec') as $month)
                                    $month_names[] = rcube_label($month);
                                $RCMAIL->output->set_env('month_names', $month_names);
                            }
                            $colprop['class'] .= ($colprop['class'] ? ' ' : '') . 'datepicker';
                            $val = format_date($val, $RCMAIL->config->get('date_format', 'Y-m-d'), false);
                        }

                        $val = rcmail_get_edit_field($col, $val, $colprop, $colprop['type']);
                        $coltypes[$field]['count']++;
                    }
                    else if ($colprop['render_func'])
                        $val = call_user_func($colprop['render_func'], $val, $col);
                    else if (is_array($colprop['options']) && isset($colprop['options'][$val]))
                        $val = $colprop['options'][$val];
                    else
                        $val = Q($val);

                    // use subtype as label
                    if ($colprop['subtypes'])
                        $label = rcmail_get_type_label($subtype);

                    // add delete button/link
                    if ($edit_mode && !($colprop['visible'] && $colprop['limit'] == 1))
                        $val .= html::a(array('href' => '#del', 'class' => 'contactfieldbutton deletebutton', 'title' => rcube_label('delete'), 'rel' => $col), $del_button);

                    // display row with label
                    if ($label) {
                        $rows .= html::div('row',
                            html::div('contactfieldlabel label', $select_subtype ? $select_subtype->show($subtype) : Q($label)) .
                            html::div('contactfieldcontent '.$colprop['type'], $val));
                    }
                    else   // row without label
                        $rows .= html::div('row', html::div('contactfield', $val));
                }

                // add option to the add-field menu
                if (!$colprop['limit'] || $coltypes[$field]['count'] < $colprop['limit']) {
                    $select_add->add($colprop['label'], $col);
                    $select_add->_count++;
                }

                // wrap rows in fieldgroup container
                if ($rows) {
                    $content .= html::tag('fieldset', array('class' => 'contactfieldgroup ' . ($colprop['subtypes'] ? 'contactfieldgroupmulti ' : '') . 'contactcontroller' . $col, 'style' => ($rows ? null : 'display:none')),
                      ($colprop['subtypes'] ? html::tag('legend', null, Q($colprop['label'])) : ' ') .
                      $rows);
                }
            }

            if (!$content)
                continue;

            // also render add-field selector
            if ($edit_mode)
                $content .= html::p('addfield', $select_add->show(null, array('style' => $select_add->_count ? null : 'display:none')));

            $content = html::div(array('id' => 'contactsection' . $section), $content);
        }
        else {
            $content = $fieldset['content'];
        }

        if ($content)
            $out .= html::tag('fieldset', null, html::tag('legend', null, Q($fieldset['name'])) . $content) ."\n";
    }

    if ($edit_mode) {
      $RCMAIL->output->set_env('coltypes', $coltypes + $coltype_labels);
      $RCMAIL->output->set_env('delbutton', $del_button);
      $RCMAIL->output->add_label('delete');
    }

    return $out;
}


function rcmail_contact_photo($attrib)
{
    global $SOURCE_ID, $CONTACTS, $CONTACT_COLTYPES, $RCMAIL, $CONFIG;

    if (!$CONTACT_COLTYPES['photo'])
        return '';

    if ($result = $CONTACTS->get_result())
        $record = $result->first();

    $photo_img = $attrib['placeholder'] ? $CONFIG['skin_path'] . $attrib['placeholder'] : 'program/blank.gif';
    $RCMAIL->output->set_env('photo_placeholder', $photo_img);
    unset($attrib['placeholder']);

    if (preg_match('!^https?://!i', $record['photo']))
        $photo_img = $record['photo'];
    else if ($record['photo'])
        $photo_img = $RCMAIL->url(array('_action' => 'photo', '_cid' => $record['ID'], '_source' => $SOURCE_ID));
    else
        $ff_value = '-del-'; // will disable delete-photo action

    $img = html::img(array('src' => $photo_img, 'border' => 1, 'alt' => ''));
    $content = html::div($attrib, $img);

    if ($RCMAIL->action == 'edit' || $RCMAIL->action == 'add') {
        $RCMAIL->output->add_gui_object('contactphoto', $attrib['id']);
        $hidden = new html_hiddenfield(array('name' => '_photo', 'id' => 'ff_photo', 'value' => $ff_value));
        $content .= $hidden->show();
    }

    return $content;
}


function rcmail_format_date_col($val)
{
    global $RCMAIL;
    return format_date($val, $RCMAIL->config->get('date_format', 'Y-m-d'), false);
}


/**
 * Returns contact ID(s) and source(s) from GET/POST data
 *
 * @return array List of contact IDs per-source
 */
function rcmail_get_cids()
{
    // contact ID (or comma-separated list of IDs) is provided in two
    // forms. If _source is an empty string then the ID is a string
    // containing contact ID and source name in form: <ID>-<SOURCE>

    $cid    = get_input_value('_cid', RCUBE_INPUT_GPC);
    $source = (string) get_input_value('_source', RCUBE_INPUT_GPC);

    if (!preg_match('/^[a-zA-Z0-9\+\/=_-]+(,[a-zA-Z0-9\+\/=_-]+)*$/', $cid)) {
        return array();
    }

    $cid        = explode(',', $cid);
    $got_source = strlen($source);
    $result     = array();

    // create per-source contact IDs array
    foreach ($cid as $id) {
        // if _source is not specified we'll find it from decoded ID
        if (!$got_source) {
            if ($sep = strrpos($id, '-')) {
                $contact_id = substr($id, 0, $sep);
                $source_id  = substr($id, $sep+1);
                if (strlen($source_id)) {
                    $result[(string)$source_id][] = $contact_id;
                }
            }
        }
        else {
            $result[$source][] = $id;
        }
    }

    return $result;
}

// register UI objects
$OUTPUT->add_handlers(array(
    'directorylist' => 'rcmail_directory_list',
//  'groupslist' => 'rcmail_contact_groups',
    'addresslist' => 'rcmail_contacts_list',
    'addressframe' => 'rcmail_contact_frame',
    'recordscountdisplay' => 'rcmail_rowcount_display',
    'searchform' => array($OUTPUT, 'search_form')
));

// register action aliases
$RCMAIL->register_action_map(array(
    'add' => 'edit.inc',
    'photo' => 'show.inc',
    'group-create' => 'groups.inc',
    'group-rename' => 'groups.inc',
    'group-delete' => 'groups.inc',
    'group-addmembers' => 'groups.inc',
    'group-delmembers' => 'groups.inc',
    'search-create' => 'search.inc',
    'search-delete' => 'search.inc',
));
