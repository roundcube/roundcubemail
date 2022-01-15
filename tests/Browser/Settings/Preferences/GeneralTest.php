<?php

namespace Tests\Browser\Settings\Preferences;

use Tests\Browser\Components\App;

class GeneralTest extends \Tests\Browser\TestCase
{
    private $settings;

    public static function setUpBeforeClass(): void
    {
        \bootstrap::init_db();
    }

    public function testGeneral()
    {
        $this->browse(function ($browser) {
            $browser->go('settings');

            if (!$browser->isDesktop()) {
                $browser->click('#settings-menu li.preferences');
                $browser->waitFor('#sections-table');
            }

            $browser->assertVisible('#sections-table tr.general.focused');

            $browser->click('#sections-table tr.general');

            if ($browser->isPhone()) {
                $browser->waitFor('#layout-content .footer a.button.submit:not(.disabled)');
                $browser->assertVisible('#layout-content .footer a.button.prev.disabled');
                $browser->assertVisible('#layout-content .footer a.button.next:not(.disabled)');
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
                    $browser->assertCheckboxState('_pretty_date', $this->app->config->get('prettydate'));

                    $browser->assertSeeIn('label[for=rcmfd_displaynext]', 'Display next');
                    $browser->assertCheckboxState('_display_next', $this->app->config->get('display_next'));

                    $browser->assertSeeIn('label[for=rcmfd_refresh_interval]', 'Refresh');
                    $browser->assertVisible('select[name=_refresh_interval]');
                    $browser->assertSelected('select[name=_refresh_interval]', $this->app->config->get('refresh_interval')/60);
                });

                // TODO: Interface Skin fieldset
                /*
                $browser->with('form.propform fieldset.skin', function ($browser) {
                    $browser->assertSeeIn('legend', 'Interface skin');
                });
                */

                // Browser Options fieldset
                $browser->with('form.propform fieldset.browser', function ($browser) {
                    $browser->assertSeeIn('legend', 'Browser Options');

                    $browser->assertSeeIn('label[for=rcmfd_standard_windows]', 'Handle popups');
                    $browser->assertCheckboxState('_standard_windows', $this->app->config->get('standard_windows'));
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

                $browser->setCheckboxState('_pretty_date', $this->settings['pretty_date']);
                $browser->setCheckboxState('_display_next', $this->settings['display_next']);
                $browser->setCheckboxState('_standard_windows', $this->settings['standard_windows']);

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
                $browser->assertSelected('_timezone', $this->settings['timezone']);
                $browser->assertSelected('_time_format', $this->settings['time_format']);
                $browser->assertSelected('_date_format', $this->settings['date_format']);
                $browser->assertSelected('_refresh_interval', $this->settings['refresh_interval']);

                $browser->assertCheckboxState('_pretty_date', $this->settings['pretty_date']);
                $browser->assertCheckboxState('_display_next', $this->settings['display_next']);
                $browser->assertCheckboxState('_standard_windows', $this->settings['standard_windows']);
            });
        });

        // Assert the options have been saved in database properly
        $prefs   = \bootstrap::get_prefs();
        $options = array_diff(array_keys($this->settings), ['refresh_interval', 'pretty_date']);

        foreach ($options as $option) {
            $this->assertEquals($this->settings[$option], $prefs[$option]);
        }

        $this->assertEquals($this->settings['pretty_date'], $prefs['prettydate']);
        $this->assertEquals($this->settings['refresh_interval'], $prefs['refresh_interval']/60);
    }
}
