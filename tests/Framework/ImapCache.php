<?php

/**
 * Test class to test rcube_imap_cache class
 *
 * @package Tests
 */
class Framework_ImapCache extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_imap_cache(new rcube_db('test'), null, null, null);

        $this->assertInstanceOf('rcube_imap_cache', $object, "Class constructor");
    }
}
