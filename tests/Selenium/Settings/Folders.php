<?php

class Selenium_Settings_Folders extends Selenium_Test
{
    public function testFolders()
    {
        $this->go('settings', 'folders');

        // task should be set to 'settings' and action to 'folders'
        $env = $this->get_env();
        $this->assertEquals('settings', $env['task']);
        $this->assertEquals('folders', $env['action']);

        $objects = $this->get_objects();

        // these objects should be there always
        $this->assertContains('quotadisplay', $objects);
        $this->assertContains('subscriptionlist', $objects);
    }
}
