<?php

/**
 * Test class to test rcube_db_mssql class
 *
 * @package Tests
 * @group database
 * @group mssql
 */
class Framework_DBMssql extends PHPUnit\Framework\TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_db_mssql('test');

        $this->assertInstanceOf('rcube_db_mssql', $object, "Class constructor");
    }
}
