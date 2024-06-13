<?php

namespace Roundcube\Tests\Browser\Settings;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Roundcube\Tests\Browser\Bootstrap;
use Roundcube\Tests\Browser\Components\App;
use Roundcube\Tests\Browser\Components\Dialog;
use Roundcube\Tests\Browser\TestCase;

class IdentitiesTest extends TestCase
{
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        Bootstrap::init_db();
    }

    public function testIdentities()
    {
        $this->browse(static function ($browser) {
            $browser->go('settings', 'identities');

            // check task and action
            $browser->with(new App(), static function ($browser) {
                $browser->assertEnv('task', 'settings');
                $browser->assertEnv('action', 'identities');

                // these objects should be there always
                $browser->assertObjects(['identitieslist']);
            });

            if ($browser->isDesktop()) {
                $browser->assertVisible('#settings-menu li.identities.selected');
            }

            // Identities list
            $browser->assertVisible('#identities-table tr:first-child.focused');
            $browser->assertSeeIn('#identities-table tr:first-child td.mail', TESTS_USER);

            // Toolbar menu
            $browser->assertToolbarMenu(['create'], ['delete']);
        });
    }

    /**
     * Test identity creation
     *
     * @group failsontravis-phone
     * @group failsonga-phone
     */
    #[Group('failsontravis-phone')]
    #[Group('failsonga-phone')]
    public function testIdentityCreate()
    {
        $this->browse(static function ($browser) {
            $browser->go('settings', 'identities');

            if ($browser->isPhone()) {
                $browser->assertVisible('.floating-action-buttons a.create:not(.disabled)')
                    ->click('.floating-action-buttons a.create')
                    ->waitFor('#preferences-frame');
            } else {
                $browser->clickToolbarMenuItem('create');
            }

            $browser->withinFrame('#preferences-frame', static function ($browser) {
                $browser->waitFor('form')
                    ->with('form fieldset:nth-of-type(1)', static function ($browser) {
                        $browser->assertSeeIn('legend', 'Settings')
                            ->assertVisible('input[name=_name]')
                            ->assertValue('input[name=_name]', '')
                            ->assertSeeIn('label[for=rcmfd_name]', 'Display Name')
                            ->assertVisible('input[name=_email]')
                            ->assertValue('input[name=_email]', '')
                            ->assertSeeIn('label[for=rcmfd_email]', 'Email')
                            ->assertVisible('input[name=_organization]')
                            ->assertValue('input[name=_organization]', '')
                            ->assertSeeIn('label[for=rcmfd_organization]', 'Organization')
                            ->assertVisible('input[name=_reply-to]')
                            ->assertValue('input[name=_reply-to]', '')
                            ->assertSeeIn('label[for=rcmfd_reply-to]', 'Reply-To')
                            ->assertVisible('input[name=_bcc]')
                            ->assertValue('input[name=_bcc]', '')
                            ->assertSeeIn('label[for=rcmfd_bcc]', 'Bcc')
                            ->assertCheckboxState('input[name=_standard]', false)
                            ->assertSeeIn('label[for=rcmfd_standard]', 'Set default');
                    })
                    ->with('form fieldset:nth-of-type(2)', static function ($browser) {
                        $browser->assertSeeIn('legend', 'Signature')
                            ->assertVisible('textarea[name=_signature]')
                            ->assertValue('textarea[name=_signature]', '');
                    })
                    ->type('_name', 'My Test')
                    ->type('_email', 'mynew@identity.com')
                    ->type('_organization', 'My Organization')
                    ->type('_reply-to', 'replyto@domain.tld')
                    ->type('_bcc', 'bcc@domain.tld')
                    ->setCheckboxState('input[name=_standard]', true)
                    ->type('_signature', 'My signature');

                if (!$browser->isPhone()) {
                    $browser->click('.formbuttons button.submit');
                }
            });

            if ($browser->isPhone()) {
                $browser->assertVisible('#layout-content .header a.back-list-button')
                    ->whenAvailable('#layout-content .footer .buttons', static function ($browser) {
                        $browser->click('a.button.submit');
                    });
            }

            $browser->waitForMessage('confirmation', 'Successfully saved.')
                ->closeMessage('confirmation')
                ->waitFor('#preferences-frame');

            $browser->withinFrame('#preferences-frame', static function ($browser) {
                $browser->whenAvailable('form', static function ($browser) {
                    $browser->assertValue('input[name=_name]', 'My Test')
                        ->assertValue('input[name=_email]', 'mynew@identity.com')
                        ->assertValue('input[name=_organization]', 'My Organization')
                        ->assertValue('input[name=_reply-to]', 'replyto@domain.tld')
                        ->assertValue('input[name=_bcc]', 'bcc@domain.tld')
                        ->assertValue('textarea[name=_signature]', 'My signature')
                        ->assertCheckboxState('input[name=_standard]', true);
                });
            });

            // Toolbar menu (Delete button is active now)
            $browser->assertToolbarMenu(['create', 'delete']);

            if ($browser->isPhone()) {
                $browser->click('#layout-content .header a.back-list-button')
                    ->waitFor('#identities-table');
            }

            // Identities list
            $browser->with('#identities-table', static function ($browser) {
                $browser->assertElementsCount('tbody tr', 2)
                    ->assertSeeIn('tbody tr:nth-child(2)', 'My Test');
            });
        });
    }

    /**
     * Test identity deletion
     *
     * @depends testIdentityCreate
     *
     * @group failsontravis-phone
     * @group failsonga-phone
     */
    #[Depends('testIdentityCreate')]
    #[Group('failsontravis-phone')]
    #[Group('failsonga-phone')]
    public function testIdentityDelete()
    {
        $this->browse(static function ($browser) {
            $browser->click('#identities-table tbody tr:first-child')
                ->waitFor('#preferences-frame')
                ->clickToolbarMenuItem('delete');

            $browser->with(new Dialog(), static function ($browser) {
                $browser->assertDialogTitle('Are you sure...')
                    ->assertDialogContent('Do you really want to delete this identity?')
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
                ->assertElementsCount('#identities-table tbody tr', 1);

            // Toolbar menu (Delete button is inactive again)
            $browser->assertToolbarMenu(['create'], ['delete']);
        });
    }

    /**
     * Test identity update
     *
     * @depends testIdentityDelete
     *
     * @group failsontravis-phone
     * @group failsonga-phone
     */
    #[Depends('testIdentityDelete')]
    #[Group('failsontravis-phone')]
    #[Group('failsonga-phone')]
    public function testIdentityUpdate()
    {
        $this->browse(static function ($browser) {
            $browser->click('#identities-table tbody tr:last-child')
                ->waitFor('#preferences-frame')
                ->waitUntilMissing('#messagestack div.loading');

            $browser->withinFrame('#preferences-frame', static function ($browser) {
                $browser->whenAvailable('form', static function ($browser) {
                    $browser->type('[name=_name]', 'Default')
                        ->type('[name=_organization]', 'Default Org');
                });

                if (!$browser->isPhone()) {
                    $browser->click('.formbuttons button.submit');
                }
            });

            if ($browser->isPhone()) {
                $browser->whenAvailable('#layout-content .footer', static function ($browser) {
                    $browser->assertVisible('a.button.prev.disabled')
                        ->assertVisible('a.button.next.disabled')
                        ->click('a.button.submit');
                });
            }

            $browser->waitForMessage('confirmation', 'Successfully saved.')
                ->closeMessage('confirmation')
                ->waitFor('#preferences-frame');

            if ($browser->isPhone()) {
                $browser->click('#layout-content .header a.back-list-button');
            }

            $browser->whenAvailable('#identities-table', static function ($browser) {
                $browser->assertSeeIn('tbody tr:last-child', 'Default <mynew@identity.com>');
            });
        });
    }

    /**
     * Test identities in mail composer
     *
     * @depends testIdentityUpdate
     *
     * @group failsontravis-phone
     * @group failsonga-phone
     */
    #[Depends('testIdentityUpdate')]
    #[Group('failsontravis-phone')]
    #[Group('failsonga-phone')]
    public function testIdentitiesInComposer()
    {
        // Add one more identity
        (new \rcube_user(1))->insert_identity([
            'email' => 'another@domain.tld',
        ]);

        $this->browse(function ($browser) {
            if ($browser->isPhone()) {
                $browser->click('a.back-sidebar-button');
            }

            // Goto Compose and test the identity selector
            $browser->clickTaskMenuItem('compose')
                ->waitFor('#compose-content')
                ->assertElementsCount('select[name=_from] > option', 2)
                ->assertSeeIn('select[name=_from] > option[selected]', 'mynew@identity.com');

            $this->assertTrue(trim($browser->value('#_bcc'), ', ') === 'bcc@domain.tld');
            $this->assertTrue(trim($browser->value('#_replyto'), ', ') === 'replyto@domain.tld');
            $this->assertTrue(strpos($browser->value('#composebody'), 'My signature') !== false);

            // TODO: Recipient input, HTML mode, identity change

            // Test "unsaved changes" dialog
            $browser->type('#compose-subject', 'subject')
                ->click('#compose_from a.edit')
                ->with(new Dialog(), static function ($browser) {
                    $browser->assertDialogTitle('Are you sure...')
                        ->assertDialogContent('The message has not been sent and has unsaved changes. Do you want to discard your changes?')
                        ->assertButton('mainaction.discard', 'Discard')
                        ->assertButton('cancel', 'Cancel')
                        ->clickButton('discard');
                });

            $browser->waitFor('#identities-table');
        });
    }
}
