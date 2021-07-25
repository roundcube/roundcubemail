<?php

namespace Tests\Browser\Plugins\Archive;

use Tests\Browser\Components\Popupmenu;

class MailTest extends \Tests\Browser\TestCase
{
    public static function setUpBeforeClass(): void
    {
        \bootstrap::init_db();
        \bootstrap::init_imap();
        \bootstrap::purge_mailbox('INBOX');
        \bootstrap::purge_mailbox('Archive');

        // import single email messages
        foreach (glob(TESTS_DIR . 'data/mail/list_00.eml') as $f) {
            \bootstrap::import_message($f, 'INBOX');
        }
    }

    public function testMailUI()
    {
        $this->browse(function ($browser) {
            $browser->go('mail');

            if (!$browser->isDesktop()) {
                $browser->click('.back-sidebar-button');
            }

            // Folders list
            $browser->whenAvailable('#mailboxlist', function ($browser) {
                $browser->assertVisible('li.mailbox.archive')
                    ->assertMissing('li.mailbox.archive .unreadcount');
            });

            if (!$browser->isDesktop()) {
                $browser->click('.back-list-button');
            }

            // Toolbar menu (Archive button inactive)
            $browser->assertToolbarMenu([], ['archive']);

            $browser->whenAvailable('#messagelist tbody', function ($browser) {
                $browser->ctrlClick('tr:last-child');
            });

            $browser->clickToolbarMenuItem('archive')
                ->waitForMessage('confirmation', 'Successfully archived')
                ->closeMessage('confirmation');

            if (!$browser->isDesktop()) {
                $browser->click('.back-sidebar-button');
            }

            // Folders list
            $browser->whenAvailable('#mailboxlist', function ($browser) {
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
                ->clickToolbarMenuItem('more')
                    ->with(new Popupmenu('message-menu'), function ($browser) {
                        $browser->clickMenuItem('move');
                    })
                    ->with(new Popupmenu('folder-selector'), function ($browser) {
                        $browser->assertVisible('li.archive')
                            ->assertSeeIn('li.archive', 'Archive');
                    })
                    ->click(); // close menus
        });
    }
}
