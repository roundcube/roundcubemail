<?php

namespace Tests\Browser\Settings\Preferences;

use Tests\Browser\Components\App;

class ServerTest extends \Tests\Browser\TestCase
{
    private $settings;

    public static function setUpBeforeClass(): void
    {
        \bootstrap::init_db();
    }

    public function testServer()
    {
        $this->settings = [
            'read_when_deleted' => true,
            'flag_for_deletion' => false,
            'skip_deleted'      => false,
            'delete_junk'       => false,
            'logout_purge'      => 'never',
            'logout_expunge'    => false,
        ];

        $this->browse(function ($browser) {
            $browser->go('settings', 'preferences');

            $browser->click('#sections-table tr.server');

            if ($browser->isPhone()) {
                $browser->whenAvailable('#layout-content .footer', function ($browser) {
                    $browser->assertVisible('a.button.submit:not(.disabled)')
                        ->assertVisible('a.button.prev:not(.disabled)')
                        ->assertVisible('a.button.next');
                });
            }

            $browser->withinFrame('#preferences-frame', function ($browser) {
                if (!$browser->isPhone()) {
                    $browser->waitFor('.formbuttons button.submit');
                }

                // check task and action
                $browser->with(new App(), function ($browser) {
                    $browser->assertEnv('task', 'settings');
                    $browser->assertEnv('action', 'edit-prefs');
                });

                // Main Options fieldset
                $browser->with('form.propform fieldset.main', function ($browser) {
                    $browser->assertSeeIn('legend', 'Main Options');

                    $browser->assertSeeIn('label[for=rcmfd_read_deleted]', 'Mark the message as read on delete')
                        ->assertCheckboxState('_read_when_deleted', $this->settings['read_when_deleted'])
                        ->setCheckboxState('_read_when_deleted', $this->settings['read_when_deleted'] = !$this->settings['read_when_deleted']);

                    $browser->assertSeeIn('label[for=rcmfd_flag_for_deletion]', 'Flag the message for deletion instead of delete')
                        ->assertCheckboxState('_flag_for_deletion', $this->settings['flag_for_deletion'])
                        ->setCheckboxState('_flag_for_deletion', $this->settings['flag_for_deletion'] = !$this->settings['flag_for_deletion']);

                    $browser->assertSeeIn('label[for=rcmfd_skip_deleted]', 'Do not show deleted messages')
                        ->assertCheckboxState('_skip_deleted', $this->settings['skip_deleted'])
                        ->setCheckboxState('_skip_deleted', $this->settings['skip_deleted'] = !$this->settings['skip_deleted']);

                    $browser->assertSeeIn('label[for=rcmfd_delete_junk]', 'Directly delete messages in Junk')
                        ->assertCheckboxState('_delete_junk',  $this->settings['delete_junk'])
                        ->setCheckboxState('_delete_junk', $this->settings['delete_junk'] = !$this->settings['delete_junk']);
                });

                // Maintenance fieldset
                $browser->with('form.propform fieldset.maintenance', function ($browser) {
                    $browser->assertSeeIn('legend', 'Maintenance');

                    $browser->assertSeeIn('label[for=rcmfd_logout_purge]', 'Clear Trash on logout')
                        ->assertVisible('select[name=_logout_purge]')
                        ->assertSelected('select[name=_logout_purge]', $this->settings['logout_purge']);

                    $this->settings['logout_purge'] = '30';
                    $browser->select('select[name=_logout_purge]', '30');

                    $browser->assertSeeIn('label[for=rcmfd_logout_expunge]', 'Compact Inbox on logout')
                        ->assertCheckboxState('_logout_expunge', $this->settings['logout_expunge'])
                        ->setCheckboxState('_logout_expunge', $this->settings['logout_expunge'] = !$this->settings['logout_expunge']);
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
                foreach ($this->settings as $key => $value) {
                    if (is_bool($value)) {
                        $browser->assertCheckboxState('_' . $key, $value);
                    }
                    else {
                        $browser->assertValue("[name=_{$key}]", $value);
                    }
                }
            });
        });

        // Assert the options have been saved in database properly
        $prefs = \bootstrap::get_prefs();

        foreach ($this->settings as $key => $value) {
            $this->assertSame($value, $prefs[$key]);
        }
    }
}
