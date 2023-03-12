<?php

/**
 * Test class to test rcube_cache_db class
 *
 * @package Tests
 */
class Framework_CacheDB extends PHPUnit\Framework\TestCase
{
    /**
     * Test common cache functionality
     */
    function test_common_cache_operations()
    {
        $rcube = rcube::get_instance();
        $db    = $rcube->get_dbh();
        $db->query('DELETE FROM cache');

        $cache = new rcube_cache_db(1, 'test', 60);

        // Set and get cache record
        $data =  ['data'];

        $cache->set('test', $data);

        $this->assertSame($data, $cache->get('test'));

        $cache->close();

        $cache = new rcube_cache_db(1, 'test', 60);

        $this->assertSame($data, $cache->get('test'));

        // Remove cached record
        $cache->remove('test');

        $this->assertSame(null, $cache->get('test'));

        $cache->close();

        $cache = new rcube_cache_db(1, 'test', 60);

        $this->assertSame(null, $cache->get('test'));

        // Call expunge methods
        $cache->expunge();
    }
}
