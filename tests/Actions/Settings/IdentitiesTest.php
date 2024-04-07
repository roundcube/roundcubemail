<?php

/**
 * Test class to test rcmail_action_settings_identities
 */
class Actions_Settings_Identities extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new rcmail_action_settings_identities();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'identities');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        self::initDB('identities');

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame('identities', $output->template);
        self::assertSame('Identities', $output->getProperty('pagetitle'));
        self::assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        self::assertTrue(strpos($result, 'test@example.org') !== false);
        self::assertMatchesRegularExpression('/list(.min)?.js/', $result);
    }

    /**
     * Test identities_list() method
     */
    public function test_identities_list()
    {
        $action = new rcmail_action_settings_identities();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'identities');

        self::initDB('identities');

        $result = $action->identities_list([]);

        $expected = '<table id="rcmIdentitiesList"><thead><tr><th class="mail">Mail</th></tr></thead>'
            . '<tbody><tr id="rcmrow1"><td class="mail">test &lt;test@example.com&gt;</td></tr>'
            . '<tr id="rcmrow2"><td class="mail">test &lt;test@example.org&gt;</td></tr></tbody></table>';

        self::assertSame($expected, $result);
    }
}
