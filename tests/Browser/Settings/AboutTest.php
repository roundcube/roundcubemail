<?php

namespace Tests\Browser\Settings;

use Tests\Browser\Components\App;
use Tests\Browser\Components\Dialog;

class AboutTest extends \Tests\Browser\TestCase
{
    public function testAbout()
    {
        $this->browse(function ($browser) {
            $browser->go('settings');

            $browser->clickTaskMenuItem('about');

            $browser->with(new Dialog(), function ($browser) {
                $browser->assertDialogTitle('About')
                    ->assertButton('cancel', 'Close')
                    ->assertVisible('@content #aboutframe');

                if ($url = \rcmail::get_instance()->config->get('support_url')) {
                    $browser->assertButton('mainaction.help', 'Get support');
                }
            });

            $browser->withinFrame('#aboutframe', function ($browser) {
                // check task and action
                $browser->with(new App(), function ($browser) {
                    $browser->assertEnv('task', 'settings');
                    $browser->assertEnv('action', 'about');
                });

                $browser->assertSee($this->app->config->get('product_name'));
                $browser->assertVisible('#pluginlist');
            });

            $browser->with(new Dialog(), function ($browser) {
                $browser->closeDialog();
            });
        });
    }
}
