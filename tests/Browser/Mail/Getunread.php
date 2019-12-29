<?php

namespace Tests\Browser\Mail;

class Getunread extends \Tests\Browser\DuskTestCase
{
    protected $msgcount = 0;

    protected function setUp()
    {
        parent::setUp();

        \bootstrap::init_imap();
        \bootstrap::purge_mailbox('INBOX');

        // import email messages
        foreach (glob(TESTS_DIR . 'data/mail/list_*.eml') as $f) {
            \bootstrap::import_message($f, 'INBOX');
            $this->msgcount++;
        }
    }

    public function testGetunread()
    {
        $this->browse(function ($browser) {
            $this->go('mail');

            // Folders list state
            $browser->waitFor('.folderlist li.inbox.unread');

            $this->assertEquals(strval($this->msgcount), $browser->text('.folderlist li.inbox span.unreadcount'));

            // Messages list state
            $this->assertCount($this->msgcount, $browser->elements('#messagelist tr.unread'));
        });
    }
}
