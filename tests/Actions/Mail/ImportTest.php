<?php

namespace Roundcube\Tests\Actions\Mail;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_mail_import
 */
class ImportTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $action = new \rcmail_action_mail_import();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'mail', 'import');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        $_SERVER['REQUEST_METHOD'] = 'POST';

        // No files uploaded case
        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('import', $result['action']);
        // TODO: Assert error message
        // $this->assertTrue(strpos($result['exec'], '') !== false);

        // Upload a EML file
        $_POST = [
            '_mbox' => 'Test',
        ];
        $_FILES['_file'] = [
            'name' => ['import.eml'],
            'type' => ['message/rfc822'],
            'tmp_name' => [__DIR__ . '/../../src/filename.eml'],
            'error' => [null],
            'size' => [123],
            'id' => [123],
        ];

        // Set expected storage function calls/results
        $storage = self::mockStorage()
            ->registerFunction('get_folder', 'Test')
            ->registerFunction('save_message', 123);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertTrue(str_contains($result['exec'], 'Successfully imported 1 messages'));
        $this->assertTrue(str_contains($result['exec'], 'this.command("list")'));

        $args = $storage->methodCalls[1]['args'];
        $this->assertSame('Test', $args[0]);
        $this->assertTrue(str_starts_with($args[1], 'From: "Thomas B." <thomas@roundcube.net>'));
        // The message date (from the Date header) is used as INTERNALDATE
        $this->assertInstanceOf(\DateTime::class, $args[5]);
        $this->assertSame('2014-05-23 19:44:50 +0200', $args[5]->format('Y-m-d H:i:s O'));

        // Upload a MBOX file
        $_FILES['_file'] = [
            'name' => ['import.eml'],
            'type' => ['text/plain'],
            'tmp_name' => [__DIR__ . '/../../src/import.mbox'],
            'error' => [null],
            'size' => [123],
            'id' => [123],
        ];

        // Set expected storage function calls/results
        $storage = self::mockStorage()
            ->registerFunction('get_folder', 'Test')
            ->registerFunction('save_message', 1)
            ->registerFunction('save_message', 2)
            ->registerFunction('save_message', 3);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertTrue(str_contains($result['exec'], 'Successfully imported 3 messages'));
        $this->assertTrue(str_contains($result['exec'], 'this.command("list")'));

        $args = $storage->methodCalls[1]['args'];
        $this->assertSame('Test', $args[0]);
        $this->assertTrue(str_starts_with($args[1], 'From: test@rc.net'));
        $this->assertStringContainsString('1234', $args[1]);
        // The date from the mbox from-line is used as INTERNALDATE
        $this->assertInstanceOf(\DateTime::class, $args[5]);
        $this->assertSame('2023-07-16 15:06:25 +0000', $args[5]->format('Y-m-d H:i:s O'));

        $args = $storage->methodCalls[2]['args'];
        $this->assertSame('Test', $args[0]);
        $this->assertTrue(str_starts_with($args[1], 'From: test1@rc.net'));
        $this->assertTrue(str_contains($args[1], "\nFrom me"));

        $args = $storage->methodCalls[3]['args'];
        $this->assertSame('Test', $args[0]);
        $this->assertTrue(str_starts_with($args[1], 'From: test2@rc.net'));
        $this->assertStringContainsString('XXXX', $args[1]);

        // TODO: Test error handling
        // TODO: Test ZIP file input
        $this->markTestIncomplete();
    }

    /**
     * Test save_message() setting the message date (INTERNALDATE)
     */
    public function test_save_message_date()
    {
        $storage = self::mockStorage()
            ->registerFunction('save_message', 1)
            ->registerFunction('save_message', 2)
            ->registerFunction('save_message', 3)
            ->registerFunction('save_message', 4)
            ->registerFunction('save_message', 5);

        // EML: use the Date header
        $message = "From: a@example.com\r\nDate: Thu, 1 Jan 2015 10:20:30 +0900\r\n\r\nBody";
        \rcmail_action_mail_import::save_message('Test', $message, 'eml');

        // EML without a body (headers only)
        $message = "From: a@example.com\r\nDate: Thu, 1 Jan 2015 10:20:30 +0900";
        \rcmail_action_mail_import::save_message('Test', $message, 'eml');

        // EML without a Date header: a date-like line in the body must be ignored
        $message = "From: a@example.com\r\nSubject: Test\r\n\r\nDate: Thu, 1 Jan 2015 10:20:30 +0900";
        \rcmail_action_mail_import::save_message('Test', $message, 'eml');

        // EML with an unparsable Date header
        $message = "From: a@example.com\r\nDate: not a date\r\n\r\nBody";
        \rcmail_action_mail_import::save_message('Test', $message, 'eml');

        // Mbox from-line without a recognized date: fall back to the Date header
        $message = "From sender@example.com\nFrom: a@example.com\nDate: Thu, 1 Jan 2015 10:20:30 +0900\n\nBody";
        \rcmail_action_mail_import::save_message('Test', $message, 'mbox');

        $dates = array_map(static function ($call) {
            return $call['args'][5] ? $call['args'][5]->format('Y-m-d H:i:s O') : null;
        }, $storage->methodCalls);

        $this->assertSame(
            [
                '2015-01-01 10:20:30 +0900',
                '2015-01-01 10:20:30 +0900',
                null,
                null,
                '2015-01-01 10:20:30 +0900',
            ],
            $dates
        );
    }
}
