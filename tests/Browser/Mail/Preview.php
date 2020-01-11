<?php

namespace Tests\Browser\Mail;

use Tests\Browser\Components\App;
use Tests\Browser\Components\Popupmenu;

class Preview extends \Tests\Browser\TestCase
{
    public static function setUpBeforeClass()
    {
        \bootstrap::init_imap();
        \bootstrap::purge_mailbox('INBOX');

        // import email messages
        foreach (glob(TESTS_DIR . 'data/mail/list_??.eml') as $f) {
            \bootstrap::import_message($f, 'INBOX');
        }
    }

    /**
     * Test opening an email in preview frame
     */
    public function testPreview()
    {
        $this->browse(function ($browser) {
            $browser->go('mail');

            $browser->waitFor('#messagelist tbody tr:first-child')
                ->click('#messagelist tbody tr:first-child')
                ->waitForMessage('loading', 'Loading...')
                ->waitFor('#messagecontframe')
                ->waitUntilMissing('#messagestack');

            // On phone check frame controls
            if ($browser->isPhone()) {
                $browser->with('#layout-content .footer', function ($browser) {
                    $browser->assertVisible('a.button.prev.disabled')
                        ->assertVisible('a.button.next:not(.disabled)')
                        ->assertVisible('a.button.reply:not(.disabled)')
                        ->assertSeeIn('a.button.prev', 'Previous')
                        ->assertSeeIn('a.button.reply', 'Reply')
                        ->assertSeeIn('a.button.next', 'Next');
                });
            }

            $browser->withinFrame('#messagecontframe', function ($browser) {
                $browser->waitFor('img.contactphoto');

                // Privacy warning
                $browser->assertVisible('#remote-objects-message.alert-warning')
                    ->assertSeeIn('#remote-objects-message', 'To protect your privacy remote resources have been blocked.');

                // Images
                $this->assertRegExp('/action=get/', $browser->attribute('p#v1attached > img', 'src'));
                $this->assertRegExp('/blocked/', $browser->attribute('p#v1remote > img', 'src'));

                // Attachments list
                $browser->with('#attachment-list', function ($browser) {
                    $browser->assertVisible('li.image.ico')
                        ->assertSeeIn('li .attachment-name', 'favicon.ico')
                        ->assertSeeIn('li .attachment-size', '(~2 KB)')
                        ->click('a.dropdown');
                });

                if (!$browser->isPhone()) {
                    $browser->waitFor('#attachmentmenu')
                        ->with('#attachmentmenu', function ($browser) {
                            $browser->assertVisible('a.extwin.disabled')
                                ->assertVisible('a.download:not(.disabled)')
                                ->click('a.download');
                    });
                }
            });

            if ($browser->isPhone()) {
                $browser->waitFor('#attachmentmenu-clone')
                    ->with('#attachmentmenu-clone', function ($browser) {
                        $browser->assertVisible('a.extwin.disabled')
                            ->assertVisible('a.download:not(.disabled)')
                            ->click('a.download');
                    });
            }

            $ico = $browser->readDownloadedFile('favicon.ico');

            $this->assertTrue(strlen($ico) == 2294);
            $this->assertSame("\0\0\1\0", substr($ico, 0, 4));
            $browser->removeDownloadedFile('favicon.ico');

            // On phone check Back button
            if ($browser->isPhone()) {
                $browser->click('#layout-content .header a.back-list-button')
                    ->assertVisible('#messagelist');
            }
        });
    }
}
