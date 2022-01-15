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
 |   Environment initialization script for functional tests              |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

if (php_sapi_name() != 'cli') {
    die("Not in shell mode (php-cli)");
}

if (!defined('INSTALL_PATH')) {
    define('INSTALL_PATH', realpath(__DIR__ . '/../../') . '/' );
}

require_once(INSTALL_PATH . 'program/include/iniset.php');

$rcmail = rcmail::get_instance(0, 'test');

define('TESTS_DIR', realpath(__DIR__) . '/');
define('TESTS_USER', $rcmail->config->get('tests_username'));
define('TESTS_PASS', $rcmail->config->get('tests_password'));

require_once(__DIR__ . '/Browser.php');
require_once(__DIR__ . '/TestCase.php');
require_once(__DIR__ . '/Components/App.php');
require_once(__DIR__ . '/Components/Dialog.php');
require_once(__DIR__ . '/Components/HtmlEditor.php');
require_once(__DIR__ . '/Components/Popupmenu.php');
require_once(__DIR__ . '/Components/RecipientInput.php');
require_once(__DIR__ . '/Components/Taskmenu.php');
require_once(__DIR__ . '/Components/Toolbarmenu.php');


/**
 * Utilities for test environment setup
 */
class bootstrap
{
    static $imap_ready = null;

