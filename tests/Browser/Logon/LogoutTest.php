<?php

namespace Roundcube\Mail\Tests\Browser\Logon;

use Roundcube\Mail\Tests\Browser\Components\App;
use Roundcube\Mail\Tests\Browser\TestCase;

class LogoutTest extends TestCase
{
    public function testLogout()
    {
        $this->browse(static function ($browser) {
            $browser->go('settings');

            // click the Logout button in taskmenu
            $browser->clickTaskMenuItem('logout');

            // task should be set to 'login'
            $browser->with(new App(), static function ($browser) {
                $browser->assertEnv('task', 'login');
            });

            // form should exist
            $browser->assertVisible('input[name="_user"]');
            $browser->assertVisible('input[name="_pass"]');
            $browser->assertMissing('#taskmenu');
        });
    }
}
