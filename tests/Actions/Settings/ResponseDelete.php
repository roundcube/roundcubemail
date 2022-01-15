<?php

/**
 * Test class to test rcmail_action_settings_response_delete
 *
 * @package Tests
 */
class Actions_Settings_ResponseDelete extends ActionTestCase
{
    /**
     * Test deleting a response
     */
    function test_delete_response()
    {
        $action = new rcmail_action_settings_response_delete;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'delete-response');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $rcmail = rcmail::get_instance();
        $rcmail->user->save_prefs(['compose_responses_static' => []]);

        self::initDB('responses');

        $responses = $rcmail->get_compose_responses();

        $this->assertCount(2, $responses);

        $rid = $responses[0]['id'];

        // Test successful request
        $_POST = ['_id' => $rid];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('delete-response', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Successfully deleted.","confirmation");') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.remove_response("' . $rid .'")') !== false);

        $responses = $rcmail->get_compose_responses();

        $this->assertCount(1, $responses);
        $this->assertTrue($responses[0]['id'] != $rid);

        // Test error
        $_POST = ['_id' => 'unknown'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('delete-response', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("An error occurred while saving.","error"') !== false);

        $this->assertCount(1, $rcmail->get_compose_responses());
    }
}
