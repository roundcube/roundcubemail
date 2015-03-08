<?php
/*
 +-------------------------------------------------------------------------+
 | Enigma Plugin for Roundcube                                             |
 |                                                                         |
 | This program is free software; you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License version 2          |
 | as published by the Free Software Foundation.                           |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License along |
 | with this program; if not, write to the Free Software Foundation, Inc., |
 | 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.             |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+
*/

/*
    This class contains only hooks and action handlers.
    Most plugin logic is placed in enigma_engine and enigma_ui classes.
*/

class enigma extends rcube_plugin
{
    public $task = 'mail|settings';
    public $rc;
    public $engine;

    private $env_loaded  = false;


    /**
     * Plugin initialization.
     */
    function init()
    {
        $this->rc = rcube::get_instance();

        if ($this->rc->task == 'mail') {
            $section = rcube_utils::get_input_value('_section', rcube_utils::INPUT_GET);

            // message parse/display hooks
            $this->add_hook('message_part_structure', array($this, 'part_structure'));
            $this->add_hook('message_part_body', array($this, 'part_body'));
            $this->add_hook('message_body_prefix', array($this, 'status_message'));

            $this->register_action('plugin.enigmaimport', array($this, 'import_file'));

            // message displaying
            if ($this->rc->action == 'show' || $this->rc->action == 'preview') {
                $this->add_hook('message_load', array($this, 'message_load'));
                $this->add_hook('template_object_messagebody', array($this, 'message_output'));
            }
            // message composing
            else if ($this->rc->action == 'compose') {
                $this->load_ui();
                $this->ui->init($section);
            }
            // message sending (and draft storing)
            else if ($this->rc->action == 'sendmail') {
                //$this->add_hook('outgoing_message_body', array($this, 'msg_encode'));
                //$this->add_hook('outgoing_message_body', array($this, 'msg_sign'));
            }

            $this->password_handler();
        }
        else if ($this->rc->task == 'settings') {
            // add hooks for Enigma settings
            $this->add_hook('settings_actions', array($this, 'settings_actions'));
//            $this->add_hook('preferences_list', array($this, 'preferences_list'));
//            $this->add_hook('preferences_save', array($this, 'preferences_save'));

            // register handler for keys/certs management
//            $this->register_action('plugin.enigma', array($this, 'preferences_ui'));
            $this->register_action('plugin.enigmakeys', array($this, 'preferences_ui'));
            $this->register_action('plugin.enigmacerts', array($this, 'preferences_ui'));

            $this->load_ui();
            $this->ui->add_css();
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
     * Handler for preferences_list hook.
     * Adds options blocks into Enigma settings sections in Preferences.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function preferences_list($p)
    {
/*
        if ($p['section'] == 'enigmasettings') {
            // This makes that section is not removed from the list
            $p['blocks']['dummy']['options']['dummy'] = array();
        }
*/
        return $p;
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
/*
        if ($p['section'] == 'enigmasettings') {
            $a['prefs'] = array(
                'dummy' => rcube_utils::get_input_value('_dummy', rcube_utils::INPUT_POST),
            );
        }
*/
        return $p;
    }

    /**
     * Handler for keys/certs management UI template.
     */
    function preferences_ui()
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
        $this->load_engine();

        $this->engine->import_file();
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
     * Handler for refresh hook.
     */
    function refresh($p)
    {
        // calling enigma_engine constructor to remove passwords
        // stored in session after expiration time
        $this->load_engine();

        return $p;
    }
}
