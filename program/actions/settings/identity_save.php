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
 |   Save an identity record or to add a new one                         |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_identity_save extends rcmail_action_settings_index
{
    protected static $mode = self::MODE_HTTP;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        $IDENTITIES_LEVEL = intval($rcmail->config->get('identities_level', 0));

        $a_save_cols = ['name', 'email', 'organization', 'reply-to', 'bcc', 'standard', 'signature', 'html_signature'];
        $a_bool_cols = ['standard', 'html_signature'];
        $updated     = false;

        // check input
        if (empty($_POST['_email']) && ($IDENTITIES_LEVEL == 0 || $IDENTITIES_LEVEL == 2)) {
            $rcmail->output->show_message('noemailwarning', 'warning');
            $rcmail->overwrite_action('edit-identity');
            return;
        }

        $save_data = [];
        foreach ($a_save_cols as $col) {
            $fname = '_'.$col;
            if (isset($_POST[$fname])) {
                $save_data[$col] = rcube_utils::get_input_string($fname, rcube_utils::INPUT_POST, true);
            }
        }

        // set "off" values for checkboxes that were not checked, and therefore
        // not included in the POST body.
        foreach ($a_bool_cols as $col) {
            $fname = '_' . $col;
            if (!isset($_POST[$fname])) {
                $save_data[$col] = 0;
            }
        }

        // make the identity a "default" if only one identity is allowed
        if ($IDENTITIES_LEVEL > 1) {
            $save_data['standard'] = 1;
        }

        // unset email address if user has no rights to change it
        if ($IDENTITIES_LEVEL == 1 || $IDENTITIES_LEVEL == 3) {
            unset($save_data['email']);
        }
        // unset all fields except signature
        else if ($IDENTITIES_LEVEL == 4) {
            foreach ($save_data as $idx => $value) {
                if ($idx != 'signature' && $idx != 'html_signature') {
                    unset($save_data[$idx]);
                }
            }
        }

        // Validate e-mail addresses
        $email_checks = !empty($save_data['email']) ? [rcube_utils::idn_to_ascii($save_data['email'])] : [];
        foreach (['reply-to', 'bcc'] as $item) {
            if (!empty($save_data[$item])) {
                foreach (rcube_mime::decode_address_list($save_data[$item], null, false) as $rcpt) {
                    $email_checks[] = rcube_utils::idn_to_ascii($rcpt['mailto']);
                }
            }
        }

        foreach ($email_checks as $email) {
            if ($email && !rcube_utils::check_email($email)) {
                // show error message
                $rcmail->output->show_message('emailformaterror', 'error', ['email' => rcube_utils::idn_to_utf8($email)], false);
                $rcmail->overwrite_action('edit-identity');
                return;
            }
        }

        if (!empty($save_data['signature']) && !empty($save_data['html_signature'])) {
            // replace uploaded images with data URIs
            $save_data['signature'] = self::attach_images($save_data['signature'], 'identity');

            // XSS protection in HTML signature (#1489251)
            $save_data['signature'] = self::wash_html($save_data['signature']);

            // clear POST data of signature, we want to use safe content
            // when the form is displayed again
            unset($_POST['_signature']);
        }

        // update an existing identity
        if (!empty($_POST['_iid'])) {
            $iid = rcube_utils::get_input_string('_iid', rcube_utils::INPUT_POST);

            if (in_array($IDENTITIES_LEVEL, [1, 3, 4])) {
                // merge with old identity data, fixes #1488834
                $identity  = $rcmail->user->get_identity($iid);
                $save_data = array_merge($identity, $save_data);

                unset($save_data['changed'], $save_data['del'], $save_data['user_id'], $save_data['identity_id']);
            }

            $plugin = $rcmail->plugins->exec_hook('identity_update', ['id' => $iid, 'record' => $save_data]);
            $save_data = $plugin['record'];

            if ($save_data['email']) {
                $save_data['email'] = rcube_utils::idn_to_ascii($save_data['email']);
            }

            if (!$plugin['abort']) {
                $updated = $rcmail->user->update_identity($iid, $save_data);
            }
            else {
                $updated = $plugin['result'];
            }

            if ($updated) {
                $rcmail->output->show_message('successfullysaved', 'confirmation');

                if (!empty($save_data['standard'])) {
                    $default_id = $iid;
                }

                // update the changed col in list
                $name = $save_data['name'] . ' <' . rcube_utils::idn_to_utf8($save_data['email']) .'>';
                $rcmail->output->command('parent.update_identity_row', $iid, rcube::Q(trim($name)));
            }
            else {
                // show error message
                $error = !empty($plugin['message']) ? $plugin['message'] : 'errorsaving';
                $rcmail->output->show_message($error, 'error', null, false);
                $rcmail->overwrite_action('edit-identity');
                return;
            }
        }
        // insert a new identity record
        else if ($IDENTITIES_LEVEL < 2) {
            if ($IDENTITIES_LEVEL == 1) {
                $save_data['email'] = $rcmail->get_user_email();
            }

            $plugin = $rcmail->plugins->exec_hook('identity_create', ['record' => $save_data]);
            $save_data = $plugin['record'];

            if ($save_data['email']) {
                $save_data['email'] = rcube_utils::idn_to_ascii($save_data['email']);
            }

            if (!$plugin['abort']) {
                $insert_id = $save_data['email'] ? $rcmail->user->insert_identity($save_data) : null;
            }
            else {
                $insert_id = $plugin['result'];
            }

            if ($insert_id) {
                $rcmail->plugins->exec_hook('identity_create_after', ['id' => $insert_id, 'record' => $save_data]);

                $rcmail->output->show_message('successfullysaved', 'confirmation', null, false);

                $_GET['_iid'] = $insert_id;

                if (!empty($save_data['standard'])) {
                    $default_id = $insert_id;
                }

                // add a new row to the list
                $name = $save_data['name'] . ' <' . rcube_utils::idn_to_utf8($save_data['email']) .'>';
                $rcmail->output->command('parent.update_identity_row', $insert_id, rcube::Q(trim($name)), true);
            }
            else {
                // show error message
                $error = !empty($plugin['message']) ? $plugin['message'] : 'errorsaving';
                $rcmail->output->show_message($error, 'error', null, false);
                $rcmail->overwrite_action('edit-identity');
                return;
            }
        }
        else {
            $rcmail->output->show_message('opnotpermitted', 'error');
        }

        // mark all other identities as 'not-default'
        if (!empty($default_id)) {
            $rcmail->user->set_default($default_id);
        }

        // go to next step
        $rcmail->overwrite_action('edit-identity');
    }
}
