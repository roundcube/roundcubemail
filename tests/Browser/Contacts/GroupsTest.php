<?php

namespace Tests\Browser\Contacts;

use Tests\Browser\Components\Dialog;
use Tests\Browser\Components\Popupmenu;

class GroupsTest extends \Tests\Browser\TestCase
{
    public static function setUpBeforeClass(): void
    {
        \bootstrap::init_db();
    }

    /**
     * Contact groups UI basics
     */
    public function testGroups()
    {
        $this->browse(function ($browser) {
            $browser->go('addressbook');

            if (!$browser->isDesktop()) {
                $browser->assertMissing('#directorylist');
                $browser->click('a.back-sidebar-button');
            }

            // Groups/Addressbooks list
            $browser->assertVisible('#directorylist');
            $browser->assertSeeIn('#directorylist li:first-child', 'Personal Addresses');

            $browser->assertSeeIn('#layout-sidebar .header', 'Groups');

            $browser->click('#layout-sidebar .header .sidebar-menu');

            $browser->with(new Popupmenu('groupoptions-menu'), function ($browser) {
                // Note: These are button class names, not action names
                $active = ['create'];
                $disabled = ['group.rename', 'group.delete', 'search', 'search.delete'];

                $browser->assertMenuState($active, $disabled);
                $browser->closeMenu();
            });
        });
    }

    /**
     * Contact group creation
     */
    public function testGroupCreate()
    {
        $this->browse(function ($browser) {
            $browser->go('addressbook');

            if (!$browser->isDesktop()) {
                $browser->click('a.back-sidebar-button');
            }

            $browser->click('#layout-sidebar .header .sidebar-menu');

            $browser->with(new Popupmenu('groupoptions-menu'), function ($browser) {
                $browser->clickMenuItem('create');
            });

            $browser->with(new Dialog(), function ($browser) {
                $browser->assertDialogTitle('Create new group')
                    ->assertButton('save.mainaction', 'Save')
                    ->assertButton('cancel', 'Cancel')
                    ->assertFocused('@content input.form-control')
                    ->type('@content input.form-control', 'New Group')
                    ->clickButton('save');
            });

            $browser->with('#directorylist', function ($browser) {
                $browser->waitFor('li:first-child ul.groups')
                    ->assertVisible('.treetoggle.expanded')
                    ->assertElementsCount('ul.groups > li.contactgroup', 1)
                    ->assertSeeIn('ul.groups > li.contactgroup', 'New Group');

                // Test expand toggle
                $browser->click('.treetoggle.expanded')
                    ->assertMissing('ul.groups')
                    ->click('.treetoggle.collapsed')
                    ->assertSeeIn('ul.groups > li.contactgroup', 'New Group');
            });
        });
    }

    /**
     * Contact group rename
     *
     * @depends testGroupCreate
     */
    public function testGroupRename()
    {
        $this->browse(function ($browser) {
            $browser->go('addressbook');

            if (!$browser->isDesktop()) {
                $browser->click('a.back-sidebar-button');
            }

            $browser->click('#directorylist ul.groups > li:first-child');

            if (!$browser->isDesktop()) {
                $browser->click('a.back-sidebar-button');
            }

            $browser->click('#layout-sidebar .header .sidebar-menu');

            $browser->with(new Popupmenu('groupoptions-menu'), function ($browser) {
                $browser->clickMenuItem('group.rename');
            });

            $browser->with(new Dialog(), function ($browser) {
                $browser
                    ->assertDialogTitle('Rename group')
                    ->assertButton('save.mainaction', 'Save')
                    ->assertButton('cancel', 'Cancel')
                    ->assertFocused('@content input.form-control')
                    ->assertValue('@content input.form-control', 'New Group')
                    ->type('@content input.form-control', 'Renamed')
                    ->clickButton('save');
            });

            $browser->with('#directorylist', function ($browser) {
                $browser->waitFor('li:first-child ul.groups')
                    ->assertVisible('.treetoggle.expanded')
                    ->assertElementsCount('ul.groups > li.contactgroup', 1)
                    ->assertSeeIn('ul.groups > li.contactgroup', 'Renamed');

                // Test if expand toggle is still working
                $browser->click('.treetoggle.expanded')
                    ->assertMissing('ul.groups')
                    ->click('.treetoggle.collapsed')
                    ->assertSeeIn('ul.groups > li.contactgroup', 'Renamed');
            });
        });
    }

    /**
     * Contact group deletion
     *
     * @depends testGroupRename
     */
    public function testGroupDelete()
    {
        $this->browse(function ($browser) {
            $browser->go('addressbook');

            if (!$browser->isDesktop()) {
                $browser->click('a.back-sidebar-button');
            }

            $browser->click('#directorylist ul.groups > li:first-child');

            if (!$browser->isDesktop()) {
                $browser->click('a.back-sidebar-button');
            }

            $browser->click('#layout-sidebar .header .sidebar-menu');

            $browser->with(new Popupmenu('groupoptions-menu'), function ($browser) {
                $browser->clickMenuItem('group.delete');
            });

            $browser->with(new Dialog(), function ($browser) {
                $browser
                    ->assertDialogTitle('Are you sure...')
                    ->assertDialogContent('Do you really want to delete selected group?')
                    ->assertButton('delete.mainaction', 'Delete')
                    ->assertButton('cancel', 'Cancel')
                    ->clickButton('delete');
            });

            $browser->with('#directorylist', function ($browser) {
                $browser->assertMissing('.treetoggle.expanded')
                    ->assertMissing('ul.groups');
            });
        });
    }
}
