<?php

namespace Tests\Browser\Plugins\Archive;

use Roundcube\Tests\Browser\Bootstrap;
use Roundcube\Tests\Browser\Components\Popupmenu;
use Roundcube\Tests\Browser\TestCase;

class MailTest extends TestCase
{
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        Bootstrap::init_db();
        Bootstrap::init_imap();
        Bootstrap::purge_mailbox('INBOX');
        Bootstrap::purge_mailbox('Archive');

        // import single email messages
        foreach (glob(TESTS_DIR . 'data/mail/list_00.eml') as $f) {
            Bootstrap::import_message($f, 'INBOX');
        }
    }

    public function testMailUI()
    {
        $this->browse(static function ($browser) {
            $browser->go('mail');

            if (!$browser->isDesktop()) {
                $browser->click('.back-sidebar-button');
            }

            // Folders list
            $browser->whenAvailable('#mailboxlist', static function ($browser) {
                $browser->assertVisible('li.mailbox.archive')
                    ->assertMissing('li.mailbox.archive .unreadcount');
            });

            if (!$browser->isDesktop()) {
                $browser->click('.back-list-button');
            }

            // Toolbar menu (Archive button inactive)
            $browser->assertToolbarMenu([], ['archive']);

            $browser->whenAvailable('#messagelist tbody', static function ($browser) {
                $browser->ctrlClick('tr:last-child');
            });

            $browser->clickToolbarMenuItem('archive')
                ->waitForMessage('confirmation', 'Successfully archived')
                ->closeMessage('confirmation');

            if (!$browser->isDesktop()) {
                $browser->click('.back-sidebar-button');
            }

            // Folders list
            $browser->whenAvailable('#mailboxlist', static function ($browser) {
                $browser->assertSeeIn('li.mailbox.archive .unreadcount', '1')
                    ->click('li.mailbox.archive')
                    ->waitUntilNotBusy();
            });

            // Messages list contains the moved message
            $browser->assertElementsCount('#messagelist tbody tr', 1);

            // Toolbar menu (Archive button inactive again)
            $browser->assertToolbarMenu([], ['archive']);

            // Test archive class on folder in folder selector
            $browser->ctrlClick('#messagelist tbody tr')
                ->clickToolbarMenuItem('more', null, false)
                ->with(new Popupmenu('message-menu'), static function ($browser) {
                    $browser->clickMenuItem('move');
                })
                ->with(new Popupmenu('folder-selector'), static function ($browser) {
                    $browser->assertVisible('li.archive')
                        ->assertSeeIn('li.archive', 'Archive');
                })
                ->click(); // close menus
        });
    }
}
