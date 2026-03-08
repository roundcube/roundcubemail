<?php

/*
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
 |   Roundcube health checker                                            |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <machniak@apheleia-it.ch>                 |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to run Roundcube health checks in CLI
 */
class rcmail_healthchecker
{
    private $args = [];

    public function __construct(array $args = [])
    {
        $this->args = $args;
    }

    /**
     * Do the checking
     */
    public function run()
    {
        $rcmail = rcmail::get_instance();

        // TODO: LDAP, memcache, spellchecker
        $checks = [
            'DB' => [$this, 'check_db'],
            'IMAP' => [$this, 'check_imap'],
            'SMTP' => [$this, 'check_smtp'],
        ];

        if (!empty($rcmail->config->get('redis_hosts'))) {
            $checks['Redis'] = [$this, 'check_redis'];
        }

        // Let plugins inject their own checks e.g. managesieve, password
        $plugin = $rcmail->plugins->exec_hook('health_check', ['checks' => $checks]);
        $status = true;

        foreach ($plugin['checks'] as $key => $callback) {
            if (is_callable($callback)) {
                $result = $callback($this->args);
                $state = !empty($result);
                $message = '';

                if (is_array($result)) {
                    [$state, $message] = $result;
                }

                echo $key . ': '
                    . ($state ? $this->color_success('OK') : $this->color_error('NOT OK'))
                    . ($message ? " ({$message})" : '') . \PHP_EOL;

                if (!$state) {
                    $status = false;
                }

                if ($key == 'IMAP' && !empty($this->args['host'])) {
                    // Set some vars than are used to resolve hostname in various config options
                    $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = $_SESSION['storage_host'] = $this->args['host'];
                }
            }
        }

        return (int) $status;
    }

    /**
     * Check SQL database connection
     *
     * @param array $args Checker input arguments (user, pass, host)
     */
    public function check_db(array $args = [])
    {
        $db = rcmail_utils::db();

        return in_array($db->table_name('system'), (array) $db->list_tables());
    }

    /**
     * Check IMAP connection
     *
     * @param array $args Checker input arguments (user, pass, host)
     */
    public function check_imap(array $args = [])
    {
        $rcmail = rcmail::get_instance();

        $hosts = $rcmail->config->get('imap_host');
        if (is_string($hosts)) {
            $host = $hosts;
        } elseif (is_array($hosts) && count($hosts) == 1) {
            $host = array_first($hosts);
        } elseif (!empty($args['host'])) {
            $host = $args['host'];
        } else {
            return [false, 'IMAP host unknown'];
        }

        $_host = rcube_utils::parse_host($host);
        [$host, $scheme, $port] = rcube_utils::parse_host_uri($_host, 143, 993);
        $ssl = in_array($scheme, ['ssl', 'imaps', 'tls']) ? $scheme : false;

        $storage = $rcmail->get_storage();

        // If user/pass is not specified we'll do just the connection check, no authentication
        if (empty($args['pass']) || empty($args['user'])) {
            $storage->set_options(['auth_type' => 'NONE']);
        }

        $result = (bool) $storage->connect($host, $args['user'] ?? '', $args['pass'] ?? '', $port, $ssl);

        $_host = preg_replace('/:[0-9]+$/', '', $_host) . ":{$port}";

        if (!$result) {
            $result = [false, "Failed to connect to {$_host}: " . $storage->get_error_str()];
        }

        return [$result, $_host];
    }

    /**
     * Check Redis connection
     *
     * @param array $args Checker input arguments (user, pass, host)
     */
    public function check_redis(array $args = [])
    {
        $rcmail = rcmail::get_instance();
        $redis = $rcmail->get_redis();

        $hosts = (array) $rcmail->config->get('redis_hosts');

        // Remove passwords if included
        $hosts = array_map(
            function ($host) {
                $items = explode(':', $host);
                if (count($items) > 3) {
                    unset($items[3]);
                    return implode(':', $items);
                }
                return $host;
            },
            $hosts
        );

        $hosts = implode(', ', $hosts);

        if (!$redis) {
            return [false, "Failed to connect to {$hosts}"];
        }

        return [true, $hosts];
    }

    /**
     * Check SMTP connection
     *
     * @param array $args Checker input arguments (user, pass, host)
     */
    public function check_smtp(array $args = [])
    {
        $smtp = new rcube_smtp();

        $result = $smtp->connect(null, null, $args['user'] ?? '', $args['pass'] ?? '');

        if (!$result) {
            return [false, 'Failed to connect to ' . $smtp->get_host()];
        }

        return [$result, $smtp->get_host()];
    }

    private function color_success($text): string
    {
        return "\033[0;32m{$text}\033[0m";
    }

    private function color_error($text): string
    {
        return "\033[0;31m{$text}\033[0m";
    }
}
