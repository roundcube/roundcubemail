<?php

namespace Tests\Browser\Settings;

use Tests\Browser\Components\App;

class Preferences extends \Tests\Browser\TestCase
{
    public function testPreferences()
    {
        $this->browse(function ($browser) {
            $browser->go('settings');

            $browser->with(new App(), function ($browser) {
                $browser->assertObjects(['sectionslist']);
            });

            $browser->assertVisible('#settings-menu li.preferences.selected');

            // On phone/tablet #sections-table is initially hidden
            if (!$browser->isDesktop()) {
                $browser->assertMissing('#sections-table');
                $browser->click('#settings-menu li.preferences');
                $browser->waitFor('#sections-table');
            }

            // Preferences actions
            $browser->with('#sections-table', function($browser) {
                $browser->assertSeeIn('tr.general', 'User Interface');
                $browser->assertSeeIn('tr.mailbox', 'Mailbox View');
                $browser->assertSeeIn('tr.mailview', 'Displaying Messages');
                $browser->assertSeeIn('tr.compose', 'Composing Messages');
                $browser->assertSeeIn('tr.addressbook', 'Contacts');
                $browser->assertSeeIn('tr.folders', 'Special Folders');
                $browser->assertSeeIn('tr.server', 'Server Settings');
            });
        });
    }
}
