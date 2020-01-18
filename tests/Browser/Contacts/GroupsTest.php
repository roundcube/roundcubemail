<?php

namespace Tests\Browser\Contacts;

use Tests\Browser\Components\Popupmenu;

class GroupsTest extends \Tests\Browser\TestCase
{
    /**
     * Contact groups UI basics
     */
    public function testGroups()
    {
        \bootstrap::init_db();

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

            $browser->waitFor('.ui-dialog');
            $browser->with('.ui-dialog', function ($browser) {
                $browser
                    ->assertSeeIn('.ui-dialog-titlebar', 'Create new group')
                    ->assertFocused('input.form-control')
                    ->type('input.form-control', 'New Group')
                    ->click('button.mainaction');
            });

            $browser->waitUntilMissing('.ui-dialog');

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

            $browser->waitFor('.ui-dialog');
            $browser->with('.ui-dialog', function ($browser) {
                $browser->assertSeeIn('.ui-dialog-titlebar', 'Rename group')
                    ->assertFocused('input.form-control')
                    ->assertValue('input.form-control', 'New Group')
                    ->type('input.form-control', 'Renamed')
                    ->click('button.mainaction');
            });

            $browser->waitUntilMissing('.ui-dialog');

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

            $browser->waitFor('.ui-dialog');
            $browser->with('.ui-dialog', function ($browser) {
                $browser->assertSeeIn('.ui-dialog-titlebar', 'Are you sure...')
                    ->assertSeeIn('.ui-dialog-content', 'Do you really want to delete selected group?')
                    ->assertFocused('button.mainaction.delete')
                    ->click('button.mainaction.delete');
            });

            $browser->waitUntilMissing('.ui-dialog');

            $browser->with('#directorylist', function ($browser) {
                $browser->assertMissing('.treetoggle.expanded')
                    ->assertMissing('ul.groups');
            });
        });
    }
}
