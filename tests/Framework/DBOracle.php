<?php

/**
 * Test class to test rcube_db_oracle class
 *
 * @package Tests
 * @group database
 * @group oracle
 */
class Framework_DBOracle extends PHPUnit\Framework\TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_db_oracle('test');

        $this->assertInstanceOf('rcube_db_oracle', $object, "Class constructor");
    }
}
