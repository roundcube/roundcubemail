<?php

namespace Tests\Browser\Plugins\Markasjunk;

use PHPUnit\Framework\Attributes\Depends;
use Roundcube\Tests\Browser\Bootstrap;
use Roundcube\Tests\Browser\TestCase;

class MailTest extends TestCase
{
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        Bootstrap::init_db();
        Bootstrap::init_imap(true);
        Bootstrap::purge_mailbox('INBOX');
        Bootstrap::purge_mailbox('Junk');

        // import single email message
        Bootstrap::import_message(TESTS_DIR . 'data/mail/list_00.eml', 'INBOX');
    }

    /**
     * Test plugin functionality in Mail UI
     */
    public function testMailUI()
    {
        $this->browse(static function ($browser) {
            $browser->go('mail');

            // Toolbar menu (both buttons inactive)
            $browser->assertToolbarMenu([], ['junk', 'notjunk']);

            $browser->whenAvailable('#messagelist tbody', static function ($browser) {
                $browser->ctrlClick('tr:last-child');
            });

            // Both buttons are active in INBOX
            $browser->assertToolbarMenu(['junk', 'notjunk'], []);

            // Mark as ham in INBOX only feeds the learning backend, no move
            $browser->clickToolbarMenuItem('notjunk')
                ->waitForMessage('confirmation', 'Successfully reported as not junk')
                ->closeMessage('confirmation')
                ->waitUntilNotBusy()
                ->assertElementsCount('#messagelist tbody tr', 1);

            $browser->whenAvailable('#messagelist tbody', static function ($browser) {
                $browser->ctrlClick('tr:last-child');
            });

            $browser->clickToolbarMenuItem('junk')
                ->waitForMessage('confirmation', 'Successfully reported as junk')
                ->closeMessage('confirmation')
                ->assertElementsCount('#messagelist tbody tr', 0) // empty list
                ->waitForMessage('confirmation', 'Message(s) moved successfully.')
                ->closeMessage('confirmation');

            if (!$browser->isDesktop()) {
                $browser->click('.back-sidebar-button');
            }

            // Folders list
            $browser->whenAvailable('#mailboxlist', static function ($browser) {
                $browser->assertSeeIn('li.mailbox.junk .unreadcount', '1')
                    ->assertMissing('li.mailbox.inbox .unreadcount')
                    ->click('li.mailbox.junk')
                    ->waitUntilNotBusy();
            });

            // Toolbar menu (both buttons inactive)
            $browser->assertToolbarMenu([], ['junk', 'notjunk']);

            // Messages list contains the moved message
            $browser->whenAvailable('#messagelist tbody', static function ($browser) {
                $browser->assertElementsCount('tr', 1)
                    ->ctrlClick('tr:last-child');
            });

            // Both buttons are active in Junk
            $browser->assertToolbarMenu(['junk', 'notjunk'], []);

            // Mark as spam in Junk only feeds the learning backend, no move
            $browser->clickToolbarMenuItem('junk')
                ->waitForMessage('confirmation', 'Successfully reported as junk')
                ->closeMessage('confirmation')
                ->waitUntilNotBusy()
                ->assertElementsCount('#messagelist tbody tr', 1);

            $browser->whenAvailable('#messagelist tbody', static function ($browser) {
                $browser->ctrlClick('tr:last-child');
            });

            $browser->clickToolbarMenuItem('notjunk')
                ->waitForMessage('confirmation', 'Successfully reported as not junk')
                ->closeMessage('confirmation')
                ->assertElementsCount('#messagelist tbody tr', 0) // empty list
                ->waitForMessage('confirmation', 'Message(s) moved successfully.')
                ->closeMessage('confirmation');

            if (!$browser->isDesktop()) {
                $browser->click('.back-sidebar-button');
            }

            // Folders list, the message is back in INBOX
            $browser->whenAvailable('#mailboxlist', static function ($browser) {
                $browser->assertMissing('li.mailbox.junk .unreadcount')
                    ->assertSeeIn('li.mailbox.inbox .unreadcount', '1')
                    ->click('li.mailbox.inbox')
                    ->waitUntilNotBusy();
            });

            // Messages list contains the moved message
            $browser->whenAvailable('#messagelist tbody', static function ($browser) {
                $browser->assertElementsCount('tr', 1);
            });
        });
    }

    /**
     * Test plugin functionality on email preview page
     *
     * @depends testMailUI
     */
    #[Depends('testMailUI')]
    public function testMailView()
    {
        $this->browse(static function ($browser) {
            $browser->go('mail');

            $browser->whenAvailable('#messagelist tbody', static function ($browser) {
                $browser->click('tr:last-child');
            });

            $browser->waitFor('#messagecontframe')
                ->waitUntilMissing('#messagestack');

            // Both buttons are active in the message view
            $browser->assertToolbarMenu(['junk', 'notjunk'], []);

            // Move the message to Junk
            $browser->clickToolbarMenuItem('junk')
                ->waitFor('#messagelist')
                ->waitUntilNotBusy()
                ->assertElementsCount('#messagelist tbody tr', 0); // empty list

            if (!$browser->isDesktop()) {
                $browser->click('.back-sidebar-button');
            }

            // Folders list
            $browser->whenAvailable('#mailboxlist', static function ($browser) {
                $browser->click('li.mailbox.junk')
                    ->waitUntilNotBusy();
            });

            $browser->whenAvailable('#messagelist tbody', static function ($browser) {
                $browser->click('tr:last-child');
            });

            $browser->waitFor('#messagecontframe')
                ->waitUntilMissing('#messagestack', 10);

            // Both buttons are active in the message view
            $browser->assertToolbarMenu(['junk', 'notjunk'], []);

            // Move the message back to INBOX
            $browser->clickToolbarMenuItem('notjunk')
                ->waitFor('#messagelist')
                ->waitUntilNotBusy()
                ->assertElementsCount('#messagelist tbody tr', 0); // empty list
        });
    }
}
