<?php

class Selenium_Mail_List extends Selenium_Test
{
    public function testCheckRecent()
    {
        $this->go('mail');

        $res = $this->ajaxResponse('list', "rcmail.command('list')");

        $this->assertEquals('list', $res['action']);
        $this->assertRegExp('/this\.set_pagetitle/', $res['exec']);
        $this->assertRegExp('/this\.set_unread_count/', $res['exec']);
        $this->assertRegExp('/this\.set_rowcount/', $res['exec']);
        $this->assertRegExp('/this\.set_message_coltypes/', $res['exec']);
//        $this->assertRegExp('/this\.add_message_row/', $res['exec']);

        $this->assertContains('current_page', $res['env']);
        $this->assertContains('exists', $res['env']);
        $this->assertContains('pagecount', $res['env']);
        $this->assertContains('pagesize', $res['env']);
        $this->assertContains('messagecount', $res['env']);
        $this->assertContains('mailbox', $res['env']);
    }
}
