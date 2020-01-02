<?php

namespace Tests\Browser\Settings;

class About extends \Tests\Browser\DuskTestCase
{
    public function testAbout()
    {
        $this->browse(function ($browser) {
            $this->go('settings');

            $this->clickTaskMenuItem('about');

            $browser->assertSeeIn('.ui-dialog-title', 'About');
            $browser->assertVisible('.ui-dialog #aboutframe');

            $browser->withinFrame('#aboutframe', function ($browser) {
                // check task and action
                $this->assertEnvEquals('task', 'settings');
                $this->assertEnvEquals('action', 'about');

                $browser->assertSee($this->app->config->get('product_name'));
                $browser->assertVisible('#pluginlist');
            });
        });
    }
}
