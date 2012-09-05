<?php

/**
 * Test class to test rcube_charset class
 *
 * @package Tests
 */
class Framework_Charset extends PHPUnit_Framework_TestCase
{

    /**
     * Data for test_clean()
     */
    function data_clean()
    {
        return array(
            array('', '', 'Empty string'),
        );
    }

    /**
     * @dataProvider data_clean
     */
    function test_clean($input, $output, $title)
    {
        $this->assertEquals(rcube_charset::clean($input), $output, $title);
    }
}
