<?php

namespace Tests\Browser\Plugins\Zipdownload;

use Tests\Browser\Components\Popupmenu;

class MailTest extends \Tests\Browser\TestCase
{
    public static function setUpBeforeClass(): void
    {
        \bootstrap::init_imap();
        \bootstrap::purge_mailbox('INBOX');

        // import single email messages
        foreach (glob(TESTS_DIR . 'data/mail/list_0?.eml') as $f) {
            \bootstrap::import_message($f, 'INBOX');
        }
    }

    public function testMailUI()
    {
        $this->browse(function ($browser) {
            $browser->go('mail');

            $browser->whenAvailable('#messagelist tbody', function ($browser) {
                $browser->ctrlClick('tr:first-child');
            });

            // Test More > Download > Source (single message selected)
            $browser->clickToolbarMenuItem('more')
                ->with(new Popupmenu('message-menu'), function ($browser) {
                    $browser->clickMenuItem('download');
                })
                ->with(new Popupmenu('zipdownload-menu'), function ($browser) {
                    $browser->assertVisible('a.download.eml:not(.disabled)')
                        ->assertSeeIn('a.download.eml', 'Source (.eml)')
                        ->assertVisible('a.download.mbox.disabled')
                        ->assertSeeIn('a.download.mbox', 'Mbox format (.zip)')
                        ->assertVisible('a.download.maildir.disabled')
                        ->assertSeeIn('a.download.maildir', 'Maildir format (.zip)')
                        ->click('a.download.eml');

                    $filename = 'Test HTML with local and remote image.eml';
                    $email = $browser->readDownloadedFile($filename);
                    $browser->removeDownloadedFile($filename);
                    $this->assertTrue(strpos($email, 'Subject: Test HTML with local and remote image') !== false);
                });

            // Test More > Download > Mailbox format (two messages selected)
            $browser->ctrlClick('#messagelist tbody tr:nth-of-type(2)')
                ->clickToolbarMenuItem('more')
                ->with(new Popupmenu('message-menu'), function ($browser) {
                    $browser->clickMenuItem('download');
                })
                ->with(new Popupmenu('zipdownload-menu'), function ($browser) {
                    $browser->assertVisible('a.download.eml.disabled')
                        ->assertVisible('a.download.mbox:not(.disabled)')
                        ->assertVisible('a.download.maildir:not(.disabled)')
                        ->click('a.download.mbox');

                    $filename = 'INBOX.zip';
                    $files = $this->getFilesFromZip($filename);
                    $browser->removeDownloadedFile($filename);

                    $this->assertSame(['INBOX.mbox'], $files);
                });

            // Test More > Download > Maildir format (two messages selected)
            $browser->clickToolbarMenuItem('more')
                ->with(new Popupmenu('message-menu'), function ($browser) {
                    $browser->clickMenuItem('download');
                })
                ->with(new Popupmenu('zipdownload-menu'), function ($browser) {
                    $browser->click('a.download.maildir');

                    $filename = 'INBOX.zip';
                    $files = $this->getFilesFromZip($filename);
                    $browser->removeDownloadedFile($filename);
                    $this->assertCount(2, $files);
                });

            // Test attachments download
            $browser->click('#messagelist tbody tr:nth-of-type(2)')
                ->waitForMessage('loading', 'Loading...')
                ->waitFor('#messagecontframe')
                ->waitUntilMissing('#messagestack')
                ->withinFrame('#messagecontframe', function ($browser) {
                    $browser->waitFor('.header-links a.zipdownload')
                        ->click('.header-links a.zipdownload');
                });

                $filename = 'Lines.zip';
                $files = $this->getFilesFromZip($filename);
                $browser->removeDownloadedFile($filename);
                $expected = ['lines.txt', 'lines_lf.txt'];
                $this->assertSame($expected, $files);
        });
    }

    /**
     * Helper to extract files list from downloaded zip file
     */
    private function getFilesFromZip($filename)
    {
        $filename = TESTS_DIR . "downloads/$filename";

        // Give the browser a chance to finish download
        if (!file_exists($filename)) {
            sleep(2);
        }

        $zip   = new \ZipArchive;
        $files = [];

        if ($zip->open($filename)) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $files[] = $zip->getNameIndex($i);
            }
        }

        $zip->close();

        return $files;
    }
}
