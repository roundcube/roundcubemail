<?php

/**
 * Subscription Options
 *
 * A plugin which can enable or disable the use of imap subscriptions.
 * It includes a toggle on the settings page under "Server Settings".
 * The preference can also be locked
 *
 * Add it to the plugins list in config.inc.php to enable the user option
 * The user option can be hidden and set globally by adding 'use_subscriptions'
 * to the 'dont_override' configure line:
 * $config['dont_override'] = ['use_subscriptions'];
 * and then set the global preference
 * $config['use_subscriptions'] = true; // or false
 *
 * Roundcube caches folder lists.  When a user changes this option or visits
 * their folder list, this cache is refreshed.  If the option is on the
 * 'dont_override' list and the global option has changed, don't expect
 * to see the change until the folder list cache is refreshed.
 *
 * @author Ziba Scott
 * @license GNU GPLv3+
 */
class subscriptions_option extends rcube_plugin
{
    public $task = 'mail|settings';

    /**
     * Plugin initialization
     */
    function init()
    {
        $dont_override = rcmail::get_instance()->config->get('dont_override', []);

        if (!in_array('use_subscriptions', $dont_override)) {
            $this->add_hook('preferences_list', [$this, 'prefs_list']);
            $this->add_hook('preferences_save', [$this, 'prefs_save']);
        }

        $this->add_hook('storage_folders', [$this, 'mailboxes_list']);
        $this->add_hook('folders_list', [$this, 'folders_list']);
    }

    /**
     * Hook to inject plugin-specific user settings
     *
     * @param array $args Hook arguments
     *
     * @return array Modified hook arguments
     */
    function prefs_list($args)
    {
        if ($args['section'] == 'server') {
            $this->add_texts('localization/', false);
            $use_subscriptions = rcmail::get_instance()->config->get('use_subscriptions', true);
            $field_id = 'rcmfd_use_subscriptions';
            $checkbox = new html_checkbox(['name' => '_use_subscriptions', 'id' => $field_id, 'value' => 1]);

            $args['blocks']['main']['options']['use_subscriptions'] = [
                'title'   => html::label($field_id, rcube::Q($this->gettext('useimapsubscriptions'))),
                'content' => $checkbox->show($use_subscriptions ? 1 : 0),
            ];
        }

        return $args;
    }

    /**
     * Hook to save plugin-specific user settings
     *
     * @param array $args Hook arguments
     *
     * @return array Modified hook arguments
     */
    function prefs_save($args)
    {
        if ($args['section'] == 'server') {
            $rcmail = rcmail::get_instance();
            $use_subscriptions = $rcmail->config->get('use_subscriptions', true);

            $args['prefs']['use_subscriptions'] = isset($_POST['_use_subscriptions']);

            // if the use_subscriptions preference changes, flush the folder cache
            if (
                ($use_subscriptions && !isset($_POST['_use_subscriptions']))
                || (!$use_subscriptions && isset($_POST['_use_subscriptions']))
            ) {
                $storage = $rcmail->get_storage();
                $storage->clear_cache('mailboxes');
            }
        }

        return $args;
    }

    function mailboxes_list($args)
    {
        $rcmail = rcmail::get_instance();

        if (!$rcmail->config->get('use_subscriptions', true)) {
            $storage = $rcmail->get_storage();

            if ($folders = $storage->list_folders_direct($args['root'], $args['name'])) {
                $folders = array_filter($folders, function($folder) use ($storage) {
                    $attrs = $storage->folder_attributes($folder);
                    return !in_array_nocase('\\Noselect', $attrs);
                });

                $args['folders'] = $folders;
            }
        }

        return $args;
    }

    function folders_list($args)
    {
        $rcmail = rcmail::get_instance();

        if (!$rcmail->config->get('use_subscriptions', true)) {
            foreach ($args['list'] as $idx => $data) {
                $args['list'][$idx]['content'] = preg_replace('/<input [^>]+>/', '', $data['content']);
            }
        }

        return $args;
    }
}
