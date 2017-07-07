<?php

/**
 * Test class to test rcube_db_mysql class
 *
 * @package Tests
 * @group database
 * @group mysql
 */
class Framework_DBMysql extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_db_mysql('test');

        $this->assertInstanceOf('rcube_db_mysql', $object, "Class constructor");
    }
}
