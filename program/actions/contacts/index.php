<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide addressbook functionality and GUI objects                   |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_index extends rcmail_action
{
    public static $aliases = [
        'add' => 'edit',
    ];

    protected static $SEARCH_MODS_DEFAULT = [
        'name'      => 1,
        'firstname' => 1,
        'surname'   => 1,
        'email'     => 1,
        '*'         => 1,
    ];

    /**
     * General definition of contact coltypes
     */
    public static $CONTACT_COLTYPES = [
        'name' => [
            'size'      => 40,
            'maxlength' => 50,
            'limit'     => 1,
            'label'     => 'name',
            'category'  => 'main'
        ],
        'firstname' => [
            'size'      => 19,
            'maxlength' => 50,
            'limit'     => 1,
            'label'     => 'firstname',
            'category'  => 'main'
        ],
        'surname' => [
            'size'      => 19,
            'maxlength' => 50,
            'limit'     => 1,
            'label'     => 'surname',
            'category'  => 'main'
        ],
        'email' => [
            'size'      => 40,
            'maxlength' => 254,
            'label'     => 'email',
            'subtypes'  => ['home', 'work', 'other'],
            'category'  => 'main'
        ],
        'middlename' => [
            'size'      => 19,
            'maxlength' => 50,
            'limit'     => 1,
            'label'     => 'middlename',
            'category'  => 'main'
        ],
        'prefix' => [
            'size'      => 8,
            'maxlength' => 20,
            'limit'     => 1,
            'label'     => 'nameprefix',
            'category'  => 'main'
        ],
        'suffix' => [
            'size'      => 8,
            'maxlength' => 20,
            'limit'     => 1,
            'label'     => 'namesuffix',
            'category'  => 'main'
        ],
        'nickname' => [
            'size'      => 40,
            'maxlength' => 50,
            'limit'     => 1,
            'label'     => 'nickname',
            'category'  => 'main'
        ],
        'jobtitle' => [
            'size'      => 40,
            'maxlength' => 128,
            'limit'     => 1,
            'label'     => 'jobtitle',
            'category'  => 'main'
        ],
        'organization' => [
            'size'      => 40,
            'maxlength' => 128,
            'limit'     => 1,
            'label'     => 'organization',
            'category'  => 'main'
        ],
        'department' => [
            'size'      => 40,
            'maxlength' => 128,
            'limit'     => 1,
            'label'     => 'department',
            'category'  => 'main'
        ],
        'gender' => [
            'type'     => 'select',
            'limit'    => 1,
            'label'    => 'gender',
            'category' => 'personal',
            'options'  => [
                'male'   => 'male',
                'female' => 'female'
            ],
        ],
        'maidenname' => [
            'size'      => 40,
            'maxlength' => 50,
            'limit'     => 1,
            'label'     => 'maidenname',
            'category'  => 'personal'
        ],
        'phone' => [
            'size'      => 40,
            'maxlength' => 20,
            'label'     => 'phone',
            'category'  => 'main',
            'subtypes'  => ['home', 'home2', 'work', 'work2', 'mobile', 'main', 'homefax', 'workfax', 'car',
                'pager', 'video', 'assistant', 'other'],
        ],
        'address' => [
            'type'     => 'composite',
            'label'    => 'address',
            'subtypes' => ['home', 'work', 'other'],
            'category' => 'main',
            'childs'   => [
                'street' => [
                    'label'     => 'street',
                    'size'      => 40,
                    'maxlength' => 50,
                ],
                'locality' => [
                    'label'     => 'locality',
                    'size'      => 28,
                    'maxlength' => 50,
                ],
                'zipcode' => [
                    'label'     => 'zipcode',
                    'size'      => 8,
                    'maxlength' => 15,
                ],
                'region' => [
                    'label'     => 'region',
                    'size'      => 12,
                    'maxlength' => 50,
                ],
                'country' => [
                    'label'     => 'country',
                    'size'      => 40,
                    'maxlength' => 50,
                ],
            ],
        ],
        'birthday' => [
            'type'      => 'date',
            'size'      => 12,
            'maxlength' => 16,
            'label'     => 'birthday',
            'limit'     => 1,
            'render_func' => 'rcmail_action_contacts_index::format_date_col',
            'category'    => 'personal'
        ],
        'anniversary' => [
            'type'      => 'date',
            'size'      => 12,
            'maxlength' => 16,
            'label'     => 'anniversary',
            'limit'     => 1,
            'render_func' => 'rcmail_action_contacts_index::format_date_col',
            'category'    => 'personal'
        ],
        'website' => [
            'size'      => 40,
            'maxlength' => 128,
            'label'     => 'website',
            'subtypes'  => ['homepage', 'work', 'blog', 'profile', 'other'],
            'category'  => 'main'
        ],
        'im' => [
            'size'      => 40,
            'maxlength' => 128,
            'label'     => 'instantmessenger',
            'subtypes'  => ['aim', 'icq', 'msn', 'yahoo', 'jabber', 'skype', 'other'],
            'category'  => 'main'
        ],
        'notes' => [
            'type'      => 'textarea',
            'size'      => 40,
            'rows'      => 15,
            'maxlength' => 500,
            'label'     => 'notes',
            'limit'     => 1
        ],
        'photo' => [
            'type'     => 'image',
            'limit'    => 1,
            'category' => 'main'
        ],
        'assistant' => [
            'size'      => 40,
            'maxlength' => 128,
            'limit'     => 1,
            'label'     => 'assistant',
            'category'  => 'personal'
        ],
        'manager' => [
            'size'      => 40,
            'maxlength' => 128,
            'limit'     => 1,
            'label'     => 'manager',
            'category'  => 'personal'
        ],
        'spouse' => [
            'size'      => 40,
            'maxlength' => 128,
            'limit'     => 1,
            'label'     => 'spouse',
            'category'  => 'personal'
        ],
    ];

    protected static $CONTACTS;
    protected static $SOURCE_ID;
    protected static $contact;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        // Prepare coltypes
        foreach (self::$CONTACT_COLTYPES as $idx => $val) {
            if (!empty($val['label'])) {
                self::$CONTACT_COLTYPES[$idx]['label'] = $rcmail->gettext($val['label']);
            }
            if (!empty($val['options'])) {
                foreach ($val['options'] as $i => $v) {
                    self::$CONTACT_COLTYPES[$idx]['options'][$i] = $rcmail->gettext($v);
                }
            }
            if (!empty($val['childs'])) {
                foreach ($val['childs'] as $i => $v) {
                    self::$CONTACT_COLTYPES[$idx]['childs'][$i]['label'] = $rcmail->gettext($v['label']);
                    if (empty($v['type'])) {
                        self::$CONTACT_COLTYPES[$idx]['childs'][$i]['type'] = 'text';
                    }
                }
            }
            if (empty($val['type'])) {
                self::$CONTACT_COLTYPES[$idx]['type'] = 'text';
            }
        }

        // Addressbook UI
        if (!$rcmail->action && !$rcmail->output->ajax_call) {
            // add list of address sources to client env
            $js_list = $rcmail->get_address_sources();

            // count all/writeable sources
            $writeable = 0;
            $count     = 0;

            foreach ($js_list as $sid => $s) {
                $count++;
                if (!$s['readonly']) {
                    $writeable++;
                }
                // unset hidden sources
                if (!empty($s['hidden'])) {
                    unset($js_list[$sid]);
                }
            }

            $rcmail->output->set_env('display_next', (bool) $rcmail->config->get('display_next'));
            $rcmail->output->set_env('search_mods', $rcmail->config->get('addressbook_search_mods', self::$SEARCH_MODS_DEFAULT));
            $rcmail->output->set_env('address_sources', $js_list);
            $rcmail->output->set_env('writable_source', $writeable);
            $rcmail->output->set_env('contact_move_enabled', $writeable > 1);
            $rcmail->output->set_env('contact_copy_enabled', $writeable > 1 || ($writeable == 1 && count($js_list) > 1));

            $rcmail->output->set_pagetitle($rcmail->gettext('contacts'));

            $_SESSION['addressbooks_count']           = $count;
            $_SESSION['addressbooks_count_writeable'] = $writeable;

            // select address book
            $source = rcube_utils::get_input_string('_source', rcube_utils::INPUT_GPC);

            // use first directory by default
            if (!strlen($source) || !isset($js_list[$source])) {
                $source = $rcmail->config->get('default_addressbook');
                if (!is_string($source) || !strlen($source) || !isset($js_list[$source])) {
                    $source = strval(key($js_list));
                }
            }

            self::$CONTACTS = self::contact_source($source, true);
        }

        // remove undo information...
        if (!empty($_SESSION['contact_undo'])) {
            // ...after timeout
            $undo      = $_SESSION['contact_undo'];
            $undo_time = $rcmail->config->get('undo_timeout', 0);
            if ($undo['ts'] < time() - $undo_time) {
                $rcmail->session->remove('contact_undo');
            }
        }

        // register UI objects
        $rcmail->output->add_handlers([
                'directorylist'       => [$this, 'directory_list'],
                'savedsearchlist'     => [$this, 'savedsearch_list'],
                'addresslist'         => [$this, 'contacts_list'],
                'addresslisttitle'    => [$this, 'contacts_list_title'],
                'recordscountdisplay' => [$this, 'rowcount_display'],
                'searchform'          => [$rcmail->output, 'search_form']
        ]);

        // Disable qr-code if imagick, iconv or BaconQrCode is not installed
        if (!$rcmail->output->ajax_call && rcmail_action_contacts_qrcode::check_support()) {
            $rcmail->output->set_env('qrcode', true);
            $rcmail->output->add_label('qrcode');
        }
    }

    // instantiate a contacts object according to the given source
    public static function contact_source($source = null, $init_env = false, $writable = false)
    {
        if ($source === null || !strlen((string) $source)) {
            $source = rcube_utils::get_input_string('_source', rcube_utils::INPUT_GPC);
        }

        $rcmail    = rcmail::get_instance();
        $page_size = $rcmail->config->get('addressbook_pagesize', $rcmail->config->get('pagesize', 50));

        // Get object
        $contacts = $rcmail->get_address_book($source, $writable);

        if (!$contacts) {
            return null;
        }

        $contacts->set_pagesize($page_size);

        // set list properties and session vars
        if (!empty($_GET['_page'])) {
            $contacts->set_page(($_SESSION['page'] = intval($_GET['_page'])));
        }
        else {
            $contacts->set_page($_SESSION['page'] ?? 1);
        }

        if ($group = rcube_utils::get_input_string('_gid', rcube_utils::INPUT_GP)) {
            $contacts->set_group($group);
        }

        if (!$init_env) {
            return $contacts;
        }

        $rcmail->output->set_env('readonly', $contacts->readonly);
        $rcmail->output->set_env('source', (string) $source);
        $rcmail->output->set_env('group', $group);

        // reduce/extend $CONTACT_COLTYPES with specification from the current $CONTACT object
        if (is_array($contacts->coltypes)) {
            // remove cols not listed by the backend class
            $contact_cols = isset($contacts->coltypes[0]) ? array_flip($contacts->coltypes) : $contacts->coltypes;
            self::$CONTACT_COLTYPES = array_intersect_key(self::$CONTACT_COLTYPES, $contact_cols);

            // add associative coltypes definition
            if (empty($contacts->coltypes[0])) {
                foreach ($contacts->coltypes as $col => $colprop) {
                    if (!empty($colprop['childs'])) {
                        foreach ($colprop['childs'] as $childcol => $childprop) {
                            $colprop['childs'][$childcol] = array_merge((array) self::$CONTACT_COLTYPES[$col]['childs'][$childcol], $childprop);
                        }
                    }

                    if (isset(self::$CONTACT_COLTYPES[$col])) {
                        self::$CONTACT_COLTYPES[$col] = array_merge(self::$CONTACT_COLTYPES[$col], $colprop);
                    }
                    else {
                        self::$CONTACT_COLTYPES[$col] = $colprop;
                    }
                }
            }
        }

        $rcmail->output->set_env('photocol', !empty(self::$CONTACT_COLTYPES['photo']));

        return $contacts;
    }

    public static function set_sourcename($abook)
    {
        $rcmail = rcmail::get_instance();

        // get address book name (for display)
        if ($abook && !empty($_SESSION['addressbooks_count']) && $_SESSION['addressbooks_count'] > 1) {
            $name = $abook->get_name();
            if (!$name) {
                $name = $rcmail->gettext('personaladrbook');
            }

            $rcmail->output->set_env('sourcename', html_entity_decode($name, ENT_COMPAT, 'UTF-8'));
        }
    }

    public static function directory_list($attrib)
    {

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmdirectorylist';
        }

        $rcmail = rcmail::get_instance();
        $out    = '';
        $jsdata = [];

        $line_templ = html::tag('li',
            ['id' => 'rcmli%s', 'class' => '%s', 'noclose' => true],
            html::a(
                [
                    'href'    => '%s',
                    'rel'     => '%s',
                    'onclick' => "return ".rcmail_output::JS_OBJECT_NAME.".command('list','%s',this)"
                ],
                '%s'
            )
        );

        $sources = (array) $rcmail->output->get_env('address_sources');
        reset($sources);

        // currently selected source
        $current = rcube_utils::get_input_string('_source', rcube_utils::INPUT_GPC);

        foreach ($sources as $j => $source) {
            $id = strval(strlen($source['id']) ? $source['id'] : $j);
            $js_id = rcube::JQ($id);

            // set class name(s)
            $class_name = 'addressbook';
            if ($current === $id) {
                $class_name .= ' selected';
            }
            if (!empty($source['readonly'])) {
                $class_name .= ' readonly';
            }
            if (!empty($source['class_name'])) {
                $class_name .= ' ' . $source['class_name'];
            }

            $name = $source['name'] ?: $id;
            $out .= sprintf($line_templ,
                rcube_utils::html_identifier($id, true),
                $class_name,
                rcube::Q($rcmail->url(['_source' => $id])),
                $source['id'],
                $js_id,
                $name
            );

            $groupdata = ['out' => $out, 'jsdata' => $jsdata, 'source' => $id];
            if (!empty($source['groups'])) {
                $groupdata = self::contact_groups($groupdata);
            }
            $jsdata = $groupdata['jsdata'];
            $out = $groupdata['out'];
            $out .= '</li>';
        }

        $rcmail->output->set_env('contactgroups', $jsdata);
        $rcmail->output->set_env('collapsed_abooks', (string) $rcmail->config->get('collapsed_abooks',''));
        $rcmail->output->add_gui_object('folderlist', $attrib['id']);
        $rcmail->output->include_script('treelist.js');

        // add some labels to client
        $rcmail->output->add_label('deletegroupconfirm', 'groupdeleting', 'addingmember', 'removingmember',
            'newgroup', 'grouprename', 'searchsave', 'namex', 'save', 'import', 'importcontacts',
            'advsearch', 'search'
        );

        return html::tag('ul', $attrib, $out, html::$common_attrib);
    }

    public static function savedsearch_list($attrib)
    {
        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmsavedsearchlist';
        }

        $rcmail = rcmail::get_instance();
        $out    = '';
        $line_templ = html::tag('li',
            ['id' => 'rcmli%s', 'class' => '%s'],
            html::a([
                    'href'    => '#',
                    'rel'     => 'S%s',
                    'onclick' => "return ".rcmail_output::JS_OBJECT_NAME.".command('listsearch', '%s', this)"
                ],
                '%s'
            )
        );

        // Saved searches
        $sources = $rcmail->user->list_searches(rcube_user::SEARCH_ADDRESSBOOK);
        foreach ($sources as $source) {
            $id    = $source['id'];
            $js_id = rcube::JQ($id);

            // set class name(s)
            $classes = ['contactsearch'];
            if (!empty($source['class_name'])) {
                $classes[] = $source['class_name'];
            }

            $out .= sprintf($line_templ,
                rcube_utils::html_identifier('S' . $id, true),
                join(' ', $classes),
                $id,
                $js_id,
                rcube::Q($source['name'] ?: $id)
            );
        }

        $rcmail->output->add_gui_object('savedsearchlist', $attrib['id']);

        return html::tag('ul', $attrib, $out, html::$common_attrib);
    }

    public static function contact_groups($args)
    {
        $rcmail = rcmail::get_instance();
        $groups = $rcmail->get_address_book($args['source'])->list_groups();
        $groups_html = '';

        if (!empty($groups)) {
            $line_templ = html::tag('li',
                ['id' => 'rcmli%s', 'class' => 'contactgroup'],
                html::a([
                        'href' => '#',
                        'rel' => '%s:%s',
                        'onclick' => "return ".rcmail_output::JS_OBJECT_NAME.".command('listgroup',{'source':'%s','id':'%s'},this)"
                    ],
                    '%s'
                )
            );

            // append collapse/expand toggle and open a new <ul>
            $is_collapsed = strpos($rcmail->config->get('collapsed_abooks',''), '&'.rawurlencode($args['source']).'&') !== false;
            $args['out'] .= html::div('treetoggle ' . ($is_collapsed ? 'collapsed' : 'expanded'), '&nbsp;');

            foreach ($groups as $group) {
                $groups_html .= sprintf($line_templ,
                    rcube_utils::html_identifier('G' . $args['source'] . $group['ID'], true),
                    $args['source'],
                    $group['ID'],
                    $args['source'],
                    $group['ID'],
                    rcube::Q($group['name'])
                );

                $args['jsdata']['G' . $args['source'] . $group['ID']] = [
                    'source' => $args['source'],
                    'id'     => $group['ID'],
                    'name'   => $group['name'],
                    'type'   => 'group'
                ];
            }
        }

        $style = !empty($is_collapsed) || empty($groups) ? 'display:none;' : null;

        $args['out'] .= html::tag('ul', ['class' => 'groups', 'style' => $style], $groups_html);

        return $args;
    }

    // return the contacts list as HTML table
    public static function contacts_list($attrib)
    {
        $rcmail = rcmail::get_instance();

        // define list of cols to be displayed
        $a_show_cols = ['name', 'action'];

        // add id to message list table if not specified
        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmAddressList';
        }

        // create XHTML table
        $out = self::table_output($attrib, [], $a_show_cols, self::$CONTACTS->primary_key);

        // set client env
        $rcmail->output->add_gui_object('contactslist', $attrib['id']);
        $rcmail->output->set_env('current_page', (int) self::$CONTACTS->list_page);
        $rcmail->output->include_script('list.js');

        // add some labels to client
        $rcmail->output->add_label('deletecontactconfirm', 'copyingcontact', 'movingcontact', 'contactdeleting');

        return $out;
    }

    public static function js_contacts_list($result, $prefix = '')
    {
        if (empty($result) || $result->count == 0) {
            return;
        }

        $rcmail = rcmail::get_instance();

        // define list of cols to be displayed
        $a_show_cols = ['name', 'action'];

        while ($row = $result->next()) {
            $emails       = rcube_addressbook::get_col_values('email', $row, true);
            $row['CID']   = $row['ID'];
            $row['email'] = reset($emails);
            $source_id  = $rcmail->output->get_env('source');
            $a_row_cols = [];
            $type       = !empty($row['_type']) ? $row['_type'] : 'person';
            $classes    = [$type];

            // build contact ID with source ID
            if (isset($row['sourceid'])) {
                $row['ID'] = $row['ID'].'-'.$row['sourceid'];
                $source_id = $row['sourceid'];
            }

            // format each col
            foreach ($a_show_cols as $col) {
                $val = null;
                switch ($col) {
                    case 'name':
                        $val = rcube::Q(rcube_addressbook::compose_list_name($row));
                        break;

                    case 'action':
                        if ($type == 'group') {
                            $val = html::a([
                                    'href'    => '#list',
                                    'rel'     => $row['ID'],
                                    'title'   => $rcmail->gettext('listgroup'),
                                    'onclick' => sprintf(
                                        "return %s.command('pushgroup',{'source':'%s','id':'%s'},this,event)",
                                        rcmail_output::JS_OBJECT_NAME,
                                        $source_id,
                                        $row['CID']
                                    ),
                                    'class'   => 'pushgroup',
                                    'data-action-link' => true,
                                ],
                                '&raquo;'
                            );
                        }
                        else {
                            $val = null;
                        }
                        break;

                    default:
                        $val = rcube::Q($row[$col]);
                        break;
                }

                if ($val !== null) {
                    $a_row_cols[$col] = $val;
                }
            }

            if (!empty($row['readonly'])) {
                $classes[] = 'readonly';
            }

            $rcmail->output->command($prefix . 'add_contact_row', $row['ID'], $a_row_cols, join(' ', $classes),
                array_intersect_key($row, ['ID' => 1,'readonly' => 1, '_type' => 1, 'email' => 1,'name' => 1])
            );
        }
    }

    public static function contacts_list_title($attrib)
    {
        $rcmail = rcmail::get_instance();
        $attrib += ['label' => 'contacts', 'id' => 'rcmabooklisttitle', 'tag' => 'span'];
        unset($attrib['name']);

        $rcmail->output->add_gui_object('addresslist_title', $attrib['id']);
        $rcmail->output->add_label('contacts','uponelevel');

        return html::tag($attrib['tag'], $attrib, $rcmail->gettext($attrib['label']), html::$common_attrib);
    }

    public static function rowcount_display($attrib)
    {
        $rcmail = rcmail::get_instance();

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmcountdisplay';
        }

        $rcmail->output->add_gui_object('countdisplay', $attrib['id']);

        if (!empty($attrib['label'])) {
            $_SESSION['contactcountdisplay'] = $attrib['label'];
        }

        return html::span($attrib, $rcmail->gettext('loading'));
    }

    public static function get_rowcount_text($result = null)
    {
        $rcmail = rcmail::get_instance();

        // read nr of contacts
        if (empty($result) && !empty(self::$CONTACTS)) {
            $result = self::$CONTACTS->get_result();
        }

        if (empty($result) || $result->count == 0) {
            return $rcmail->gettext('nocontactsfound');
        }

        $page_size = $rcmail->config->get('addressbook_pagesize', $rcmail->config->get('pagesize', 50));

        return $rcmail->gettext([
                'name'  => !empty($_SESSION['contactcountdisplay']) ? $_SESSION['contactcountdisplay'] : 'contactsfromto',
                'vars'  => [
                    'from'  => $result->first + 1,
                    'to'    => min($result->count, $result->first + $page_size),
                    'count' => $result->count
                ]
        ]);
    }

    public static function get_type_label($type)
    {
        $rcmail = rcmail::get_instance();
        $label  = 'type' . $type;

        if ($rcmail->text_exists($label, '*', $domain)) {
            return $rcmail->gettext($label, $domain);
        }

        if (
            preg_match('/\w+(\d+)$/', $label, $m)
            && ($label = preg_replace('/(\d+)$/', '', $label))
            && $rcmail->text_exists($label, '*', $domain)
        ) {
            return $rcmail->gettext($label, $domain) . ' ' . $m[1];
        }

        return ucfirst($type);
    }

    public static function contact_form($form, $record, $attrib = null)
    {
        $rcmail = rcmail::get_instance();

        // group fields
        $head_fields = [
            'source'       => ['source'],
            'names'        => ['prefix','firstname','middlename','surname','suffix'],
            'displayname'  => ['name'],
            'nickname'     => ['nickname'],
            'organization' => ['organization'],
            'department'   => ['department'],
            'jobtitle'     => ['jobtitle'],
        ];

        // Allow plugins to modify contact form content
        $plugin = $rcmail->plugins->exec_hook('contact_form', [
                'form'        => $form,
                'record'      => $record,
                'head_fields' => $head_fields
        ]);

        $form        = $plugin['form'];
        $record      = $plugin['record'];
        $head_fields = $plugin['head_fields'];
        $edit_mode   = $rcmail->action != 'show' && $rcmail->action != 'print';
        $compact     = self::get_bool_attr($attrib, 'compact-form');
        $use_labels  = self::get_bool_attr($attrib, 'use-labels');
        $with_source = self::get_bool_attr($attrib, 'with-source');
        $out         = '';

        if (!empty($attrib['deleteicon'])) {
            $del_button = html::img([
                    'src' => $rcmail->output->get_skin_file($attrib['deleteicon']),
                    'alt' => $rcmail->gettext('delete')
            ]);
        }
        else {
            $del_button = html::span('inner', $rcmail->gettext('delete'));
        }

        unset($attrib['deleteicon']);

        // get default coltypes
        $coltypes       = self::$CONTACT_COLTYPES;
        $coltype_labels = [];
        $business_mode  = $rcmail->config->get('contact_form_mode') === 'business';

        foreach ($coltypes as $col => $prop) {
            if (!empty($prop['subtypes'])) {
                // re-order subtypes, so 'work' is before 'home'
                if ($business_mode) {
                    $work_opts = array_filter($prop['subtypes'], function($var) { return strpos($var, 'work') !== false; });
                    if (!empty($work_opts)) {
                        $coltypes[$col]['subtypes'] = $prop['subtypes'] = array_merge(
                            $work_opts,
                            array_diff($prop['subtypes'], $work_opts)
                        );
                    }
                }

                $subtype_names  = array_map('rcmail_action_contacts_index::get_type_label', $prop['subtypes']);
                $select_subtype = new html_select([
                        'name'  => "_subtype_{$col}[]",
                        'class' => 'contactselectsubtype custom-select',
                        'title' => $prop['label'] . ' ' . $rcmail->gettext('type')
                ]);
                $select_subtype->add($subtype_names, $prop['subtypes']);

                $coltypes[$col]['subtypes_select'] = $select_subtype->show();
            }

            if (!empty($prop['childs'])) {
                foreach ($prop['childs'] as $childcol => $cp) {
                    $coltype_labels[$childcol] = ['label' => $cp['label']];
                }
            }
        }

        foreach ($form as $section => $fieldset) {
            // skip empty sections
            if (empty($fieldset['content'])) {
                continue;
            }

            $select_add = new html_select([
                    'class'        => 'addfieldmenu custom-select',
                    'rel'          => $section,
                    'data-compact' => $compact ? "true" : null
            ]);

            $select_add->add($rcmail->gettext('addfield'), '');
            $select_add_count = 0;

            // render head section with name fields (not a regular list of rows)
            if ($section == 'head') {
                $content = '';

                // unset display name if it is composed from name parts
                $dname = rcube_addressbook::compose_display_name(['name' => ''] + (array) $record);
                if (isset($record['name']) && $record['name'] == $dname) {
                    unset($record['name']);
                }

                foreach ($head_fields as $blockname => $colnames) {
                    $fields     = '';
                    $block_attr = ['class' => $blockname  . (count($colnames) == 1 ? ' row' : '')];

                    foreach ($colnames as $col) {
                        if ($col == 'source') {
                            if (!$with_source || !($source = $rcmail->output->get_env('sourcename'))) {
                                continue;
                            }

                            if (!$edit_mode) {
                                $record['source'] = $rcmail->gettext('addressbook') . ': ' . $source;
                            }
                            else if ($rcmail->action == 'add') {
                                $record['source'] = $source;
                            }
                            else {
                                continue;
                            }
                        }
                        // skip cols unknown to the backend
                        else if (empty($coltypes[$col])) {
                            continue;
                        }

                        // skip cols not listed in the form definition
                        if (is_array($fieldset['content']) && !in_array($col, array_keys($fieldset['content']))) {
                            continue;
                        }

                        // only string values are expected here
                        if (isset($record[$col]) && is_array($record[$col])) {
                            $record[$col] = join(' ', $record[$col]);
                        }

                        if (!$edit_mode) {
                            if (!empty($record[$col])) {
                                $fields .= html::span('namefield ' . $col, rcube::Q($record[$col])) . ' ';
                            }
                        }
                        else {
                            $visible = true;
                            $colprop = [];

                            if (!empty($fieldset['content'][$col])) {
                                $colprop += (array) $fieldset['content'][$col];
                            }

                            if (!empty($coltypes[$col])) {
                                $colprop += (array) $coltypes[$col];
                            }

                            if (empty($colprop['id'])) {
                                $colprop['id'] = 'ff_' . $col;
                            }

                            if (empty($record[$col]) && empty($colprop['visible'])) {
                                $visible          = false;
                                $colprop['style'] = $use_labels ? null : 'display:none';
                                $select_add->add($colprop['label'], $col);
                            }

                            if ($col == 'source') {
                                $input = self::source_selector(['id' => $colprop['id']]);
                            }
                            else {
                                $val   = $record[$col] ?? null;
                                $input = rcube_output::get_edit_field($col, $val, $colprop);
                            }

                            if ($use_labels) {
                                $_content = html::label($colprop['id'], rcube::Q($colprop['label'])) . html::div(null, $input);
                                if (count($colnames) > 1) {
                                    $fields .= html::div(['class' => 'row', 'style' => $visible ? null : 'display:none'], $_content);
                                }
                                else {
                                    $fields .= $_content;
                                    $block_attr['style'] = $visible ? null : 'display:none';
                                }
                            }
                            else {
                                $fields .= $input;
                            }
                        }
                    }

                    if ($fields) {
                        $content .= html::div($block_attr, $fields);
                    }
                }

                if ($edit_mode) {
                    $content .= html::p('addfield', $select_add->show(null));
                }

                $legend = !empty($fieldset['name']) ? html::tag('legend', null, rcube::Q($fieldset['name'])) : '';
                $out   .= html::tag('fieldset', $attrib, $legend . $content, html::$common_attrib) ."\n";
                continue;
            }

            $content = '';
            if (is_array($fieldset['content'])) {
                foreach ($fieldset['content'] as $col => $colprop) {
                    // remove subtype part of col name
                    $tokens = explode(':', $col);
                    $field  = $tokens[0];

                    if (empty($tokens[1])) {
                        $subtype = $business_mode ? 'work' : 'home';
                    }
                    else {
                        $subtype = $tokens[1];
                    }

                    // skip cols unknown to the backend
                    if (empty($coltypes[$field]) && empty($colprop['value'])) {
                        continue;
                    }

                    // merge colprop with global coltype configuration
                    if (!empty($coltypes[$field])) {
                        $colprop += $coltypes[$field];
                    }

                    if (!isset($colprop['type'])) {
                        $colprop['type'] = 'text';
                    }

                    $label = $colprop['label'] ?? $rcmail->gettext($col);

                    // prepare subtype selector in edit mode
                    if ($edit_mode && isset($colprop['subtypes']) && is_array($colprop['subtypes'])) {
                        $subtype_names  = array_map('rcmail_action_contacts_index::get_type_label', $colprop['subtypes']);
                        $select_subtype = new html_select([
                                'name'  => "_subtype_{$col}[]",
                                'class' => 'contactselectsubtype custom-select',
                                'title' => $colprop['label'] . ' ' . $rcmail->gettext('type')
                        ]);
                        $select_subtype->add($subtype_names, $colprop['subtypes']);
                    }
                    else {
                        $select_subtype = null;
                    }

                    $rows = '';

                    list($values, $subtypes) = self::contact_field_values($record, "$field:$subtype", $colprop);

                    foreach ($values as $i => $val) {
                        if (!empty($subtypes[$i])) {
                            $subtype = $subtypes[$i];
                        }

                        $fc            = intval($coltypes[$field]['count'] ?? 0);
                        $colprop['id'] = 'ff_' . $col . $fc;
                        $row_class     = 'row';

                        // render composite field
                        if ($colprop['type'] == 'composite') {
                            $row_class .= ' composite';
                            $composite  = [];
                            $template   = $rcmail->config->get($col . '_template', '{'.join('} {', array_keys($colprop['childs'])).'}');
                            $j = 0;

                            foreach ($colprop['childs'] as $childcol => $cp) {
                                if (!empty($val) && is_array($val)) {
                                    if (!empty($val[$childcol])) {
                                        $childvalue = $val[$childcol];
                                    }
                                    else {
                                        $childvalue = $val[$j] ?? null;
                                    }
                                }
                                else {
                                    $childvalue = '';
                                }

                                if ($edit_mode) {
                                    if (!empty($colprop['subtypes']) || $colprop['limit'] != 1) {
                                        $cp['array'] = true;
                                    }

                                    $cp_type = $cp['type'] ?? null;
                                    $composite['{'.$childcol.'}'] = rcube_output::get_edit_field($childcol, $childvalue, $cp, $cp_type) . ' ';
                                }
                                else {
                                    if (!empty($cp['render_func'])) {
                                        $childval = call_user_func($cp['render_func'], $childvalue, $childcol);
                                    }
                                    else {
                                        $childval = rcube::Q($childvalue);
                                    }

                                    $composite['{' . $childcol . '}'] = html::span('data ' . $childcol, $childval) . ' ';
                                }

                                $j++;
                            }

                            $coltypes[$field] += (array) $colprop;

                            if (isset($coltypes[$field]['count'])) {
                                $coltypes[$field]['count']++;
                            }
                            else {
                                $coltypes[$field]['count'] = 1;
                            }

                            $val = preg_replace('/\{\w+\}/', '', strtr($template, $composite));

                            if ($compact) {
                                $val = html::div('content', str_replace('<br/>', '', $val));
                            }
                        }
                        else if ($edit_mode) {
                            // call callback to render/format value
                            if (!empty($colprop['render_func'])) {
                                $val = call_user_func($colprop['render_func'], $val, $col);
                            }

                            $coltypes[$field] = (array) $colprop + $coltypes[$field];

                            if (!empty($colprop['subtypes']) || $colprop['limit'] != 1) {
                                $colprop['array'] = true;
                            }

                            // load jquery UI datepicker for date fields
                            if (isset($colprop['type']) && $colprop['type'] == 'date') {
                                $colprop['class'] = (!empty($colprop['class']) ? $colprop['class'] . ' ' : '') . 'datepicker';
                                if (empty($colprop['render_func'])) {
                                    $val = self::format_date_col($val);
                                }
                            }

                            $val = rcube_output::get_edit_field($col, $val, $colprop, $colprop['type']);

                            if (empty($coltypes[$field]['count'])) {
                                $coltypes[$field]['count'] = 1;
                            }
                            else {
                                $coltypes[$field]['count']++;
                            }
                        }
                        else if (!empty($colprop['render_func'])) {
                            $val = call_user_func($colprop['render_func'], $val, $col);
                        }
                        else if (isset($colprop['options']) && isset($colprop['options'][$val])) {
                            $val = $colprop['options'][$val];
                        }
                        else {
                            $val = rcube::Q($val);
                        }

                        // use subtype as label
                        if (!empty($colprop['subtypes'])) {
                            $label = self::get_type_label($subtype);
                        }

                        $_del_btn = html::a([
                                'href'  => '#del',
                                'class' => 'contactfieldbutton deletebutton',
                                'title' => $rcmail->gettext('delete'),
                                'rel'   => $col
                            ],
                            $del_button
                        );

                        // add delete button/link
                        if (!$compact && $edit_mode
                            && (empty($colprop['visible']) || empty($colprop['limit']) || $colprop['limit'] > 1)
                        ) {
                            $val .= $_del_btn;
                        }

                        // display row with label
                        if ($label) {
                            if ($rcmail->action == 'print') {
                                $_label = rcube::Q($colprop['label'] . ($label != $colprop['label'] ? ' (' . $label . ')' : ''));
                                if (!$compact) {
                                    $_label = html::div('contactfieldlabel label', $_label);
                                }
                            }
                            else if ($select_subtype) {
                                $_label = $select_subtype->show($subtype);
                                if (!$compact) {
                                    $_label = html::div('contactfieldlabel label', $_label);
                                }
                            }
                            else {
                                $_label = html::label(['class' => 'contactfieldlabel label', 'for' => $colprop['id']], rcube::Q($label));
                            }

                            if (!$compact) {
                                $val = html::div('contactfieldcontent ' . $colprop['type'], $val);
                            }
                            else {
                                $val .= $_del_btn;
                            }

                            $rows .= html::div($row_class, $_label . $val);
                        }
                        // row without label
                        else {
                            $rows .= html::div($row_class, $compact ? $val : html::div('contactfield', $val));
                        }
                    }

                    // add option to the add-field menu
                    if (empty($colprop['limit']) || empty($coltypes[$field]['count']) || $coltypes[$field]['count'] < $colprop['limit']) {
                        $select_add->add($colprop['label'], $col);
                        $select_add_count++;
                    }

                    // wrap rows in fieldgroup container
                    if ($rows) {
                        $c_class    = 'contactfieldgroup '
                            . (!empty($colprop['subtypes']) ? 'contactfieldgroupmulti ' : '')
                            . 'contactcontroller' . $col;
                        $with_label = !empty($colprop['subtypes']) && $rcmail->action != 'print';
                        $content   .= html::tag(
                            'fieldset',
                            ['class' => $c_class],
                            ($with_label ? html::tag('legend', null, rcube::Q($colprop['label'])) : ' ') . $rows
                        );
                    }
                }

                if (!$content && (!$edit_mode || !$select_add_count)) {
                    continue;
                }

                // also render add-field selector
                if ($edit_mode) {
                    $content .= html::p('addfield', $select_add->show(null, ['style' => $select_add_count ? null : 'display:none']));
                }

                $content = html::div(['id' => 'contactsection' . $section], $content);
            }
            else {
                $content = $fieldset['content'];
            }

            if ($content) {
                $fattribs = !empty($attrib['fieldset-class']) ? ['class' => $attrib['fieldset-class']] : null;
                $fcontent = html::tag('legend', null, rcube::Q($fieldset['name'])) . $content;
                $out .= html::tag('fieldset', $fattribs, $fcontent) . "\n";
            }
        }

        if ($edit_mode) {
            $rcmail->output->set_env('coltypes', $coltypes + $coltype_labels);
            $rcmail->output->set_env('delbutton', $del_button);
            $rcmail->output->add_label('delete');
        }

        return $out;
    }

    public static function contact_field_values($record, $field_name, $colprop)
    {
        list($field, $subtype) = explode(':', $field_name);

        $subtypes = [];
        $values   = [];

        if (!empty($colprop['value'])) {
            $values = (array) $colprop['value'];
        }
        else if (!empty($colprop['subtypes'])) {
            // iterate over possible subtypes and collect values with their subtype
            $c_values = rcube_addressbook::get_col_values($field, $record);

            foreach ($colprop['subtypes'] as $st) {
                if (isset($c_values[$st])) {
                    foreach ((array) $c_values[$st] as $value) {
                        $i = count($values);
                        $subtypes[$i] = $st;
                        $values[$i]   = $value;
                    }

                    $c_values[$st] = null;
                }
            }

            // TODO: add $st to $select_subtype if missing ?
            foreach ($c_values as $st => $vals) {
                foreach ((array) $vals as $value) {
                    $i = count($values);
                    $subtypes[$i] = $st;
                    $values[$i]   = $value;
                }
            }
        }
        else if (isset($record[$field_name])) {
            $values = $record[$field_name];
        }
        else if (isset($record[$field])) {
            $values = $record[$field];
        }

        // hack: create empty values array to force this field to be displayed
        if (empty($values) && !empty($colprop['visible'])) {
            $values = [''];
        }

        if (!is_array($values)) {
            // $values can be an object, don't use (array)$values syntax
            $values = !empty($values) ? [$values] : [];
        }

        return [$values, $subtypes];
    }

    public static function contact_photo($attrib)
    {
        if ($result = self::$CONTACTS->get_result()) {
            $record = $result->first();
        }
        else {
            $record = ['photo' => null, '_type' => 'contact'];
        }

        $rcmail = rcmail::get_instance();

        if (!empty($record['_type']) && $record['_type'] == 'group' && !empty($attrib['placeholdergroup'])) {
            $photo_img = $rcmail->output->abs_url($attrib['placeholdergroup'], true);
            $photo_img = $rcmail->output->asset_url($photo_img);
        }
        elseif (!empty($attrib['placeholder'])) {
            $photo_img = $rcmail->output->abs_url($attrib['placeholder'], true);
            $photo_img = $rcmail->output->asset_url($photo_img);
        }
        else {
            $photo_img = 'data:image/gif;base64,' . rcmail_output::BLANK_GIF;
        }

        $rcmail->output->set_env('photo_placeholder', $photo_img);

        unset($attrib['placeholder']);

        $plugin = $rcmail->plugins->exec_hook('contact_photo', [
                'record' => $record,
                'data'   => $record['photo'] ?? null,
                'attrib' => $attrib
        ]);

        // check if we have photo data from contact form
        if (!empty(self::$contact)) {
            if (!empty(self::$contact['photo'])) {
                if (self::$contact['photo'] == '-del-') {
                    $record['photo'] = '';
                }
                else if (!empty($_SESSION['contacts']['files'][self::$contact['photo']])) {
                    $record['photo'] = $file_id = self::$contact['photo'];
                }
            }
        }

        $ff_value = '';

        if (!empty($plugin['url'])) {
            $photo_img = $plugin['url'];
        }
        else if (!empty($record['photo']) && preg_match('!^https?://!i', $record['photo'])) {
            $photo_img = $record['photo'];
        }
        else if (!empty($record['photo'])) {
            $url = ['_action' => 'photo', '_cid' => $record['ID'], '_source' => self::$SOURCE_ID];
            if (!empty($file_id)) {
                $url['_photo'] = $ff_value = $file_id;
            }
            $photo_img = $rcmail->url($url);
        }
        else {
            $ff_value = '-del-'; // will disable delete-photo action
        }

        $content = html::div($attrib, html::img([
                'src'     => $photo_img,
                'alt'     => $rcmail->gettext('contactphoto'),
                'onerror' => 'this.onerror = null; this.src = rcmail.env.photo_placeholder;',
        ]));

        if (!empty(self::$CONTACT_COLTYPES['photo']) && ($rcmail->action == 'edit' || $rcmail->action == 'add')) {
            $rcmail->output->add_gui_object('contactphoto', $attrib['id']);
            $hidden = new html_hiddenfield(['name' => '_photo', 'id' => 'ff_photo', 'value' => $ff_value]);
            $content .= $hidden->show();
        }

        return $content;
    }

    public static function format_date_col($val)
    {
        $rcmail = rcmail::get_instance();
        return $rcmail->format_date($val, $rcmail->config->get('date_format', 'Y-m-d'), false);
    }

    /**
     * Updates saved search after data changed
     */
    public static function search_update($return = false)
    {
        $rcmail = rcmail::get_instance();

        if (empty($_REQUEST['_search'])) {
            return false;
        }

        $search_request = $_REQUEST['_search'];

        if (!isset($_SESSION['contact_search'][$search_request])) {
            return false;
        }

        $search   = (array) $_SESSION['contact_search'][$search_request];
        $sort_col = $rcmail->config->get('addressbook_sort_col', 'name');
        $afields  = $return ? $rcmail->config->get('contactlist_fields') : ['name', 'email'];
        $records  = [];

        foreach ($search as $s => $set) {
            $source = $rcmail->get_address_book($s);

            // reset page
            $source->set_page(1);
            $source->set_pagesize(9999);
            $source->set_search_set($set);

            // get records
            $result = $source->list_records($afields);

            if (!$result->count) {
                unset($search[$s]);
                continue;
            }

            if ($return) {
                while ($row = $result->next()) {
                    $row['sourceid'] = $s;
                    $key = rcube_addressbook::compose_contact_key($row, $sort_col);
                    $records[$key] = $row;
                }
                unset($result);
            }

            $search[$s] = $source->get_search_set();
        }

        $_SESSION['contact_search'][$search_request] = $search;

        return $records;
    }

    /**
     * Returns contact ID(s) and source(s) from GET/POST data
     *
     * @param string $filter       Return contact identifier for this specific source
     * @param int    $request_type Type of the input var (rcube_utils::INPUT_*)
     *
     * @return array List of contact IDs per-source
     */
    public static function get_cids($filter = null, $request_type = rcube_utils::INPUT_GPC)
    {
        // contact ID (or comma-separated list of IDs) is provided in two
        // forms. If _source is an empty string then the ID is a string
        // containing contact ID and source name in form: <ID>-<SOURCE>

        $cid    = rcube_utils::get_input_value('_cid', $request_type);
        $source = rcube_utils::get_input_string('_source', rcube_utils::INPUT_GPC);

        if (is_array($cid)) {
            return $cid;
        }

        if (!is_string($cid) || !preg_match('/^[a-zA-Z0-9\+\/=_-]+(,[a-zA-Z0-9\+\/=_-]+)*$/', $cid)) {
            return [];
        }

        $cid        = explode(',', $cid);
        $got_source = strlen($source);
        $result     = [];

        // create per-source contact IDs array
        foreach ($cid as $id) {
            // extract source ID from contact ID (it's there in search mode)
            // see #1488959 and #1488862 for reference
            if (!$got_source) {
                if ($sep = strrpos($id, '-')) {
                    $contact_id = substr($id, 0, $sep);
                    $source_id  = (string) substr($id, $sep+1);
                    if (strlen($source_id)) {
                        $result[$source_id][] = $contact_id;
                    }
                }
            }
            else {
                if (substr($id, -($got_source+1)) === "-$source") {
                    $id = substr($id, 0, -($got_source+1));
                }
                $result[$source][] = $id;
            }
        }

        return $filter !== null ? $result[$filter] : $result;
    }

    /**
     * Returns HTML code for an addressbook selector
     *
     * @param array $attrib Template object attributes
     *
     * @return string HTML code of a <select> element, or <span> if there's only one writeable source
     */
    public static function source_selector($attrib)
    {
        $rcmail       = rcmail::get_instance();
        $sources_list = $rcmail->get_address_sources(true, true);

        if (count($sources_list) < 2) {
            $source      = $sources_list[self::$SOURCE_ID];
            $hiddenfield = new html_hiddenfield(['name' => '_source', 'value' => self::$SOURCE_ID]);

            return html::span($attrib, $source['name'] . $hiddenfield->show());
        }

        $attrib['name']       = '_source';
        $attrib['is_escaped'] = true;
        $attrib['onchange']   = rcmail_output::JS_OBJECT_NAME . ".command('save', 'reload', this.form)";

        $select = new html_select($attrib);

        foreach ($sources_list as $source) {
            $select->add($source['name'], $source['id']);
        }

        return $select->show(self::$SOURCE_ID);
    }
}
