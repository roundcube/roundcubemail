<?php

class Selenium_Logout extends Selenium_Test
{
    public function testLogout()
    {
        $this->go('mail');

        $this->click_button('logout');

        sleep(TESTS_SLEEP);

        // task should be set to 'login'
        $env = $this->get_env();
        $this->assertEquals('login', $env['task']);

        // form should exist
        $user_input = $this->byCssSelector('form input[name="_user"]');
    }
}
