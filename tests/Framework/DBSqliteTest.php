<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_db_sqlite class
 *
 * @group database
 * @group sqlite
 */
class Framework_DBSqlite extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_db_sqlite('test');

        $this->assertInstanceOf('rcube_db_sqlite', $object, 'Class constructor');
    }
}
