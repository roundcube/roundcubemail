<?php

/**
 * Test class to test rcube_imap class
 *
 * @package Tests
 */
class Framework_Imap extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_imap;

        $this->assertInstanceOf('rcube_imap', $object, "Class constructor");
    }
}
