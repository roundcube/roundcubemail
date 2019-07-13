<?php

/**
 +-------------------------------------------------------------------------+
 | Enigma Plugin for Roundcube                                             |
 |                                                                         |
 | Copyright (C) The Roundcube Dev Team                                    |
 |                                                                         |
 | Licensed under the GNU General Public License version 3 or              |
 | any later version with exceptions for skins & plugins.                  |
 | See the README file for a full license statement.                       |
 +-------------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+
*/

/**
 * This class contains only hooks and action handlers.
 * Most plugin logic is placed in enigma_engine and enigma_ui classes.
 */
class enigma extends rcube_plugin
{
    public $task = 'mail|settings|cli';
    public $rc;
    public $engine;

    private $ui;
    private $settings_ui;
    private $env_loaded = false;


    /**
     * Plugin initialization.
     */
    function init()
    {
        $this->rc = rcube::get_instance();

        if ($this->rc->task == 'mail') {
            // message parse/display hooks
            $this->add_hook('message_part_structure', array($this, 'part_structure'));
            $this->add_hook('message_part_body', array($this, 'part_body'));
            $this->add_hook('message_body_prefix', array($this, 'status_message'));

            $this->register_action('plugin.enigmaimport', array($this, 'import_file'));
            $this->register_action('plugin.enigmakeys', array($this, 'keys_ui'));

            // load the Enigma plugin configuration
            $this->load_config();

            $enabled = $this->rc->config->get('enigma_encryption', true);

            // message displaying
            if ($this->rc->action == 'show' || $this->rc->action == 'preview' || $this->rc->action == 'print') {
                $this->add_hook('message_load', array($this, 'message_load'));
                $this->add_hook('template_object_messagebody', array($this, 'message_output'));
            }
            // message composing
            else if ($enabled && $this->rc->action == 'compose') {
                $this->add_hook('message_compose_body', array($this, 'message_compose'));

                $this->load_ui();
                $this->ui->init();
            }
            // message sending (and draft storing)
            else if ($enabled && $this->rc->action == 'send') {
                $this->add_hook('message_ready', array($this, 'message_ready'));
            }

            $this->password_handler();
        }
        else if ($this->rc->task == 'settings') {
            // add hooks for Enigma settings
            $this->add_hook('settings_actions', array($this, 'settings_actions'));
            $this->add_hook('preferences_sections_list', array($this, 'preferences_sections_list'));
            $this->add_hook('preferences_list', array($this, 'preferences_list'));
            $this->add_hook('preferences_save', array($this, 'preferences_save'));
            $this->add_hook('identity_form', array($this, 'identity_form'));

            // register handler for secure background style update
            $this->register_action('plugin.enigmabg', array($this, 'background_style'));

            // register handler for keys/certs management
            $this->register_action('plugin.enigmakeys', array($this, 'keys_ui'));
//            $this->register_action('plugin.enigmacerts', array($this, 'keys_ui'));

            $this->load_ui();

            if (empty($_REQUEST['_framed']) || strpos($this->rc->action, 'plugin.enigma') === 0
                || $this->rc->action == 'edit-prefs' || $this->rc->action == 'save-prefs'
            ) {
                $this->ui->add_css();
            }

            if ($this->rc->action == 'edit-prefs' || $this->rc->action == 'save-prefs') {
                $this->ui->add_js();
            }

            $this->password_handler();
        }
        else if ($this->rc->task == 'cli') {
            $this->add_hook('user_delete_commit', array($this, 'user_delete'));
        }

        $this->add_hook('refresh', array($this, 'refresh'));
    }

    /**
     * Plugin environment initialization.
     */
    function load_env()
    {
        if ($this->env_loaded) {
            return;
        }

        $this->env_loaded = true;

        // Add include path for Enigma classes and drivers
        $include_path = $this->home . '/lib' . PATH_SEPARATOR;
        $include_path .= ini_get('include_path');
        set_include_path($include_path);

        // load the Enigma plugin configuration
        $this->load_config();

        // include localization (if wasn't included before)
        $this->add_texts('localization/');
    }

    /**
     * Plugin UI initialization.
     */
    function load_ui($all = false)
    {
        if (!$this->ui) {
            // load config/localization
            $this->load_env();

            // Load UI
            $this->ui = new enigma_ui($this, $this->home);
        }

        if ($all) {
            $this->ui->add_css();
            $this->ui->add_js();
        }
    }

    /**
     * Plugin Settings UI initialization.
     */
    function load_settings_ui()
    {
        if (!$this->settings_ui) {
            $this->load_ui();
            $this->settings_ui = new enigma_settings($this);
        }
    }

    /**
     * Plugin engine initialization.
     */
    function load_engine()
    {
        if ($this->engine) {
            return $this->engine;
        }

        // load config/localization
        $this->load_env();

        return $this->engine = new enigma_engine($this);
    }

    /**
     * Handler for message_part_structure hook.
     * Called for every part of the message.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function part_structure($p)
    {
        $this->load_engine();

        return $this->engine->part_structure($p);
    }

    /**
     * Handler for message_part_body hook.
     * Called to get body of a message part.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function part_body($p)
    {
        $this->load_engine();

        return $this->engine->part_body($p);
    }

    /**
     * Handler for keys/certs management UI template.
     */
    function keys_ui()
    {
        $this->load_ui();

        $this->ui->init();
    }

