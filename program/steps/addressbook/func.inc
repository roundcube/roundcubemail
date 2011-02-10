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

// add list of address sources to client env
$js_list = $RCMAIL->get_address_sources();

// select source
$source = get_input_value('_source', RCUBE_INPUT_GPC);

// if source is not set use first directory
if (empty($source))
    $source = $js_list[key($js_list)]['id'];

// instantiate a contacts object according to the given source
$CONTACTS = $RCMAIL->get_address_book($source);

$CONTACTS->set_pagesize($CONFIG['pagesize']);

// set list properties and session vars
if (!empty($_GET['_page']))
    $CONTACTS->set_page(($_SESSION['page'] = intval($_GET['_page'])));
else
    $CONTACTS->set_page(isset($_SESSION['page']) ?$_SESSION['page'] : 1);
  
if (!empty($_REQUEST['_gid']))
    $CONTACTS->set_group(get_input_value('_gid', RCUBE_INPUT_GPC));

// set message set for search result
if (!empty($_REQUEST['_search']) && isset($_SESSION['search'][$_REQUEST['_search']]))
    $CONTACTS->set_search_set($_SESSION['search'][$_REQUEST['_search']]);

// set data source env
$OUTPUT->set_env('source', $source ? $source : '0');
$OUTPUT->set_env('readonly', $CONTACTS->readonly, false);
if (!$OUTPUT->ajax_call) {
    $OUTPUT->set_env('address_sources', $js_list);
    $OUTPUT->set_pagetitle(rcube_label('addressbook'));
}


// general definition of contact coltypes
$CONTACT_COLTYPES = array(
  'name'         => array('type' => 'text', 'size' => 40, 'limit' => 1, 'label' => rcube_label('name')),
  'firstname'    => array('type' => 'text', 'size' => 19, 'limit' => 1, 'label' => rcube_label('firstname')),
  'surname'      => array('type' => 'text', 'size' => 19, 'limit' => 1, 'label' => rcube_label('surname')),
  'middlename'   => array('type' => 'text', 'size' => 19, 'limit' => 1, 'label' => rcube_label('middlename')),
  'prefix'       => array('type' => 'text', 'size' => 8,  'limit' => 1, 'label' => rcube_label('nameprefix')),
  'suffix'       => array('type' => 'text', 'size' => 8,  'limit' => 1, 'label' => rcube_label('namesuffix')),
  'nickname'     => array('type' => 'text', 'size' => 40, 'limit' => 1, 'label' => rcube_label('nickname')),
  'jobtitle'     => array('type' => 'text', 'size' => 40, 'limit' => 1, 'label' => rcube_label('jobtitle')),
  'organization' => array('type' => 'text', 'size' => 19, 'limit' => 1, 'label' => rcube_label('organization')),
  'department'   => array('type' => 'text', 'size' => 19, 'limit' => 1, 'label' => rcube_label('department')),
  'gender'       => array('type' => 'select', 'limit' => 1, 'label' => rcube_label('gender'), 'options' => array('male' => rcube_label('male'), 'female' => rcube_label('female'))),
  'maidenname'   => array('type' => 'text', 'size' => 40, 'limit' => 1, 'label' => rcube_label('maidenname')),
  'email'        => array('type' => 'text', 'size' => 40, 'label' => rcube_label('email'), 'subtypes' => array('home','work','other')),
  'phone'        => array('type' => 'text', 'size' => 40, 'label' => rcube_label('phone'), 'subtypes' => array('home','home2','work','work2','mobile','main','homefax','workfax','car','pager','video','assistant','other')),
  'address'      => array('type' => 'composite', 'label' => rcube_label('address'), 'subtypes' => array('home','work','other'), 'childs' => array(
    'street'     => array('type' => 'text', 'size' => 40, 'label' => rcube_label('street')),
    'locality'   => array('type' => 'text', 'size' => 28, 'label' => rcube_label('locality')),
    'zipcode'    => array('type' => 'text', 'size' => 8, 'label' => rcube_label('zipcode')),
    'region'     => array('type' => 'text', 'size' => 12, 'label' => rcube_label('region')),
    'country'    => array('type' => 'text', 'size' => 40, 'label' => rcube_label('country')),
  )),
  'birthday'     => array('type' => 'date', 'size' => 12, 'label' => rcube_label('birthday'), 'limit' => 1, 'render_func' => 'rcmail_format_date_col'),
  'anniversary'  => array('type' => 'date', 'size' => 12, 'label' => rcube_label('anniversary'), 'limit' => 1, 'render_func' => 'rcmail_format_date_col'),
  'website'      => array('type' => 'text', 'size' => 40, 'label' => rcube_label('website'), 'subtypes' => array('homepage','work','blog','other')),
  'im'           => array('type' => 'text', 'size' => 40, 'label' => rcube_label('instantmessenger'), 'subtypes' => array('aim','icq','msn','yahoo','jabber','skype','other')),
  'notes'        => array('type' => 'textarea', 'size' => 40, 'rows' => 15, 'label' => rcube_label('notes'), 'limit' => 1),
  'photo'        => array('type' => 'image', 'limit' => 1),
  'assistant'    => array('type' => 'text', 'size' => 40, 'limit' => 1, 'label' => rcube_label('assistant')),
  'manager'      => array('type' => 'text', 'size' => 40, 'limit' => 1, 'label' => rcube_label('manager')),
  'spouse'       => array('type' => 'text', 'size' => 40, 'limit' => 1, 'label' => rcube_label('spouse')),
  // TODO: define fields for vcards like GEO, KEY
);

