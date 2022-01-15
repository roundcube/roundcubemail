<?php

namespace Tests\Browser\Mail;

class GetunreadTest extends \Tests\Browser\TestCase
{
    protected static $msgcount = 0;

    public static function setUpBeforeClass(): void
    {
        \bootstrap::init_imap(true);
        \bootstrap::purge_mailbox('INBOX');

        // import email messages
        foreach (glob(TESTS_DIR . 'data/mail/list_??.eml') as $f) {
            \bootstrap::import_message($f, 'INBOX');
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

            $this->assertEquals(strval(self::$msgcount), $browser->text('.folderlist li.inbox span.unreadcount'));
        });
    }
}
