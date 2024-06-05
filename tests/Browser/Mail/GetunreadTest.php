<?php

namespace Roundcube\Tests\Browser\Mail;

use Roundcube\Tests\Browser\Bootstrap;
use Roundcube\Tests\Browser\TestCase;

class GetunreadTest extends TestCase
{
    protected static $msgcount = 0;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        Bootstrap::init_imap(true);
        Bootstrap::purge_mailbox('INBOX');

        // import email messages
        foreach (glob(TESTS_DIR . 'data/mail/list_??.eml') as $f) {
            Bootstrap::import_message($f, 'INBOX');
            self::$msgcount++;
        }
    }

    public function testGetunread()
    {
        $this->browse(function ($browser) {
            $browser->go('mail');

            $browser->waitFor('#messagelist tbody tr');

            // Messages list state
            $browser->assertElementsCount('#messagelist tbody tr.unread', self::$msgcount);

            if (!$browser->isDesktop()) {
                $browser->click('.back-sidebar-button');
            }

            // Folders list state
            $browser->assertVisible('.folderlist li.inbox.unread');

            $this->assertSame(strval(self::$msgcount), $browser->text('.folderlist li.inbox span.unreadcount'));
        });
    }
}
