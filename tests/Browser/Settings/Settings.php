<?php

namespace Tests\Browser\Settings;

class Settings extends \Tests\Browser\DuskTestCase
{
    public function testSettings()
    {
        $this->browse(function ($browser) {
            $this->go('settings');

            // task should be set to 'settings'
            $this->assertEnvEquals('task', 'settings');

            $browser->assertSeeIn('#layout-sidebar .header', 'Settings');

            // Sidebar menu
            $browser->with('#settings-menu', function($browser) {
                $browser->assertSeeIn('li.preferences', 'Preferences');
                $browser->assertSeeIn('li.folders', 'Folders');
                $browser->assertSeeIn('li.identities', 'Identities');
                $browser->assertSeeIn('li.responses', 'Responses');
            });

            // Task menu
            $this->assertTaskMenu('settings');
        });
    }
}
