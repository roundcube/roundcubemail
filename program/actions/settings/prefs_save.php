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
 |   Save user preferences to DB and to the current session              |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_prefs_save extends rcmail_action
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

        $CURR_SECTION  = rcube_utils::get_input_string('_section', rcube_utils::INPUT_POST);
        $dont_override = (array) $rcmail->config->get('dont_override');
        $a_user_prefs  = [];

        // set options for specified section
        switch ($CURR_SECTION) {
        case 'general':
            $a_user_prefs = [
                'language'     => self::prefs_input('language', '/^[a-zA-Z0-9_-]+$/'),
                'timezone'     => self::prefs_input('timezone', '/^[a-zA-Z_\/-]+$/'),
                'date_format'  => self::prefs_input('date_format', '/^[a-zA-Z_.\/ -]+$/'),
                'time_format'  => self::prefs_input('time_format', '/^[a-zA-Z0-9: ]+$/'),
                'prettydate'   => isset($_POST['_pretty_date']),
                'display_next' => isset($_POST['_display_next']),
                'refresh_interval' => self::prefs_input_int('refresh_interval') * 60,
                'standard_windows' => isset($_POST['_standard_windows']),
                'skin'         => self::prefs_input('skin', '/^[a-zA-Z0-9_.-]+$/'),
            ];

            // compose derived date/time format strings
            if (
                (isset($_POST['_date_format']) || isset($_POST['_time_format']))
                && !empty($a_user_prefs['date_format'])
                && !empty($a_user_prefs['time_format'])
            ) {
                $a_user_prefs['date_short'] = 'D ' . $a_user_prefs['time_format'];
                $a_user_prefs['date_long']  = $a_user_prefs['date_format'] . ' ' . $a_user_prefs['time_format'];
            }

            break;

        case 'mailbox':
            $a_user_prefs = [
                'layout'             => self::prefs_input('layout', '/^[a-z]+$/'),
                'mail_read_time'     => self::prefs_input_int('mail_read_time'),
                'autoexpand_threads' => self::prefs_input_int('autoexpand_threads'),
                'check_all_folders'  => isset($_POST['_check_all_folders']),
                'mail_pagesize'      => max(2, self::prefs_input_int('mail_pagesize')),
            ];

            break;

        case 'mailview':
            $a_user_prefs = [
                'message_extwin'     => self::prefs_input_int('message_extwin'),
                'message_show_email' => isset($_POST['_message_show_email']),
                'prefer_html'        => isset($_POST['_prefer_html']),
                'inline_images'      => isset($_POST['_inline_images']),
                'show_images'        => self::prefs_input_int('show_images'),
                'mdn_requests'       => self::prefs_input_int('mdn_requests'),
                'default_charset'    => self::prefs_input('default_charset', '/^[a-zA-Z0-9-]+$/'),
            ];

            break;

        case 'compose':
            $a_user_prefs = [
                'compose_extwin'     => self::prefs_input_int('compose_extwin'),
                'htmleditor'         => self::prefs_input_int('htmleditor'),
                'draft_autosave'     => self::prefs_input_int('draft_autosave'),
                'mime_param_folding' => self::prefs_input_int('mime_param_folding'),
                'force_7bit'         => isset($_POST['_force_7bit']),
                'mdn_default'        => isset($_POST['_mdn_default']),
                'dsn_default'        => isset($_POST['_dsn_default']),
                'reply_same_folder'  => isset($_POST['_reply_same_folder']),
                'spellcheck_before_send' => isset($_POST['_spellcheck_before_send']),
                'spellcheck_ignore_syms' => isset($_POST['_spellcheck_ignore_syms']),
                'spellcheck_ignore_nums' => isset($_POST['_spellcheck_ignore_nums']),
                'spellcheck_ignore_caps' => isset($_POST['_spellcheck_ignore_caps']),
                'show_sig'           => self::prefs_input_int('show_sig'),
                'reply_mode'         => self::prefs_input_int('reply_mode'),
                'sig_below'          => isset($_POST['_sig_below']),
                'strip_existing_sig' => isset($_POST['_strip_existing_sig']),
                'sig_separator'      => isset($_POST['_sig_separator']),
                'default_font'       => self::prefs_input('default_font', '/^[a-zA-Z ]+$/'),
                'default_font_size'  => self::prefs_input('default_font_size', '/^[0-9]+pt$/'),
                'reply_all_mode'     => self::prefs_input_int('reply_all_mode'),
                'forward_attachment' => !empty($_POST['_forward_attachment']),
                'compose_save_localstorage' => self::prefs_input_int('compose_save_localstorage'),
            ];

            break;

        case 'addressbook':
            $a_user_prefs = [
                'default_addressbook'  => rcube_utils::get_input_string('_default_addressbook', rcube_utils::INPUT_POST, true),
                'collected_recipients' => rcube_utils::get_input_string('_collected_recipients', rcube_utils::INPUT_POST, true),
                'collected_senders'    => rcube_utils::get_input_string('_collected_senders', rcube_utils::INPUT_POST, true),
                'autocomplete_single'  => isset($_POST['_autocomplete_single']),
                'addressbook_sort_col' => self::prefs_input('addressbook_sort_col', '/^[a-z_]+$/'),
                'addressbook_name_listing' => self::prefs_input_int('addressbook_name_listing'),
                'addressbook_pagesize' => max(2, self::prefs_input_int('addressbook_pagesize')),
                'contact_form_mode'    => self::prefs_input('contact_form_mode', '/^(private|business)$/'),
            ];

            break;

        case 'server':
            $a_user_prefs = [
                'read_when_deleted' => isset($_POST['_read_when_deleted']),
                'skip_deleted'      => isset($_POST['_skip_deleted']),
                'flag_for_deletion' => isset($_POST['_flag_for_deletion']),
                'delete_junk'       => isset($_POST['_delete_junk']),
                'logout_purge'      => self::prefs_input('logout_purge', '/^(all|never|30|60|90)$/'),
                'logout_expunge'    => isset($_POST['_logout_expunge']),
            ];

            break;

        case 'folders':
            $a_user_prefs = [
                'show_real_foldernames' => isset($_POST['_show_real_foldernames']),
                // stop using SPECIAL-USE (#4782)
                'lock_special_folders'  => !in_array('lock_special_folders', $dont_override),
            ];

            foreach (rcube_storage::$folder_types as $type) {
                $a_user_prefs[$type . '_mbox'] = rcube_utils::get_input_string('_' . $type . '_mbox', rcube_utils::INPUT_POST, true);
            };

            break;

        case 'encryption':
            $a_user_prefs = [
                'mailvelope_main_keyring' => isset($_POST['_mailvelope_main_keyring']),
            ];

            break;
        }

        $plugin = rcmail::get_instance()->plugins->exec_hook('preferences_save',
            ['prefs' => $a_user_prefs, 'section' => $CURR_SECTION]);

        $a_user_prefs = $plugin['prefs'];

        // don't override these parameters
        foreach ($dont_override as $p) {
            $a_user_prefs[$p] = $rcmail->config->get($p);
        }

        // verify some options
        switch ($CURR_SECTION) {
        case 'general':
            // switch UI language
            if (isset($_POST['_language']) && $a_user_prefs['language'] != $_SESSION['language']) {
                $rcmail->load_language($a_user_prefs['language']);
                $rcmail->output->command('reload', 500);
            }

            // switch skin (if valid, otherwise unset the pref and fall back to default)
            if (!$rcmail->output->check_skin($a_user_prefs['skin'])) {
                unset($a_user_prefs['skin']);
            }
            else if ($rcmail->config->get('skin') != $a_user_prefs['skin']) {
                $rcmail->output->command('reload', 500);
            }

            $a_user_prefs['timezone'] = (string) $a_user_prefs['timezone'];

            $min_refresh_interval = (int) $rcmail->config->get('min_refresh_interval');
            if (!empty($a_user_prefs['refresh_interval']) && $min_refresh_interval) {
                if ($a_user_prefs['refresh_interval'] < $min_refresh_interval) {
                    $a_user_prefs['refresh_interval'] = $min_refresh_interval;
                }
            }

            break;

        case 'mailbox':
            // force min size
            if ($a_user_prefs['mail_pagesize'] < 1) {
                $a_user_prefs['mail_pagesize'] = 10;
            }

            $max_pagesize = (int) $rcmail->config->get('max_pagesize');
            if ($max_pagesize && ($a_user_prefs['mail_pagesize'] > $max_pagesize)) {
                $a_user_prefs['mail_pagesize'] = $max_pagesize;
            }

            break;

        case 'addressbook':
            // force min size
            if ($a_user_prefs['addressbook_pagesize'] < 1) {
                $a_user_prefs['addressbook_pagesize'] = 10;
            }

            $max_pagesize = (int) $rcmail->config->get('max_pagesize');
            if ($max_pagesize && ($a_user_prefs['addressbook_pagesize'] > $max_pagesize)) {
                $a_user_prefs['addressbook_pagesize'] = $max_pagesize;
            }

            break;

        case 'folders':
            $storage  = $rcmail->get_storage();
            $specials = [];

            foreach (rcube_storage::$folder_types as $type) {
                $specials[$type] = $a_user_prefs[$type . '_mbox'];
            }

            $storage->set_special_folders($specials);

            break;

        case 'server':
            if (isset($a_user_prefs['logout_purge']) && !is_numeric($a_user_prefs['logout_purge'])) {
                $a_user_prefs['logout_purge'] = $a_user_prefs['logout_purge'] !== 'never';
            }

            break;
        }

        // Save preferences
        if (empty($plugin['abort'])) {
            $saved = $rcmail->user->save_prefs($a_user_prefs);
        }
        else {
            $saved = $plugin['result'];
        }

        if ($saved) {
            $rcmail->output->show_message('successfullysaved', 'confirmation');
        }
        else {
            $rcmail->output->show_message(!empty($plugin['message']) ? $plugin['message'] : 'errorsaving', 'error');
        }

        // display the form again
        $rcmail->overwrite_action('edit-prefs');
    }

    /**
     * Get option value from POST and validate with a regex
     */
    public static function prefs_input($name, $regex)
    {
        $rcmail = rcmail::get_instance();
        $value  = rcube_utils::get_input_value('_' . $name, rcube_utils::INPUT_POST);

        if (!is_string($value)) {
            $value = null;
        }

        if ($value !== null && strlen($value) && !preg_match($regex, $value)) {
            $value = $rcmail->config->get($name);
        }

        return $value;
    }

    /**
     * Get integer option value from POST
     */
    public static function prefs_input_int($name)
    {
        $rcmail = rcmail::get_instance();
        $value  = rcube_utils::get_input_value('_' . $name, rcube_utils::INPUT_POST);

        return (int) $value;
    }
}
