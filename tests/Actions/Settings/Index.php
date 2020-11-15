<?php

/**
 * Test class to test rcmail_action_settings_index
 *
 * @package Tests
 */
class Actions_Settings_Index extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_settings_index;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'preferences');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $action->run();

        $result = $output->getOutput();

        $this->assertSame('Preferences', $output->getProperty('pagetitle'));
    }

    /**
     * Test sections_list() method
     */
    function test_sections_list()
    {
        $result = rcmail_action_settings_index::sections_list([]);
        $this->assertTrue(strpos($result, '<table id="rcmsectionslist">') === 0);
    }

    /**
     * Test user_prefs() method
     */
    function test_user_prefs()
    {
        $result = rcmail_action_settings_index::user_prefs('general');
        $this->assertSame('general', $result[0]['general']['id']);
    }

    /**
     * Test get_skins() method
     */
    function test_get_skins()
    {
        $result = rcmail_action_settings_index::get_skins();
        sort($result);
        $this->assertSame(['classic', 'elastic', 'larry'], $result);
    }

    /**
     * Test settings_tabs() method
     */
    function test_settings_tabs()
    {
        $result = rcmail_action_settings_index::settings_tabs([]);
        $this->assertTrue(strpos($result, '<span id="settingstabpreferences" class="preferences selected"><a title="Edit user preferences" href="./?_task=settings&amp;_action=preferences"') === 0);
    }

    /**
     * Test timezone_label() method
     */
    function test_timezone_label()
    {
        $result = rcmail_action_settings_index::timezone_label('Europe/Warsaw');
        $this->assertSame('Europe/Warsaw', $result);
    }

    /**
     * Test timezone_standard_time_label() method
     */
    function test_timezone_standard_time_data()
    {
        $result = rcmail_action_settings_index::timezone_standard_time_data('UTC');
        $this->assertSame('+00:00', $result['offset']);
    }
}
