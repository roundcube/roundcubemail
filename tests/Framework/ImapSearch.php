<?php

/**
 * Test class to test rcube_imap_search class
 *
 * @package Tests
 */
class Framework_ImapSearch extends PHPUnit\Framework\TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_imap_search([], true);

        $this->assertInstanceOf('rcube_imap_search', $object, "Class constructor");
    }
}
