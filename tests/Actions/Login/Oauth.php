<?php

/**
 * Test class to test rcmail_action_login_oauth
 *
 * @package Tests
 */
class Actions_Login_Oauth extends ActionTestCase
{
    /**
     * Test run
     */
    function test_run_login_redirect()
    {
        $action = new rcmail_action_login_oauth;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'login', '');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame("ERROR: Missing required OAuth config options 'oauth_auth_uri', 'oauth_client_id'", trim(StderrMock::$output));
        $this->assertSame(null, $output->getOutput());
    }
}
