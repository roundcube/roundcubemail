<?php

/**
 * Test class to test rcube_content_filter class
 *
 * @package Tests
 */
class Framework_ContentFilter extends PHPUnit\Framework\TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_content_filter();

        $this->assertInstanceOf('rcube_content_filter', $object, "Class constructor");
    }
}
