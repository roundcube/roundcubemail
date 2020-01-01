<?php

namespace Tests\Browser\Settings\Preferences;

class General extends \Tests\Browser\DuskTestCase
{
    protected function tearDown()
    {
        parent::tearDown();

        // Reset user preferences back to defaults
        $db = $this->app->get_dbh();
        $db->query("UPDATE users SET preferences = '' WHERE username = ?", TESTS_USER);
    }

    public function testGeneral()
    {
        $this->browse(function ($browser) {
            $this->go('settings');

            $browser->click('#sections-table tr.general');

            $browser->assertVisible('#sections-table tr.general.focused');

            $browser->withinFrame('#preferences-frame', function ($browser) {
                // check task and action
                $this->assertEnvEquals('task', 'settings');
                $this->assertEnvEquals('action', 'edit-prefs');

                $browser->assertVisible('.formbuttons button.submit');

                // Main Options fieldset
                $browser->with('form.propform fieldset.main', function ($browser) {
                    $browser->assertSeeIn('legend', 'Main Options');

                    $browser->assertSeeIn('label[for=rcmfd_lang]', 'Language');
                    $browser->assertVisible('select[name=_language]');
                    $browser->assertSelected('select[name=_language]', 'en_US');

                    $browser->assertSeeIn('label[for=rcmfd_timezone]', 'Time zone');
                    $browser->assertVisible('select[name=_timezone]');
                    // we don't know what timezone has been autodetected
                    // $browser->assertSelected('select[name=_timezone]', 'auto');

                    $browser->assertSeeIn('label[for=rcmfd_time_format]', 'Time format');
                    $browser->assertVisible('select[name=_time_format]');
                    $browser->assertSelected('select[name=_time_format]', $this->app->config->get('time_format'));

                    $browser->assertSeeIn('label[for=rcmfd_date_format]', 'Date format');
                    $browser->assertVisible('select[name=_date_format]');
                    $browser->assertSelected('select[name=_date_format]', $this->app->config->get('date_format'));

                    $browser->assertSeeIn('label[for=rcmfd_prettydate]', 'Pretty dates');
                    $this->assertCheckboxState('_pretty_date', $this->app->config->get('prettydate'));

                    $browser->assertSeeIn('label[for=rcmfd_displaynext]', 'Display next');
                    $this->assertCheckboxState('_display_next', $this->app->config->get('display_next'));

                    $browser->assertSeeIn('label[for=rcmfd_refresh_interval]', 'Refresh');
                    $browser->assertVisible('select[name=_refresh_interval]');
                    $browser->assertSelected('select[name=_refresh_interval]', $this->app->config->get('refresh_interval')/60);
                });

                // Interface Skin fieldset
                $browser->with('form.propform fieldset.skin', function ($browser) {
                    $browser->assertSeeIn('legend', 'Interface skin');

                    // TODO
                });

                // Browser Options fieldset
                $browser->with('form.propform fieldset.browser', function ($browser) {
                    $browser->assertSeeIn('legend', 'Browser Options');

                    $browser->assertSeeIn('label[for=rcmfd_standard_windows]', 'Handle popups');
                    $this->assertCheckboxState('_standard_windows', $this->app->config->get('standard_windows'));
                });
            });
        });
    }

    /**
     * Test (all) User Interface preferences change
     *
     * @depends testGeneral
     */
    public function testPreferencesChange()
    {
        // Values we're changing to
        // TODO: Skin or language change causes page reload, we should test this separately

        $this->settings = [
            'timezone' => 'Pacific/Midway',
            'time_format' => 'h:i A',
            'date_format' => 'd-m-Y',
            'refresh_interval' => 60,
            'pretty_date' => !boolval($this->app->config->get('prettydate')),
            'display_next' => !boolval($this->app->config->get('display_next')),
            'standard_windows' => !boolval($this->app->config->get('standard_windows')),
        ];

        $this->browse(function ($browser) {
            // Update preferences
            $browser->withinFrame('#preferences-frame', function ($browser) {
                $browser->select('_timezone', $this->settings['timezone']);
                $browser->select('_time_format', $this->settings['time_format']);
                $browser->select('_date_format', $this->settings['date_format']);
                $browser->select('_refresh_interval', $this->settings['refresh_interval']);

                $this->setCheckboxState('_pretty_date', $this->settings['pretty_date']);
                $this->setCheckboxState('_display_next', $this->settings['display_next']);
                $this->setCheckboxState('_standard_windows', $this->settings['standard_windows']);

                // Submit form
                $browser->click('.formbuttons button.submit');
            });

            $this->waitForMessage('confirmation', 'Successfully saved');

            // Verify if every option has been updated
            $browser->withinFrame('#preferences-frame', function ($browser) {
                $browser->assertSelected('_timezone', $this->settings['timezone']);
                $browser->assertSelected('_time_format', $this->settings['time_format']);
                $browser->assertSelected('_date_format', $this->settings['date_format']);
                $browser->assertSelected('_refresh_interval', $this->settings['refresh_interval']);

                $this->assertCheckboxState('_pretty_date', $this->settings['pretty_date']);
                $this->assertCheckboxState('_display_next', $this->settings['display_next']);
                $this->assertCheckboxState('_standard_windows', $this->settings['standard_windows']);

                // Assert the options have been saved in database properly
                $prefs   = \bootstrap::get_prefs();
                $options = array_diff(array_keys($this->settings), ['refresh_interval', 'pretty_date']);

                foreach ($options as $option) {
                    $this->assertEquals($this->settings[$option], $prefs[$option]);
                }

                $this->assertEquals($this->settings['pretty_date'], $prefs['prettydate']);
                $this->assertEquals($this->settings['refresh_interval'], $prefs['refresh_interval']/60);
            });
        });
    }
}