    /**
     * Handler for message_body_prefix hook.
     * Called for every displayed (content) part of the message.
     * Adds infobox about signature verification and/or decryption
     * status above the body.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function status_message($p)
    {
        $this->load_ui();

        return $this->ui->status_message($p);
    }

    /**
     * Handler for message_load hook.
     * Check message bodies and attachments for keys/certs.
     */
    function message_load($p)
    {
        $this->load_ui();

        return $this->ui->message_load($p);
    }

    /**
     * Handler for template_object_messagebody hook.
     * This callback function adds a box below the message content
     * if there is a key/cert attachment available
     */
    function message_output($p)
    {
        $this->load_ui();

        return $this->ui->message_output($p);
    }

    /**
     * Handler for attached keys/certs import
     */
    function import_file()
    {
        $this->load_ui();

        $this->ui->import_file();
    }

    /**
     * Handle password submissions
     */
    function password_handler()
    {
        $this->load_engine();

        $this->engine->password_handler();
    }

    /**
     * Handle message_ready hook (encryption/signing)
     */
    function message_ready($p)
    {
        $this->load_ui();

        return $this->ui->message_ready($p);
    }

    /**
     * Handle message_compose_body hook
     */
    function message_compose($p)
    {
        $this->load_ui();

        return $this->ui->message_compose($p);
    }

    /**
     * Handler for refresh hook.
     */
    function refresh($p)
    {
        // calling enigma_engine constructor to remove passwords
        // stored in session after expiration time
        $this->load_engine();

        return $p;
    }

    /**
     * Handle delete_user_commit hook
     */
    function user_delete($p)
    {
        $this->load_engine();

        $p['abort'] = $p['abort'] || !$this->engine->delete_user_data($p['username']);

        return $p;
    }

    /**
     * Handler for settings_actions hook.
     * Adds Enigma settings section into preferences.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function settings_actions($args)
    {
        // add labels
        $this->add_texts('localization/');

        // register as settings action
        $args['actions'][] = array(
            'action' => 'plugin.enigmakeys',
            'class'  => 'enigma keys',
            'label'  => 'enigmakeys',
            'title'  => 'enigmakeys',
            'domain' => 'enigma',
        );
/*
        $args['actions'][] = array(
            'action' => 'plugin.enigmacerts',
            'class'  => 'enigma certs',
            'label'  => 'enigmacerts',
            'title'  => 'enigmacerts',
            'domain' => 'enigma',
        );
*/
        return $args;
    }

    /**
     * Handler for preferences_sections_list hook.
     * Adds Encryption settings section into preferences sections list.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function preferences_sections_list($p)
    {
        $p['list']['enigma'] = array(
            'id' => 'enigma', 'section' => $this->gettext('encryption'),
        );

        return $p;
    }

    /**
     * Handler for preferences_list hook.
     * Adds options blocks into Enigma settings sections in Preferences.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function preferences_list($p)
    {
        if ($p['section'] == 'enigma') {
            $this->load_settings_ui();

            return $this->settings_ui->preferences_list($p);
        }
    }

    /**
     * Handler for preferences_save hook.
     * Executed on Enigma settings form submit.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function preferences_save($p)
    {
        if ($p['section'] == 'enigma') {
            $this->load_settings_ui();

            return $this->settings_ui->preferences_save($p);
        }
    }

    /**
     * Handler for background testing action
     */
    function background_style()
    {
        $this->load_env();

        $this->rc->output->command('enigma_bg_update', enigma_settings::security_bg_style(true));
        $this->rc->output->send();
    }

    /**
     * Handler for 'identity_form' plugin hook.
     *
     * This will list private keys matching this identity
     * and add a link to enigma key management action.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function identity_form($p)
    {
        if (isset($p['form']['encryption']) && !empty($p['record']['identity_id'])) {
            $content = '';

            // find private keys for this identity
            if ($p['record']['email']) {
                $listing = array();
                $engine  = $this->load_engine();
                $keys    = (array) $engine->list_keys($p['record']['email']);

                foreach ($keys as $key) {
                    if ($key->get_type() === enigma_key::TYPE_KEYPAIR) {
                        $listing[] = html::tag('li', null,
                            html::tag('strong', 'uid', html::quote($key->id))
                            . ' ' . html::tag('span', 'identity', html::quote($key->name))
                        );
                    }
                }

                if (count($listing)) {
                    $content .= html::p(null, $this->gettext(array('name' => 'identitymatchingprivkeys', 'vars' => array('nr' => count($listing)))));
                    $content .= html::tag('ul', 'keylist', join('\n', $listing));
                }
                else {
                    $content .= html::p(null, $this->gettext('identitynoprivkeys'));
                }
            }

            // add button linking to enigma key management
            $button_attr = array(
                'class'  => 'button',
                'href'   => $this->rc->url(array('action' => 'plugin.enigmakeys')),
                'target' => '_parent',
            );
            $content .= html::p(null, html::a($button_attr, $this->gettext('managekeys')));

            // rename class to avoid Mailvelope key management to kick in
            $p['form']['encryption']['attrs'] = array('class' => 'enigma-identity-encryption');
            // fill fieldset content with our stuff
            $p['form']['encryption']['content'] = html::div('identity-encryption-block', $content);
        }

        return $p;
    }
}
