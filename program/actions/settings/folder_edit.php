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
 |   Provide functionality to edit a folder                              |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_folder_edit extends rcmail_action_settings_folders
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

        $rcmail->output->add_handlers([
                'folderdetails' => [$this, 'folder_form'],
        ]);

        $rcmail->output->add_label('nonamewarning');

        $rcmail->output->send('folderedit');
    }

    public static function folder_form($attrib)
    {
        // WARNING: folder names in UI are encoded with RCUBE_CHARSET
        $rcmail  = rcmail::get_instance();
        $storage = $rcmail->get_storage();

        // edited folder name (empty in create-folder mode)
        $mbox = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_GPC, true);

        // predefined path for new folder
        $parent = rcube_utils::get_input_string('_path', rcube_utils::INPUT_GPC, true);

        $threading_supported = $storage->get_capability('THREAD');
        $dual_use_supported  = $storage->get_capability(rcube_storage::DUAL_USE_FOLDERS);
        $delimiter           = $storage->get_hierarchy_delimiter();

        // Get mailbox parameters
        if (strlen($mbox)) {
            $options   = self::folder_options($mbox);
            $namespace = $storage->get_namespace();

            $path   = explode($delimiter, $mbox);
            $folder = array_pop($path);
            $path   = implode($delimiter, $path);
            $folder = rcube_charset::convert($folder, 'UTF7-IMAP');

            $hidden_fields = ['name' => '_mbox', 'value' => $mbox];
        }
        else {
            $options       = [];
            $path          = $parent;
            $folder        = '';
            $hidden_fields = [];

            // allow creating subfolders of INBOX folder
            if ($path == 'INBOX') {
                $path = $storage->mod_folder($path, 'in');
            }
        }

        // remove personal namespace prefix
        $path_id = null;
        if (strlen($path)) {
            $path_id = $path;
            $path    = $storage->mod_folder($path . $delimiter);
            if ($path[strlen($path)-1] == $delimiter) {
                $path = substr($path, 0, -1);
            }
        }

        $form = [];

        // General tab
        $form['props'] = [
            'name' => $rcmail->gettext('properties'),
        ];

        // Location (name)
        if (!empty($options['protected'])) {
            $foldername = str_replace($delimiter, ' &raquo; ', rcube::Q(self::localize_foldername($mbox, false, true)));
        }
        else if (!empty($options['norename'])) {
            $foldername = rcube::Q($folder);
        }
        else {
            if (isset($_POST['_name'])) {
                $folder = trim(rcube_utils::get_input_string('_name', rcube_utils::INPUT_POST, true));
            }

            $foldername = new html_inputfield(['name' => '_name', 'id' => '_name', 'size' => 30, 'class' => 'form-control']);
            $foldername = '<span class="input-group">' . $foldername->show($folder);

            if (!empty($options['special']) && ($sname = self::localize_foldername($mbox, false, true)) != $folder) {
                $foldername .= ' <span class="input-group-append"><span class="input-group-text">(' . rcube::Q($sname) .')</span></span>';
            }

            $foldername .= '</span>';
        }

        $form['props']['fieldsets']['location'] = [
            'name'  => $rcmail->gettext('location'),
            'content' => [
                'name' => [
                    'label' => $rcmail->gettext('foldername'),
                    'value' => $foldername,
                ],
            ],
        ];

        if (!empty($options) && (!empty($options['norename']) || !empty($options['protected']))) {
            // prevent user from moving folder
            $hidden_path = new html_hiddenfield(['name' => '_parent', 'value' => $path]);
            $form['props']['fieldsets']['location']['content']['name']['value'] .= $hidden_path->show();
        }
        else {
            $selected   = $_POST['_parent'] ?? $path_id;
            $exceptions = [$mbox];

            // Exclude 'prefix' namespace from parent folders list (#1488349)
            // If INBOX. namespace exists, folders created as INBOX subfolders
            // will be listed at the same level - selecting INBOX as a parent does nothing
            if ($prefix = $storage->get_namespace('prefix')) {
                $exceptions[] = substr($prefix, 0, -1);
            }

            $select = self::folder_selector([
                    'id'          => '_parent',
                    'name'        => '_parent',
                    'noselection' => '---',
                    'maxlength'   => 150,
                    'unsubscribed' => true,
                    'skip_noinferiors' => true,
                    'exceptions'  => $exceptions,
                    'additional'  => is_string($selected) && strlen($selected) ? [$selected] : null,
            ]);

            $form['props']['fieldsets']['location']['content']['parent'] = [
                'label' => $rcmail->gettext('parentfolder'),
                'value' => $select->show($selected),
            ];
        }

        // Settings
        $form['props']['fieldsets']['settings'] = [
            'name'  => $rcmail->gettext('settings'),
        ];

        // For servers that do not support both sub-folders and messages in a folder
        if (!$dual_use_supported) {
            if (!strlen($mbox)) {
                $select = new html_select(['name' => '_type', 'id' => '_type']);
                $select->add($rcmail->gettext('dualusemail'), 'mail');
                $select->add($rcmail->gettext('dualusefolder'), 'folder');

                $value = rcube_utils::get_input_string('_type', rcube_utils::INPUT_POST);
                $value = $select->show($value ?: 'mail');
            }
            else {
                $value = $options['noselect'] ? 'folder' : 'mail';
                $value = $rcmail->gettext('dualuse' . $value);
            }

            $form['props']['fieldsets']['settings']['content']['type'] = [
                'label' => $rcmail->gettext('dualuselabel'),
                'value' => $value,
            ];
        }

        // Settings: threading
        if ($threading_supported && ($mbox == 'INBOX' || (empty($options['noselect']) && empty($options['is_root'])))) {
            $value  = 0;
            $select = new html_select(['name' => '_viewmode', 'id' => '_viewmode']);

            $select->add($rcmail->gettext('list'), 0);
            $select->add($rcmail->gettext('threads'), 1);

            if (isset($_POST['_viewmode'])) {
                $value = (int) $_POST['_viewmode'];
            }
            else if (strlen($mbox)) {
                $a_threaded   = $rcmail->config->get('message_threading', []);
                $default_mode = $rcmail->config->get('default_list_mode', 'list');

                $value = (int) ($a_threaded[$mbox] ?? $default_mode == 'threads');
            }

            $form['props']['fieldsets']['settings']['content']['viewmode'] = [
                'label' => $rcmail->gettext('listmode'),
                'value' => $select->show($value),
            ];
        }

        $msgcount = 0;

        // Information (count, size) - Edit mode
        if (strlen($mbox)) {
            // Number of messages
            $form['props']['fieldsets']['info'] = [
                'name'    => $rcmail->gettext('info'),
                'content' => []
            ];

            if ((!$options['noselect'] && !$options['is_root']) || $mbox == 'INBOX') {
                $msgcount = (int) $storage->count($mbox, 'ALL', true, false);

                if ($msgcount) {
                    // Get the size on servers with supposed-to-be-fast method for that
                    if ($storage->get_capability('STATUS=SIZE')) {
                        $size = $storage->folder_size($mbox);
                        if ($size !== false) {
                            $size = self::show_bytes($size);
                        }
                    }

                    // create link with folder-size command
                    if (!isset($size) || $size === false) {
                        $onclick = sprintf("return %s.command('folder-size', '%s', this)",
                            rcmail_output::JS_OBJECT_NAME, rcube::JQ($mbox));

                        $attr = ['href' => '#', 'onclick' => $onclick, 'id' => 'folder-size'];
                        $size = html::a($attr, $rcmail->gettext('getfoldersize'));
                    }
                }
                else {
                    // no messages -> zero size
                    $size = 0;
                }

                $form['props']['fieldsets']['info']['content']['count'] = [
                    'label' => $rcmail->gettext('messagecount'),
                    'value' => $msgcount
                ];
                $form['props']['fieldsets']['info']['content']['size'] = [
                    'label' => $rcmail->gettext('size'),
                    'value' => $size,
                ];
            }

            // show folder type only if we have non-private namespaces
            if (!empty($namespace['shared']) || !empty($namespace['others'])) {
                $form['props']['fieldsets']['info']['content']['foldertype'] = [
                    'label' => $rcmail->gettext('foldertype'),
                    'value' => $rcmail->gettext($options['namespace'] . 'folder')
                ];
            }
        }

        // Allow plugins to modify folder form content
        $plugin = $rcmail->plugins->exec_hook('folder_form', [
                'form'        => $form,
                'options'     => $options,
                'name'        => $mbox,
                'parent_name' => $parent
        ]);

        $form = $plugin['form'];

        // Set form tags and hidden fields
        list($form_start, $form_end) = self::get_form_tags($attrib, 'save-folder', null, $hidden_fields);

        unset($attrib['form'], $attrib['id']);

        // return the complete edit form as table
        $out = "$form_start\n";

        // Create form output
        foreach ($form as $idx => $tab) {
            if (!empty($tab['fieldsets']) && is_array($tab['fieldsets'])) {
                $content = '';
                foreach ($tab['fieldsets'] as $fieldset) {
                    $subcontent = self::get_form_part($fieldset, $attrib);
                    if ($subcontent) {
                        $subcontent = html::tag('legend', null, rcube::Q($fieldset['name'])) . $subcontent;
                        $content .= html::tag('fieldset', null, $subcontent) ."\n";
                    }
                }
            }
            else {
                $content = self::get_form_part($tab, $attrib);
            }

            if ($idx != 'props') {
                $out .= html::tag('fieldset', null, html::tag('legend', null, rcube::Q($tab['name'])) . $content) ."\n";
            }
            else {
                $out .= $content ."\n";
            }
        }

        $out .= "\n$form_end";

        $rcmail->output->set_env('messagecount', $msgcount);
        $rcmail->output->set_env('folder', $mbox);

        if ($mbox !== null && empty($_POST)) {
            $rcmail->output->command('parent.set_quota', self::quota_content(null, $mbox));
        }

        return $out;
    }

    public static function get_form_part($form, $attrib = [])
    {
        $rcmail  = rcmail::get_instance();
        $content = '';

        if (!empty($form['content']) && is_array($form['content'])) {
            $table = new html_table(['cols' => 2]);

            foreach ($form['content'] as $col => $colprop) {
                $colprop['id'] = '_' . $col;
                $label = !empty($colprop['label']) ? $colprop['label'] : $rcmail->gettext($col);

                $table->add('title', html::label($colprop['id'], rcube::Q($label)));
                $table->add(null, $colprop['value']);
            }

            $content = $table->show($attrib);
        }
        else if (isset($form['content'])) {
            $content = $form['content'];
        }

        return $content;
    }
}
