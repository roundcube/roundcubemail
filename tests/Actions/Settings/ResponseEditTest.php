<?php

/**
 * Test class to test rcmail_action_settings_response_edit
 */
class Actions_Settings_ResponseEdit extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new rcmail_action_settings_response_edit();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'edit-response');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $rcmail = rcmail::get_instance();
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

        self::assertSame('responseedit', $output->template);
        self::assertSame('Edit response', $output->getProperty('pagetitle'));
        self::assertTrue($output->get_env('readonly'));
        self::assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        self::assertTrue(strpos($result, "rcmail.gui_object('editform', 'form')") !== false);
        self::assertTrue(strpos($result, 'tinymce.min.js') !== false);
        self::assertTrue(strpos($result, 'Static Response One</textarea>') !== false);

        // Test writable response
        $_GET = ['_id' => $responses[2]['id']];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame('responseedit', $output->template);
        self::assertSame('Edit response', $output->getProperty('pagetitle'));
        self::assertFalse($output->get_env('readonly'));
        self::assertTrue(strpos($result, 'test response 2&lt;/b&gt;&lt;/p&gt;</textarea>') !== false);
    }

    /**
     * Test response_form() method
     */
    public function test_response_form()
    {
        $result = rcmail_action_settings_response_edit::response_form([]);

        self::assertTrue(strpos(trim($result), '<table>') === 0);
    }
}
