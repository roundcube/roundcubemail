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
            $browser->with('#taskmenu', function($browser) {
                $browser->assertVisible('a.compose:not(.disabled):not(.selected)');
                $browser->assertVisible('a.mail:not(.disabled):not(.selected)');
                $browser->assertVisible('a.contacts:not(.disabled):not(.selected)');
                $browser->assertVisible('a.settings:not(.disabled).selected');
                $browser->assertVisible('a.about:not(.disabled):not(.selected)');
                $browser->assertVisible('a.logout:not(.disabled):not(.selected)');
            });
        });
    }
}
