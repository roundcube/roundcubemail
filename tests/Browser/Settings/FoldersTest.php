<?php

namespace Tests\Browser\Settings;

use Tests\Browser\Components\App;

class FoldersTest extends \Tests\Browser\TestCase
{
    public static function setUpBeforeClass()
    {
        \bootstrap::init_imap();
        \bootstrap::reset_mailboxes();
    }

    /**
     * Test Folders UI
     */
    public function testFolders()
    {
        $this->browse(function ($browser) {
            $browser->go('settings', 'folders');

            // task should be set to 'settings' and action to 'folders'
            $browser->with(new App(), function ($browser) {
                $browser->assertEnv('task', 'settings');
                $browser->assertEnv('action', 'folders');

                // these objects should be there always
                $browser->assertObjects(['quotadisplay', 'subscriptionlist']);
            });

            if ($browser->isDesktop()) {
                $browser->assertVisible('#settings-menu li.folders.selected');
            }

            if ($browser->isPhone()) {
                $browser->assertVisible('.floating-action-buttons a.create:not(.disabled)');
            }
            else {
                $browser->assertMissing('.floating-action-buttons a.create:not(.disabled)');
            }

            // Toolbar menu
            $browser->assertToolbarMenu(['create'], ['delete', 'purge']);

            // Folders list
            $browser->with('#subscription-table', function ($browser) {
                $browser->assertElementsCount('li', 5)
                    // Note: first li element is root which is hidden in Elastic
                    ->assertHasClass('li:nth-child(2)', 'inbox')
                    ->assertSeeIn('li:nth-child(2)', 'Inbox')
                    ->assertPresent('li:nth-child(2) [type=checkbox][disabled]')
                    ->assertHasClass('li:nth-child(3)', 'drafts')
                    ->assertSeeIn('li:nth-child(3)', 'Drafts')
                    ->assertPresent('li:nth-child(3) [type=checkbox][disabled]')
                    ->assertHasClass('li:nth-child(4)', 'sent')
                    ->assertSeeIn('li:nth-child(4)', 'Sent')
                    ->assertPresent('li:nth-child(4) [type=checkbox][disabled]')
                    ->assertHasClass('li:nth-child(5)', 'junk')
                    ->assertSeeIn('li:nth-child(5)', 'Junk')
                    ->assertPresent('li:nth-child(5) [type=checkbox][disabled]')
                    ->assertHasClass('li:nth-child(6)', 'trash')
                    ->assertSeeIn('li:nth-child(6)', 'Trash')
                    ->assertPresent('li:nth-child(6) [type=checkbox][disabled]');
            });
        });
    }

    /**
     * Test folder creation
     */
    public function testFolderCreate()
    {
        $this->browse(function ($browser) {
            $browser->go('settings', 'folders');

            if ($browser->isPhone()) {
                $browser->assertVisible('.floating-action-buttons a.create:not(.disabled)')
                    ->click('.floating-action-buttons a.create')
                    ->waitFor('#preferences-frame');
            }
            else {
                $browser->clickToolbarMenuItem('create');
            }

            $browser->withinFrame('#preferences-frame', function($browser) {
                $browser->waitFor('form')
                    ->with('form fieldset', function ($browser) {
                        $browser->assertVisible('input[name=_name]')
                            ->assertValue('input[name=_name]', '')
                            ->assertVisible('select[name=_parent]')
                            ->assertSelected('select[name=_parent]', '');
                    })
/*
                    ->with('form fieldset:last-child', function ($browser) {
                        $browser->assertSeeIn('legend', 'Settings')
                            ->assertVisible('select[name=_viewmode]')
                            ->assertSelected('select[name=_viewmode]', '0');
                    })
*/
                    ->type('input[name=_name]', 'Test');

                if (!$browser->isPhone()) {
                    $browser->click('.formbuttons button.submit');
                }
            });

            if ($browser->isPhone()) {
                $browser->assertVisible('#layout-content .header a.back-list-button')
                    ->assertVisible('#layout-content .footer .buttons a.button.submit')
                    ->click('#layout-content .footer .buttons a.button.submit')
                    ->waitFor('#subscription-table');
            }
            else {
                $browser->waitForMessage('confirmation', 'Folder created successfully.');
            }

            $browser->closeMessage('confirmation');

            // Folders list
            $browser->with('#subscription-table', function ($browser) {
                // Note: li.root is hidden in Elastic
                $browser->waitFor('li.mailbox:nth-child(7)')
                    ->assertElementsCount('li', 6)
                    ->assertPresent('li.mailbox:nth-child(7) [type=checkbox]:not([disabled])')
                    ->click('li.mailbox:nth-child(7)');
            });

            if ($browser->isPhone()) {
                $browser->waitFor('#preferences-frame');
            }

            $browser->withinFrame('#preferences-frame', function($browser) {
                $browser->waitFor('form');
                // TODO
            });

            // Test unsubscribe of the newly created folder
            if ($browser->isPhone()) {
                $browser->click('a.back-list-button')
                    ->waitFor('#subscription-table');
            }

            $browser->setCheckboxState('#subscription-table li:nth-child(7) input', false)
                ->waitForMessage('confirmation', 'Folder successfully unsubscribed.');
        });
    }
}
