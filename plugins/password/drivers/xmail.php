<?php
/**
 * XMail Password Driver
 *
 * Driver for XMail password
 *
 * @version 2.0
 * @author Helio Cavichiolo Jr <helio@hcsistemas.com.br>
 *
 * Setup xmail_host, xmail_user, xmail_pass and xmail_port into
 * config.inc.php of password plugin as follows:
 *
 * $rcmail_config['xmail_host'] = 'localhost';
 * $rcmail_config['xmail_user'] = 'YourXmailControlUser';
 * $rcmail_config['xmail_pass'] = 'YourXmailControlPass';
 * $rcmail_config['xmail_port'] = 6017;
 *
 */

class rcube_xmail_password
{
    function save($currpass, $newpass)
    {
        $rcmail = rcmail::get_instance();
        list($user,$domain) = explode('@', $_SESSION['username']);

        $xmail = new XMail;

        $xmail->hostname = $rcmail->config->get('xmail_host');
        $xmail->username = $rcmail->config->get('xmail_user');
        $xmail->password = $rcmail->config->get('xmail_pass');
        $xmail->port = $rcmail->config->get('xmail_port');

        if (!$xmail->connect()) {
            raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to connect to mail server"
            ), true, false);
            return PASSWORD_CONNECT_ERROR;
        }
        else if (!$xmail->send("userpasswd\t".$domain."\t".$user."\t".$newpass."\n")) {
            $xmail->close();
            raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to change password"
            ), true, false);
            return PASSWORD_ERROR;
        }
        else {
            $xmail->close();
            return PASSWORD_SUCCESS;
        }
    }
}

class XMail {
    var $socket;
    var $hostname = 'localhost';
    var $username = 'xmail';
    var $password = '';
    var $port = 6017;

    function send($msg)
    {
        socket_write($this->socket,$msg);
        if (substr($in = socket_read($this->socket, 512, PHP_BINARY_READ),0,1) != "+") {
            return false;
        }
        return true;
    }

    function connect()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
        if ($this->socket < 0)
            return false;

        $result = socket_connect($this->socket, $this->hostname, $this->port);
        if ($result < 0) {
            socket_close($this->socket);
            return false;
        }

        if (substr($in = socket_read($this->socket, 512, PHP_BINARY_READ),0,1) != "+") {
            socket_close($this->socket);
            return false;
        }

        if (!$this->send("$this->username\t$this->password\n")) {
            socket_close($this->socket);
            return false;
        }
        return true;
    }

    function close()
    {
        $this->send("quit\n");
        socket_close($this->socket);
    }
}

