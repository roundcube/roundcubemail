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
 |   Save a contact entry or to add a new one                            |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_save extends rcmail_action_contacts_index
{
    protected static $mode = self::MODE_HTTP;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail        = rcmail::get_instance();
        $contacts      = self::contact_source(null, true);
        $cid           = rcube_utils::get_input_string('_cid', rcube_utils::INPUT_POST);
        $source        = rcube_utils::get_input_string('_source', rcube_utils::INPUT_GPC);
        $return_action = empty($cid) ? 'add' : 'edit';

        // Source changed, display the form again
        if (!empty($_GET['_reload'])) {
            $rcmail->overwrite_action($return_action);
            return;
        }

        // cannot edit record
        if (!$contacts || $contacts->readonly) {
            $rcmail->output->show_message('contactreadonly', 'error');
            $rcmail->overwrite_action($return_action);
            return;
        }

        // read POST values into hash array
        $a_record = self::process_input();

        // do input checks (delegated to $contacts instance)
        if (!$contacts->validate($a_record)) {
            $err = (array) $contacts->get_error();
            $rcmail->output->show_message(!empty($err['message']) ? rcube::Q($err['message']) : 'formincomplete', 'warning');
            $rcmail->overwrite_action($return_action, ['contact' => $a_record]);
            return;
        }

        // get raw photo data if changed
        if (isset($a_record['photo'])) {
            if ($a_record['photo'] == '-del-') {
                $a_record['photo'] = '';
            }
            else if (!empty($_SESSION['contacts']['files'][$a_record['photo']])) {
                $tempfile = $_SESSION['contacts']['files'][$a_record['photo']];
                $tempfile = $rcmail->plugins->exec_hook('attachment_get', $tempfile);
                if ($tempfile['status']) {
                    $a_record['photo'] = $tempfile['data'] ?: @file_get_contents($tempfile['path']);
                }
            }
            else {
                unset($a_record['photo']);
            }

            // cleanup session data
            $rcmail->plugins->exec_hook('attachments_cleanup', ['group' => 'contact']);
            $rcmail->session->remove('contacts');
        }

        // update an existing contact
        if (!empty($cid)) {
            $plugin = $rcmail->plugins->exec_hook('contact_update', [
                    'id'     => $cid,
                    'record' => $a_record,
                    'source' => $source
            ]);

            $a_record = $plugin['record'];

            if (!$plugin['abort']) {
                $result = $contacts->update($cid, $a_record);
            }
            else {
                $result = $plugin['result'];
            }

            if ($result) {
                // show confirmation
                $rcmail->output->show_message('successfullysaved', 'confirmation', null, false);

                // in search mode, just reload the list (#1490015)
                if (!empty($_REQUEST['_search'])) {
                    $rcmail->output->command('parent.command', 'list');
                    $rcmail->output->send('iframe');
                }

                $newcid = null;

                // LDAP DN change
                if (is_string($result) && strlen($result) > 1) {
                    $newcid = $result;
                    // change cid in POST for 'show' action
                    $_POST['_cid'] = $newcid;
                }

                // refresh contact data for list update and 'show' action
                $contact = $contacts->get_record($newcid ?: $cid, true);

                // Plugins can decide to remove the contact on edit, e.g. automatic_addressbook
                // Best we can do is to refresh the list (#5522)
                if (empty($contact)) {
                    $rcmail->output->command('parent.command', 'list');
                    $rcmail->output->send('iframe');
                }

                // Update contacts list
                $a_js_cols = [];
                $record    = $contact;
                $record['email'] = array_first($contacts->get_col_values('email', $record, true));
                $record['name']  = rcube_addressbook::compose_list_name($record);

                foreach (['name'] as $col) {
                    $a_js_cols[] = rcube::Q((string) $record[$col]);
                }

                // performance: unset some big data items we don't need here
                $record = array_intersect_key($record, ['ID' => 1,'email' => 1,'name' => 1]);
                $record['_type'] = 'person';

                // update the changed col in list
                $rcmail->output->command('parent.update_contact_row', $cid, $a_js_cols, $newcid, $source, $record);

                $rcmail->overwrite_action('show', ['contact' => $contact]);
            }
            else {
                // show error message
                $error = self::error_str($contacts, $plugin);

                $rcmail->output->show_message($error, 'error', null, false);
                $rcmail->overwrite_action('show');
            }
        }
        // insert a new contact
        else {
            // Name of the addressbook already selected on the list
            $orig_source = rcube_utils::get_input_string('_orig_source', rcube_utils::INPUT_GPC);

            if (!strlen($source)) {
                $source = $orig_source;
            }

            // show notice if existing contacts with same e-mail are found
            foreach ($contacts->get_col_values('email', $a_record, true) as $email) {
                if ($email && ($res = $contacts->search('email', $email, 1, false, true)) && $res->count) {
                    $rcmail->output->show_message('contactexists', 'notice', null, false);
                    break;
                }
            }

            $plugin = $rcmail->plugins->exec_hook('contact_create', [
                    'record' => $a_record,
                    'source' => $source
            ]);

            $a_record = $plugin['record'];

            // insert record and send response
            if (!$plugin['abort']) {
                $insert_id = $contacts->insert($a_record);
            }
            else {
                $insert_id = $plugin['result'];
            }

            if ($insert_id) {
                $contacts->reset();

                // add new contact to the specified group
                if ($contacts->groups && $contacts->group_id) {
                    $plugin = $rcmail->plugins->exec_hook('group_addmembers', [
                            'group_id' => $contacts->group_id,
                            'ids'      => $insert_id,
                            'source'   => $source
                    ]);

                    if (!$plugin['abort']) {
                        if (($maxnum = $rcmail->config->get('max_group_members', 0)) && ($contacts->count()->count + 1 > $maxnum)) {
                            // @FIXME: should we remove the contact?
                            $msgtext = $rcmail->gettext(['name' => 'maxgroupmembersreached', 'vars' => ['max' => $maxnum]]);
                            $rcmail->output->command('parent.display_message', $msgtext, 'warning');
                        }
                        else {
                            $contacts->add_to_group($plugin['group_id'], $plugin['ids']);
                        }
                    }
                }

                // show confirmation
                $rcmail->output->show_message('successfullysaved', 'confirmation', null, false);

                $rcmail->output->command('parent.set_rowcount', $rcmail->gettext('loading'));
                $rcmail->output->command('parent.list_contacts');

                $rcmail->output->send('iframe');
            }
            else {
                // show error message
                $error = self::error_str($contacts, $plugin);
                $rcmail->output->show_message($error, 'error', null, false);
                $rcmail->overwrite_action('add');
            }
        }
    }

    public static function process_input()
    {
        $record = [];

        foreach (rcmail_action_contacts_index::$CONTACT_COLTYPES as $col => $colprop) {
            if (!empty($colprop['composite'])) {
                continue;
            }

            $fname = '_' . $col;

            // gather form data of composite fields
            if (!empty($colprop['childs'])) {
                $values = [];
                foreach ($colprop['childs'] as $childcol => $cp) {
                    $vals = rcube_utils::get_input_value('_' . $childcol, rcube_utils::INPUT_POST, true);
                    foreach ((array) $vals as $i => $val) {
                        $values[$i][$childcol] = $val;
                    }
                }

                if (isset($_REQUEST['_subtype_' . $col])) {
                    $subtypes = (array) rcube_utils::get_input_value('_subtype_' . $col, rcube_utils::INPUT_POST);
                }
                else {
                    $subtypes = [''];
                }

                foreach ($subtypes as $i => $subtype) {
                    $suffix = $subtype ? ":$subtype" : '';
                    if ($values[$i]) {
                        $record[$col . $suffix][] = $values[$i];
                    }
                }
            }
            // assign values and subtypes
            else if (isset($_POST[$fname]) && is_array($_POST[$fname])) {
                $values   = rcube_utils::get_input_value($fname, rcube_utils::INPUT_POST, true);
                $subtypes = rcube_utils::get_input_value('_subtype_' . $col, rcube_utils::INPUT_POST);

                foreach ($values as $i => $val) {
                    if ($col == 'email') {
                        // extract email from full address specification, e.g. "Name" <addr@domain.tld>
                        $addr = rcube_mime::decode_address_list($val, 1, false);
                        if (!empty($addr) && ($addr = array_pop($addr)) && $addr['mailto']) {
                            $val = $addr['mailto'];
                        }
                    }

                    $subtype = $subtypes[$i] ? ':'.$subtypes[$i] : '';
                    $record[$col.$subtype][] = $val;
                }
            }
            else if (isset($_POST[$fname])) {
                $record[$col] = rcube_utils::get_input_value($fname, rcube_utils::INPUT_POST, true);

                // normalize the submitted date strings
                if ($colprop['type'] == 'date') {
                    if ($record[$col] && ($dt = rcube_utils::anytodatetime($record[$col]))) {
                        $record[$col] = $dt->format('Y-m-d');
                    }
                    else {
                        unset($record[$col]);
                    }
                }
            }
        }

        // Generate contact's display name (must be before validation)
        if (empty($record['name'])) {
            $record['name'] = rcube_addressbook::compose_display_name($record, true);

            // Reset it if equals to email address (from compose_display_name())
            $email = rcube_addressbook::get_col_values('email', $record, true);
            if (isset($email[0]) && $record['name'] == $email[0]) {
                $record['name'] = '';
            }
        }

        return $record;
    }

    public static function error_str($contacts, $plugin)
    {
        if (!empty($plugin['message'])) {
            return $plugin['message'];
        }

        $err = $contacts->get_error();

        if (!empty($err['message'])) {
            return $err['message'];
        }

        return 'errorsaving';
    }
}
