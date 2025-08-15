<?php

namespace Roundcube\Tests\Actions\Settings;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputHtmlMock;

/**
 * Test class to test rcmail_action_settings_response_edit
 */
class ResponseEditTest extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new \rcmail_action_settings_response_edit();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'settings', 'edit-response');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        $rcmail = \rcmail::get_instance();
        $rcmail->user->save_prefs([
            'compose_responses_static' => [
                ['name' => 'static 1', 'text' => 'Static Response One'],
            ],
        ]);

        self::initDB('responses');

        $responses = $rcmail->get_compose_responses();

        // Test read-only response
        $_GET = ['_id' => $responses[0]['id']];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('responseedit', $output->template);
        $this->assertSame('Edit response', $output->getProperty('pagetitle'));
        $this->assertTrue($output->get_env('readonly'));
        $this->assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        $this->assertTrue(str_contains($result, "rcmail.gui_object('editform', 'form')"));
        $this->assertTrue(str_contains($result, 'tinymce.min.js'));
        $this->assertTrue(str_contains($result, 'Static Response One</textarea>'));

        // Test writable response
        $_GET = ['_id' => $responses[2]['id']];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('responseedit', $output->template);
        $this->assertSame('Edit response', $output->getProperty('pagetitle'));
        $this->assertFalse($output->get_env('readonly'));
        $this->assertTrue(str_contains($result, 'test response 2&lt;/b&gt;&lt;/p&gt;</textarea>'));
    }

    /**
     * Test response_form() method
     */
    public function test_response_form()
    {
        $result = \rcmail_action_settings_response_edit::response_form([]);

        $this->assertTrue(str_starts_with(trim($result), '<table>'));
    }
}
