<?php

/*
 +-----------------------------------------------------------------------+
 | tests/Selenium/bootstrap.php                                          |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2009-2014, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Environment initialization script for unit tests                    |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

if (php_sapi_name() != 'cli')
  die("Not in shell mode (php-cli)");

if (!defined('INSTALL_PATH')) define('INSTALL_PATH', realpath(__DIR__ . '/../../') . '/' );

define('TESTS_DIR', __DIR__ . '/');

if (@is_dir(TESTS_DIR . 'config')) {
    define('RCUBE_CONFIG_DIR', TESTS_DIR . 'config');
}

require_once(INSTALL_PATH . 'program/include/iniset.php');

// Extend include path so some plugin test won't fail
$include_path = ini_get('include_path') . PATH_SEPARATOR . TESTS_DIR . '..';
if (set_include_path($include_path) === false) {
    die("Fatal error: ini_set/set_include_path does not work.");
}

$rcmail = rcmail::get_instance('test');

define('TESTS_URL',     $rcmail->config->get('tests_url'));
define('TESTS_BROWSER', $rcmail->config->get('tests_browser', 'firefox'));
define('TESTS_USER',    $rcmail->config->get('tests_username'));
define('TESTS_PASS',    $rcmail->config->get('tests_password'));
define('TESTS_SLEEP',   $rcmail->config->get('tests_sleep', 5));

PHPUnit_Extensions_Selenium2TestCase::shareSession(true);


/**
 * satisfy PHPUnit
 */
class bootstrap
{
    /**
     * Wipe and re-initialize (mysql) database
     */
    public static function init_db()
    {
        $rcmail = rcmail::get_instance();

        // drop all existing tables first
        $db = $rcmail->get_dbh();
        $db->query("SET FOREIGN_KEY_CHECKS=0");
        $sql_res = $db->query("SHOW TABLES");
        while ($sql_arr = $db->fetch_array($sql_res)) {
            $table = reset($sql_arr);
            $db->query("DROP TABLE $table");
        }

        // init database with schema
        $dsn = parse_url($rcmail->config->get('db_dsnw'));
        $db_name = trim($dsn['path'], '/');

        if ($dsn['scheme'] == 'mysql' || $dsn['scheme'] == 'mysqli') {
            system(sprintf('cat %s %s | mysql -h %s -u %s --password=%s %s',
                realpath(INSTALL_PATH . '/SQL/mysql.initial.sql'),
                realpath(TESTS_DIR . '/Selenium/data/mysql.sql'),
                escapeshellarg($dsn['host']),
                escapeshellarg($dsn['user']),
                escapeshellarg($dsn['pass']),
                escapeshellarg($db_name)
            ));
        }
    }

    /**
     * Wipe the configured IMAP account and fill with test data
     */
    public static function init_imap()
    {
        if (!TESTS_USER)
            return false;

        // TBD.
    }
}

// @TODO: make sure mailbox has some content (always the same) or is empty
// @TODO: plugins: enable all?

/**
 * Base class for all tests in this directory
 */
class Selenium_Test extends PHPUnit_Extensions_Selenium2TestCase
{
    protected function setUp()
    {
        $this->setBrowser(TESTS_BROWSER);

        // Set root to our index.html, for better performance
        // See https://github.com/sebastianbergmann/phpunit-selenium/issues/217
        $baseurl = preg_replace('!/index(-.+)?\.php^!', '', TESTS_URL);
        $this->setBrowserUrl($baseurl . '/tests/Selenium');
    }

    protected function login()
    {
        $this->go('mail');

        $user_input = $this->byCssSelector('form input[name="_user"]');
        $pass_input = $this->byCssSelector('form input[name="_pass"]');
        $submit     = $this->byCssSelector('form input[type="submit"]');

        $user_input->value(TESTS_USER);
        $pass_input->value(TESTS_PASS);

        // submit login form
        $submit->click();

        // wait after successful login
        sleep(TESTS_SLEEP);
    }

    protected function go($task = 'mail', $action = null)
    {
        $this->url(TESTS_URL . '?_task=' . $task);

        // wait for interface load (initial ajax requests, etc.)
        sleep(TESTS_SLEEP);

        if ($action) {
            $this->click_button($action);

            sleep(TESTS_SLEEP);
        }
    }

    protected function get_env()
    {
        return $this->execute(array(
            'script' => 'return rcmail.env;',
            'args' => array(),
        ));
    }

    protected function get_buttons($action)
    {
        $buttons = $this->execute(array(
            'script' => "return rcmail.buttons['$action'];",
            'args' => array(),
        ));

        if (is_array($buttons)) {
            foreach ($buttons as $idx => $button) {
                $buttons[$idx] = $button['id'];
            }
        }

        return (array) $buttons;
    }

    protected function get_objects()
    {
        return $this->execute(array(
            'script' => "var i,r = []; for (i in rcmail.gui_objects) r.push(i); return r;",
            'args' => array(),
        ));
    }

    protected function click_button($action)
    {
        $buttons = $this->get_buttons($action);
        $id      = array_shift($buttons);

        // this doesn't work for me
        $this->byId($id)->click();
    }

    protected function ajaxResponse($action, $script = '', $button = false)
    {
        if (!$script && !$button) {
            $script = "rcmail.command('$action')";
        }

        $script = 
        "if (!window.test_ajax_response) {
            window.test_ajax_response_object = {};
            function test_ajax_response(response)
            {
                if (response.response && response.response.action) {
                    window.test_ajax_response_object[response.response.action] = response.response;
                }
            }
            rcmail.addEventListener('responsebefore', test_ajax_response);
        }
        window.test_ajax_response_object['$action'] = null;
        $script;
        ";

        // run request
        $this->execute(array(
            'script' => $script,
            'args' => array(),
        ));

        if ($button) {
            $this->click_button($action);
        }

        // wait
        sleep(TESTS_SLEEP);

        // get response
        $response = $this->execute(array(
            'script' => "return window.test_ajax_response_object['$action'];",
            'args' => array(),
        ));

        return $response;
    }
}
