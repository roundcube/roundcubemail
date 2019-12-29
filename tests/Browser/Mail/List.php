<?php

namespace Tests\Browser\Mail;

class MailList extends \Tests\Browser\DuskTestCase
{
    protected function setUp()
    {
        parent::setUp();

        \bootstrap::init_imap();
        \bootstrap::purge_mailbox('INBOX');

        // import email messages
        foreach (glob(TESTS_DIR . 'data/mail/list_00.eml') as $f) {
            \bootstrap::import_message($f, 'INBOX');
        }
    }

    public function testList()
    {
        $this->browse(function ($browser) {
            $this->go('mail');

            $this->assertCount(1, $browser->elements('#messagelist tbody tr'));

            // check message list
            $browser->assertVisible('#messagelist tbody tr:first-child.unread');

            $this->assertEquals('Lines', $browser->text('#messagelist tbody tr:first-child span.subject'));

            //$browser->assertVisible('#messagelist tbody tr:first-child span.msgicon.unread');
        });
    }
}
