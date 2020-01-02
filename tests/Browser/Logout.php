<?php

namespace Tests\Browser;

class Logout extends DuskTestCase
{
    public function testLogout()
    {
        $this->browse(function ($browser) {
            $this->go('settings');

            // click the Logout button in taskmenu
            $this->clickTaskMenuItem('logout');

            // task should be set to 'login'
            $this->assertEnvEquals('task', 'login');

            // form should exist
            $browser->assertVisible('input[name="_user"]');
            $browser->assertVisible('input[name="_pass"]');
            $browser->assertMissing('#taskmenu');
        });
    }
}
