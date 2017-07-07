<?php

class Selenium_Mail_List extends Selenium_Test
{
    protected function setUp()
    {
        parent::setUp();

        bootstrap::init_imap();
        bootstrap::purge_mailbox('INBOX');

        // import email messages
        foreach (glob(TESTS_DIR . 'Selenium/data/mail/list_00.eml') as $f) {
            bootstrap::import_message($f, 'INBOX');
        }
    }

    public function testList()
    {
        $this->go('mail');

        $res = $this->ajaxResponse('list', "rcmail.command('list')");

        $this->assertEquals('list', $res['action']);
        $this->assertRegExp('/this\.set_pagetitle/', $res['exec']);
        $this->assertRegExp('/this\.set_unread_count/', $res['exec']);
        $this->assertRegExp('/this\.set_rowcount/', $res['exec']);
        $this->assertRegExp('/this\.set_message_coltypes/', $res['exec']);

        $this->assertContains('current_page', $res['env']);
        $this->assertContains('exists', $res['env']);
        $this->assertContains('pagecount', $res['env']);
        $this->assertContains('pagesize', $res['env']);
        $this->assertContains('messagecount', $res['env']);
        $this->assertContains('mailbox', $res['env']);

        $this->assertEquals($res['env']['mailbox'], 'INBOX');
        $this->assertEquals($res['env']['messagecount'], 1);

        // check message list
        $row = $this->byCssSelector('.messagelist tbody tr:first-child');
        $this->assertHasClass('unread', $row);

        $subject = $this->byCssSelector('.messagelist tbody tr:first-child td.subject');
        $this->assertEquals('Lines', $this->getText($subject));

        $icon = $this->byCssSelector('.messagelist tbody tr:first-child td.status span');
        $this->assertHasClass('unread', $icon);
    }
}
