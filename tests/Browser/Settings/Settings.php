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
            $browser->assertVisible('#settings-menu');
            $browser->assertSeeIn('#settings-menu li.preferences', 'Preferences');
            $browser->assertSeeIn('#settings-menu li.folders', 'Folders');
            $browser->assertSeeIn('#settings-menu li.identities', 'Identities');
            $browser->assertSeeIn('#settings-menu li.responses', 'Responses');
        });
    }
}
