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
        // First test, we're already on the logon page
        $this->browse(function ($browser) {
            $browser->visit('/');

            $browser->assertTitleContains($this->app->config->get('product_name'));

            // task should be set to 'login'
            $this->assertEnvEquals('task', 'login');

            // Logon form
            $browser->assertVisible('#logo');
            $browser->assertVisible('#login-form');
            $browser->assertVisible('#rcmloginuser');
            $browser->assertVisible('#rcmloginpwd');
            $browser->assertVisible('#rcmloginsubmit');
            $browser->assertSee($this->app->config->get('product_name'));

            // Support link
            if ($url = $this->app->config->get('support_url')) {
                $browser->assertSeeLink('Get support');
                $this->assertStringStartsWith($url, $browser->attribute('.support-link', 'href'));
            }

            // test valid login
            $this->go('mail');

            // task should be set to 'mail' now
            $this->assertEnvEquals('task', 'mail');
        });
    }
}
