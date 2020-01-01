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
    public function testAddressbook()
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

            $browser->assertSeeIn('#layout-sidebar .header', 'Groups');

            // Groups/Addressbooks list
            $browser->assertVisible('#directorylist');
            $browser->assertSeeIn('#directorylist li:first-child', 'Personal Addresses');
            $browser->assertMissing('#directorylist .treetoggle.expanded');

            // Contacts list
            $browser->assertVisible('#contacts-table');

            // Contacts list menu
            $browser->assertVisible('#toolbar-list-menu a.select:not(.disabled)');

            // Toolbar menu
            $browser->with('#toolbar-menu', function($browser) {
                $browser->assertVisible('a.create:not(.disabled)');
                $browser->assertVisible('a.print.disabled');
                $browser->assertVisible('a.delete.disabled');
                $browser->assertVisible('a.search:not(.disabled)');
                $browser->assertVisible('a.import:not(.disabled)');
                $browser->assertVisible('a.export:not(.disabled)');
                $browser->assertVisible('a.more.disabled');
            });

            // Contact frame
            $browser->assertVisible('#contact-frame');

            // Task menu
            $browser->with('#taskmenu', function($browser) {
                $browser->assertVisible('a.compose:not(.disabled):not(.selected)');
                $browser->assertVisible('a.mail:not(.disabled):not(.selected)');
                $browser->assertVisible('a.contacts.selected');
                $browser->assertVisible('a.settings:not(.disabled):not(.selected)');
                $browser->assertVisible('a.about:not(.disabled):not(.selected)');
                $browser->assertVisible('a.logout:not(.disabled):not(.selected)');
            });
        });
    }
}
