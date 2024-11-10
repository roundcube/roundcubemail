<?php

namespace Roundcube\Tests\Browser\Settings;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Roundcube\Tests\Browser\Bootstrap;
use Roundcube\Tests\Browser\Components\App;
use Roundcube\Tests\Browser\Components\Dialog;
use Roundcube\Tests\Browser\Components\Popupmenu;
use Roundcube\Tests\Browser\TestCase;

class ResponsesTest extends TestCase
{
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        Bootstrap::init_db();
    }

    public function testResponses()
    {
        $this->browse(static function ($browser) {
            $browser->go('settings', 'responses');

            $browser->with(new App(), static function ($browser) {
                // check task and action
                $browser->assertEnv('task', 'settings');
                $browser->assertEnv('action', 'responses');

                // these objects should be there always
                $browser->assertObjects(['responseslist']);
            });

            if ($browser->isDesktop()) {
                $browser->assertVisible('#settings-menu li.responses.selected');
            }

            // Responses list
            $browser->assertPresent('#responses-table')
                ->assertMissing('#responses-table tr');

            // Toolbar menu
            $browser->assertToolbarMenu(['create'], ['delete']);
        });
    }

    /**
     * Test response creation
     *
     * @group failsontravis-phone
     * @group failsonga-phone
     */
    #[Group('failsontravis-phone')]
    #[Group('failsonga-phone')]
    public function testResponseCreate()
    {
        \rcmail::get_instance()->get_dbh()->exec_script("
            DELETE FROM responses;
            INSERT INTO responses (user_id, name, data, is_html) VALUES (1, 'response 1', 'test response 1', '0');
            INSERT INTO responses (user_id, name, data, is_html) VALUES (1, 'response 2', '<p><b>test response 2</b></p>', '1');
        ");

        $this->browse(static function ($browser) {
            $browser->go('settings', 'responses');

            if ($browser->isPhone()) {
                $browser->assertVisible('.floating-action-buttons a.create:not(.disabled)')
                    ->click('.floating-action-buttons a.create')
                    ->waitFor('#preferences-frame');
            } else {
                $browser->clickToolbarMenuItem('create');
            }

            $browser->withinFrame('#preferences-frame', static function ($browser) {
                $browser->waitFor('form')
                    ->with('form', static function ($browser) {
                        $browser->assertVisible('input[name=_name]')
                            ->assertValue('input[name=_name]', '')
                            ->assertSeeIn('label[for=ffname]', 'Name')
                            ->assertVisible('textarea[name=_text]')
                            ->assertValue('textarea[name=_text]', '');
                    })
                    ->type('_name', 'Test')
                    ->type('_text', 'Response Body');

                if (!$browser->isPhone()) {
                    $browser->click('.formbuttons button.submit');
                }
            });

            if ($browser->isPhone()) {
                $browser->assertVisible('#layout-content .header a.back-list-button')
                    ->waitFor('#layout-content .footer .buttons')
                    ->click('#layout-content .footer .buttons a.button.submit');
            }

            $browser->waitForMessage('confirmation', 'Successfully saved.')
                ->closeMessage('confirmation')
                ->waitFor('#preferences-frame');

            $browser->withinFrame('#preferences-frame', static function ($browser) {
                $browser->waitFor('form')
                    ->with('form', static function ($browser) {
                        $browser->assertVisible('input[name=_name]')
                            ->assertValue('input[name=_name]', 'Test')
                            ->assertValue('textarea[name=_text]', 'Response Body');
                    });
            });

            if ($browser->isPhone()) {
                $browser->click('#layout-content .header a.back-list-button')
                    ->waitFor('#responses-table');
            }

            // Responses list
            $browser->with('#responses-table', static function ($browser) {
                $browser->assertElementsCount('tbody tr', 3)
                    ->assertSeeIn('tbody tr:nth-child(3)', 'Test');
            });

            if ($browser->isPhone()) {
                $browser->click('#responses-table tbody tr:last-child')
                    ->waitFor('#preferences-frame');
            }

            // Toolbar menu (Delete button is active now)
            $browser->assertToolbarMenu(['create', 'delete']);
        });
    }

    /**
     * Test response deletion
     *
     * @depends testResponseCreate
     *
     * @group failsontravis-phone
     * @group failsonga-phone
     */
    #[Depends('testResponseCreate')]
    #[Group('failsontravis-phone')]
    #[Group('failsonga-phone')]
    public function testResponseDelete()
    {
        $this->browse(static function ($browser) {
            $browser->clickToolbarMenuItem('delete');

            $browser->with(new Dialog(), static function ($browser) {
                $browser->assertDialogTitle('Are you sure...')
                    ->assertDialogContent('Do you really want to delete this response text?')
                    ->assertButton('mainaction.delete', 'Delete')
                    ->assertButton('cancel', 'Cancel')
                    ->clickButton('mainaction.delete');
            });

            $browser->waitForMessage('confirmation', 'Successfully deleted.')
                ->closeMessage('confirmation');

            // Preview frame should reset to the watermark page
            $browser->withinFrame('#preferences-frame', static function ($browser) {
                $browser->waitUntilMissing('> div');
            });

            $browser->waitFor('#layout-list')
                ->assertElementsCount('#responses-table tbody tr', 2);

            // Toolbar menu (Delete button is inactive again)
            $browser->assertToolbarMenu(['create'], ['delete']);
        });
    }

    /**
     * Test responses in mail composer
     *
     * @depends testResponseDelete
     *
     * @group failsontravis-phone
     * @group failsonga-phone
     */
    #[Depends('testResponseDelete')]
    #[Group('failsontravis-phone')]
    #[Group('failsonga-phone')]
    public function testResponsesInComposer()
    {
        $this->browse(static function ($browser) {
            if ($browser->isPhone()) {
                $browser->click('a.back-sidebar-button');
            }

            // Goto Compose and test the responses menu
            $browser->clickTaskMenuItem('compose')
                ->waitFor('#compose-content')
                ->clickToolbarMenuItem('responses', null, false)
                ->with(new Popupmenu('responses-menu'), static function ($browser) {
                    $browser->assertMenuState(['edit.responses'])
                        ->with('#responseslist', static function ($browser) {
                            $browser->assertElementsCount('li', 2)
                                ->assertSeeIn('li:nth-child(1) a.insertresponse', 'response 1')
                                ->assertSeeIn('li:nth-child(2) a.insertresponse', 'response 2');
                        })
                        ->closeMenu();
                })
                ->closeToolbarMenu();

            // Insert a response to the message body
            $browser->type('#composebody', 'Body and ')
                ->clickToolbarMenuItem('responses', null, false)
                ->waitFor('#responseslist')
                ->click('#responseslist li:nth-child(1) a.insertresponse')
                ->waitUntilMissing('#responses-menu');

            $browser->waitUntilMissing('.popover-overlay')
                ->waitForMessage('confirmation', 'Response inserted successfully.')
                ->closeMessage('confirmation')
                ->assertValue('#composebody', 'Body and test response 1');

            // TODO: Test HTML mode, test response creation
        });
    }

    /**
     * Test response update
     *
     * @depends testResponsesInComposer
     *
     * @group failsontravis-phone
     * @group failsonga-phone
     */
    #[Depends('testResponsesInComposer')]
    #[Group('failsontravis-phone')]
    #[Group('failsonga-phone')]
    public function testResponseUpdate()
    {
        $this->browse(static function ($browser) {
            // We're in mail compose, use responses menu to goto Settings > Responses
            $browser->clickToolbarMenuItem('responses', null, false)
                ->waitFor('#responses-menu')
                ->click('#responses-menu a.edit.responses')
                ->with(new Dialog(), static function ($browser) {
                    $browser->assertDialogTitle('Are you sure...')
                        ->assertDialogContent('The message has not been sent and has unsaved changes. Do you want to discard your changes?')
                        ->assertButton('mainaction.discard', 'Discard')
                        ->assertButton('cancel', 'Cancel')
                        ->clickButton('discard');
                });

            $browser->waitFor('#responses-table')
                ->assertSeeIn('#responses-table tbody tr:first-child td', 'response 1')
                ->click('#responses-table tbody tr:first-child')
                ->waitFor('#preferences-frame');

            $browser->withinFrame('#preferences-frame', static function ($browser) {
                $browser->waitFor('form')
                    ->with('form', static function ($browser) {
                        $browser->assertValue('[name=_name]', 'response 1')
                            ->assertValue('[name=_text]', 'test response 1')
                            ->type('[name=_name]', 'Test 11')
                            ->type('[name=_text]', 'Response 11');
                    });

                if (!$browser->isPhone()) {
                    $browser->click('.formbuttons button.submit');
                }
            });

            if ($browser->isPhone()) {
                $browser->waitFor('#layout-content .footer')
                    ->with('#layout-content .footer', static function ($browser) {
                        $browser->assertVisible('a.button.prev.disabled')
                            ->assertVisible('a.button.next:not(.disabled)')
                            ->click('a.button.submit');
                    });
            }

            $browser->waitForMessage('confirmation', 'Successfully saved.')
                ->closeMessage('confirmation');

            if ($browser->isPhone()) {
                $browser->click('#layout-content .header a.back-list-button')
                    ->waitFor('#responses-table');
            }

            // Responses list
            $browser->with('#responses-table', static function ($browser) {
                $browser->assertSeeIn('tbody tr:nth-child(1)', 'Test 11');
            });
        });
    }
}
