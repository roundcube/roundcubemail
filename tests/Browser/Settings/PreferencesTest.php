<?php

namespace Roundcube\Tests\Browser\Settings;

use Roundcube\Tests\Browser\Components\App;
use Roundcube\Tests\Browser\TestCase;

class PreferencesTest extends TestCase
{
    public function testPreferences()
    {
        $this->browse(static function ($browser) {
            $browser->go('settings');

            $browser->with(new App(), static function ($browser) {
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
            $browser->with('#sections-table', static function ($browser) {
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
