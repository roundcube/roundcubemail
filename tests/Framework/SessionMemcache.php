<?php

/**
 * Test class to test rcube_session_memcache class
 *
 * @package Tests
 * @group database
 */

class Framework_SessionMemcache extends PHPUnit_Framework_TestCase
{

    /** @var rcube_session_memcache */
    private $object;

    /** @var rcube_config */
    private $config;

    /** @var Memcached */
    private $memcache;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        if (getenv('TEST_MEMCACHE') === false) {
            $this->markTestSkipped('Tests for rcube_session_memcache disabled by default.');
        }

        $this->config = new rcube_config();
        $this->config->set('session_storage', 'memcache');

        $this->object = rcube_session::factory($this->config);
        $this->object->set_lifetime(-60);
    }


    /**
     * Class constructor test
     */
    public function test_class()
    {
        $this->assertInstanceOf('rcube_session_memcache', $this->object, "Class constructor");
    }

    /**
     * Write data to memcache and read again
     */
    public function test_write_and_read()
    {
        /** @var string $vars */
        $vars = serialize(array('foo' => 'bar'));

        $writeRequest = $this->object->write('1234', $vars);
        $this->assertTrue($writeRequest);

        $readResponse = $this->object->read('1234');
        $this->assertEquals($vars, $readResponse);
    }

    /**
     * Try to read data by invalid session key
     */
    public function test_read_not_found()
    {
        $readResponse = $this->object->read('5678');
        $this->assertEmpty($readResponse);
    }

    /**
     * Write, delete and read data
     */
    public function test_write_and_read_deleted()
    {
        /** @var string $vars */
        $vars = serialize(array('foo' => 'bar'));

        $writeRequest = $this->object->write('1234', $vars);
        $this->assertTrue($writeRequest);

        $deleteRequest = $this->object->destroy('1234');
        $this->assertTrue($deleteRequest);

        $readResponse = $this->object->read('1234');
        $this->assertEmpty($readResponse);
    }

}
