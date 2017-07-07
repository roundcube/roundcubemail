<?php

class Selenium_Settings_About extends Selenium_Test
{
    public function testAbout()
    {
        $this->url(TESTS_URL . '?_task=settings&_action=about');
        sleep(TESTS_SLEEP);

        // check task and action
        $env = $this->get_env();
        $this->assertEquals('settings', $env['task']);
        $this->assertEquals('about', $env['action']);
    }
}
