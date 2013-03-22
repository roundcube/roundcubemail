<?php

/**
 * Test class to test rcube_washtml class
 *
 * @package Tests
 */
class Framework_Washtml extends PHPUnit_Framework_TestCase
{

    /**
     * Test the elimination of some XSS vulnerabilities
     */
    function test_html_xss3()
    {
        // #1488850
        $html = '<p><a href="data:text/html,&lt;script&gt;alert(document.cookie)&lt;/script&gt;">Firefox</a>'
            .'<a href="vbscript:alert(document.cookie)">Internet Explorer</a></p>';

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertNotRegExp('/data:text/', $washed, "Remove data:text/html links");
        $this->assertNotRegExp('/vbscript:/', $washed, "Remove vbscript: links");
    }

    /**
     * Test fixing of invalid href (#1488940)
     */
    function test_href()
    {
        $html = "<p><a href=\"\nhttp://test.com\n\">Firefox</a>";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertRegExp('|href="http://test.com">|', $washed, "Link href with newlines (#1488940)");
    }

    /**
     * Test handling HTML comments
     */
    function test_comments()
    {
        $washer = new rcube_washtml;

        $html   = "<!--[if gte mso 10]><p>p1</p><!--><p>p2</p>";
        $washed = $washer->wash($html);

        $this->assertEquals('<!-- html ignored --><!-- body ignored --><p>p2</p>', $washed, "HTML conditional comments (#1489004)");

        $html   = "<!--TestCommentInvalid><p>test</p>";
        $washed = $washer->wash($html);

        $this->assertEquals('<!-- html ignored --><!-- body ignored --><p>test</p>', $washed, "HTML invalid comments (#1487759)");
    }

}
