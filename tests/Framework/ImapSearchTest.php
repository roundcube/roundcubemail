<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_imap_search class
 */
class Framework_ImapSearch extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_imap_search([], true);

        $this->assertInstanceOf('rcube_imap_search', $object, 'Class constructor');
    }
}
