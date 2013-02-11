<?php

class Selenium_Mail_Mail extends Selenium_Test
{
    public function testMail()
    {
        $this->go('mail');

        // check task
        $env = $this->get_env();
        $this->assertEquals('mail', $env['task']);

        $objects = $this->get_objects();

        // these objects should be there always
        $this->assertContains('qsearchbox', $objects);
        $this->assertContains('mailboxlist', $objects);
        $this->assertContains('messagelist', $objects);
        $this->assertContains('quotadisplay', $objects);
        $this->assertContains('search_filter', $objects);
        $this->assertContains('countdisplay', $objects);
    }
}
