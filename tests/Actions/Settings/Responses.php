<?php

/**
 * Test class to test rcmail_action_settings_responses
 *
 * @package Tests
 */
class Actions_Settings_Responses extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_settings_responses;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'responses');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('responses', $output->template);
        $this->assertSame('Responses', $output->getProperty('pagetitle'));
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
        $this->assertRegExp('/list(.min)?.js/', $result);
    }

    /**
     * Test inserting a response
     */
    function test_run_insert()
    {
        $action = new rcmail_action_settings_responses;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'responses');

        $rcmail = rcmail::get_instance();
        $rcmail->user->save_prefs(['compose_responses' => []]);

        $_POST = [
            '_insert' => 1,
            '_name' => 'insert',
            '_text' => 'insert-text',
        ];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Successfully saved.","confirmation");') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.add_response_item({') !== false);

        $responses = $rcmail->get_compose_responses();

        $this->assertCount(1, $responses);
        $this->assertSame('insert', $responses[0]['name']);
        $this->assertSame('insert-text', $responses[0]['text']);
    }

    /**
     * Test responses_list() method
     */
    function test_responses_list()
    {
        $rcmail = rcmail::get_instance();
        $rcmail->user->save_prefs(['compose_responses' => []]);

        $action = new rcmail_action_settings_responses;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'responses');

        $result = $action->responses_list([]);
        $expected = '<table id="rcmresponseslist"><thead><tr><th class="name">Display Name</th></tr></thead><tbody></tbody></table>';

        $this->assertSame($expected, $result);
    }
}
