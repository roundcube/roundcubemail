<?php

/**
 * Test class to test rcmail_action class
 *
 * @package Tests
 */
class Rcmail_RcmailAction extends ActionTestCase
{

    /**
     * Test rcmail_action::set_env_config()
     */
    function test_set_env_config()
    {
        $rcmail = rcmail::get_instance();

        $this->assertFalse($rcmail->config->get('ip_check'));
        rcmail_action::set_env_config(['ip_check']);
        $this->assertNull($rcmail->output->get_env('ip_check'));

        $rcmail->config->set('ip_check', true);
        rcmail_action::set_env_config(['ip_check']);
        $this->assertTrue($rcmail->output->get_env('ip_check'));
    }
}
