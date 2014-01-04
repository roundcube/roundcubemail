<?php

/**
 * Managesieve (Sieve Filters)
 *
 * Plugin that adds a possibility to manage Sieve filters in Thunderbird's style.
 * It's clickable interface which operates on text scripts and communicates
 * with server using managesieve protocol. Adds Filters tab in Settings.
 *
 * @version @package_version@
 * @author Aleksander Machniak <alec@alec.pl>
 *
 * Configuration (see config.inc.php.dist)
 *
 * Copyright (C) 2008-2013, The Roundcube Dev Team
 * Copyright (C) 2011-2013, Kolab Systems AG
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

class managesieve extends rcube_plugin
{
    public $task = 'mail|settings';
    private $rc;
    private $engine;

    function init()
    {
        $this->rc = rcmail::get_instance();

        // register actions
        $this->register_action('plugin.managesieve', array($this, 'managesieve_actions'));
        $this->register_action('plugin.managesieve-action', array($this, 'managesieve_actions'));
        $this->register_action('plugin.managesieve-save', array($this, 'managesieve_save'));

        if ($this->rc->task == 'settings') {
            $this->add_hook('settings_actions', array($this, 'settings_actions'));
            $this->init_ui();
        }
        else if ($this->rc->task == 'mail') {
            // register message hook
            $this->add_hook('message_headers_output', array($this, 'mail_headers'));

            // inject Create Filter popup stuff
            if (empty($this->rc->action) || $this->rc->action == 'show') {
                $this->mail_task_handler();
            }
        }
    }

    /**
     * Initializes plugin's UI (localization, js script)
     */
    function init_ui()
    {
        if ($this->ui_initialized) {
            return;
        }

        // load localization
        $this->add_texts('localization/', array('filters','managefilters'));
        $this->include_script('managesieve.js');

        $this->ui_initialized = true;
    }

    /**
     * Adds Filters section in Settings
     */
    function settings_actions($args)
    {
        // register as settings action
        $args['actions'][] = array('action' => 'plugin.managesieve', 'class' => 'filter', 'label' => 'filters', 'domain' => 'managesieve');
        return $args;
    }

    /**
     * Add UI elements to the 'mailbox view' and 'show message' UI.
     */
    function mail_task_handler()
    {
        // make sure we're not in ajax request
        if ($this->rc->output->type != 'html') {
            return;
        }

        // use jQuery for popup window
        $this->require_plugin('jqueryui');

        // include js script and localization
        $this->init_ui();

        // include styles
        $skin_path = $this->local_skin_path();
        if (is_file($this->home . "/$skin_path/managesieve_mail.css")) {
            $this->include_stylesheet("$skin_path/managesieve_mail.css");
        }

        // add 'Create filter' item to message menu
        $this->api->add_content(html::tag('li', null, 
            $this->api->output->button(array(
                'command'  => 'managesieve-create',
                'label'    => 'managesieve.filtercreate',
                'type'     => 'link',
                'classact' => 'icon filterlink active',
                'class'    => 'icon filterlink',
                'innerclass' => 'icon filterlink',
            ))), 'messagemenu');

        // register some labels/messages
        $this->rc->output->add_label('managesieve.newfilter', 'managesieve.usedata',
            'managesieve.nodata', 'managesieve.nextstep', 'save');

        $this->rc->session->remove('managesieve_current');
    }

    /**
     * Get message headers for popup window
     */
    function mail_headers($args)
    {
        // this hook can be executed many times
        if ($this->mail_headers_done) {
            return $args;
        }

        $this->mail_headers_done = true;

        $headers = $args['headers'];
        $ret     = array();

        if ($headers->subject)
            $ret[] = array('Subject', rcube_mime::decode_header($headers->subject));

        // @TODO: List-Id, others?
        foreach (array('From', 'To') as $h) {
            $hl = strtolower($h);
            if ($headers->$hl) {
                $list = rcube_mime::decode_address_list($headers->$hl);
                foreach ($list as $item) {
                    if ($item['mailto']) {
                        $ret[] = array($h, $item['mailto']);
                    }
                }
            }
        }

        if ($this->rc->action == 'preview')
            $this->rc->output->command('parent.set_env', array('sieve_headers' => $ret));
        else
            $this->rc->output->set_env('sieve_headers', $ret);


        return $args;
    }

    /**
     * Plugin action handler
     */
    function managesieve_actions()
    {
        $this->init_ui();
        $engine = $this->get_engine();
        $engine->actions();
    }

    /**
     * Forms save action handler
     */
    function managesieve_save()
    {
        // load localization
        $this->add_texts('localization/', array('filters','managefilters'));

        // include main js script
        if ($this->api->output->type == 'html') {
            $this->include_script('managesieve.js');
        }

        $engine = $this->get_engine();
        $engine->save();
    }

    /**
     * Initializes engine object
     */
    private function get_engine()
    {
        if (!$this->engine) {
            $this->load_config();

            // Add include path for internal classes
            $include_path = $this->home . '/lib' . PATH_SEPARATOR;
            $include_path .= ini_get('include_path');
            set_include_path($include_path);

            $this->engine = new rcube_sieve_engine($this);
        }

        return $this->engine;
    }
}
