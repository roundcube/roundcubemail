<?php

namespace Roundcube\Tests\Actions\Settings;

use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_settings_prefs_save
 */
class PrefsSaveTest extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new \rcmail_action_settings_prefs_save();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'settings', 'save-prefs');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        // TODO: Test all sections
        $_POST['_section'] = 'general';

        $action->run();

        $this->assertSame('edit-prefs', \rcmail::get_instance()->action);
        $this->assertSame('successfullysaved', $output->getProperty('message'));
    }

    /**
     * Test run() method - show_photo_urls in the address book section
     */
    public function test_run_show_photo_urls()
    {
        $action = new \rcmail_action_settings_prefs_save();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'settings', 'save-prefs');
        $rcmail = \rcmail::get_instance();

        // Catch the preferences on their way to the database, without writing them
        $prefs = null;
        $hook = static function ($args) use (&$prefs) {
            $prefs = $args['prefs'];
            $args['abort'] = true;
            return $args;
        };

        $rcmail->plugins->register_hook('preferences_update', $hook);

        $_POST = ['_section' => 'addressbook', '_show_photo_urls' => '1'];
        $action->run();

        $this->assertTrue($prefs['show_photo_urls']);

        $_POST = ['_section' => 'addressbook'];
        $action->run();

        $this->assertFalse($prefs['show_photo_urls']);

        // The configured value is kept if the user must not change it
        $rcmail->config->set('dont_override', ['show_photo_urls']);
        $rcmail->config->set('show_photo_urls', true);

        $action->run();

        $this->assertTrue($prefs['show_photo_urls']);

        $rcmail->plugins->unregister_hook('preferences_update', $hook);
        $rcmail->config->set('dont_override', []);
        $rcmail->config->set('show_photo_urls', false);
    }

    /**
     * Test prefs_input() method
     */
    public function test_prefs_input()
    {
        $action = new \rcmail_action_settings_prefs_save();

        $_POST = ['_test' => 'test'];

        \rcmail::get_instance()->config->set('test', null);

        $this->assertNull($action->prefs_input('unset', '/test/'));
        $this->assertSame('test', $action->prefs_input('test', '/^test/'));
        $this->assertNull($action->prefs_input('test', '/^a/'));
    }
}
