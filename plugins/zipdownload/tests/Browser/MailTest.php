<?php

namespace Tests\Browser\Plugins\Zipdownload;

use Roundcube\Tests\Browser\Bootstrap;
use Roundcube\Tests\Browser\Components\Popupmenu;
use Roundcube\Tests\Browser\TestCase;

class MailTest extends TestCase
{
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        Bootstrap::init_imap(true);
        Bootstrap::purge_mailbox('INBOX');

        // import single email messages
        foreach (glob(TESTS_DIR . 'data/mail/list_0?.eml') as $f) {
            Bootstrap::import_message($f, 'INBOX');
        }
    }

    public function testMailUI()
    {
        $this->browse(function ($browser) {
            $browser->go('mail');

            $browser->whenAvailable('#messagelist tbody', static function ($browser) {
                $browser->ctrlClick('tr:first-child');
            });

            // Test More > Download > Source (single message selected)
            $browser->clickToolbarMenuItem('more', null, false)
                ->with(new Popupmenu('message-menu'), static function ($browser) {
                    $browser->clickMenuItem('download', null, false);
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
                    $this->assertTrue(str_contains($email, 'Subject: Test HTML with local and remote image'));
                });

            // Test More > Download > Mailbox format (two messages selected)
            $browser->ctrlClick('#messagelist tbody tr:nth-of-type(2)')
                ->clickToolbarMenuItem('more', null, false)
                ->with(new Popupmenu('message-menu'), static function ($browser) {
                    $browser->clickMenuItem('download', null, false);
                })
                ->with(new Popupmenu('zipdownload-menu'), function ($browser) {
                    $browser->assertVisible('a.download.eml.disabled')
                        ->assertVisible('a.download.mbox:not(.disabled)')
                        ->assertVisible('a.download.maildir:not(.disabled)')
                        ->click('a.download.mbox');

                    $filename = 'INBOX.zip';
                    $files = $this->getFilesFromZip($browser, $filename);
                    $browser->removeDownloadedFile($filename);

                    $this->assertSame(['INBOX.mbox'], $files);
                });

            // Test More > Download > Maildir format (two messages selected)
            $browser->clickToolbarMenuItem('more', null, false)
                ->waitFor('#message-menu')
                ->with(new Popupmenu('message-menu'), static function ($browser) {
                    $browser->clickMenuItem('download', null, false);
                })
                ->waitFor('#zipdownload-menu')
                ->with(new Popupmenu('zipdownload-menu'), function ($browser) {
                    $browser->click('a.download.maildir');

                    $filename = 'INBOX.zip';
                    $files = $this->getFilesFromZip($browser, $filename);
                    $browser->removeDownloadedFile($filename);
                    $this->assertCount(2, $files);
                });

            // Test attachments download
            $browser->click('#messagelist tbody tr:nth-of-type(2)')
                ->waitForMessage('loading', 'Loading...')
                ->waitFor('#messagecontframe')
                ->waitUntilMissing('#messagestack')
                ->withinFrame('#messagecontframe', static function ($browser) {
                    $browser->waitFor('.header-links a.zipdownload')
                        ->click('.header-links a.zipdownload');
                });

            $filename = 'Lines.zip';
            $files = $this->getFilesFromZip($browser, $filename);
            $browser->removeDownloadedFile($filename);
            $expected = ['lines.txt', 'lines_lf.txt'];
            $this->assertSame($expected, $files);
        });
    }

    /**
     * Helper to extract files list from downloaded zip file
     */
    private function getFilesFromZip($browser, $filename)
    {
        $filename = $browser->getDownloadedFilePath($filename);

        // Give the browser a chance to finish download
        $attempts = 0;
        while (!file_exists($filename)) {
            if ($attempts > 9) {
                throw new \Exception("File not found even after waiting period: {$filename}");
            }
            sleep(1);
            $attempts++;
        }
        // Wait until the file size doesn't change anymore to be sure to have
        // the full file. Under some circumstances the file apparently was used
        // before its content was fully written (and sync'ed across the FS
        // mounts).
        $attempts = 0;
        do {
            if ($attempts > 9) {
                throw new \Exception("File size continues to change, something is wrong! File: {$filename}");
            }
            $filesize1 = stat($filename)['size'];
            sleep(1);
            $filesize2 = stat($filename)['size'];
            $attempts++;
        } while ($filesize1 !== $filesize2);

        $zip = new \ZipArchive();
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
