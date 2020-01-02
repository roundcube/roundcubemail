<?php

namespace Tests\Browser\Contacts;

class Contacts extends \Tests\Browser\DuskTestCase
{
    protected function setUp()
    {
        parent::setUp();

        \bootstrap::init_db();
    }

    /**
     * Contacts UI Basics
     */
    public function testContactsUI()
    {
        $this->browse(function ($browser) {
            $this->go('addressbook');

            // check task
            $this->assertEnvEquals('task', 'addressbook');

            $objects = $this->getObjects();

            // these objects should be there always
            $this->assertContains('qsearchbox', $objects);
            $this->assertContains('folderlist', $objects);
            $this->assertContains('contactslist', $objects);
            $this->assertContains('countdisplay', $objects);

            if (!$this->isDesktop()) {
                $browser->assertMissing('#directorylist');
                $browser->click('a.back-sidebar-button');
            }

            $browser->assertSeeIn('#layout-sidebar .header', 'Groups');

            // Groups/Addressbooks list
            $browser->assertVisible('#directorylist');
            $browser->assertSeeIn('#directorylist li:first-child', 'Personal Addresses');
            $browser->assertMissing('#directorylist .treetoggle.expanded');

            // Contacts list
            if (!$this->isDesktop()) {
                $browser->assertMissing('#contacts-table');
                $browser->click('#directorylist li:first-child');
                $browser->waitFor('#contacts-table');
            }
            else {
                $browser->assertVisible('#contacts-table');
            }

            // Contacts list menu
            if ($this->isPhone()) {
                $this->assertToolbarMenu(['select'], []);
            }
            else if ($this->isTablet()) {
                $browser->click('.toolbar-list-button');
                $browser->assertVisible('#toolbar-list-menu a.select:not(.disabled)');
                $browser->click();
            }
            else {
                $browser->assertVisible('#toolbar-list-menu a.select:not(.disabled)');
            }

            // Toolbar menu
            $this->assertToolbarMenu(
                ['create', 'search', 'import', 'export'], // active items
                ['print', 'delete', 'more'], // inactive items
            );

            // Contact frame
            if (!$this->isPhone()) {
                $browser->assertVisible('#contact-frame');
            }

            // Task menu
            $this->assertTaskMenu('contacts');
        });
    }
}
