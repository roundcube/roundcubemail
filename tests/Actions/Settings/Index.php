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
        $this->assertContains('elastic', $result);
    }

    /**
     * Test settings_tabs() method
     */
    function test_settings_tabs()
    {
        $result = rcmail_action_settings_index::settings_tabs([]);
        $nodes  = getHTMLNodes($result, "//span[@id='settingstabpreferences']");

        $this->assertCount(1, $nodes);
        $this->assertSame('preferences selected', $nodes[0]->getAttribute('class'));
        $this->assertCount(1, $nodes[0]->childNodes);
        $link = $nodes[0]->firstChild;
        $this->assertSame('a', $link->nodeName);
        $this->assertSame('Edit user preferences', $link->getAttribute('title'));
        $this->assertStringEndsWith('?_task=settings&_action=preferences', $link->getAttribute('href'));
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

    /**
     * Test attach_images() method
     */
    function test_attach_images()
    {
        $result = rcmail_action_settings_index::attach_images('<p>test</p>', 'identity');

        // TODO: test image replacement

        $this->assertSame('<p>test</p>', $result);
    }

    /**
     * Test wash_html() method
     */
    function test_wash_html()
    {
        $result = rcmail_action_settings_index::wash_html('<p>test</p>');

        $this->assertSame('<p>test</p>', $result);
    }
}
