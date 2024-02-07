<?php

/**
 * Test class to test rcmail_action_settings_response_get
 */
class Actions_Settings_ResponseGet extends ActionTestCase
{
    /**
     * Fetching a response
     */
    public function test_get_response()
    {
        $action = new rcmail_action_settings_response_get();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'response-get');

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

        // Test a static response (plain text converted to html)

        $_GET = ['_id' => $responses[0]['id'], '_is_html' => 1];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('response-get', $result['action']);
        self::assertTrue(preg_match('/this\.insert_response\(([^)]+)\);/', $result['exec'], $m) === 1);
        $data = json_decode($m[1], true);
        self::assertSame($responses[0]['id'], $data['id']);
        self::assertSame('static 1', $data['name']);
        self::assertTrue($data['is_html']);
        self::assertSame('<div class="pre">Static Response One</div>', $data['data']);

        // Test unknown identifier

        $_GET = ['_id' => 'unknown'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('response-get', $result['action']);
        self::assertSame('', $result['exec']);

        // Test a normal response (html converted to text)

        $_GET = ['_id' => $responses[2]['id'], '_is_html' => 0];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('response-get', $result['action']);
        self::assertTrue(preg_match('/this\.insert_response\(([^)]+)\);/', $result['exec'], $m) === 1);
        $data = json_decode($m[1], true);
        self::assertSame($responses[2]['id'], $data['id']);
        self::assertSame('response 2', $data['name']);
        self::assertFalse($data['is_html']);
        self::assertSame('test response 2', $data['data']);
    }
}
