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

        $responses = [
            [
                'name' => 'test',
                'text' => '****',
                'format' => 'text',
                'key' => '1234',
            ]
        ];

        $rcmail = rcmail::get_instance();
        $rcmail->user->save_prefs(['compose_responses' => $responses]);

        $_GET = ['_key' => '1234'];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('responseedit', $output->template);
        $this->assertSame('Edit response', $output->getProperty('pagetitle'));
        $this->assertSame(false, $output->get_env('readonly'));
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
        $this->assertTrue(strpos($result, "rcmail.gui_object('editform', 'form')") !== false);
        $this->assertTrue(strpos($result, "****") !== false);
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
