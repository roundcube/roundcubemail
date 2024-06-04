<?php

namespace Roundcube\Tests\Browser\Contacts;

use Roundcube\Tests\Browser\Bootstrap;
use Roundcube\Tests\Browser\Components\App;
use Roundcube\Tests\Browser\TestCase;

class ContactsTest extends TestCase
{
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        Bootstrap::init_db();
    }

    /**
     * Contacts UI Basics
     */
    public function testContactsUI()
    {
        $this->browse(static function ($browser) {
            $browser->go('addressbook');

            $browser->with(new App(), static function ($browser) {
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
            } else {
                $browser->assertVisible('#contacts-table');
            }

            // Contacts list menu
            if ($browser->isPhone()) {
                $browser->assertToolbarMenu(['select'], []);
            } elseif ($browser->isTablet()) {
                $browser->click('.toolbar-list-button')
                    ->waitFor('#toolbar-list-menu')
                    ->assertVisible('#toolbar-list-menu a.select:not(.disabled)')
                    ->click();
            } else {
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
