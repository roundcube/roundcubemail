<?php

namespace Tests\Browser\Settings;

use Tests\Browser\Components\App;

class PreferencesTest extends \Tests\Browser\TestCase
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
                $browser->assertMissing('#sections-table')
                    ->click('#settings-menu li.preferences')
                    ->waitFor('#sections-table');
            }
            // Preferences actions
            $browser->with('#sections-table', function($browser) {
                $browser->assertSeeIn('tr.general', 'User Interface')
                    ->assertSeeIn('tr.mailbox', 'Mailbox View')
                    ->assertSeeIn('tr.mailview', 'Displaying Messages')
                    ->assertSeeIn('tr.compose', 'Composing Messages')
                    ->assertSeeIn('tr.addressbook', 'Contacts')
                    ->assertSeeIn('tr.folders', 'Special Folders')
                    ->assertSeeIn('tr.server', 'Server Settings');
            });
        });
    }
}
