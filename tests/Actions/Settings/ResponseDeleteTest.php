<?php

/**
 * Test class to test rcmail_action_settings_response_delete
 */
class Actions_Settings_ResponseDelete extends ActionTestCase
{
    /**
     * Test deleting a response
     */
    public function test_delete_response()
    {
        $action = new rcmail_action_settings_response_delete();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'delete-response');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $rcmail = rcmail::get_instance();
        $rcmail->user->save_prefs(['compose_responses_static' => []]);

        self::initDB('responses');

        $responses = $rcmail->get_compose_responses();

        self::assertCount(2, $responses);

        $rid = $responses[0]['id'];

        // Test successful request
        $_POST = ['_id' => $rid];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('delete-response', $result['action']);
        self::assertTrue(strpos($result['exec'], 'this.display_message("Successfully deleted.","confirmation");') !== false);
        self::assertTrue(strpos($result['exec'], 'this.remove_response("' . $rid . '")') !== false);

        $responses = $rcmail->get_compose_responses();

        self::assertCount(1, $responses);
        self::assertTrue($responses[0]['id'] != $rid);

        // Test error
        $_POST = ['_id' => 'unknown'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('delete-response', $result['action']);
        self::assertTrue(strpos($result['exec'], 'this.display_message("An error occurred while saving.","error"') !== false);

        self::assertCount(1, $rcmail->get_compose_responses());
    }
}
