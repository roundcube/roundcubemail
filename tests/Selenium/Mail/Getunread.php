<?php

class Selenium_Mail_Getunread extends Selenium_Test
{
    public function testGetunread()
    {
        $this->go('mail');

        $res = $this->ajaxResponse('getunread', "rcmail.http_request('getunread')");

        $this->assertEquals('getunread', $res['action']);
    }
}
