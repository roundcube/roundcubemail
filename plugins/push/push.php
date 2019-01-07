<?php

/**
 * Push aka Instant Updates
 *
 * @author Aleksander Machniak <alec@alec.pl>
 *
 * Copyright (C) 2010-2019 The Roundcube Dev Team
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
class push extends rcube_plugin
{
    public $task = 'mail';
    public $rc;


    /**
     * Plugin initialization
     */
    public function init()
    {
        $this->rc = rcube::get_instance();

        $this->add_hook('ready', array($this, 'ready'));
    }

    /**
     * Startup hook handler
     */
    public function ready($args)
    {
        if ($this->rc->output->type == 'html' && !$this->rc->action) {
            $this->load_config();

            $debug = (bool) $this->rc->config->get('push_debug');
            $port  = $this->rc->config->get('push_service_port', 9501);
            $url   = $this->rc->config->get('push_url') ?: 'wss://%n:%p';
            $url   = rcube_utils::parse_host($url);
            $url   = str_replace('%p', $port, $url);

            $this->rc->output->set_env('push_url', $url);
            $this->rc->output->set_env('push_debug', $debug);
            $this->rc->output->set_env('sessid', session_id());

            $this->include_script('push.js');
        }

        return $args;
    }
}