// reduce/extend $CONTACT_COLTYPES with specification from the current $CONTACT object
if (is_array($CONTACTS->coltypes)) {
    // remove cols not listed by the backend class
    $contact_cols = $CONTACTS->coltypes[0] ? array_flip($CONTACTS->coltypes) : $CONTACTS->coltypes;
    $CONTACT_COLTYPES = array_intersect_key($CONTACT_COLTYPES, $contact_cols);
    // add associative coltypes definition
    if (!$CONTACTS->coltypes[0]) {
        foreach ($CONTACTS->coltypes as $col => $colprop)
            $CONTACT_COLTYPES[$col] = $CONTACT_COLTYPES[$col] ? array_merge($CONTACT_COLTYPES[$col], $colprop) : $colprop;
    }
}

$OUTPUT->set_env('photocol', is_array($CONTACT_COLTYPES['photo']));


function rcmail_directory_list($attrib)
{
    global $RCMAIL, $OUTPUT;

    if (!$attrib['id'])
        $attrib['id'] = 'rcmdirectorylist';

    $out = '';
    $local_id = '0';
    $jsdata = array();
    $current = get_input_value('_source', RCUBE_INPUT_GPC);
    $line_templ = html::tag('li', array(
        'id' => 'rcmli%s', 'class' => 'addressbook %s'),
        html::a(array('href' => '%s',
            'rel' => '%s',
            'onclick' => "return ".JS_OBJECT_NAME.".command('list','%s',this)"), '%s'));

    // currently selected is the first address source in the list
    if (!isset($current))
        $current = strval(key((array)$OUTPUT->env['address_sources']));

    foreach ((array)$OUTPUT->env['address_sources'] as $j => $source) {
        $id = strval($source['id'] ? $source['id'] : $j);
        $js_id = JQ($id);
        $out .= sprintf($line_templ,
            html_identifier($id),
            ($current === $id ? 'selected' : ''),
            Q(rcmail_url(null, array('_source' => $id))),
            $source['id'],
            $js_id, (!empty($source['name']) ? Q($source['name']) : Q($id)));

        $groupdata = array('out' => $out, 'jsdata' => $jsdata, 'source' => $id);
        if ($source['groups'])
            $groupdata = rcmail_contact_groups($groupdata);
        $jsdata = $groupdata['jsdata'];
        $out = $groupdata['out'];
    }

    $OUTPUT->set_env('contactgroups', $jsdata); 
    $OUTPUT->add_gui_object('folderlist', $attrib['id']);

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


// return the message list as HTML table
function rcmail_contacts_list($attrib)
{
    global $CONTACTS, $OUTPUT;

    // define list of cols to be displayed
    $a_show_cols = array('name');

    // count contacts for this user
    $result = $CONTACTS->list_records($a_show_cols);

    // add id to message list table if not specified
    if (!strlen($attrib['id']))
        $attrib['id'] = 'rcmAddressList';

    // create XHTML table
    $out = rcube_table_output($attrib, $result->records, $a_show_cols, $CONTACTS->primary_key);

    // set client env
    $OUTPUT->add_gui_object('contactslist', $attrib['id']);
    $OUTPUT->set_env('current_page', (int)$CONTACTS->list_page);
    $OUTPUT->set_env('pagecount', ceil($result->count/$CONTACTS->page_size));
    $OUTPUT->include_script('list.js');

    // add some labels to client
    $OUTPUT->add_label('deletecontactconfirm');

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
    
        // format each col
        foreach ($a_show_cols as $col)
            $a_row_cols[$col] = Q($row[$col]);

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

    return html::span($attrib, rcmail_get_rowcount_text());
}


function rcmail_get_rowcount_text()
{
    global $CONTACTS;
  
    // read nr of contacts
    $result = $CONTACTS->get_result();
    if (!$result) {
        $result = $CONTACTS->count();
    }

    if ($result->count == 0)
        $out = rcube_label('nocontactsfound');
    else
        $out = rcube_label(array(
            'name'  => 'contactsfromto',
            'vars'  => array(
            'from'  => $result->first + 1,
            'to'    => min($result->count, $result->first + $CONTACTS->page_size),
            'count' => $result->count)
        ));

    return $out;
}


function rcmail_get_type_label($type)
{
    $label = 'type'.$type;
    if (rcube_label_exists($label))
        return rcube_label($label);
    else if (preg_match('/\w+(\d+)$/', $label, $m)
            && ($label = preg_replace('/(\d+)$/', '', $label))
            && rcube_label_exists($label))
        return rcube_label($label) . ' ' . $m[1];
    
    return ucfirst($type);
}


function rcmail_contact_form($form, $record, $attrib = null)
{
    global $RCMAIL, $CONFIG;

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
    $coltype_lables = array();
    
    foreach ($coltypes as $col => $prop) {
        if ($prop['subtypes']) {
            $subtype_names = array_map('rcmail_get_type_label', $prop['subtypes']);
            $select_subtype = new html_select(array('name' => '_subtype_'.$col.'[]', 'class' => 'contactselectsubtype'));
            $select_subtype->add($subtype_names, $prop['subtypes']);
            $coltypes[$col]['subtypes_select'] = $select_subtype->show();
        }
        if ($prop['childs']) {
            foreach ($prop['childs'] as $childcol => $cp)
                $coltype_lables[$childcol] = array('label' => $cp['label']);
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
            
            // TODO: use the save name composition function as in save.inc
            $names_arr = array($record['prefix'], $record['firstname'], $record['middlename'], $record['surname'], $record['suffix']);
            if ($record['name'] == join(' ', array_filter($names_arr)))
              unset($record['name']);

            // group fields
            $field_blocks = array(
                'names'    => array('prefix','firstname','middlename','surname','suffix'),
                'displayname' => array('name'),
                'nickname' => array('nickname'),
                'jobnames' => array('organization','department','jobtitle'),
            );
            foreach ($field_blocks as $blockname => $colnames) {
                $fields = '';
                foreach ($colnames as $col) {
                    // skip cols unknown to the backend
                    if (!$coltypes[$col])
                        continue;

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
                        $val = strtr($template, $composite);
                    }
                    else if ($edit_mode) {
                        // call callback to render/format value
                        if ($colprop['render_func'])
                            $val = call_user_func($colprop['render_func'], $val, $col);

                        $coltypes[$field] = (array)$colprop + $coltypes[$field];

                        if ($colprop['subtypes'] || $colprop['limit'] != 1)
                            $colprop['array'] = true;

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
                $content .= html::tag('fieldset', array('class' => 'contactfieldgroup ' . ($colprop['subtypes'] ? 'contactfieldgroupmulti ' : '') . 'contactcontroller' . $col, 'style' => ($rows ? null : 'display:none')),
                  ($colprop['subtypes'] ? html::tag('legend', null, Q($colprop['label'])) : ' ') .
                  $rows);
            }

            // also render add-field selector
            if ($edit_mode)
                $content .= html::p('addfield', $select_add->show(null, array('style' => $select_add->_count ? null : 'display:none')));

            $content = html::div(array('id' => 'contactsection' . $section), $content);
        }
        else {
            $content = $fieldset['content'];
        }

        $out .= html::tag('fieldset', null, html::tag('legend', null, Q($fieldset['name'])) . $content) ."\n";
    }

    if ($edit_mode) {
      $RCMAIL->output->set_env('coltypes', $coltypes + $coltype_lables);
      $RCMAIL->output->set_env('delbutton', $del_button);
      $RCMAIL->output->add_label('delete');
    }

    return $out;
}


function rcmail_contact_photo($attrib)
{
    global $CONTACTS, $CONTACT_COLTYPES, $RCMAIL, $CONFIG;
    
    if ($result = $CONTACTS->get_result())
        $record = $result->first();
    
    $photo_img = $attrib['placeholder'] ? $CONFIG['skin_path'] . $attrib['placeholder'] : 'program/blank.gif';
    unset($attrib['placeholder']);
    
    if ($CONTACT_COLTYPES['photo']) {
        $RCMAIL->output->set_env('photo_placeholder', $photo_img);
        
        if ($record['photo'])
            $photo_img = $RCMAIL->url(array('_action' => 'photo', '_cid' => $record['ID'], '_source' => $_REQUEST['_source']));
        $img = html::img(array('src' => $photo_img, 'border' => 1, 'alt' => ''));
        $content = html::div($attrib, $img);
      
        if ($RCMAIL->action == 'edit' || $RCMAIL->action == 'add') {
            $RCMAIL->output->add_gui_object('contactphoto', $attrib['id']);
            $hidden = new html_hiddenfield(array('name' => '_photo', 'id' => 'ff_photo'));
            $content .= $hidden->show();
        }
  }
  
  return $content;
}


function rcmail_format_date_col($val)
{
    global $RCMAIL;
    return format_date($val, $RCMAIL->config->get('date_format', 'Y-m-d'));
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
    'upload-photo' => 'save.inc',
    'group-create' => 'groups.inc',
    'group-rename' => 'groups.inc',
    'group-delete' => 'groups.inc',
    'group-addmembers' => 'groups.inc',
    'group-delmembers' => 'groups.inc',
));
