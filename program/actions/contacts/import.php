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
 |   Import contacts from a vCard or CSV file                            |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_import extends rcmail_action_contacts_index
{
    const UPLOAD_ERR_CSV_FIELDS = 101;

    protected static $stats;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail     = rcmail::get_instance();
        $importstep = 'import_form';
        $has_map    = isset($_POST['_map']) && is_array($_POST['_map']);

        if ($has_map || (isset($_FILES['_file']) && is_array($_FILES['_file']))) {
            $replace      = (bool) rcube_utils::get_input_string('_replace', rcube_utils::INPUT_GPC);
            $target       = rcube_utils::get_input_string('_target', rcube_utils::INPUT_GPC);
            $with_groups  = (int) rcube_utils::get_input_string('_groups', rcube_utils::INPUT_GPC);

            // reload params for CSV field mapping screen
            if ($has_map && !empty($_SESSION['contactcsvimport']['params'])) {
                $params = $_SESSION['contactcsvimport']['params'];

                $replace     = $params['replace'];
                $target      = $params['target'];
                $with_groups = $params['with_groups'];
            }

            $vcards       = [];
            $csvs         = [];
            $map          = [];
            $upload_error = null;

            $CONTACTS = $rcmail->get_address_book($target, true);

            if (!$CONTACTS->groups) {
                $with_groups = false;
            }

            if ($CONTACTS->readonly) {
                $rcmail->output->show_message('addresswriterror', 'error');
            }
            else {
                $filepaths = [];
                if ($has_map) {
                    $filepaths = $_SESSION['contactcsvimport']['files'];
                }
                else {
                    foreach ((array) $_FILES['_file']['tmp_name'] as $i => $filepath) {
                        // Process uploaded file if there is no error
                        $err = $_FILES['_file']['error'][$i];

                        if ($err) {
                            $upload_error = $err;
                        }
                        else {
                            $filepaths[] = $filepath;
                        }
                    }
                }

                foreach ($filepaths as $filepath) {
                    $file_content = file_get_contents($filepath);

                    // let rcube_vcard do the hard work :-)
                    $vcard_o = new rcube_vcard();
                    $vcard_o->extend_fieldmap($CONTACTS->vcard_map);
                    $v_list = $vcard_o->import($file_content);

                    if (!empty($v_list)) {
                        $vcards = array_merge($vcards, $v_list);
                        continue;
                    }

                    // no vCards found, try CSV
                    $csv = new rcube_csv2vcard($_SESSION['language']);

                    if ($has_map) {
                        $skip_head = isset($_POST['_skip_header']);
                        $map       = rcube_utils::get_input_value('_map', rcube_utils::INPUT_GPC);
                        $map       = array_filter($map);

                        $csv->set_map($map);
                        $csv->import($file_content, false, $skip_head);

                        unlink($filepath);
                    }
                    else {
                        // save uploaded file for the real import in the next step
                        $temp_csv = rcube_utils::temp_filename('csvimpt');
                        if (move_uploaded_file($filepath, $temp_csv) && file_exists($temp_csv)) {
                            $fields   = $csv->get_fields();
                            $last_map = $map;
                            $map = $csv->import($file_content, true);

                            // when multiple CSV files are uploaded check they all have the same structure
                            if ($last_map && $last_map !== $map) {
                                $csvs = [];
                                $upload_error = self::UPLOAD_ERR_CSV_FIELDS;
                                break;
                            }

                            $csvs[] = $temp_csv;
                        }
                        else {
                            $upload_error = UPLOAD_ERR_CANT_WRITE;
                        }

                        continue;
                    }

                    $v_list = $csv->export();

                    if (!empty($v_list)) {
                        $vcards = array_merge($vcards, $v_list);
                    }
                }
            }

            if (count($csvs) > 0) {
                // csv import, show field mapping options
                $importstep = 'import_map';

                $_SESSION['contactcsvimport']['files']  = $csvs;
                $_SESSION['contactcsvimport']['params'] = [
                    'replace'     => $replace,
                    'target'      => $target,
                    'with_groups' => $with_groups,
                    'fields'      => !empty($fields) ? $fields : [],
                ];

                // Stored separately due to nested array limitations in session
                $_SESSION['contactcsvimport']['map'] = $map;

                // Re-enable the import button
                $rcmail->output->command('parent.import_state_set', 'error');
            }
            elseif (count($vcards) > 0) {
                // import vcards
                self::$stats = new stdClass;
                self::$stats->names         = [];
                self::$stats->skipped_names = [];
                self::$stats->count         = count($vcards);
                self::$stats->inserted = 0;
                self::$stats->skipped  = 0;
                self::$stats->invalid  = 0;
                self::$stats->errors   = 0;

                if ($replace) {
                    $CONTACTS->delete_all($CONTACTS->groups && $with_groups < 2);
                }

                if ($with_groups) {
                    $import_groups = $CONTACTS->list_groups();
                }

                foreach ($vcards as $vcard) {
                    $a_record = $vcard->get_assoc();

                    // Generate contact's display name (must be before validation), the same we do in save.inc
                    if (empty($a_record['name'])) {
                        $a_record['name'] = rcube_addressbook::compose_display_name($a_record, true);
                        // Reset it if equals to email address (from compose_display_name())
                        if ($a_record['name'] == $a_record['email'][0]) {
                            $a_record['name'] = '';
                        }
                    }

                    // skip invalid (incomplete) entries
                    if (!$CONTACTS->validate($a_record, true)) {
                        self::$stats->invalid++;
                        continue;
                    }

                    // We're using UTF8 internally
                    $email = null;
                    if (isset($vcard->email[0])) {
                        $email = $vcard->email[0];
                        $email = rcube_utils::idn_to_utf8($email);
                    }

                    if (!$replace) {
                        $existing = null;
                        // compare e-mail address
                        if ($email) {
                            $existing = $CONTACTS->search('email', $email, 1, false);
                        }
                        // compare display name if email not found
                        if ((!$existing || !$existing->count) && $vcard->displayname) {
                            $existing = $CONTACTS->search('name', $vcard->displayname, 1, false);
                        }
                        if ($existing && $existing->count) {
                            self::$stats->skipped++;
                            self::$stats->skipped_names[] = $vcard->displayname ?: $email;
                            continue;
                        }
                    }

                    $a_record['vcard'] = $vcard->export();

                    $plugin   = $rcmail->plugins->exec_hook('contact_create', ['record' => $a_record, 'source' => null]);
                    $a_record = $plugin['record'];

                    // insert record and send response
                    if (empty($plugin['abort'])) {
                        $success = $CONTACTS->insert($a_record);
                    }
                    else {
                        $success = $plugin['result'];
                    }

                    if ($success) {
                        // assign groups for this contact (if enabled)
                        if ($with_groups && !empty($a_record['groups'])) {
                            foreach (explode(',', $a_record['groups'][0]) as $group_name) {
                                if ($group_id = self::import_group_id($group_name, $CONTACTS, $with_groups == 1, $import_groups)) {
                                    $CONTACTS->add_to_group($group_id, $success);
                                }
                            }
                        }

                        self::$stats->inserted++;
                        self::$stats->names[] = $a_record['name'] ?: $email;
                    }
                    else {
                        self::$stats->errors++;
                    }
                }

                $importstep = 'import_confirm';
                $_SESSION['contactcsvimport'] = null;

                $rcmail->output->command('parent.import_state_set', self::$stats->inserted ? 'reload' : 'ok');
            }
            else {
                if ($upload_error == self::UPLOAD_ERR_CSV_FIELDS) {
                    $rcmail->output->show_message('csvfilemismatch', 'error');
                }
                else {
                    self::upload_error($upload_error);
                }

                $rcmail->output->command('parent.import_state_set', 'error');
            }
        }

        $rcmail->output->set_pagetitle($rcmail->gettext('importcontacts'));

        $rcmail->output->add_handlers([
                'importstep' => [$this, $importstep],
        ]);

        // render page
        if ($rcmail->output->template_exists('contactimport')) {
            $rcmail->output->send('contactimport');
        }
        else {
            $rcmail->output->send('importcontacts'); // deprecated
        }
    }

    /**
     * Handler function to display the import/upload form
     */
    public static function import_form($attrib)
    {
        $rcmail = rcmail::get_instance();
        $target = rcube_utils::get_input_string('_target', rcube_utils::INPUT_GPC);

        $attrib += ['id' => 'rcmImportForm'];

        $writable_books = $rcmail->get_address_sources(true, true);
        $max_filesize   = self::upload_init();

        $form   = '';
        $hint   = $rcmail->gettext(['id' => 'importfile', 'name' => 'maxuploadsize', 'vars' => ['size' => $max_filesize]]);
        $table  = new html_table(['cols' => 2]);
        $upload = new html_inputfield([
                'type'     => 'file',
                'name'     => '_file[]',
                'id'       => 'rcmimportfile',
                'size'     => 40,
                'multiple' => 'multiple',
                'class'    => 'form-control-file',
        ]);

        $table->add('title', html::label('rcmimportfile', $rcmail->gettext('importfromfile')));
        $table->add(null, $upload->show() . html::div('hint', $hint));

        // addressbook selector
        if (count($writable_books) > 1) {
            $select = new html_select([
                    'name'       => '_target',
                    'id'         => 'rcmimporttarget',
                    'is_escaped' => true,
                    'class'      => 'custom-select'
            ]);

            foreach ($writable_books as $book) {
                $select->add($book['name'], $book['id']);
            }

            $table->add('title', html::label('rcmimporttarget', $rcmail->gettext('importtarget')));
            $table->add(null, $select->show($target));
        }
        else {
            $abook = new html_hiddenfield(['name' => '_target', 'value' => key($writable_books)]);
            $form .= $abook->show();
        }

        $form .= html::tag('input', ['type' => 'hidden', 'name' => '_unlock', 'value' => '']);

        // selector for group import options
        if (count($writable_books) >= 1 || $writable_books[0]->groups) {
            $select = new html_select([
                    'name'       => '_groups',
                    'id'         => 'rcmimportgroups',
                    'is_escaped' => true,
                    'class'      => 'custom-select'
            ]);
            $select->add($rcmail->gettext('none'), '0');
            $select->add($rcmail->gettext('importgroupsall'), '1');
            $select->add($rcmail->gettext('importgroupsexisting'), '2');

            $table->add('title', html::label('rcmimportgroups', $rcmail->gettext('importgroups')));
            $table->add(null, $select->show(rcube_utils::get_input_value('_groups', rcube_utils::INPUT_GPC)));
        }

        // checkbox to replace the entire address book
        $check_replace = new html_checkbox(['name' => '_replace', 'value' => 1, 'id' => 'rcmimportreplace']);
        $table->add('title', html::label('rcmimportreplace', $rcmail->gettext('importreplace')));
        $table->add(null, $check_replace->show(rcube_utils::get_input_string('_replace', rcube_utils::INPUT_GPC)));

        $form .= $table->show(['id' => null] + $attrib);

        // remove any info left over info from previous import attempts
        $_SESSION['contactcsvimport'] = null;

        $rcmail->output->set_env('writable_source', !empty($writable_books));
        $rcmail->output->add_label('selectimportfile','importwait');
        $rcmail->output->add_gui_object('importform', $attrib['id']);

        $attrib = [
            'action'  => $rcmail->url('import'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ] + $attrib;

        return html::p(null, rcube::Q($rcmail->gettext('importdesc'), 'show'))
            . $rcmail->output->form_tag($attrib, $form);
    }

    /**
     * Render the field mapping page for the CSV import process
     */
    public static function import_map($attrib)
    {
        $rcmail = rcmail::get_instance();
        $params = $_SESSION['contactcsvimport']['params'];

        // hide groups field from list when group import disabled
        if (empty($params['with_groups'])) {
            unset($params['fields']['groups']);
        }

        $fieldlist = new html_select(['name' => '_map[]']);
        $fieldlist->add($rcmail->gettext('fieldnotmapped'), '');
        foreach ($params['fields'] as $id => $name) {
            $fieldlist->add($name, $id);
        }

        $field_table = new html_table(['cols' => 2] + $attrib);

        if ($classes = $attrib['table-header-class']) {
            $field_table->set_header_attribs($classes);
        }

        $field_table->add_header($attrib['table-col-source-class'] ?: null, $rcmail->gettext('source'));
        $field_table->add_header($attrib['table-col-destination-class'] ?: null, $rcmail->gettext('destination'));

        $map = $_SESSION['contactcsvimport']['map'];
        foreach ($map['source'] as $i => $name) {
            $field_table->add('title', html::label('rcmimportmap' . $i, rcube::Q($name)));
            $field_table->add(null, $fieldlist->show(array_key_exists($i, $map['destination']) ? $map['destination'][$i] : '', ['id' => 'rcmimportmap' . $i]));
        }

        $form = '';
        $form .= html::tag('input', ['type' => 'hidden', 'name' => '_unlock', 'value' => '']);

        // show option to import data from first line of the file
        $check_header = new html_checkbox(['name' => '_skip_header', 'value' => 1, 'id' => 'rcmskipheader']);
        $form .= html::p(null, html::label('rcmskipheader', $check_header->show(1) . $rcmail->gettext('skipheader')));

        $form .= $field_table->show();

        $attrib = ['action' => $rcmail->url('import'), 'method' => 'post'] + $attrib + ['id' => 'rcmImportFormMap'];

        $rcmail->output->add_gui_object('importformmap', $attrib['id']);

        return html::p(null, rcube::Q($rcmail->gettext('importmapdesc'), 'show'))
            . $rcmail->output->form_tag($attrib, $form);
    }

    /**
     * Render the confirmation page for the import process
     */
    public static function import_confirm($attrib)
    {
        $rcmail = rcmail::get_instance();
        $vars   = get_object_vars(self::$stats);
        $vars['names'] = $vars['skipped_names'] = '';

        $content = html::p(null, $rcmail->gettext([
                'name' => 'importconfirm',
                'nr'   => self::$stats->inserted,
                'vars' => $vars,
            ]) . (self::$stats->names ? ':' : '.')
        );

        if (self::$stats->names) {
            $content .= html::p('em', join(', ', array_map(['rcube', 'Q'], self::$stats->names)));
        }

        if (self::$stats->skipped) {
            $content .= html::p(null, $rcmail->gettext([
                    'name' => 'importconfirmskipped',
                    'nr'   => self::$stats->skipped,
                    'vars' => $vars,
                ]) . ':')
                . html::p('em', join(', ', array_map(['rcube', 'Q'], self::$stats->skipped_names)));
        }

        return html::div($attrib, $content);
    }

    /**
     * Returns the matching group id. If group doesn't exist, it'll be created if allowed.
     */
    public static function import_group_id($group_name, $contacts, $create, &$import_groups)
    {
        $group_id = 0;
        foreach ($import_groups as $group) {
            if (strtolower($group['name']) === strtolower($group_name)) {
                $group_id = $group['ID'];
                break;
            }
        }

        // create a new group
        if (!$group_id && $create) {
            $new_group = $contacts->create_group($group_name);

            if (empty($new_group['ID'])) {
                $new_group['ID'] = $new_group['id'];
            }

            $import_groups[] = $new_group;
            $group_id        = $new_group['ID'];
        }

        return $group_id;
    }
}
