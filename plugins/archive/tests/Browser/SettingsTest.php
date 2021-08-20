<?php

namespace Tests\Browser\Plugins\Archive;

class SettingsTest extends \Tests\Browser\TestCase
{
    public static function setUpBeforeClass(): void
    {
        \bootstrap::init_db();
    }

    /**
     * Test Folders UI
     */
    public function testFolders()
    {
        $this->browse(function ($browser) {
            $browser->go('settings', 'folders');

            // Folders list
            $browser->with('#subscription-table', function ($browser) {
                $browser->assertHasClass('li:nth-child(7)', 'archive')
                    ->assertSeeIn('li:nth-child(7)', 'Archive')
                    ->assertPresent('li:nth-child(7) [type=checkbox][disabled]');
            });
        });
    }

    /**
     * Test Preferences UI
     */
    public function testPreferences()
    {
        $this->browse(function ($browser) {
            $browser->go('settings');

            if (!$browser->isDesktop()) {
                $browser->click('#settings-menu li.preferences')
                    ->waitFor('#sections-table');
            }

            $browser->click('#sections-table tr.folders');

            if ($browser->isPhone()) {
                $browser->waitFor('#layout-content .footer a.button.submit:not(.disabled)');
            }

            $browser->withinFrame('#preferences-frame', function ($browser) {
                if (!$browser->isPhone()) {
                    $browser->waitFor('.formbuttons button.submit');
                }

                // Main Options fieldset
                $browser->with('form.propform fieldset.main', function ($browser) {
                    $browser->assertSeeIn('legend', 'Main Options');

                    $browser->assertSeeIn('label[for=_archive_mbox]', 'Archive')
                        ->assertVisible('select[name=_archive_mbox]')
                        ->assertSelected('select[name=_archive_mbox]', 'Archive');

                    $browser->select('_archive_mbox', 'Drafts');
                });

                // Archive fieldset
                $browser->with('form.propform fieldset.archive', function ($browser) {
                    $browser->assertSeeIn('legend', 'Archive');

                    $browser->assertSeeIn('label[for=ff_archive_type]', 'Divide archive by')
                        ->assertVisible('select[name=_archive_type]')
                        ->assertSelected('select[name=_archive_type]', '')
                        ->with('select[name=_archive_type]', function ($browser) {
                            $browser->assertValue('option:nth-child(1)', '')
                                ->assertSeeIn('option:nth-child(1)', 'None')
                                ->assertValue('option:nth-child(2)', 'year')
                                ->assertSeeIn('option:nth-child(2)', 'Year (e.g. Archive/2012)')
                                ->assertValue('option:nth-child(3)', 'month')
                                ->assertSeeIn('option:nth-child(3)', 'Month (e.g. Archive/2012/06)')
                                ->assertValue('option:nth-child(4)', 'tbmonth')
                                ->assertSeeIn('option:nth-child(4)', 'Month - Thunderbird compatible (e.g. Archive/2012/2012-06)')
                                ->assertValue('option:nth-child(5)', 'sender')
                                ->assertSeeIn('option:nth-child(5)', 'Sender email')
                                ->assertValue('option:nth-child(6)', 'folder')
                                ->assertSeeIn('option:nth-child(6)', 'Original folder');
                        });

                    $browser->select('_archive_type', 'year');
                });

                // Submit form
                if (!$browser->isPhone()) {
                    $browser->click('.formbuttons button.submit');
                }
            });

            if ($browser->isPhone()) {
                $browser->click('#layout-content .footer a.submit');
            }

            $browser->waitForMessage('confirmation', 'Successfully saved');

            // Verify if every option has been updated
            $browser->withinFrame('#preferences-frame', function ($browser) {
                $browser->assertSelected('_archive_mbox', 'Drafts');
                $browser->assertSelected('_archive_type', 'year');
            });
        });
    }

    /**
     * Test Preferences UI (Server Settings)
     */
    public function testServerSettings()
    {
        $this->browse(function ($browser) {
            $browser->go('settings', 'preferences');

            $browser->click('#sections-table tr.server');

            $browser->withinFrame('#preferences-frame', function ($browser) {
                if (!$browser->isPhone()) {
                    $browser->waitFor('.formbuttons button.submit');
                }

                // Main Options fieldset
                $browser->with('form.propform fieldset.main', function ($browser) {
                    $browser->assertSeeIn('label[for=ff_read_on_archive]', 'Mark the message as read on archive')
                        ->assertCheckboxState('_read_on_archive', false)
                        ->setCheckboxState('_read_on_archive', true);
                });

                // Submit form
                if (!$browser->isPhone()) {
                    $browser->click('.formbuttons button.submit');
                }
            });

            if ($browser->isPhone()) {
                $browser->click('#layout-content .footer a.submit');
            }

            $browser->waitForMessage('confirmation', 'Successfully saved');

            // Verify if every option has been updated
            $browser->withinFrame('#preferences-frame', function ($browser) {
                $browser->assertCheckboxState('_read_on_archive', true);
            });
        });
    }
}
