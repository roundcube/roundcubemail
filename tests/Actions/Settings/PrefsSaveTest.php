<?php

/**
 * Test class to test rcmail_action_settings_prefs_save
 */
class Actions_Settings_PrefsSave extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new rcmail_action_settings_prefs_save();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'save-prefs');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        // TODO: Test all sections
        $_POST['_section'] = 'general';

        $action->run();

        self::assertSame('edit-prefs', rcmail::get_instance()->action);
        self::assertSame('successfullysaved', $output->getProperty('message'));
    }

    /**
     * Test prefs_input() method
     */
    public function test_prefs_input()
    {
        $action = new rcmail_action_settings_prefs_save();

        $_POST = ['_test' => 'test'];

        rcmail::get_instance()->config->set('test', null);

        self::assertNull($action->prefs_input('unset', '/test/'));
        self::assertSame('test', $action->prefs_input('test', '/^test/'));
        self::assertNull($action->prefs_input('test', '/^a/'));
    }
}
