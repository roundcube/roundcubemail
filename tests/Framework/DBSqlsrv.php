<?php

/**
 * Test class to test rcube_db_sqlsrv class
 *
 * @package Tests
 * @group database
 * @group sqlrsv
 */
class Framework_DBSqlsrv extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_db_sqlsrv('test');

        $this->assertInstanceOf('rcube_db_sqlsrv', $object, "Class constructor");
    }
}
