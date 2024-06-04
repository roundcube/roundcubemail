<?php

namespace Roundcube\Tests\Rcmail;

use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_html_page class
 */
class HtmlPageTest extends ActionTestCase
{
    /**
     * Test html page output
     */
    public function test_html_output()
    {
        $page = new \rcmail_html_page();

        $page->register_inline_warning('Test', 'Button', 'http://url');

        ob_start();
        $page->write();
        $output = ob_get_contents();
        ob_end_clean();

        $expected_body = '<body><div class="rcmail-inline-message rcmail-inline-warning"><span>Test</span>'
            . '<p class="rcmail-inline-buttons"><button onclick="location.href = \'http://url\'">Button</button></p></div>';

        $this->assertTrue(strpos($output, '<html') === 0);
        $this->assertTrue(strpos($output, $expected_body) !== false);
    }
}
