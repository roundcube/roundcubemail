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

        self::initDB('responses');

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('responses', $output->template);
        $this->assertSame('Responses', $output->getProperty('pagetitle'));
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
        $this->assertTrue(stripos($result, "<table ") !== false);
        $this->assertMatchesRegularExpression('/list(.min)?.js/', $result);
    }

    /**
     * Test responses_list() method
     */
    function test_responses_list()
    {
        $rcmail = rcmail::get_instance();
        $rcmail->user->save_prefs([
            'compose_responses_static' => [
                ['name' => 'static 1', 'text' => 'Static Response One'],
            ]
        ]);

        self::initDB('responses');

        $action = new rcmail_action_settings_responses;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'responses');

        $result = $action->responses_list([]);
        $expected = '<table id="rcmresponseslist"><thead><tr><th class="name">Display Name</th></tr></thead><tbody>'
            . '<tr id="rcmrowstatic-95b793e15a90ad8b"><td class="name">static 1</td></tr>'
            . '<tr id="rcmrow1"><td class="name">response 1</td></tr>'
            . '<tr id="rcmrow2"><td class="name">response 2</td></tr>'
            . '</tbody></table>';

        $this->assertSame($expected, $result);
    }
}
