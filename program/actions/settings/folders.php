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
 |   Provide functionality of folders listing                            |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_folders extends rcmail_action_settings_index
{
    protected static $mode = self::MODE_HTTP;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail  = rcmail::get_instance();
        $storage = $rcmail->get_storage();

        $rcmail->output->set_pagetitle($rcmail->gettext('folders'));
        $rcmail->output->set_env('prefix_ns', $storage->get_namespace('prefix'));
        $rcmail->output->set_env('quota', (bool) $storage->get_capability('QUOTA'));
        $rcmail->output->include_script('treelist.js');

        // add some labels to client
        $rcmail->output->add_label('deletefolderconfirm', 'purgefolderconfirm', 'movefolderconfirm',
            'folderdeleting', 'foldermoving', 'foldersubscribing', 'folderunsubscribing',
            'move', 'quota');

        // register UI objects
        $rcmail->output->add_handlers([
                'foldersubscription' => [$this, 'folder_subscriptions'],
                'folderfilter'       => [$this, 'folder_filter'],
                'quotadisplay'       => [$rcmail, 'quota_display'],
                'searchform'         => [$rcmail->output, 'search_form'],
        ]);

        $rcmail->output->send('folders');
    }

    // build table with all folders listed by server
    public static function folder_subscriptions($attrib)
    {
        $rcmail  = rcmail::get_instance();
        $storage = $rcmail->get_storage();

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmSubscriptionlist';
        }

        // get folders from server
        $storage->clear_cache('mailboxes', true);

        $a_unsubscribed  = $storage->list_folders();
        $a_subscribed    = $storage->list_folders_subscribed('', '*', null, null, true); // unsorted
        $delimiter       = $storage->get_hierarchy_delimiter();
        $namespace       = $storage->get_namespace();
        $special_folders = array_flip(array_merge(['inbox' => 'INBOX'], $storage->get_special_folders()));
        $protect_default = $rcmail->config->get('protect_default_folders');
        $seen            = [];
        $list_folders    = [];

        // pre-process folders list
        foreach ($a_unsubscribed as $i => $folder) {
            $folder_id     = $folder;
            $folder        = $storage->mod_folder($folder);
            $foldersplit   = explode($delimiter, $folder);
            $name          = rcube_charset::convert(array_pop($foldersplit), 'UTF7-IMAP');
            $is_special    = isset($special_folders[$folder_id]);
            $parent_folder = $is_special ? '' : join($delimiter, $foldersplit);
            $level         = $is_special ? 0 : count($foldersplit);

            // add any necessary "virtual" parent folders
            if ($parent_folder && empty($seen[$parent_folder])) {
                for ($i = 1; $i <= $level; $i++) {
                    $ancestor_folder = join($delimiter, array_slice($foldersplit, 0, $i));
                    if ($ancestor_folder) {
                        if (empty($seen[$ancestor_folder])) {
                            $seen[$ancestor_folder] = true;
                            $ancestor_name = rcube_charset::convert($foldersplit[$i-1], 'UTF7-IMAP');
                            $list_folders[] = [
                                'id'      => $ancestor_folder,
                                'name'    => $ancestor_name,
                                'level'   => $i-1,
                                'virtual' => true,
                            ];
                        }
                    }
                }
            }

            // Handle properly INBOX.INBOX situation
            if (isset($seen[$folder])) {
                continue;
            }

            $seen[$folder] = true;

            $list_folders[] = [
                'id'    => $folder_id,
                'name'  => $name,
                'level' => $level,
            ];
        }

        unset($seen);

        $checkbox_subscribe = new html_checkbox([
                'name'    => '_subscribed[]',
                'title'   => $rcmail->gettext('changesubscription'),
                'onclick' => rcmail_output::JS_OBJECT_NAME.".command(this.checked?'subscribe':'unsubscribe',this.value)",
        ]);

        $js_folders = [];
        $folders    = [];
        $collapsed  = (string) $rcmail->config->get('collapsed_folders');

        // create list of available folders
        foreach ($list_folders as $i => $folder) {
            $sub_key       = array_search($folder['id'], $a_subscribed);
            $is_subscribed = $sub_key !== false;
            $is_special    = isset($special_folders[$folder['id']]);
            $is_protected  = $folder['id'] == 'INBOX' || ($protect_default && $is_special);
            $noselect      = false;
            $classes       = [];

            $folder_utf8    = rcube_charset::convert($folder['id'], 'UTF7-IMAP');
            $display_folder = rcube::Q($is_special ? self::localize_foldername($folder['id'], false, true) : $folder['name']);

            if (!empty($folder['virtual'])) {
                $classes[] = 'virtual';
            }

            // Check \Noselect flag (of existing folder)
            if (!$is_protected && in_array($folder['id'], $a_unsubscribed)) {
                $attrs = $storage->folder_attributes($folder['id']);
                $noselect = in_array_nocase('\\Noselect', $attrs);
            }

            $is_disabled = (($is_protected && $is_subscribed) || $noselect);

            // Below we will disable subscription option for "virtual" folders
            // according to namespaces, but only if they aren't already subscribed.
            // User should be able to unsubscribe from the folder
            // even if it doesn't exists or is not accessible (OTRS:1000059)
            if (!$is_subscribed && !$is_disabled && !empty($namespace) && !empty($folder['virtual'])) {
                // check if the folder is a namespace prefix, then disable subscription option on it
                if (!$is_disabled && $folder['level'] == 0) {
                    $fname = $folder['id'] . $delimiter;
                    foreach ($namespace as $ns) {
                        if (is_array($ns)) {
                            foreach ($ns as $item) {
                                if ($item[0] === $fname) {
                                    $is_disabled = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }
                // check if the folder is an other users virtual-root folder, then disable subscription option on it
                if (!$is_disabled && $folder['level'] == 1 && !empty($namespace['other'])) {
                    $parts = explode($delimiter, $folder['id']);
                    $fname = $parts[0] . $delimiter;
                    foreach ($namespace['other'] as $item) {
                        if ($item[0] === $fname) {
                            $is_disabled = true;
                            break;
                        }
                    }
                }
                // check if the folder is shared, then disable subscription option on it (if not subscribed already)
                if (!$is_disabled) {
                    $tmp_ns = array_merge((array)$namespace['other'], (array)$namespace['shared']);
                    foreach ($tmp_ns as $item) {
                        if (strlen($item[0]) && strpos($folder['id'], $item[0]) === 0) {
                            $is_disabled = true;
                            break;
                        }
                    }
                }
            }

            $is_collapsed = strpos($collapsed, '&'.rawurlencode($folder['id']).'&') !== false;
            $folder_id    = rcube_utils::html_identifier($folder['id'], true);

            if ($folder_class = self::folder_classname($folder['id'])) {
                $classes[] = $folder_class;
            }

            $folders[$folder['id']] = [
                'idx'         => $folder_id,
                'folder_imap' => $folder['id'],
                'folder'      => $folder_utf8,
                'display'     => $display_folder,
                'protected'   => $is_protected || !empty($folder['virtual']),
                'class'       => join(' ', $classes),
                'subscribed'  => $is_subscribed,
                'level'       => $folder['level'],
                'collapsed'   => $is_collapsed,
                'content'     => html::a(['href' => '#'], $display_folder)
                    . $checkbox_subscribe->show(($is_subscribed ? $folder['id'] : ''),
                        ['value' => $folder['id'], 'disabled' => $is_disabled ? 'disabled' : ''])
            ];
        }

        $plugin = $rcmail->plugins->exec_hook('folders_list', ['list' => $folders]);

        // add drop-target representing 'root'
        $root = [
            'idx'         => rcube_utils::html_identifier('*', true),
            'folder_imap' => '*',
            'folder'      => '',
            'display'     => '',
            'protected'   => true,
            'class'       => 'root',
            'content'     => '<span>&nbsp;</span>',
        ];

        $folders        = [];
        $plugin['list'] = array_values($plugin['list']);

        array_unshift($plugin['list'], $root);

        for ($i = 0, $length = count($plugin['list']); $i<$length; $i++) {
            $folders[] = self::folder_tree_element($plugin['list'], $i, $js_folders);
        }

        $rcmail->output->add_gui_object('subscriptionlist', $attrib['id']);
        $rcmail->output->set_env('subscriptionrows', $js_folders);
        $rcmail->output->set_env('defaultfolders', array_keys($special_folders));
        $rcmail->output->set_env('collapsed_folders', $collapsed);
        $rcmail->output->set_env('delimiter', $delimiter);

        return html::tag('ul', $attrib, implode('', $folders), html::$common_attrib);
    }

    public static function folder_tree_element($folders, &$key, &$js_folders)
    {
        $data = $folders[$key];
        $idx  = 'rcmli' . $data['idx'];

        $js_folders[$data['folder_imap']] = [$data['folder'], $data['display'], $data['protected']];
        $content          = $data['content'];
        $attribs          = [
            'id'    => $idx,
            'class' => trim($data['class'] . ' mailbox')
        ];

        if (!isset($data['level'])) {
            $data['level'] = 0;
        }

        $children = [];
        while (!empty($folders[$key+1]) && ($folders[$key+1]['level'] > $data['level'])) {
            $key++;
            $children[] = self::folder_tree_element($folders, $key, $js_folders);
        }

        if (!empty($children)) {
            $content .= html::div('treetoggle ' . (!empty($data['collapsed']) ? 'collapsed' : 'expanded'), '&nbsp;')
                . html::tag('ul', ['style' => !empty($data['collapsed']) ? "display:none" : null],
                    implode("\n", $children));
        }

        return html::tag('li', $attribs, $content);
    }

    public static function folder_filter($attrib)
    {
        $rcmail    = rcmail::get_instance();
        $storage   = $rcmail->get_storage();
        $namespace = $storage->get_namespace();

        if (empty($namespace['personal']) && empty($namespace['shared']) && empty($namespace['other'])) {
            return '';
        }

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmfolderfilter';
        }

        if (!self::get_bool_attr($attrib, 'noevent')) {
            $attrib['onchange'] = rcmail_output::JS_OBJECT_NAME . '.folder_filter(this.value)';
        }

        $roots  = [];
        $select = new html_select($attrib);
        $select->add($rcmail->gettext('all'), '---');

        foreach (array_keys($namespace) as $type) {
            foreach ((array)$namespace[$type] as $ns) {
                $root  = rtrim($ns[0], $ns[1]);
                $label = $rcmail->gettext('namespace.' . $type);

                if (count($namespace[$type]) > 1) {
                    $label .= ' (' . rcube_charset::convert($root, 'UTF7-IMAP', RCUBE_CHARSET) . ')';
                }

                $select->add($label, $root);

                if (strlen($root)) {
                    $roots[] = $root;
                }
            }
        }

        $rcmail->output->add_gui_object('foldersfilter', $attrib['id']);
        $rcmail->output->set_env('ns_roots', $roots);

        return $select->show();
    }

    public static function folder_options($mailbox)
    {
        $rcmail  = rcmail::get_instance();
        $options = $rcmail->get_storage()->folder_info($mailbox);
        $options['protected'] = !empty($options['is_root'])
            || strtoupper($mailbox) === 'INBOX'
            || (!empty($options['special']) && $rcmail->config->get('protect_default_folders'));

        return $options;
    }

    /**
     * Updates (or creates) folder row in the subscriptions table
     *
     * @param string $name       Folder name
     * @param string $oldname    Old folder name (for update)
     * @param bool   $subscribe  Checks subscription checkbox
     * @param string $class_name CSS class name for folder row
     */
    public static function update_folder_row($name, $oldname = null, $subscribe = false, $class_name = null)
    {
        $rcmail      = rcmail::get_instance();
        $storage     = $rcmail->get_storage();
        $delimiter   = $storage->get_hierarchy_delimiter();
        $options     = self::folder_options($name);
        $name_utf8   = rcube_charset::convert($name, 'UTF7-IMAP');
        $foldersplit = explode($delimiter, $storage->mod_folder($name));
        $level       = count($foldersplit) - 1;
        $class_name  = trim($class_name . ' mailbox');

        if (!empty($options['protected'])) {
            $display_name = self::localize_foldername($name);
        }
        else {
            $display_name = rcube_charset::convert($foldersplit[$level], 'UTF7-IMAP');
        }

        $protected = !empty($options['protected']) || !empty($options['noselect']);

        if ($oldname === null) {
            $rcmail->output->command('add_folder_row', $name, $name_utf8, $display_name,
                $protected, $subscribe, $class_name);
        }
        else {
            $rcmail->output->command('replace_folder_row', $oldname, $name, $name_utf8, $display_name,
                $protected, $class_name);
        }
    }
}
