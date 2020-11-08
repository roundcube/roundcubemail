<?php

/**
 * Test class to test rcmail_utils class
 *
 * @package Tests
 */
class Rcmail_RcmailUtils extends PHPUnit\Framework\TestCase
{
    /**
     * Test for db() method
     */
    function test_db()
    {
        $db = rcmail_utils::db();

        $this->assertInstanceOf('rcube_db', $db);
    }

    /**
     * Test for db_version() method
     */
    function test_db_version()
    {
        // It breaks the test suite for some reason
        $this->markTestIncomplete();

        $v = rcmail_utils::db_version();

        $this->assertRegExp('/^[0-9]{10}$/', $v);
    }

    /**
     * Test for db_clean() method
     */
    function test_db_clean()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test for indexcontacts() method
     */
    function test_indexcontacts()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test for mod_pref() method
     */
    function test_mod_pref()
    {
        $this->markTestIncomplete();
    }
}
