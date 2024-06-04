<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_cache_db class
 */
class CacheDBTest extends TestCase
{
    /**
     * Test common cache functionality
     */
    public function test_common_cache_operations()
    {
        $rcube = \rcube::get_instance();
        $db = $rcube->get_dbh();
        $db->query('DELETE FROM cache');

        $cache = new \rcube_cache_db(1, 'test', 60);

        // Set and get cache record
        $data = ['data'];

        $cache->set('test', $data);

        $this->assertSame($data, $cache->get('test'));

        $cache->close();

        $cache = new \rcube_cache_db(1, 'test', 60);

        $this->assertSame($data, $cache->get('test'));

        // Remove cached record
        $cache->remove('test');

        $this->assertNull($cache->get('test'));

        $cache->close();

        $cache = new \rcube_cache_db(1, 'test', 60);

        $this->assertNull($cache->get('test'));

        // Call expunge methods
        $cache->expunge();
    }
}
