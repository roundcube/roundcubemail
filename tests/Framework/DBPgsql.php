<?php

/**
 * Test class to test rcube_db_pgsql class
 *
 * @package Tests
 * @group database
 * @group postgres
 */
class Framework_DBPgsql extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_db_pgsql('test');

        $this->assertInstanceOf('rcube_db_pgsql', $object, "Class constructor");
    }
}
