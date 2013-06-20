<?php

class Selenium_Addressbook_Import extends Selenium_Test
{
    public function testImport()
    {
        $this->go('addressbook', 'import');

        // check task and action
        $env = $this->get_env();
        $this->assertEquals('addressbook', $env['task']);
        $this->assertEquals('import', $env['action']);

        $objects = $this->get_objects();

        // these objects should be there always
        $this->assertContains('importform', $objects);
    }

    public function testImport2()
    {
        $this->go('addressbook', 'import');

        $objects = $this->get_objects();

        // these objects should be there always
        $this->assertContains('importform', $objects);
    }
}
