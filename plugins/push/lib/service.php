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

require_once __DIR__ . '/cache.php';

class push_service extends rcube
{
    protected $server;
    protected $clients;
    protected $parser;
    protected $token;
    protected $debug = false;


    /**
     * This implements the 'singleton' design pattern
     *
     * @param int    $mode Unused
     * @param string $env  Unused
     *
     * @return kolab_sync The one and only instance
     */
    public static function get_instance($mode = 0, $env = '')
    {
        if (!self::$instance || !is_a(self::$instance, 'push_service')) {
            self::$instance = new push_service();
            self::$instance->startup();  // init AFTER object was linked with self::$instance
        }

        return self::$instance;
    }

    /**
     * Initialization of class instance
     */
    public function startup()
    {
        require_once __DIR__ . '/parser_notify.php';
        require_once __DIR__ . '/../push.php';

        // Use the plugin to load configuration from its config file
        $plugins = rcube_plugin_api::get_instance();
        $plugin = new push($plugins);
        $plugin->load_config();

        $host       = $this->config->get('push_service_host') ?: "0.0.0.0";
        $port       = $this->config->get('push_service_post') ?: 9501;
        $cache_size = $this->config->get('push_cache_size') ?: 1024;

        $this->debug   = $this->config->get('push_debug', false);
        $this->token   = $this->config->get('push_token');
        $this->clients = new push_cache($cache_size);
        $this->parser  = new push_parser_notify;

        // Setup the server
        $this->server = new swoole_websocket_server($host, $port); // TODO: SSL
        $this->server->set((array) $this->config->get('push_service_config'));
        $this->server->on('open', array($this, 'http_open'));
        $this->server->on('message', array($this, 'http_message'));
        $this->server->on('request', array($this, 'http_request'));
        $this->server->on('close', array($this, 'http_close'));

        $this->log_debug("S: Service start.");

        $this->server->start();
    }

    /**
     * Here we handle connections from websocket client
     */
    public function http_open(swoole_websocket_server $server, swoole_http_request $request)
    {
        $this->log_debug("[{$request->fd}] Handshake success");
    }

    /**
     * Here we close http/websocket connections
     */
    public function http_close(swoole_websocket_server $server, int $fd)
    {
        $this->log_debug("[{$fd}] Closed");

        // Remove connection entry from cache
        $this->clients->del_client($fd);
    }

    /**
     * Here we handle messages from websocket clients
     */
    public function http_message(swoole_websocket_server $server, swoole_websocket_frame $frame)
    {
        $data = json_decode($frame->data, true);

        $this->log_debug("[{$frame->fd}] Received message", $data);

        if ($data && $data['action'] == 'authenticate'
            && ($user = $this->authenticate($data['session'], $data['token']))
        ) {
            $this->log_debug("[{$frame->fd}] Registered session for " . $user['username']);
            $this->clients->add_client($frame->fd, $user['username'], $user);
        }
    }

    /**
     * Non-websocket request handler.
     * Here we handle Push notifications from external systems (IMAP servers).
     */
    public function http_request(swoole_http_request $request, swoole_http_response $response)
    {
        $this->log_debug("[{$request->fd}] HTTP {$request->server['request_method']} request");

        if ($request->server['request_method'] !== 'POST') {
            $response->status('404');
            $response->end();
            return;
        }

        // Check security token (if configured)
        if ($this->token) {
            $token = $request->header['x-token'];
            if (!$token && preg_match('/^Bearer (.+)/', $request->header['authorization'], $m)) {
                $token = $m[1];
            }

            if ($token !== $this->token) {
                $this->log_debug("[{$request->fd}] 401 Invalid token");
                $response->status('401');
                $response->end();
                return;
            }
        }

        // Send 200 response
        // $response->header("Content-Type", "text/plain; charset=utf-8");
        $response->end();

        $this->notification($request);
    }

    /**
     * Similar to rcube::console(), writes to logs/push if debug option is enabled
     */
    public function log_debug()
    {
        if ($this->debug) {
            $msg = array();
            foreach (func_get_args() as $arg) {
                $msg[] = !is_string($arg) ? var_export($arg, true) : $arg;
            }

            rcube::write_log('push', implode(";\n", $msg));
        }
    }

    /**
     * Handles incoming notification request
     */
    protected function notification($request)
    {
        // Read POST data, or JSON from POST body
        $data = $request->post;
        if (empty($data)) {
            $data = $request->rawcontent();
            if ($data && $data[0] == '{') {
                $data = json_decode($data, true);
            }
        }

        $this->log_debug("Received notification", $data);

        // Parse notification data (convert to internal format)
        $event = $this->parser->parse($data);

        if (!empty($event) && $event['folder_user']) {
            $user = $this->clients->get($event['folder_user']);

            if ($user) {
                $this->broadcast($user, $event);
            }

            // TODO: broadcast to old_folder_user
            // TODO: shared folders
        }
    }

    /**
     * Broadcast the message to all specified websocket clients
     */
    protected function broadcast($client, $event)
    {
        // remove null items
        $event = array_filter($event, function($v) { return !is_null($v); });

        foreach ((array) $client['sockets'] as $fd) {
            if ($json = json_encode($event)) {
                $this->log_debug("[$fd] Sending message to " . $client['username'], $json);
                $this->server->push($fd, $json);
            }
        }
    }

    /**
     * Websocket authentication.
     *
     * Checks if specified session ID and request token match
     * and returns user metadata from that session
     */
    protected function authenticate($session_id, $token)
    {
        if (!$token || !$session_id) {
            return;
        }

        $session = rcube_session::factory($this->config);

        if ($data = $session->read($session_id)) {
            $session = $session->unserialize($data);
            if ($session && $token === $session['request_token']) {
                return array(
                    'username' => $session['username'],
                    // 'password' => $session['password'],
                );
            }
        }
    }
}
