<?php

/**
 * Test class to test rcube_imap_generic class
 *
 * @package Tests
 */
class Framework_ImapGeneric extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_imap_generic;

        $this->assertInstanceOf('rcube_imap_generic', $object, "Class constructor");
    }
}
