<?php

/**
 * Test class to test rcube_result_set class
 */
class Framework_ResultSet extends PHPUnit\Framework\TestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_result_set();

        $this->assertInstanceOf('rcube_result_set', $object, 'Class constructor');
    }
}
