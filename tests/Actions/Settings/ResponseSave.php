<?php

/**
 * Test class to test rcmail_action_settings_response_save
 *
 * @package Tests
 */
class Actions_Settings_ResponseSave extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_settings_response_save;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'save-response');

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

        $_POST = [
            '_key' => '1234',
            '_name' => 'test1',
            '_text' => 'text1',
        ];

        $action->run();

        $this->assertSame('edit-response', rcmail::get_instance()->action);
        $this->assertSame('successfullysaved', $output->getProperty('message'));

        $responses = $rcmail->get_compose_responses();

        $this->assertCount(1, $responses);
        $this->assertSame('test1', $responses[0]['name']);
        $this->assertSame('text1', $responses[0]['text']);

        // TODO: Test error handling and new response creation
    }
}
