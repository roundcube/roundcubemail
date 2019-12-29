<?php

namespace Tests\Browser\Settings;

class Settings extends \Tests\Browser\DuskTestCase
{
    public function testSettings()
    {
        $this->browse(function ($browser) {
            $this->go('settings');

            // task should be set to 'settings'
            $this->assertEnvEquals('task', 'settings');

            $objects = $this->getObjects();

            $this->assertContains('sectionslist', $objects);
        });
    }
}
