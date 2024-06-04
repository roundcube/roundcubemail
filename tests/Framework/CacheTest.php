<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_cache class
 */
class CacheTest extends TestCase
{
    /**
     * Test factory method
     */
    public function test_factory()
    {
        $object = \rcube_cache::factory('db', 1);

        $this->assertInstanceOf(\rcube_cache_db::class, $object, 'Class constructor');
        $this->assertInstanceOf(\rcube_cache::class, $object, 'Class constructor');
    }

    /**
     * key_name() method
     */
    public function test_key_name()
    {
        $this->assertSame('test', \rcube_cache::key_name('test'));

        $params = ['test1' => 'test2'];
        $this->assertSame('test.ad0234829205b9033196ba818f7a872b', \rcube_cache::key_name('test', $params));
    }
}
