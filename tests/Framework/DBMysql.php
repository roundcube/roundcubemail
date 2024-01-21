<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_db_mysql class
 *
 * @group database
 * @group mysql
 */
class Framework_DBMysql extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_db_mysql('test');

        $this->assertInstanceOf('rcube_db_mysql', $object, 'Class constructor');
    }
}
