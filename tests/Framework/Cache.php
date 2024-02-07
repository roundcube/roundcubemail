<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_cache class
 */
class Framework_Cache extends TestCase
{
    /**
     * Test factory method
     */
    public function test_factory()
    {
        $object = rcube_cache::factory('db', 1);

        self::assertInstanceOf('rcube_cache_db', $object, 'Class constructor');
        self::assertInstanceOf('rcube_cache', $object, 'Class constructor');
    }

    /**
     * key_name() method
     */
    public function test_key_name()
    {
        self::assertSame('test', rcube_cache::key_name('test'));

        $params = ['test1' => 'test2'];
        self::assertSame('test.ad0234829205b9033196ba818f7a872b', rcube_cache::key_name('test', $params));
    }
}
