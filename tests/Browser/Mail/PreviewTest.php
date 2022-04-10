<?php

namespace Tests\Browser\Mail;

use Tests\Browser\Components\App;
use Tests\Browser\Components\Dialog;
use Tests\Browser\Components\Popupmenu;

class PreviewTest extends \Tests\Browser\TestCase
{
    public static function setUpBeforeClass(): void
    {
        \bootstrap::init_imap(true);
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
                $this->assertMatchesRegularExpression('/action=get/', $browser->attribute('p#v1attached > img', 'src'));
                $this->assertMatchesRegularExpression('/blocked/', $browser->attribute('p#v1remote > img', 'src'));

                // Attachments list
                $browser->assertMissing('#attachment-list');
            });

            // On phone check Back button
            if ($browser->isPhone()) {
                $browser->click('#layout-content .header a.back-list-button')
                    ->assertVisible('#messagelist');
            }

            $browser->click('#messagelist tbody tr:nth-child(2)')
                ->waitForMessage('loading', 'Loading...')
                ->waitFor('#messagecontframe')
                ->waitUntilMissing('#messagestack');

            // On phone check frame controls
            if ($browser->isPhone()) {
                $browser->with('#layout-content .footer', function ($browser) {
                    $browser->assertVisible('a.button.prev:not(.disabled)')
                        ->assertVisible('a.button.next.disabled')
                        ->assertVisible('a.button.reply:not(.disabled)');
                });
            }

            $browser->withinFrame('#messagecontframe', function ($browser) {
                $browser->waitFor('img.contactphoto')
                    ->assertMissing('#remote-objects-message');

                // Attachments list
                $browser->with('#attachment-list', function ($browser) {
                    $browser->assertVisible('li:nth-child(1).text.plain')
                        ->assertSeeIn('li:nth-child(1) .attachment-name', 'lines.txt')
                        ->assertSeeIn('li:nth-child(1) .attachment-size', '(~13 B)')
                        ->assertVisible('li:nth-child(2).text.plain')
                        ->assertSeeIn('li:nth-child(2) .attachment-name', 'lines_lf.txt')
                        ->assertSeeIn('li:nth-child(2) .attachment-size', '(~11 B)')
                        ->click('li:nth-child(1) a.dropdown');
                });

                if (!$browser->isPhone()) {
                    $browser->waitFor('#attachmentmenu')
                        ->with('#attachmentmenu', function ($browser) {
                            $browser->assertVisible('a.extwin:not(.disabled)')
                                ->assertVisible('a.download:not(.disabled)')
                                ->click('a.download');
                    });
                }
            });

            if ($browser->isPhone()) {
                $browser->waitFor('#attachmentmenu-clone')
                    ->with('#attachmentmenu-clone', function ($browser) {
                        $browser->assertVisible('a.extwin:not(.disabled)')
                            ->assertVisible('a.download:not(.disabled)')
                            ->click('a.download');
                    });
            }

            $txt = $browser->readDownloadedFile('lines.txt');

            $this->assertTrue(strlen($txt) == 13);
            $this->assertSame("foo\r\nbar\r\ngna", $txt);
            $browser->removeDownloadedFile('lines.txt');

            // On phone check Back button
            if ($browser->isPhone()) {
                $browser->click('#layout-content .header a.back-list-button')
                    ->assertVisible('#messagelist');
            }
        });
    }

    /**
     * Test "X more..." link on mail preview with many recipients,
     * and some more
     *
     * @group failsonga-phone
     */
    public function testPreviewMorelink()
    {
        $this->browse(function ($browser) {
            $browser->go('mail');

            $browser->waitFor('#messagelist tbody tr:last-child')
                ->click('#messagelist tbody tr:last-child')
                //->waitForMessage('loading', 'Loading...')
                ->waitFor('#messagecontframe')
                ->waitUntilMissing('#messagestack');

            $browser->withinFrame('#messagecontframe', function ($browser) {
                $browser->waitFor('img.contactphoto');

                $browser->assertSeeIn('.subject', 'Lines')
                    ->assertSeeIn('.message-part div.pre', 'Plain text message body.')
                    ->assertVisible('.message-part div.pre .sig');

                $browser->assertMissing('.header-headers')
                    ->click('a.headers-details')
                    ->waitFor('.header-headers')
                    ->assertVisible('.header.cc')
                    ->assertSeeIn('.header.cc', 'test10@domain.tld')
                    ->assertDontSeeIn('.header.cc', 'test11@domain.tld')
                    ->assertSeeIn('.header.cc a.morelink', '2 more...')
                    ->click('.header.cc a.morelink');
            });

            $browser->with(new Dialog(), function ($browser) {
                $browser->assertDialogTitle('Cc')
                    ->assertDialogContent('test1@domain.tld')
                    ->assertDialogContent('test12@domain.tld')
                    ->assertElementsCount('@content span.adr', 12)
                    ->assertButton('cancel', 'Close')
                    ->clickButton('cancel');
            });
        });
    }
}
