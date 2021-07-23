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

        $_POST = ['_key' => '1234'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('delete-response', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Successfully deleted.","confirmation");') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.remove_response("1234")') !== false);

        $this->assertSame([], $rcmail->get_compose_responses());
    }
}
