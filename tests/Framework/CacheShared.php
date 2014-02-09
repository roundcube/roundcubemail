<?php

/**
 * Test class to test rcube_cache_shared class
 *
 * @package Tests
 */
class Framework_CacheShared extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_cache_shared('db');

        $this->assertInstanceOf('rcube_cache_shared', $object, "Class constructor");
    }
}
