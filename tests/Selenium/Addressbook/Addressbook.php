<?php

class Selenium_Addressbook_Addressbook extends Selenium_Test
{
    public function testAddressbook()
    {
        $this->go('addressbook');

        // check task
        $env = $this->get_env();
        $this->assertEquals('addressbook', $env['task']);

        $objects = $this->get_objects();

        // these objects should be there always
        $this->assertContains('qsearchbox', $objects);
        $this->assertContains('folderlist', $objects);
        $this->assertContains('contactslist', $objects);
        $this->assertContains('countdisplay', $objects);
    }
}
