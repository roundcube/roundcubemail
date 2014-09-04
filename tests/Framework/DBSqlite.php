<?php

/**
 * Test class to test rcube_db_sqlite class
 *
 * @package Tests
 */
class Framework_DBSqlite extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_db_sqlite('test');

        $this->assertInstanceOf('rcube_db_sqlite', $object, "Class constructor");
    }
}
