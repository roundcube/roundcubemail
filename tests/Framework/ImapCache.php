<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_imap_cache class
 */
class Framework_ImapCache extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_imap_cache(new rcube_db('test'), null, null, null);

        $this->assertInstanceOf('rcube_imap_cache', $object, 'Class constructor');
    }
}
