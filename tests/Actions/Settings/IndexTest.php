<?php

namespace Roundcube\Tests\Actions\Settings;

use Roundcube\Tests\ActionTestCase;

use function Roundcube\Tests\getHTMLNodes;

/**
 * Test class to test rcmail_action_settings_index
 */
class IndexTest extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new \rcmail_action_settings_index();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'settings', 'preferences');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        $action->run();

        $result = $output->getOutput();

        $this->assertSame('Preferences', $output->getProperty('pagetitle'));
    }

    /**
     * Test sections_list() method
     */
    public function test_sections_list()
    {
        $result = \rcmail_action_settings_index::sections_list([]);
        $this->assertTrue(str_starts_with($result, '<table id="rcmsectionslist"'));
    }

    /**
     * Test user_prefs() method
     */
    public function test_user_prefs()
    {
        $result = \rcmail_action_settings_index::user_prefs('general');
        $this->assertSame('general', $result[0]['general']['id']);
    }

    /**
     * Test user_prefs() method - show_photo_urls in the address book section
     */
    public function test_user_prefs_show_photo_urls()
    {
        $rcmail = \rcmail::get_instance();

        foreach ([true, false] as $value) {
            $rcmail->config->set('show_photo_urls', $value);

            $result = \rcmail_action_settings_index::user_prefs('addressbook');
            $option = $result[0]['addressbook']['blocks']['main']['options']['show_photo_urls'];
            $input = getHTMLNodes($option['content'], "//input[@name='_show_photo_urls']");

            $this->assertSame('<label for="rcmfd_show_photo_urls">Allow external photos</label>', $option['title']);
            $this->assertCount(1, $input);
            $this->assertSame('checkbox', $input[0]->getAttribute('type'));
            $this->assertSame($value, $input[0]->hasAttribute('checked'));
        }

        // Not shown if the user must not change it
        $rcmail->config->set('dont_override', ['show_photo_urls']);

        $result = \rcmail_action_settings_index::user_prefs('addressbook');

        $this->assertArrayNotHasKey('show_photo_urls', $result[0]['addressbook']['blocks']['main']['options']);

        $rcmail->config->set('dont_override', []);
    }

    /**
     * Test get_skins() method
     */
    public function test_get_skins()
    {
        $result = \rcmail_action_settings_index::get_skins();
        $this->assertContains('elastic', $result);
    }

    /**
     * Test settings_tabs() method
     */
    public function test_settings_tabs()
    {
        $result = \rcmail_action_settings_index::settings_tabs([]);
        $nodes = getHTMLNodes($result, "//span[@id='settingstabpreferences']");

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
    public function test_timezone_label()
    {
        $result = \rcmail_action_settings_index::timezone_label('Europe/Warsaw');
        $this->assertSame('Europe/Warsaw', $result);
    }

    /**
     * Test timezone_standard_time_label() method
     */
    public function test_timezone_standard_time_data()
    {
        $result = \rcmail_action_settings_index::timezone_standard_time_data('UTC');
        $this->assertSame('+00:00', $result['offset']);
    }

    /**
     * Test attach_images() method
     */
    public function test_attach_images()
    {
        $result = \rcmail_action_settings_index::attach_images('<p>test</p>', 'identity');

        // TODO: test image replacement

        $this->assertSame('<p>test</p>', $result);
    }

    /**
     * Test wash_html() method
     */
    public function test_wash_html()
    {
        $result = \rcmail_action_settings_index::wash_html('<p>test</p>');

        $this->assertSame('<p>test</p>', $result);

        $resultLink = \rcmail_action_settings_index::wash_html('<a href="https://roundcube.net" target="_blank">test</a>');

        $this->assertSame('<a href="https://roundcube.net" target="_blank">test</a>', $resultLink);
    }
}
