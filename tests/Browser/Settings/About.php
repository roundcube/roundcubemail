<?php

namespace Tests\Browser\Settings;

class About extends \Tests\Browser\DuskTestCase
{
    public function testAbout()
    {
        $this->browse(function ($browser) {
            $this->go('settings', 'about');

            // check task and action
            $this->assertEnvEquals('task', 'settings');
            $this->assertEnvEquals('action', 'about');

            $browser->assertVisible('#pluginlist');
        });
    }
}
