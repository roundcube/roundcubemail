<?php

class Selenium_Mail_CheckRecent extends Selenium_Test
{
    public function testCheckRecent()
    {
        $this->go('mail');

        $res = $this->ajaxResponse('check-recent', "rcmail.command('checkmail')");

        $this->assertEquals('check-recent', $res['action']);
        $this->assertRegExp('/this\.set_unread_count/', $res['exec']);
    }
}
