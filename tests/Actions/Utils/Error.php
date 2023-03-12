<?php

/**
 * Test class to test rcmail_action_utils_error
 *
 * @package Tests
 */
class Actions_Utils_Error extends ActionTestCase
{
    /**
     * Test run() method in HTTP mode
     */
    function test_run_http()
    {
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'mail', 'test');
        $action = new rcmail_action_utils_error;

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Default error
        $this->runAndAssert($action, OutputHtmlMock::E_EXIT, []);

        $result = $output->getOutput();

        $this->assertSame('error', $output->template);
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
        $this->assertTrue(strpos($result, '<h3 class="error-title">SERVER ERROR!</h3>') !== false);
        $this->assertTrue(strpos($result, '<div class="error-text">Error No. [500]</div>') !== false);

        // TODO: Test error text for all error types
    }

    /**
     * Test run() method in AJAX mode
     */
    function test_run_ajax()
    {
        $_SERVER['HTTP_HOST']      = 'test.com';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '';

        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'compose');
        $action = new rcmail_action_utils_error;

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Default error
        $args = ['code' => 600];
        $this->runAndAssert($action, OutputJsonMock::E_EXIT, $args);

        $this->assertSame(null, $output->getOutput());
        $this->assertSame(["HTTP/1.0 500 Server Error!"], $output->headers);

        // 401
        $args = ['code' => 401, 'message' => 'test'];
        $this->runAndAssert($action, OutputJsonMock::E_EXIT, $args);

        $this->assertSame(null, $output->getOutput());
        $this->assertSame(["HTTP/1.0 401 Authorization Failed"], $output->headers);

        // 403
        $args = ['code' => 403, 'message' => 'test'];
        $this->runAndAssert($action, OutputJsonMock::E_EXIT, $args);

        $this->assertSame(null, $output->getOutput());
        $this->assertSame(["HTTP/1.0 403 Request Check Failed"], $output->headers);

        // 404
        $args = ['code' => 404, 'message' => 'test'];
        $this->runAndAssert($action, OutputJsonMock::E_EXIT, $args);

        $this->assertSame(null, $output->getOutput());
        $this->assertSame(["HTTP/1.0 404 File Not Found"], $output->headers);

        // 410
        $args = ['code' => 410, 'message' => 'test'];
        $this->runAndAssert($action, OutputJsonMock::E_EXIT, $args);

        $this->assertSame(null, $output->getOutput());
        $this->assertSame(["HTTP/1.0 410 Server Error!"], $output->headers);

        // 450
        $args = ['code' => 450, 'message' => 'test'];
        $this->runAndAssert($action, OutputJsonMock::E_EXIT, $args);

        $this->assertSame(null, $output->getOutput());
        $this->assertSame(["HTTP/1.0 450 Compose session error"], $output->headers);

        // 601
        $args = ['code' => 601, 'message' => 'test'];
        $this->runAndAssert($action, OutputJsonMock::E_EXIT, $args);

        $this->assertSame(null, $output->getOutput());
        $this->assertSame(["HTTP/1.0 500 Configuration error"], $output->headers);

        // 603
        $args = ['code' => 603, 'message' => 'test'];
        $this->runAndAssert($action, OutputJsonMock::E_EXIT, $args);

        $this->assertSame(null, $output->getOutput());
        $this->assertSame(["HTTP/1.0 500 Database Error!"], $output->headers);
    }
}
