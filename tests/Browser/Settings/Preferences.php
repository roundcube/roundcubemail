<?php

namespace Tests\Browser\Settings;

class Preferences extends \Tests\Browser\DuskTestCase
{
    public function testPreferences()
    {
        $this->browse(function ($browser) {
            $this->go('settings');

            $objects = $this->getObjects();

            $this->assertContains('sectionslist', $objects);

            $browser->assertVisible('#settings-menu li.preferences.selected');

            // Preferences actions
            $browser->assertVisible('#sections-table');
            $browser->assertSeeIn('#sections-table tr.general', 'User Interface');
            $browser->assertSeeIn('#sections-table tr.mailbox', 'Mailbox View');
            $browser->assertSeeIn('#sections-table tr.mailview', 'Displaying Messages');
            $browser->assertSeeIn('#sections-table tr.compose', 'Composing Messages');
            $browser->assertSeeIn('#sections-table tr.addressbook', 'Contacts');
            $browser->assertSeeIn('#sections-table tr.folders', 'Special Folders');
            $browser->assertSeeIn('#sections-table tr.server', 'Server Settings');
        });
    }
}
