<?php

/**
 * Test class to test rcube_db_sqlite class
 *
 * @package Tests
 * @group database
 * @group sqlite
 */
class Framework_DBSqlite extends PHPUnit\Framework\TestCase
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
