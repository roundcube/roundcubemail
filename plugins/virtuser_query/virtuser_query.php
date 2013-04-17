<?php

/**
 * DB based User-to-Email and Email-to-User lookup
 *
 * Add it to the plugins list in config/main.inc.php and set
 * SQL queries to resolve usernames, e-mail addresses and hostnames from the database
 * %u will be replaced with the current username for login.
 * %m will be replaced with the current e-mail address for login.
 *
 * Queries should select the user's e-mail address, username or the imap hostname as first column
 * The email query could optionally select identity data columns in specified order:
 *    name, organization, reply-to, bcc, signature, html_signature
 *
 * $rcmail_config['virtuser_query'] = array('email' => '', 'user' => '', 'host' => '');
 *
 * The email query can return more than one record to create more identities.
 * This requires identities_level option to be set to value less than 2.
 *
 * @version @package_version@
 * @author Aleksander Machniak <alec@alec.pl>
 * @author Steffen Vogel
 */
class virtuser_query extends rcube_plugin
{
    private $config;
    private $app;

    function init()
    {
        $this->app = rcmail::get_instance();
        $this->config = $this->app->config->get('virtuser_query');

        if (!empty($this->config)) {
            if (is_string($this->config)) {
                $this->config = array('email' => $this->config);
            }

            if ($this->config['email']) {
                $this->add_hook('user2email', array($this, 'user2email'));
            }
            if ($this->config['user']) {
                $this->add_hook('email2user', array($this, 'email2user'));
            }
            if ($this->config['host']) {
                $this->add_hook('authenticate', array($this, 'user2host'));
            }
        }
    }

    /**
     * User > Email
     */
    function user2email($p)
    {
        $dbh = $this->app->get_dbh();

        $sql_result = $dbh->query(preg_replace('/%u/', $dbh->escape($p['user']), $this->config['email']));

        while ($sql_arr = $dbh->fetch_array($sql_result)) {
            if (strpos($sql_arr[0], '@')) {
                if ($p['extended'] && count($sql_arr) > 1) {
                    $result[] = array(
                        'email'         => rcube_idn_to_ascii($sql_arr[0]),
                        'name'          => $sql_arr[1],
                        'organization'  => $sql_arr[2],
                        'reply-to'      => rcube_idn_to_ascii($sql_arr[3]),
                        'bcc'           => rcube_idn_to_ascii($sql_arr[4]),
                        'signature'     => $sql_arr[5],
                        'html_signature' => (int)$sql_arr[6],
                    );
                }
                else {
                    $result[] = $sql_arr[0];
                }

                if ($p['first']) {
                    break;
                }
            }
        }

        $p['email'] = $result;

        return $p;
    }

    /**
     * EMail > User
     */
    function email2user($p)
    {
        $dbh = $this->app->get_dbh();

        $sql_result = $dbh->query(preg_replace('/%m/', $dbh->escape($p['email']), $this->config['user']));

        if ($sql_arr = $dbh->fetch_array($sql_result)) {
            $p['user'] = $sql_arr[0];
        }

        return $p;
    }

    /**
     * User > Host
     */
    function user2host($p)
    {
        $dbh = $this->app->get_dbh();

        $sql_result = $dbh->query(preg_replace('/%u/', $dbh->escape($p['user']), $this->config['host']));

        if ($sql_arr = $dbh->fetch_array($sql_result)) {
            $p['host'] = $sql_arr[0];
        }

        return $p;
    }

}

