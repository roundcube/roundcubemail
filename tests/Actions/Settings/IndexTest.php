<?php

/**
 * Test class to test rcmail_action_settings_index
 */
class Actions_Settings_Index extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new rcmail_action_settings_index();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'preferences');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $action->run();

        $result = $output->getOutput();

        self::assertSame('Preferences', $output->getProperty('pagetitle'));
    }

    /**
     * Test sections_list() method
     */
    public function test_sections_list()
    {
        $result = rcmail_action_settings_index::sections_list([]);
        self::assertTrue(strpos($result, '<table id="rcmsectionslist"') === 0);
    }

    /**
     * Test user_prefs() method
     */
    public function test_user_prefs()
    {
        $result = rcmail_action_settings_index::user_prefs('general');
        self::assertSame('general', $result[0]['general']['id']);
    }

    /**
     * Test get_skins() method
     */
    public function test_get_skins()
    {
        $result = rcmail_action_settings_index::get_skins();
        self::assertContains('elastic', $result);
    }

    /**
     * Test settings_tabs() method
     */
    public function test_settings_tabs()
    {
        $result = rcmail_action_settings_index::settings_tabs([]);
        $nodes = getHTMLNodes($result, "//span[@id='settingstabpreferences']");

        self::assertCount(1, $nodes);
        self::assertSame('preferences selected', $nodes[0]->getAttribute('class'));
        self::assertCount(1, $nodes[0]->childNodes);
        $link = $nodes[0]->firstChild;
        self::assertSame('a', $link->nodeName);
        self::assertSame('Edit user preferences', $link->getAttribute('title'));
        self::assertStringEndsWith('?_task=settings&_action=preferences', $link->getAttribute('href'));
    }

    /**
     * Test timezone_label() method
     */
    public function test_timezone_label()
    {
        $result = rcmail_action_settings_index::timezone_label('Europe/Warsaw');
        self::assertSame('Europe/Warsaw', $result);
    }

    /**
     * Test timezone_standard_time_label() method
     */
    public function test_timezone_standard_time_data()
    {
        $result = rcmail_action_settings_index::timezone_standard_time_data('UTC');
        self::assertSame('+00:00', $result['offset']);
    }

    /**
     * Test attach_images() method
     */
    public function test_attach_images()
    {
        $result = rcmail_action_settings_index::attach_images('<p>test</p>', 'identity');

        // TODO: test image replacement

        self::assertSame('<p>test</p>', $result);
    }

    /**
     * Test wash_html() method
     */
    public function test_wash_html()
    {
        $result = rcmail_action_settings_index::wash_html('<p>test</p>');

        self::assertSame('<p>test</p>', $result);
    }
}
