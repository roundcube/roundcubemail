<?php

namespace Tests\Browser\Contacts;

use Tests\Browser\Components\App;

class ContactsTest extends \Tests\Browser\TestCase
{
    public static function setUpBeforeClass(): void
    {
        \bootstrap::init_db();
    }

    /**
     * Contacts UI Basics
     */
    public function testContactsUI()
    {
        $this->browse(function ($browser) {
            $browser->go('addressbook');

            $browser->with(new App(), function ($browser) {
                // check task
                $browser->assertEnv('task', 'addressbook');

                // these objects should be there always
                $browser->assertObjects(['qsearchbox', 'folderlist', 'contactslist', 'countdisplay']);
            });

            if (!$browser->isDesktop()) {
                $browser->assertMissing('#directorylist');
                $browser->click('a.back-sidebar-button');
            }

            // Groups/Addressbooks list
            $browser->assertVisible('#directorylist');
            $browser->assertSeeIn('#directorylist li:first-child', 'Personal Addresses');
            $browser->assertMissing('#directorylist .treetoggle.expanded');

            // Contacts list
            if (!$browser->isDesktop()) {
                $browser->assertMissing('#contacts-table');
                $browser->click('#directorylist li:first-child');
                $browser->waitFor('#contacts-table');
            }
            else {
                $browser->assertVisible('#contacts-table');
            }

            // Contacts list menu
            if ($browser->isPhone()) {
                $browser->assertToolbarMenu(['select'], []);
            }
            else if ($browser->isTablet()) {
                $browser->click('.toolbar-list-button')
                    ->waitFor('#toolbar-list-menu')
                    ->assertVisible('#toolbar-list-menu a.select:not(.disabled)')
                    ->click();
            }
            else {
                $browser->assertVisible('#toolbar-list-menu a.select:not(.disabled)');
            }

            // Toolbar menu
            $browser->assertToolbarMenu(
                ['create', 'search', 'import', 'export'], // active items
                ['print', 'delete', 'more'], // inactive items
            );

            // Contact frame
            if (!$browser->isPhone()) {
                $browser->assertVisible('#contact-frame');
            }

            // Task menu
            $browser->assertTaskMenu('contacts');
        });
    }
}
