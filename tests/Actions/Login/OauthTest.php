<?php

namespace Roundcube\Mail\Tests\Actions\Login;

/**
 * Test class to test rcmail_action_login_oauth
 */
class OauthTest extends \ActionTestCase
{
    /**
     * Test run
     */
    public function test_run_login_redirect()
    {
        $action = new \rcmail_action_login_oauth();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'login', '');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $this->runAndAssert($action, \OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame("ERROR: Missing required OAuth config options 'oauth_auth_uri', 'oauth_client_id'", trim(\StderrMock::$output));
        $this->assertNull($output->getOutput());
    }
}
