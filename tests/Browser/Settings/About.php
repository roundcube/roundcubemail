<?php

namespace Tests\Browser\Settings;

use Tests\Browser\Components\App;

class About extends \Tests\Browser\TestCase
{
    public function testAbout()
    {
        $this->browse(function ($browser) {
            $browser->go('settings');

            $browser->clickTaskMenuItem('about');

            $browser->assertSeeIn('.ui-dialog-title', 'About');
            $browser->assertVisible('.ui-dialog #aboutframe');

            $browser->withinFrame('#aboutframe', function ($browser) {
                // check task and action
                $browser->with(new App(), function ($browser) {
                    $browser->assertEnv('task', 'settings');
                    $browser->assertEnv('action', 'about');
                });

                $browser->assertSee($this->app->config->get('product_name'));
                $browser->assertVisible('#pluginlist');
            });
        });
    }
}
