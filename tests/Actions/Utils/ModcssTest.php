<?php

namespace Roundcube\Tests\Actions\Utils;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputHtmlMock;

use function Roundcube\Tests\setHttpClientMock;

/**
 * Test class to test rcmail_action_utils_modcss
 */
class ModcssTest extends ActionTestCase
{
    /**
     * Test for run()
     */
    public function test_run()
    {
        $action = new \rcmail_action_utils_modcss();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'utils', 'modcss');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        // No input parameters
        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $this->assertSame(403, $output->getProperty('errorCode'));
        $this->assertSame('Unauthorized request', $output->getProperty('errorMessage'));
        $this->assertNull($output->getOutput());

        // Invalid url
        $_GET['_u'] = '****';
        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $this->assertSame(403, $output->getProperty('errorCode'));
        $this->assertSame('Unauthorized request', $output->getProperty('errorMessage'));
        $this->assertNull($output->getOutput());

        // Valid url but not "registered"
        $url = 'https://raw.githubusercontent.com/roundcube/roundcubemail/master/aaaaaaaaaa';
        $key = 'tmp-123.css';
        $_GET['_u'] = $key;

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $this->assertSame(403, $output->getProperty('errorCode'));
        $this->assertSame('Unauthorized request', $output->getProperty('errorMessage'));
        $this->assertNull($output->getOutput());

        // Valid url pointing to non-existing resource
        $_SESSION['modcssurls'][$key] = $url;

        setHttpClientMock([
            ['code' => 404],
            ['code' => 200, 'headers' => ['Content-Type' => 'text/css'], 'response' => 'div.pre { display: none; }'],
        ]);

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $this->assertSame(404, $output->getProperty('errorCode'));
        $this->assertSame('Invalid response returned by server', $output->getProperty('errorMessage'));
        $this->assertNull($output->getOutput());

        // Valid url pointing to an existing resource
        $url = 'https://raw.githubusercontent.com/roundcube/roundcubemail/master/program/resources/tinymce/content.css';
        $_SESSION['modcssurls'][$key] = $url;
        $_GET['_p'] = 'prefix';
        $_GET['_c'] = 'cid';

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $this->assertNull($output->getProperty('errorCode'));
        $this->assertSame(['Content-Type: text/css'], $output->getProperty('headers'));
        $this->assertStringContainsString('#cid div.prefixpre', $output->getOutput());
    }
}
