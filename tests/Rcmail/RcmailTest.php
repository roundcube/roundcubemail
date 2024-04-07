<?php

/**
 * Test class to test rcmail class
 */
class Rcmail_Rcmail extends ActionTestCase
{
    protected function setUp(): void
    {
        // set some HTTP env vars
        $_SERVER['HTTP_HOST'] = 'mail.example.org';
        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';
        $_SERVER['HTTPS'] = true;
        $_SERVER['X_FORWARDED_PATH'] = '/proxied/';

        rcmail::get_instance()->filename = '';
    }

    /**
     * Test action_handler() method
     */
    public function test_action_handler()
    {
        $rcmail = rcmail::get_instance();

        // Test keep-alive action handler
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'test', 'keep-alive');
        $e = null;

        try {
            $rcmail->action_handler();
        } catch (ExitException $e) {
        }

        $result = $output->getOutput();

        self::assertSame(OutputJsonMock::E_EXIT, $e->getCode());
        self::assertTrue(empty($result['exec']));

        // Test refresh action handler
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'refresh');

        try {
            $rcmail->action_handler();
        } catch (ExitException $e) {
        }

        $result = $output->getOutput();

        self::assertSame(OutputJsonMock::E_EXIT, $e->getCode());
        self::assertTrue(empty($result['exec']));

        // TODO: Test non-existing action handler
    }

    /**
     * Test rcmail::get_address_book()
     */
    public function test_get_address_book()
    {
        $rcmail = rcmail::get_instance();

        $result = $rcmail->get_address_book(0);

        self::assertInstanceOf('rcube_contacts', $result);

        $source_id = $rcmail->get_address_book_id($result);

        self::assertSame(0, $source_id);
    }

    /**
     * Test rcmail::get_compose_responses()
     */
    public function test_get_compose_responses()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail::login()
     */
    public function test_login()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail::logout_actions()
     */
    public function test_logout_actions()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail::get_address_sources()
     */
    public function test_get_address_sources()
    {
        $rcmail = rcmail::get_instance();

        $result = $rcmail->get_address_sources();

        self::assertCount(3, $result);
        self::assertSame('Personal Addresses', $result[0]['name']);
        self::assertSame('Collected Recipients', $result[1]['name']);
        self::assertSame('Trusted Senders', $result[2]['name']);

        $result = $rcmail->get_address_sources(true);

        self::assertCount(1, $result);
        self::assertSame('Personal Addresses', $result[0]['name']);

        // TODO: Test more cases
    }

    /**
     * Test rcmail::url()
     */
    public function test_url()
    {
        $rcmail = rcmail::get_instance();

        self::assertSame(
            '/sub/?_task=cli&_action=test',
            $rcmail->url('test'),
            'Action only'
        );

        self::assertSame(
            '/sub/?_task=cli&_action=test&_a=AA',
            $rcmail->url(['action' => 'test', 'a' => 'AA']),
            'Unprefixed parameters'
        );

        self::assertSame(
            '/sub/?_task=cli&_action=test&_b=BB',
            $rcmail->url(['_action' => 'test', '_b' => 'BB', '_c' => null]),
            'Prefixed parameters (skip empty)'
        );
        self::assertSame('/sub/?_task=cli', $rcmail->url([]), 'Empty input');

        self::assertSame(
            '/sub/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(['_action' => 'test', '_mode' => 'ABS'], true),
            'Absolute URL'
        );

        self::assertSame(
            'https://mail.example.org/sub/?_task=calendar&_action=test&_mode=FQ',
            $rcmail->url(['task' => 'calendar', '_action' => 'test', '_mode' => 'FQ'], true, true),
            'Fully Qualified URL'
        );

        // with different SCRIPT_NAME values
        $_SERVER['SCRIPT_NAME'] = 'index.php';
        self::assertSame(
            '/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(['_action' => 'test', '_mode' => 'ABS'], true),
            'Absolute URL (root)'
        );

        $_SERVER['SCRIPT_NAME'] = '';
        self::assertSame(
            '/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(['_action' => 'test', '_mode' => 'ABS'], true),
            'Absolute URL (root)'
        );

        $_SERVER['REQUEST_URI'] = '/rc/?_task=mail';
        self::assertSame('/rc/?_task=cli', $rcmail->url([]), 'Empty input with REQUEST_URI prefix');

        $rcmail->config->set('request_path', 'X_FORWARDED_PATH');
        self::assertSame('/proxied/?_task=cli', $rcmail->url([]), 'Consider request_path config (_SERVER)');

        $rcmail->config->set('request_path', '/test');
        self::assertSame('/test/?_task=cli', $rcmail->url([]), 'Consider request_path config (/path)');
        $rcmail->config->set('request_path', '/test/');
        self::assertSame('/test/?_task=cli', $rcmail->url([]), 'Consider request_path config (/path/)');

        $_SERVER['REQUEST_URI'] = null;
        $rcmail->config->set('request_path', null);

        $_SERVER['HTTPS'] = false;
        $_SERVER['SERVER_PORT'] = '8080';
        self::assertSame(
            'http://mail.example.org:8080/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(['_action' => 'test', '_mode' => 'ABS'], true, true),
            'Full URL with port'
        );
    }

    /**
     * Test rcmail::request_security_check()
     */
    public function test_request_security_check()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail::contact_create() and rcmail::contact_exists()
     */
    public function test_contact_create_and_contact_exists()
    {
        self::initDB('contacts');

        $rcmail = rcmail::get_instance();
        $db = $rcmail->get_dbh();
        $source = $rcmail->get_address_book(rcube_addressbook::TYPE_DEFAULT, true);

        $contact_id = $rcmail->contact_create(['email' => 'test@xn--e1aybc.xn--p1ai'], $source, $error);

        self::assertNull($error);
        self::assertTrue($contact_id != false);

        $sql_result = $db->query("SELECT * FROM `contacts` WHERE `contact_id` = {$contact_id}");
        $contact = $db->fetch_assoc($sql_result);

        self::assertSame('test@тест.рф', $contact['email']);
        self::assertSame('Test', $contact['name']);

        $result = $rcmail->contact_exists('test@xn--e1aybc.xn--p1ai', rcube_addressbook::TYPE_DEFAULT);

        self::assertTrue($result);

        $result = $rcmail->contact_exists('test@тест.рф', rcube_addressbook::TYPE_DEFAULT);

        self::assertTrue($result);
    }

    /**
     * Test rcmail::user_date()
     */
    public function test_user_date()
    {
        $rcmail = rcmail::get_instance();

        $date = $rcmail->user_date();

        self::assertMatchesRegularExpression('/[a-z]{3}, [0-9]{1,2} [a-z]{3} ' . date('Y H:i:s') . ' [+-][0-9]{4}/i', $date);
    }

    /**
     * Test rcmail::find_asset()
     */
    public function test_find_asset()
    {
        $rcmail = rcmail::get_instance();

        $result = $rcmail->find_asset('non-existing.js');
        self::assertNull($result);

        $result = $rcmail->find_asset('program/resources/blocked.gif');
        self::assertSame('program/resources/blocked.gif', $result);
    }

    /**
     * Test rcmail::format_date()
     */
    public function test_format_date()
    {
        $rcmail = rcmail::get_instance();

        // this test depends on system timezone if not set
        date_default_timezone_set('UTC');
        $rcmail->config->set('prettydate', true);

        $date = $rcmail->format_date(date('Y-m-d H:i:s'));
        self::assertSame('Today ' . date('H:i'), $date);

        // Test various formats
        setlocale(\LC_ALL, 'en_US');
        ini_set('intl.default_locale', 'en_US');
        $date = new DateTime('2020-06-01 12:20:30', new DateTimeZone('UTC'));

        self::assertSame('2020-06-01 12:20', $rcmail->format_date($date));
        self::assertSame('2020-06-01 12:20', $rcmail->format_date($date, 'Y-m-d H:i'));
        self::assertSame(' Mon', $rcmail->format_date($date, ' D'));
        self::assertSame('D Monday', $rcmail->format_date($date, '\D l'));
        self::assertSame('Jun June', $rcmail->format_date($date, 'M F'));
        $date_x = '6/1/20, 12:20 PM';
        // @phpstan-ignore-next-line
        if (defined('INTL_ICU_VERSION') && version_compare(\INTL_ICU_VERSION, '72.1', '>=')) {
            // Starting with ICU 72.1, a NARROW NO-BREAK SPACE (NNBSP)
            // is used instead of an ASCII space before the meridian.
            $date_x = "6/1/20, 12:20\u{202f}PM";
        }
        self::assertSame($date_x, $rcmail->format_date($date, 'x'));
        self::assertSame('1591014030', $rcmail->format_date($date, 'U'));
        self::assertSame('2020-06-01T12:20:30+00:00', $rcmail->format_date($date, 'c'));
    }
}
