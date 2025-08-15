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
}
