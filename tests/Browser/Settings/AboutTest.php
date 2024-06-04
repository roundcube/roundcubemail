<?php

namespace Roundcube\Tests\Browser\Settings;

use Roundcube\Tests\Browser\Components\App;
use Roundcube\Tests\Browser\Components\Dialog;
use Roundcube\Tests\Browser\TestCase;

class AboutTest extends TestCase
{
    public function testAbout()
    {
        $this->browse(function ($browser) {
            $browser->go('settings');

            $browser->clickTaskMenuItem('about');

            $browser->with(new Dialog(), static function ($browser) {
                $browser->assertDialogTitle('About')
                    ->assertButton('cancel', 'Close')
                    ->assertVisible('@content #aboutframe');

                if ($url = \rcmail::get_instance()->config->get('support_url')) {
                    $browser->assertButton('mainaction.help', 'Get support');
                }
            });

            $browser->withinFrame('#aboutframe', function ($browser) {
                // check task and action
                $browser->with(new App(), static function ($browser) {
                    $browser->assertEnv('task', 'settings');
                    $browser->assertEnv('action', 'about');
                });

                $browser->assertSee($this->app->config->get('product_name'));
                $browser->assertVisible('#pluginlist');
            });

            $browser->with(new Dialog(), static function ($browser) {
                $browser->closeDialog();
            });
        });
    }
}
