<?php

/**
 * Test class to test rcmail_action_settings_response_edit
 *
 * @package Tests
 */
class Actions_Settings_ResponseEdit extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_settings_response_edit;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'edit-response');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $rcmail = rcmail::get_instance();
        $rcmail->user->save_prefs([
            'compose_responses_static' => [
                ['name' => 'static 1', 'text' => 'Static Response One'],
            ]
        ]);

        self::initDB('responses');

        $responses = $rcmail->get_compose_responses();

        // Test read-only response
        $_GET = ['_id' => $responses[0]['id']];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('responseedit', $output->template);
        $this->assertSame('Edit response', $output->getProperty('pagetitle'));
        $this->assertSame(true, $output->get_env('readonly'));
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
        $this->assertTrue(strpos($result, "rcmail.gui_object('editform', 'form')") !== false);
        $this->assertTrue(strpos($result, "tinymce.min.js") !== false);
        $this->assertTrue(strpos($result, "Static Response One</textarea>") !== false);

        // Test writable response
        $_GET = ['_id' => $responses[2]['id']];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('responseedit', $output->template);
        $this->assertSame('Edit response', $output->getProperty('pagetitle'));
        $this->assertSame(false, $output->get_env('readonly'));
        $this->assertTrue(strpos($result, "test response 2&lt;/b&gt;&lt;/p&gt;</textarea>") !== false);
    }

    /**
     * Test response_form() method
     */
    function test_response_form()
    {
        $result = rcmail_action_settings_response_edit::response_form([]);

        $this->assertTrue(strpos(trim($result), '<table>') === 0);
    }
}
