<?php

namespace Roundcube\Tests\Browser\Logon;

use Roundcube\Tests\Browser\Bootstrap;
use Roundcube\Tests\Browser\Components\App;
use Roundcube\Tests\Browser\TestCase;

class LoginTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        Bootstrap::init_db();
        Bootstrap::init_imap(true);
    }

    public function testLogin()
    {
        // First test, we're already on the logon page
        $this->browse(function ($browser) {
            $browser->visit('/');

            $browser->assertTitleContains($this->app->config->get('product_name'));

            // task should be set to 'login'
            $browser->with(new App(), static function ($browser) {
                $browser->assertEnv('task', 'login');
            });

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
            $browser->go('mail');

            // task should be set to 'mail' now
            $browser->with(new App(), static function ($browser) {
                $browser->assertEnv('task', 'mail');
            });
        });
    }
}
