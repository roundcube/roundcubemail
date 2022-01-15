<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) Kolab Systems AG                                        |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Search action (and form) for address book contacts                  |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_search extends rcmail_action_contacts_index
{
    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        if (empty($_GET['_form'])) {
            self::contact_search();
        }

        $rcmail->output->add_handler('searchform', [$this, 'contact_search_form']);
        $rcmail->output->send('contactsearch');
    }

    public static function contact_search()
    {
        $rcmail = rcmail::get_instance();
        $adv    = isset($_POST['_adv']);
        $sid    = rcube_utils::get_input_string('_sid', rcube_utils::INPUT_GET);
        $search = null;

        // get search criteria from saved search
        if ($sid && ($search = $rcmail->user->get_search($sid))) {
            $fields = $search['data']['fields'];
            $search = $search['data']['search'];
        }
        // get fields/values from advanced search form
        else if ($adv) {
            foreach (array_keys($_POST) as $key) {
                $s = trim(rcube_utils::get_input_string($key, rcube_utils::INPUT_POST, true));
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
            $search = trim(rcube_utils::get_input_string('_q', rcube_utils::INPUT_GET, true));
            $fields = rcube_utils::get_input_string('_headers', rcube_utils::INPUT_GET);

            if (empty($fields)) {
                $fields = array_keys(self::$SEARCH_MODS_DEFAULT);
            }
            else {
                $fields = array_filter(explode(',', $fields));
            }

            // update search_mods setting
            $old_mods    = $rcmail->config->get('addressbook_search_mods');
            $search_mods = array_fill_keys($fields, 1);

            if ($old_mods != $search_mods) {
                $rcmail->user->save_prefs(['addressbook_search_mods' => $search_mods]);
            }

            if (in_array('*', $fields)) {
                $fields = '*';
            }
        }

        // Values matching mode
        $mode = (int) $rcmail->config->get('addressbook_search_mode');
        $mode |= rcube_addressbook::SEARCH_GROUPS;

        // get sources list
        $sources    = $rcmail->get_address_sources();
        $sort_col   = $rcmail->config->get('addressbook_sort_col', 'name');
        $afields    = $rcmail->config->get('contactlist_fields');
        $page_size  = $rcmail->config->get('addressbook_pagesize', $rcmail->config->get('pagesize', 50));
        $search_set = [];
        $records    = [];

        foreach ($sources as $s) {
            $source = $rcmail->get_address_book($s['id']);

            // check if search fields are supported....
            if (is_array($fields)) {
                $cols = !empty($source->coltypes[0]) ? array_flip($source->coltypes) : $source->coltypes;
                $supported = 0;

                foreach ($fields as $f) {
                    if (array_key_exists($f, $cols)) {
                        $supported ++;
                    }
                }

                // in advanced search we require all fields (AND operator)
                // in quick search we require at least one field (OR operator)
                if (($adv && $supported < count($fields)) || (!$adv && !$supported)) {
                    continue;
                }
            }

            // reset page
            $source->set_page(1);
            $source->set_pagesize(9999);

            // get contacts count
            $result = $source->search($fields, $search, $mode, false);

            if (empty($result) || !$result->count) {
                continue;
            }

            // get records
            $result = $source->list_records($afields);

            while ($row = $result->next()) {
                $row['sourceid'] = $s['id'];
                $key = rcube_addressbook::compose_contact_key($row, $sort_col);
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
        if ($page_size < $count) {
            $records = array_slice($records, 0, $page_size);
        }

        $result->records = array_values($records);

        // search request ID
        $search_request = md5('addr'
            . (is_array($fields) ? implode(',', $fields) : $fields)
            . (is_array($search) ? implode(',', $search) : $search)
        );

        // save search settings in session
        $_SESSION['contact_search'][$search_request] = $search_set;
        $_SESSION['contact_search_params'] = ['id' => $search_request, 'data' => [$fields, $search]];
        $_SESSION['page'] = 1;

        if ($adv) {
            $rcmail->output->command('list_contacts_clear');
        }

        if ($result->count > 0) {
            // create javascript list
            self::js_contacts_list($result);
            $rcmail->output->show_message('contactsearchsuccessful', 'confirmation', ['nr' => $result->count]);
        }
        else {
            $rcmail->output->show_message('nocontactsfound', 'notice');
        }

        // update message count display
        $rcmail->output->set_env('search_request', $search_request);
        $rcmail->output->set_env('pagecount', ceil($result->count / $page_size));
        $rcmail->output->command('set_rowcount', self::get_rowcount_text($result));
        // Re-set current source
        $rcmail->output->set_env('search_id', $sid);
        $rcmail->output->set_env('source', '');
        $rcmail->output->set_env('group', '');
        // Re-set list header
        $rcmail->output->command('set_group_prop', null);

        if (!$sid) {
            // unselect currently selected directory/group
            $rcmail->output->command('unselect_directory');
            // enable "Save search" command
            $rcmail->output->command('enable_command', 'search-create', true);
        }

        $rcmail->output->command('update_group_commands');

        // send response
        $rcmail->output->send();
    }

    public static function contact_search_form($attrib)
    {
        $rcmail       = rcmail::get_instance();
        $i_size       = !empty($attrib['size']) ? $attrib['size'] : 30;
        $short_labels = self::get_bool_attr($attrib, 'short-legend-labels');

        $form = [
            'main' => [
                'name'    => $rcmail->gettext('properties'),
                'content' => [],
            ],
            'personal' => [
                'name'    => $rcmail->gettext($short_labels ? 'personal' : 'personalinfo'),
                'content' => [],
            ],
            'other' => [
                'name'    => $rcmail->gettext('other'),
                'content' => [],
            ],
        ];

        // get supported coltypes from all address sources
        $sources  = $rcmail->get_address_sources();
        $coltypes = [];

        foreach ($sources as $s) {
            $CONTACTS = $rcmail->get_address_book($s['id']);

            if (!empty($CONTACTS->coltypes)) {
                $contact_cols = isset($CONTACTS->coltypes[0]) ? array_flip($CONTACTS->coltypes) : $CONTACTS->coltypes;
                $coltypes     = array_merge($coltypes, $contact_cols);
            }
        }

        // merge supported coltypes with global coltypes
        foreach ($coltypes as $col => $colprop) {
            if (!empty(rcmail_action_contacts_index::$CONTACT_COLTYPES[$col])) {
                $coltypes[$col] = array_merge(rcmail_action_contacts_index::$CONTACT_COLTYPES[$col], (array) $colprop);
            }
            else {
                $coltypes[$col] = (array) $colprop;
            }
        }

        // build form fields list
        foreach ($coltypes as $col => $colprop) {
            if (!isset($colprop['type'])) {
                $colprop['type'] = 'text';
            }
            if ($colprop['type'] != 'image' && empty($colprop['nosearch'])) {
                $ftype    = $colprop['type'] == 'select' ? 'select' : 'text';
                $label    = $colprop['label'] ?? $rcmail->gettext($col);
                $category = !empty($colprop['category']) ? $colprop['category'] : 'other';

                // load jquery UI datepicker for date fields
                if ($colprop['type'] == 'date') {
                    $colprop['class'] = (!empty($colprop['class']) ? $colprop['class'] . ' ' : '') . 'datepicker';
                }
                else if ($ftype == 'text') {
                    $colprop['size'] = $i_size;
                }

                $colprop['id'] = '_search_' . $col;

                $content  = html::div('row',
                    html::label(['class' => 'contactfieldlabel label', 'for' => $colprop['id']], rcube::Q($label))
                    . html::div('contactfieldcontent', rcube_output::get_edit_field('search_' . $col, '', $colprop, $ftype))
                );

                $form[$category]['content'][] = $content;
            }
        }

        $hiddenfields = new html_hiddenfield();
        $hiddenfields->add(['name' => '_adv', 'value' => 1]);

        $out = $rcmail->output->request_form([
                'name'    => 'form',
                'method'  => 'post',
                'task'    => $rcmail->task,
                'action'  => 'search',
                'noclose' => true,
            ] + $attrib, $hiddenfields->show()
        );

        $rcmail->output->add_gui_object('editform', $attrib['id']);

        unset($attrib['name']);
        unset($attrib['id']);

        foreach ($form as $f) {
            if (!empty($f['content'])) {
                $content = html::div('contactfieldgroup', join("\n", $f['content']));
                $legend  = html::tag('legend', null, rcube::Q($f['name']));

                $out .= html::tag('fieldset', $attrib, $legend . $content) . "\n";
            }
        }

        return $out . '</form>';
    }
}
