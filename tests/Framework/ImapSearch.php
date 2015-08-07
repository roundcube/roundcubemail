<?php

/**
 * Test class to test rcube_imap_search class
 *
 * @package Tests
 */
class Framework_ImapSearch extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_imap_search(array(), true);

        $this->assertInstanceOf('rcube_imap_search', $object, "Class constructor");
    }
}
