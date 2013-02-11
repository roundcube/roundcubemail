<?php

class Selenium_Settings_Settings extends Selenium_Test
{
    public function testSettings()
    {
        $this->go('settings');

        // task should be set to 'settings'
        $env = $this->get_env();
        $this->assertEquals('settings', $env['task']);

        $objects = $this->get_objects();

        $this->assertContains('sectionslist', $objects);
    }
}
