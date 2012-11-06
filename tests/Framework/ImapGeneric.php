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

    /**
     * Test for uncompressMessageSet
     */
    function test_uncompressMessageSet()
    {
        $result = rcube_imap_generic::uncompressMessageSet(null);
        $this->assertSame(array(), $result);
        $this->assertCount(0, $result);

        $result = rcube_imap_generic::uncompressMessageSet('1');
        $this->assertSame(array(1), $result);
        $this->assertCount(1, $result);

        $result = rcube_imap_generic::uncompressMessageSet('1:3');
        $this->assertSame(array(1, 2, 3), $result);
        $this->assertCount(3, $result);
    }
}
