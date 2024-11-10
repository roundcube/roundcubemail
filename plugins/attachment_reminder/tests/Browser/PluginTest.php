<?php

namespace Tests\Browser\Plugins\AttachmentReminder;

use PHPUnit\Framework\Attributes\Depends;
use Roundcube\Tests\Browser\Bootstrap;
use Roundcube\Tests\Browser\Components\Dialog;
use Roundcube\Tests\Browser\TestCase;

class PluginTest extends TestCase
{
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        Bootstrap::init_db();
    }

    /**
     * Test Preferences UI (Composing Messages)
     */
    public function testPreferences()
    {
        $this->browse(static function ($browser) {
            $browser->go('settings', 'preferences');

            $browser->click('#sections-table tr.compose');

            $browser->withinFrame('#preferences-frame', static function ($browser) {
                if (!$browser->isPhone()) {
                    $browser->waitFor('.formbuttons button.submit');
                }

                // Main Options fieldset
                $browser->with('form.propform fieldset.main', static function ($browser) {
                    $browser->assertSeeIn('label[for=rcmfd_attachment_reminder]', 'Remind about forgotten attachments')
                        ->assertCheckboxState('_attachment_reminder', false)
                        ->setCheckboxState('_attachment_reminder', true);
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
            $browser->withinFrame('#preferences-frame', static function ($browser) {
                $browser->assertCheckboxState('_attachment_reminder', true);
            });
        });
    }

    /**
     * Test Mail Compose page
     *
     * @depends testPreferences
     */
    #[Depends('testPreferences')]
    public function testMailCompose()
    {
        $this->browse(static function ($browser) {
            $send_btn = $browser->isPhone() ? '.buttons a.send' : '.formbuttons button.send';

            $browser->go('mail', 'compose');

            $browser->waitFor('#compose_to')
                ->type('#compose_to input', 'test@domain.tld')
                ->type('#compose-subject', 'subject')
                ->type('#composebody', 'File attached')
                ->click($send_btn);

            // Expect a dialog, Click "Attach a file" button
            $browser->with(new Dialog(), static function ($browser) {
                $browser->assertDialogTitle('Missing attachment?')
                    ->assertDialogContent('Did you forget to attach a file?')
                    ->assertButton('mainaction.attach', 'Attach a file')
                    ->assertButton('send', 'Send')
                    ->clickButton('mainaction.attach');
            });

            // Click the Send button again
            $browser->click($send_btn);

            // Expect the dialog again, click Send button (in the dialog)
            $browser->with(new Dialog(), static function ($browser) {
                $browser->assertDialogTitle('Missing attachment?')
                    ->clickButton('send');
            });

            $browser->waitForMessage('confirmation', 'Message sent successfully.');
        });
    }
}
