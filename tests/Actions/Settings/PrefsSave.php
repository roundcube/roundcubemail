<?php

/**
 * Test class to test rcmail_action_settings_prefs_save
 *
 * @package Tests
 */
class Actions_Settings_PrefsSave extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_settings_prefs_save;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'save-prefs');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // TODO: Test all sections
        $_POST['_section'] = 'general';

        $action->run();

        $this->assertSame('edit-prefs', rcmail::get_instance()->action);
        $this->assertSame('successfullysaved', $output->getProperty('message'));
    }

    /**
     * Test prefs_input() method
     */
    function test_prefs_input()
    {
        $action = new rcmail_action_settings_prefs_save;

        $_POST = ['_test' => 'test'];

        rcmail::get_instance()->config->set('test', null);

        $this->assertSame(null, $action->prefs_input('unset', '/test/'));
        $this->assertSame('test', $action->prefs_input('test', '/^test/'));
        $this->assertSame(null, $action->prefs_input('test', '/^a/'));
    }
}
