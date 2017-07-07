<?php

class Selenium_Login extends Selenium_Test
{
    protected function setUp()
    {
        bootstrap::init_db();
        bootstrap::init_imap();
        parent::setUp();
    }

    public function testLogin()
    {
        // first test, we're already on the login page
        $this->url(TESTS_URL);

        // task should be set to 'login'
        $env = $this->get_env();
        $this->assertEquals('login', $env['task']);

        // test valid login
        $this->login();

        // task should be set to 'mail' now
        $env = $this->get_env();
        $this->assertEquals('mail', $env['task']);
    }
}
