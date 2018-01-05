<?php

/**
 * Test class to test rcube_session_memcache class
 *
 * @package Tests
 * @group database
 */

class Framework_SessionMemcache extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor test
     */
    public function test_class()
    {
        $config = new rcube_config();
        $config->set('session_storage', 'memcache');

        $object = rcube_session::factory($config);
        $this->assertInstanceOf('rcube_session_memcache', $object, 'Class constructor');

        $this->assertEquals('memcached', ini_get('session.save_handler'));
        $this->assertEquals(0, ini_get('memcached.sess_locking'));
    }

}
