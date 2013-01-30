<?php

class Selenium_Settings_Identities extends Selenium_Test
{
    public function testIdentities()
    {
        $this->go('settings', 'identities');

        // check task and action
        $env = $this->get_env();
        $this->assertEquals('settings', $env['task']);
        $this->assertEquals('identities', $env['action']);

        $objects = $this->get_objects();

        // these objects should be there always
        $this->assertContains('identitieslist', $objects);
    }
}
