<?php

/**
 * Test class to test rcmail_action_login_oauth
 */
class Actions_Login_Oauth extends ActionTestCase
{
    /**
     * Test run
     */
    public function test_run_login_redirect()
    {
        $action = new rcmail_action_login_oauth();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'login', '');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame("ERROR: Missing required OAuth config options 'oauth_auth_uri', 'oauth_client_id'", trim(StderrMock::$output));
        self::assertNull($output->getOutput());
    }
}
