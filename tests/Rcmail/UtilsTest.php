<?php

namespace Roundcube\Tests\Rcmail;

use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_utils class
 */
class UtilsTest extends ActionTestCase
{
    /**
     * Test for db() method
     */
    public function test_db()
    {
        $db = \rcmail_utils::db();

        $this->assertInstanceOf(\rcube_db::class, $db);
    }

    /**
     * Test for db_version() method
     */
    public function test_db_version()
    {
        $v = \rcmail_utils::db_version();

        $this->assertMatchesRegularExpression('/^[0-9]{10}$/', $v);
    }

    /**
     * Test for db_clean() method
     */
    public function test_db_clean()
    {
        ob_start();
        \rcmail_utils::db_clean(7);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertTrue(strpos($output, '0 records deleted') !== false);
    }

    /**
     * Test for indexcontacts() method
     */
    public function test_indexcontacts()
    {
        self::initDB('contacts');

        ob_start();
        \rcmail_utils::indexcontacts();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertTrue(strpos($output, 'Indexing contacts for user') === 0);
    }

    /**
     * Test for mod_pref() method
     */
    public function test_mod_pref()
    {
        self::initDB('init');

        $db = \rcmail::get_instance()->get_dbh();

        ob_start();
        \rcmail_utils::mod_pref('test', []);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertTrue(strpos($output, 'Updating prefs for user 1') !== false);
        $this->assertTrue(strpos($output, 'saved') !== false);

        $query = $db->query('SELECT preferences FROM `users` WHERE `user_id` = 1');
        $result = $db->fetch_assoc($query);

        $prefs = unserialize($result['preferences']);

        $this->assertSame([], $prefs['test']);
    }
}