    /**
     * Wipe and re-initialize database
     */
    public static function init_db()
    {
        $rcmail = rcmail::get_instance();
        $dsn = rcube_db::parse_dsn($rcmail->config->get('db_dsnw'));
        $db = $rcmail->get_dbh();

        if ($dsn['phptype'] == 'mysql' || $dsn['phptype'] == 'mysqli') {
            // drop all existing tables first
            $db->query("SET FOREIGN_KEY_CHECKS=0");
            $sql_res = $db->query("SHOW TABLES");
            while ($sql_arr = $db->fetch_array($sql_res)) {
                $table = reset($sql_arr);
                $db->query("DROP TABLE $table");
            }

            self::init_db_user($db);

            // init database with schema
            system(sprintf('cat %s %s | mysql -h %s -u %s --password=%s %s',
                realpath(INSTALL_PATH . '/SQL/mysql.initial.sql'),
                realpath(TESTS_DIR . 'data/data.sql'),
                escapeshellarg($dsn['hostspec']),
                escapeshellarg($dsn['username']),
                escapeshellarg($dsn['password']),
                escapeshellarg($dsn['database'])
            ));
        }
        else if ($dsn['phptype'] == 'sqlite') {
            $db->closeConnection();
            // delete database file
            system(sprintf('rm -f %s', escapeshellarg($dsn['database'])));

            self::init_db_user($db);

            // load sample test data
            // Note: exec_script() does not really work with these queries
            $sql = file_get_contents(TESTS_DIR . 'data/data.sql');
            $sql = preg_split('/;\n/', $sql, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($sql as $query) {
                $result = $db->query($query);
                if ($db->is_error($result)) {
                    rcube::raise_error($db->is_error(), false, true);
                }
            }
        }
    }

    /**
     * Create user/identity record for the test user
     */
    private static function init_db_user($db)
    {
        $rcmail = rcmail::get_instance();
        $imap_host = $rcmail->config->get('imap_host');

        if ($host = parse_url($imap_host, PHP_URL_HOST)) {
            $imap_host = $host;
        }

        $db->query("INSERT INTO `users` (`username`, `mail_host`, `language`)"
                . " VALUES (?, ?, 'en_US')", TESTS_USER, $imap_host);

        $db->query("INSERT INTO `identities` (`user_id`, `email`, `standard`)"
                . " VALUES (1, ?, '1')", TESTS_USER);
    }

    /**
     * Wipe the configured IMAP account and fill with test data
     */
    public static function init_imap($force = false)
    {
        if (!TESTS_USER) {
            return false;
        }
        else if (!$force && self::$imap_ready !== null) {
            return self::$imap_ready;
        }

        self::connect_imap(TESTS_USER, TESTS_PASS);

        return self::$imap_ready;
    }

    /**
     * Authenticate to IMAP with the given credentials
     */
    public static function connect_imap($username, $password)
    {
        $rcmail = rcmail::get_instance();
        $imap = $rcmail->get_storage();

        if ($imap->is_connected()) {
            $imap->close();
            self::$imap_ready = false;
        }

        $imap_host = $rcmail->config->get('imap_host');
        $imap_port = 143;
        $imap_ssl = false;

        $a_host = parse_url($imap_host);

        if (!empty($a_host['host'])) {
            $imap_host = $a_host['host'];
            $imap_ssl  = isset($a_host['scheme']) && in_array($a_host['scheme'], ['ssl','imaps','tls']) ? $a_host['scheme'] : false;
            $imap_port = $a_host['port'] ?? ($imap_ssl && $imap_ssl != 'tls' ? 993 : 143);
        }

        if (!$imap->connect($imap_host, $username, $password, $imap_port, $imap_ssl)) {
            rcube::raise_error("IMAP error: unable to authenticate with user " . TESTS_USER, false, true);
        }

        if (in_array('archive', (array) $rcmail->config->get('plugins'))) {
            // Register special folder type for the Archive plugin.
            // As we're in cli mode the plugin can't do it by its own
            rcube_storage::$folder_types[] = 'archive';
        }

        self::$imap_ready = true;
    }

    /**
     * Import the given file into IMAP
     */
    public static function import_message($filename, $mailbox = 'INBOX')
    {
        if (!self::init_imap()) {
            rcube::raise_error(__METHOD__ . ': IMAP connection unavailable', false, true);
        }

        $file = file_get_contents($filename);
        $imap = rcmail::get_instance()->get_storage();

        $imap->save_message($mailbox, $file);
    }

    /**
     * Delete all messages from the given mailbox
     */
    public static function purge_mailbox($mailbox)
    {
        if (!self::init_imap()) {
            rcube::raise_error(__METHOD__ . ': IMAP connection unavailable', false, true);
        }

        $imap = rcmail::get_instance()->get_storage();
        $imap->delete_message('*', $mailbox);
    }

    /**
     * Make sure the given mailbox exists in IMAP
     */
    public static function ensure_mailbox($mailbox, $empty = false)
    {
        if (!self::init_imap()) {
            rcube::raise_error(__METHOD__ . ': IMAP connection unavailable', false, true);
        }

        $imap = rcmail::get_instance()->get_storage();

        $folders = $imap->list_folders();
        if (!in_array($mailbox, $folders)) {
            $imap->create_folder($mailbox, true);
        }
        else if ($empty) {
            $imap->delete_message('*', $mailbox);
        }
    }

    /**
     * Make sure only special folders exist in IMAP
     */
    public static function reset_mailboxes()
    {
        if (!self::init_imap()) {
            rcube::raise_error(__METHOD__ . ': IMAP connection unavailable', false, true);
        }

        $rcmail       = rcmail::get_instance();
        $imap         = $rcmail->get_storage();
        $got_defaults = $rcmail->config->get('create_default_folders');
        $vendor       = $imap->get_vendor();

        // Note: We do not expect IMAP server auto-creating any folders
        foreach ($imap->list_folders() as $folder) {
            if ($folder != 'INBOX' && (!$got_defaults || !$imap->is_special_folder($folder))) {
                // GreenMail throws errors when unsubscribing a deleted folder
                if ($vendor == 'greenmail') {
                    $imap->conn->deleteFolder($folder);
                }
                else {
                    $imap->delete_folder($folder);
                }
            }
        }
    }

    /**
     * Check IMAP capabilities
     */
    public static function get_storage()
    {
        if (!self::init_imap()) {
            rcube::raise_error(__METHOD__ . ': IMAP connection unavailable', false, true);
        }

        return rcmail::get_instance()->get_storage();
    }

    /**
     * Return user preferences directly from database
     */
    public static function get_prefs()
    {
        $rcmail = rcmail::get_instance();

        // Create a separate connection to the DB, otherwise
        // we hit some strange and hard to investigate locking issues
        $db = rcube_db::factory($rcmail->config->get('db_dsnw'), $rcmail->config->get('db_dsnr'), false);
        $db->set_debug((bool)$rcmail->config->get('sql_debug'));

        $query  = $db->query("SELECT preferences FROM users WHERE username = ?", TESTS_USER);
        $record = $db->fetch_assoc($query);

        return unserialize($record['preferences']);
    }
}
