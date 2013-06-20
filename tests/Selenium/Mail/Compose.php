<?php

class Selenium_Mail_Compose extends Selenium_Test
{
    public function testCompose()
    {
        $this->go('mail', 'compose');

        // check task and action
        $env = $this->get_env();
        $this->assertEquals('mail', $env['task']);
        $this->assertEquals('compose', $env['action']);

        $objects = $this->get_objects();

        // these objects should be there always
        $this->assertContains('qsearchbox', $objects);
        $this->assertContains('addressbookslist', $objects);
        $this->assertContains('contactslist', $objects);
        $this->assertContains('messageform', $objects);
        $this->assertContains('attachmentlist', $objects);
        $this->assertContains('filedrop', $objects);
        $this->assertContains('uploadform', $objects);
    }
}
