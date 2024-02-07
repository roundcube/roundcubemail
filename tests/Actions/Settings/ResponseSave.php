<?php

/**
 * Test class to test rcmail_action_settings_response_save
 */
class Actions_Settings_ResponseSave extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new rcmail_action_settings_response_save();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'save-response');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $rcmail = rcmail::get_instance();
        $rcmail->user->save_prefs(['compose_responses_static' => []]);

        self::initDB('responses');

        $responses = $rcmail->get_compose_responses();

        // Test updating an existing response
        $_POST = [
            '_id' => $responses[0]['id'],
            '_name' => 'name1',
            '_text' => 'text1',
        ];

        $action->run();

        self::assertSame('edit-response', rcmail::get_instance()->action);
        self::assertSame('successfullysaved', $output->getProperty('message'));

        $response = $rcmail->get_compose_response($responses[0]['id']);

        self::assertSame('name1', $response['name']);
        self::assertSame('text1', $response['data']);
        self::assertTrue(empty($response['is_html']));

        // Test updating an existing response (change format)
        $_POST = [
            '_id' => $responses[0]['id'],
            '_name' => 'name2',
            '_text' => '<p>text2</p>',
            '_is_html' => 1,
        ];

        $action->run();

        self::assertSame('edit-response', rcmail::get_instance()->action);
        self::assertSame('successfullysaved', $output->getProperty('message'));

        $response = $rcmail->get_compose_response($responses[0]['id']);

        self::assertSame('name2', $response['name']);
        self::assertSame('<p>text2</p>', $response['data']);
        self::assertTrue(!empty($response['is_html']));

        // Test adding a response
        $_POST = [
            '_name' => 'aaa',
            '_text' => '<p>text3</p>',
            '_is_html' => 1,
        ];

        $action->run();

        self::assertSame('edit-response', rcmail::get_instance()->action);
        self::assertSame('successfullysaved', $output->getProperty('message'));

        $responses = $rcmail->get_compose_responses();
        $response = $rcmail->get_compose_response($responses[0]['id']);

        self::assertSame('aaa', $responses[0]['name']);
        self::assertSame('<p>text3</p>', $response['data']);
        self::assertTrue(!empty($response['is_html']));

        // TODO: Test error handling
    }
}
