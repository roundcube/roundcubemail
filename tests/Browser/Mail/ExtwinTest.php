<?php

namespace Roundcube\Tests\Browser\Mail;

use Roundcube\Tests\Browser\Bootstrap;
use Roundcube\Tests\Browser\Components\App;
use Roundcube\Tests\Browser\Components\Popupmenu;
use Roundcube\Tests\Browser\TestCase;
use PHPUnit\Framework\Assert;

class ExtwinTest extends TestCase
{
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        Bootstrap::init_imap(true);
        Bootstrap::purge_mailbox('INBOX');

        // import email messages
        foreach (glob(TESTS_DIR . 'data/mail/list_00.eml') as $f) {
            Bootstrap::import_message($f, 'INBOX');
        }
    }

    /**
     * Test opening mime parts in an external window.
     */
    public function testExtwin()
    {
        $this->browse(function ($browser) {
            $current_window = null;
            $new_window = null;
            $browser->go('mail');

            $browser->waitFor('#messagelist tbody tr:first-child')
                ->click('#messagelist tbody tr:first-child');

            // TODO: Does all of this work on phone-sized screens, too?

            $browser->waitFor('#messagecontframe');
            $browser->withinFrame('#messagecontframe', function ($browser) use (&$current_window, &$new_window) {
                $browser->waitFor('#message-content');
                [$current_window, $new_window] = $browser->openWindow(static function ($browser) {
                    $browser->click('ul#attachment-list li:first-of-type a.filename');
                });
            });
            $browser->driver->switchTo()->window($new_window);
            $browser->waitUntilMissing('.loading');

            # Test file content
            $browser->waitFor('#messagepartframe');
            $browser->withinFrame('#messagepartframe', static function ($browser) {
                $browser->waitFor('pre');
                $browser->assertElementsCount('*', 1);
                $browser->assertSeeIn('pre', "foo\nbar\ngna");
            });

            # Test toolbar to have 3 buttons.
            $browser->assertElementsCount('.header .toolbar > *', 3);

            # Test info popup
            $filename = 'lines.txt';

            $browser->click('.header .toolbar li:nth-child(1) a.info');
            $browser->waitFor('.ui-dialog');
            $browser->assertSeeIn('.ui-dialog .ui-dialog-content table.listing tr:nth-child(1) .title', "Name:");
            $browser->assertSeeIn('.ui-dialog .ui-dialog-content table.listing tr:nth-child(1) .header', "lines.txt");
            $browser->assertSeeIn('.ui-dialog .ui-dialog-content table.listing tr:nth-child(2) .title', "Type:");
            $browser->assertSeeIn('.ui-dialog .ui-dialog-content table.listing tr:nth-child(2) .header', "text/plain");
            $browser->assertSeeIn('.ui-dialog .ui-dialog-content table.listing tr:nth-child(3) .title', "Size:");
            $browser->assertSeeIn('.ui-dialog .ui-dialog-content table.listing tr:nth-child(3) .header', "~13 B");
            $browser->click('.ui-dialog .ui-dialog-titlebar-close');
            $browser->waitUntilMissing('.ui-dialog');

            # Test download
            $browser->click('.header .toolbar li:nth-child(2) a.download');
            $file_contents = $browser->readDownloadedFile($filename);
            $browser->removeDownloadedFile($filename);
            $this->assertEquals($file_contents, "foo\r\nbar\r\ngna");

            # Click the "print" button to check if it works. Unfortunately we can't test the actual printing dialog.
            $browser->click('.header .toolbar li:nth-child(3) a.print');

            $browser->driver->close();
            $browser->driver->switchTo()->window($current_window);
        });
    }
}
