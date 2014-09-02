<?php

/**
 * Test class to test rcmail class
 *
 * @package Tests
 */
class RcmailFunc extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        // set some HTTP env vars
        $_SERVER['HTTP_HOST'] = 'mail.example.org';
        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';
        $_SERVER['HTTPS'] = true;

        rcmail::get_instance()->filename = '';
    }

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = rcmail::get_instance();
        $this->assertInstanceOf('rcmail', $object, "Class singleton");
    }

    /**
     * Test rcmail::url()
     */
    function test_url()
    {
        $rcmail = rcmail::get_instance();
        $this->assertEquals(
            './?_task=cli&_action=test',
            $rcmail->url('test'),
            "Action only"
        );
        $this->assertEquals(
            './?_task=cli&_action=test&_a=AA',
            $rcmail->url(array('action' => 'test', 'a' => 'AA')),
            "Unprefixed parameters"
        );
        $this->assertEquals(
            './?_task=cli&_action=test&_b=BB',
            $rcmail->url(array('_action' => 'test', '_b' => 'BB', '_c' => null)),
            "Prefixed parameters (skip empty)"
        );
        $this->assertEquals(
            '/sub/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(array('_action' => 'test', '_mode' => 'ABS'), true),
            "Absolute URL"
        );

        $this->assertEquals(
            'https://mail.example.org/sub/?_task=calendar&_action=test&_mode=FQ',
            $rcmail->url(array('task' => 'calendar', '_action' => 'test', '_mode' => 'FQ'), true, true),
            "Fully Qualified URL"
        );

        // with different SCRIPT_NAME values
        $_SERVER['SCRIPT_NAME'] = 'index.php';
        $this->assertEquals(
            '/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(array('_action' => 'test', '_mode' => 'ABS'), true),
            "Absolute URL (root)"
        );
        $_SERVER['SCRIPT_NAME'] = '';
        $this->assertEquals(
            '/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(array('_action' => 'test', '_mode' => 'ABS'), true),
            "Absolute URL (root)"
        );

        $_SERVER['HTTPS'] = false;
        $_SERVER['SERVER_PORT'] = '8080';
        $this->assertEquals(
            'http://mail.example.org:8080/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(array('_action' => 'test', '_mode' => 'ABS'), true, true),
            "Full URL with port"
        );
    }
}
