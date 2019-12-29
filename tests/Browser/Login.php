<?php

namespace Tests\Browser;

class Login extends DuskTestCase
{
    protected function setUp()
    {
        parent::setUp();

        \bootstrap::init_db();
        \bootstrap::init_imap();
    }

    public function testLogin()
    {
        // first test, we're already on the login page
        $this->browse(function ($browser) {
            $browser->visit('/');

            $browser->assertTitleContains($this->app->config->get('product_name'));

            // task should be set to 'login'
            $this->assertEnvEquals('task', 'login');

            $browser->assertVisible('#rcmloginuser');
            $browser->assertVisible('#rcmloginpwd');

            // test valid login
            $this->go('mail');

            // task should be set to 'mail' now
            $this->assertEnvEquals('task', 'mail');
        });
    }
}
