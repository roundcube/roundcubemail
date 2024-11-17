<?php

namespace Roundcube\Tests\Rcmail;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\ExitException;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail class
 */
class RcmailTest extends ActionTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        // set some HTTP env vars
        $_SERVER['HTTP_HOST'] = 'mail.example.org';
        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';
        $_SERVER['HTTPS'] = true;
        $_SERVER['X_FORWARDED_PATH'] = '/proxied/';

        \rcmail::get_instance()->filename = '';
    }

    /**
     * Test action_handler() method
     */
    public function test_action_handler()
    {
        $rcmail = \rcmail::get_instance();

        // Test keep-alive action handler
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'test', 'keep-alive');
        $e = null;

        try {
            $rcmail->action_handler();
        } catch (ExitException $e) {
        }

        $result = $output->getOutput();

        $this->assertSame(OutputJsonMock::E_EXIT, $e->getCode());
        $this->assertTrue(empty($result['exec']));

        // Test refresh action handler
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'settings', 'refresh');

        try {
            $rcmail->action_handler();
        } catch (ExitException $e) {
        }

        $result = $output->getOutput();

        $this->assertSame(OutputJsonMock::E_EXIT, $e->getCode());
        $this->assertTrue(empty($result['exec']));

        // TODO: Test non-existing action handler
    }

    /**
     * Test rcmail::get_address_book()
     */
    public function test_get_address_book()
    {
        $rcmail = \rcmail::get_instance();

        $result = $rcmail->get_address_book(0);

        $this->assertInstanceOf(\rcube_contacts::class, $result);

        $source_id = $rcmail->get_address_book_id($result);

        $this->assertSame(0, $source_id);
    }

    /**
     * Test rcmail::get_compose_responses()
     */
    public function test_get_compose_responses()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail::login()
     */
    public function test_login()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail::logout_actions()
     */
    public function test_logout_actions()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail::get_address_sources()
     */
    public function test_get_address_sources()
    {
        $rcmail = \rcmail::get_instance();

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
    public function test_url()
    {
        $rcmail = \rcmail::get_instance();

        $this->assertSame(
            '/sub/?_task=cli&_action=test',
            $rcmail->url('test'),
            'Action only'
        );

        $this->assertSame(
            '/sub/?_task=cli&_action=test&_a=AA',
            $rcmail->url(['action' => 'test', 'a' => 'AA']),
            'Unprefixed parameters'
        );

        $this->assertSame(
            '/sub/?_task=cli&_action=test&_b=BB',
            $rcmail->url(['_action' => 'test', '_b' => 'BB', '_c' => null]),
            'Prefixed parameters (skip empty)'
        );
        $this->assertSame('/sub/?_task=cli', $rcmail->url([]), 'Empty input');

        $this->assertSame(
            '/sub/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(['_action' => 'test', '_mode' => 'ABS'], true),
            'Absolute URL'
        );

        $this->assertSame(
            'https://mail.example.org/sub/?_task=calendar&_action=test&_mode=FQ',
            $rcmail->url(['task' => 'calendar', '_action' => 'test', '_mode' => 'FQ'], true, true),
            'Fully Qualified URL'
        );

        // with different SCRIPT_NAME values
        $_SERVER['SCRIPT_NAME'] = 'index.php';
        $this->assertSame(
            '/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(['_action' => 'test', '_mode' => 'ABS'], true),
            'Absolute URL (root)'
        );

        $_SERVER['SCRIPT_NAME'] = '';
        $this->assertSame(
            '/?_task=cli&_action=test&_mode=ABS',
            $rcmail->url(['_action' => 'test', '_mode' => 'ABS'], true),
            'Absolute URL (root)'
        );

        $_SERVER['REQUEST_URI'] = '/rc/?_task=mail';
        $this->assertSame('/rc/?_task=cli', $rcmail->url([]), 'Empty input with REQUEST_URI prefix');

        $rcmail->config->set('request_path', 'X_FORWARDED_PATH');
        $this->assertSame('/proxied/?_task=cli', $rcmail->url([]), 'Consider request_path config (_SERVER)');

        $rcmail->config->set('request_path', '/test');
        $this->assertSame('/test/?_task=cli', $rcmail->url([]), 'Consider request_path config (/path)');
        $rcmail->config->set('request_path', '/test/');
        $this->assertSame('/test/?_task=cli', $rcmail->url([]), 'Consider request_path config (/path/)');

        $_SERVER['REQUEST_URI'] = null;
        $rcmail->config->set('request_path', null);

        $_SERVER['HTTPS'] = false;
        $_SERVER['SERVER_PORT'] = '8080';
        $this->assertSame(
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
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail::contact_create() and rcmail::contact_exists()
     */
    public function test_contact_create_and_contact_exists()
    {
        self::initDB('contacts');

        $rcmail = \rcmail::get_instance();
        $db = $rcmail->get_dbh();
        $source = $rcmail->get_address_book(\rcube_addressbook::TYPE_DEFAULT, true);

        $contact_id = $rcmail->contact_create(['email' => 'test@xn--e1aybc.xn--p1ai'], $source, $error);

        $this->assertNull($error);
        $this->assertTrue($contact_id != false);

        $sql_result = $db->query("SELECT * FROM `contacts` WHERE `contact_id` = {$contact_id}");
        $contact = $db->fetch_assoc($sql_result);

        $this->assertSame('test@тест.рф', $contact['email']);
        $this->assertSame('Test', $contact['name']);

        $result = $rcmail->contact_exists('test@xn--e1aybc.xn--p1ai', \rcube_addressbook::TYPE_DEFAULT);

        $this->assertTrue($result);

        $result = $rcmail->contact_exists('test@тест.рф', \rcube_addressbook::TYPE_DEFAULT);

        $this->assertTrue($result);
    }

    /**
     * Test rcmail::user_date()
     */
    public function test_user_date()
    {
        $rcmail = \rcmail::get_instance();

        $date = $rcmail->user_date();

        $this->assertMatchesRegularExpression('/[a-z]{3}, [0-9]{1,2} [a-z]{3} ' . date('Y H:i:s') . ' [+-][0-9]{4}/i', $date);
    }

    /**
     * Test rcmail::find_asset()
     */
    public function test_find_asset()
    {
        $rcmail = \rcmail::get_instance();

        $result = $rcmail->find_asset('non-existing.js');
        $this->assertNull($result);

        $result = $rcmail->find_asset('program/resources/blocked.gif');
        $this->assertSame('program/resources/blocked.gif', $result);
    }

    /**
     * Test rcmail::format_date()
     */
    public function test_format_date()
    {
        $rcmail = \rcmail::get_instance();

        // this test depends on system timezone if not set
        date_default_timezone_set('UTC');
        $rcmail->config->set('prettydate', true);

        $date = $rcmail->format_date(date('Y-m-d H:i:s'));
        $this->assertSame('Today ' . date('H:i'), $date);

        // Test various formats
        setlocale(\LC_ALL, 'en_US');
        ini_set('intl.default_locale', 'en_US');
        $date = new \DateTime('2020-06-01 12:20:30', new \DateTimeZone('UTC'));

        $this->assertSame('2020-06-01 12:20', $rcmail->format_date($date));
        $this->assertSame('2020-06-01 12:20', $rcmail->format_date($date, 'Y-m-d H:i'));
        $this->assertSame(' Mon', $rcmail->format_date($date, ' D'));
        $this->assertSame('D Monday', $rcmail->format_date($date, '\D l'));
        $this->assertSame('Jun June', $rcmail->format_date($date, 'M F'));
        $date_x = '6/1/20, 12:20 PM';
        // @phpstan-ignore-next-line
        if (defined('INTL_ICU_VERSION') && version_compare(\INTL_ICU_VERSION, '72.1', '>=')) {
            // Starting with ICU 72.1, a NARROW NO-BREAK SPACE (NNBSP)
            // is used instead of an ASCII space before the meridian.
            $date_x = "6/1/20, 12:20\u{202f}PM";
        }
        $this->assertSame($date_x, $rcmail->format_date($date, 'x'));
        $this->assertSame('1591014030', $rcmail->format_date($date, 'U'));
        $this->assertSame('2020-06-01T12:20:30+00:00', $rcmail->format_date($date, 'c'));
    }
}
