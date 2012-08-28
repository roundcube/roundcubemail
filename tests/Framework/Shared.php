<?php

/**
 * Test class to test rcube_shared functions
 *
 * @package Tests
 */
class Framework_Shared extends PHPUnit_Framework_TestCase
{

    /**
     * rcube_shared.inc: in_array_nocase()
     */
    function test_in_array_nocase()
    {
        $haystack = array('Test');
        $needle = 'test';
        $result = in_array_nocase($needle, $haystack);

        $this->assertTrue($result, $title);

        $result = in_array_nocase($needle, null);

        $this->assertFalse($result, $title);
    }

    /**
     * rcube_shared.inc: get_boolean()
     */
    function test_get_boolean()
    {
        $input = array(
            false, 'false', '0', 'no', 'off', 'nein', 'FALSE', '', null,
        );

        foreach ($input as $idx => $value) {
            $this->assertFalse(get_boolean($value), "Invalid result for $idx test item");
        }

        $input = array(
            true, 'true', '1', 1, 'yes', 'anything', 1000,
        );

        foreach ($input as $idx => $value) {
            $this->assertTrue(get_boolean($value), "Invalid result for $idx test item");
        }
    }

    /**
     * rcube_shared.inc: parse_bytes()
     */
    function test_parse_bytes()
    {
        $data = array(
            '1'      => 1,
            '1024'   => 1024,
            '2k'     => 2 * 1024,
            '2 k'     => 2 * 1024,
            '2kb'    => 2 * 1024,
            '2kB'    => 2 * 1024,
            '2m'     => 2 * 1048576,
            '2 m'     => 2 * 1048576,
            '2mb'    => 2 * 1048576,
            '2mB'    => 2 * 1048576,
            '2g'     => 2 * 1024 * 1048576,
            '2 g'     => 2 * 1024 * 1048576,
            '2gb'    => 2 * 1024 * 1048576,
            '2gB'    => 2 * 1024 * 1048576,
        );

        foreach ($data as $value => $expected) {
            $result = parse_bytes($value);
            $this->assertEquals($expected, $result, "Invalid parse_bytes() result for $value");
        }
    }

    /**
     * rcube_shared.inc: slashify()
     */
    function test_slashify()
    {
        $data = array(
            'test'    => 'test/',
            'test/'   => 'test/',
            ''        => '/',
            "\\"      => "\\/",
        );

        foreach ($data as $value => $expected) {
            $result = slashify($value);
            $this->assertEquals($expected, $result, "Invalid slashify() result for $value");
        }

    }

    /**
     * rcube_shared.inc: unslashify()
     */
    function test_unslashify()
    {
        $data = array(
            'test'      => 'test',
            'test/'     => 'test',
            '/'         => '',
            "\\/"       => "\\",
            'test/test' => 'test/test',
            'test//'    => 'test',
        );

        foreach ($data as $value => $expected) {
            $result = unslashify($value);
            $this->assertEquals($expected, $result, "Invalid unslashify() result for $value");
        }

    }

    /**
     * rcube_shared.inc: get_offset_sec()
     */
    function test_get_offset_sec()
    {
        $data = array(
            '1s'    => 1,
            '1m'    => 1 * 60,
            '1h'    => 1 * 60 * 60,
            '1d'    => 1 * 60 * 60 * 24,
            '1w'    => 1 * 60 * 60 * 24 * 7,
            '1y'    => (int) '1y',
            100     => 100,
            '100'   => 100,
        );

        foreach ($data as $value => $expected) {
            $result = get_offset_sec($value);
            $this->assertEquals($expected, $result, "Invalid get_offset_sec() result for $value");
        }

    }

    /**
     * rcube_shared.inc: array_keys_recursive()
     */
    function test_array_keys_recursive()
    {
        $input = array(
            'one' => array(
                'two' => array(
                    'three' => array(),
                    'four' => 'something',
                ),
            ),
            'five' => 'test',
        );

        $result     = array_keys_recursive($input);
        $input_str  = 'one,two,three,four,five';
        $result_str = implode(',', $result);

        $this->assertEquals($input_str, $result_str, "Invalid array_keys_recursive() result");
    }
}
