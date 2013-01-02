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

}
