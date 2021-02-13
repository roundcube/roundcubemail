<?php

/**
 * Test class to test rcmail class
 *
 * @package Tests
 */
class Rcmail_Rcmail extends ActionTestCase
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
     * Test action_handler() method
     */
    function test_action_handler()
    {
        $rcmail = rcmail::get_instance();

        // Test keep-alive action handler
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'test', 'keep-alive');

        try {
            $rcmail->action_handler();
        }
        catch (ExitException $e) {
        }

        $result = $output->getOutput();

        $this->assertSame(OutputJsonMock::E_EXIT, $e->getCode());
        $this->assertTrue(empty($result['exec']));

        // Test refresh action handler
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'refresh');

        try {
            $rcmail->action_handler();
        }
        catch (ExitException $e) {
        }

        $result = $output->getOutput();

        $this->assertSame(OutputJsonMock::E_EXIT, $e->getCode());
        $this->assertTrue(empty($result['exec']));

        // TODO: Test non-existing action handler
    }

    /**
     * Test rcmail::get_address_book()
     */
    function test_get_address_book()
    {
        $rcmail = rcmail::get_instance();

        $result = $rcmail->get_address_book(0);

        $this->assertInstanceOf('rcube_contacts', $result);

        $source_id = $rcmail->get_address_book_id($result);

        $this->assertSame(0, $source_id);
    }

    /**
     * Test rcmail::get_compose_responses()
     */
    function test_get_compose_responses()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail::login()
     */
    function test_login()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail::logout_actions()
     */
    function test_logout_actions()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail::get_address_sources()
     */
    function test_get_address_sources()
    {
        $rcmail = rcmail::get_instance();

        $result = $rcmail->get_address_sources();

        $this->assertCount(3, $result);
        $this->assertSame('Personal Addresses', $result[0]['name']);
        $this->assertSame('Collected Recipients', $result[1]['name']);
        $this->assertSame('Trusted Senders', $result[2]['name']);

        $result = $rcmail->get_address_sources(true);

        $this->assertCount(1, $result);
        $this->assertSame('Personal Addresses', $result[0]['name']);

        // TODO: Test more cases
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
            $rcmail->url(['action' => 'test', 'a' => 'AA']),
            "Unprefixed parameters"
        );
        $this->assertEquals(
            './?_task=cli&_action=test&_b=BB',
            $rcmail->url(['_action' => 'test', '_b' => 'BB', '_c' => null]),
            "Prefixed parameters (skip empty)"
        );
        $this->assertEquals(
            '/sub/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(['_action' => 'test', '_mode' => 'ABS'], true),
            "Absolute URL"
        );

        $this->assertEquals(
            'https://mail.example.org/sub/?_task=calendar&_action=test&_mode=FQ',
            $rcmail->url(['task' => 'calendar', '_action' => 'test', '_mode' => 'FQ'], true, true),
            "Fully Qualified URL"
        );

        // with different SCRIPT_NAME values
        $_SERVER['SCRIPT_NAME'] = 'index.php';
        $this->assertEquals(
            '/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(['_action' => 'test', '_mode' => 'ABS'], true),
            "Absolute URL (root)"
        );
        $_SERVER['SCRIPT_NAME'] = '';
        $this->assertEquals(
            '/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(['_action' => 'test', '_mode' => 'ABS'], true),
            "Absolute URL (root)"
        );

        $_SERVER['HTTPS'] = false;
        $_SERVER['SERVER_PORT'] = '8080';
        $this->assertEquals(
            'http://mail.example.org:8080/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(['_action' => 'test', '_mode' => 'ABS'], true, true),
            "Full URL with port"
        );
    }

    /**
     * Test rcmail::request_security_check()
     */
    function test_request_security_check()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail::contact_create()
     */
    function test_contact_create()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail::contact_exists()
     */
    function test_contact_exists()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail::user_date()
     */
    function test_user_date()
    {
        $rcmail = rcmail::get_instance();

        $date = $rcmail->user_date();

        $this->assertRegExp('/[a-z]{3}, [0-9]{1,2} [a-z]{3} ' . date('Y H:i:s') . ' [+-][0-9]{4}/i', $date);
    }

    /**
     * Test rcmail::find_asset()
     */
    function test_find_asset()
    {
        $rcmail = rcmail::get_instance();

        $result = $rcmail->find_asset('non-existing.js');
        $this->assertNull($result);

        $result = $rcmail->find_asset('program/resources/blocked.gif');
        $this->assertSame('program/resources/blocked.gif', $result);
    }

    /**
     * Test rcmail::format_date()
     */
    function test_format_date()
    {
        $rcmail = rcmail::get_instance();

        $date = $rcmail->format_date(date('Y-m-d H:i:s'));
        $this->assertSame('Today ' . date('H:i'), $date);

        // TODO: Test more cases
    }
}
