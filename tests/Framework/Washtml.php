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

        $this->assertEquals('<!-- node type 8 --><!-- html ignored --><!-- body ignored --><p>p2</p>', $washed, "HTML conditional comments (#1489004)");

        $html   = "<!--TestCommentInvalid><p>test</p>";
        $washed = $washer->wash($html);

        $this->assertEquals('<!-- html ignored --><!-- body ignored --><p>test</p>', $washed, "HTML invalid comments (#1487759)");

        $html   = "<p>para1</p><!-- comment --><p>para2</p>";
        $washed = $washer->wash($html);

        $this->assertEquals('<!-- html ignored --><!-- body ignored --><p>para1</p><!-- node type 8 --><p>para2</p>', $washed, "HTML comments - simple comment");

        $html   = "<p>para1</p><!-- <hr> comment --><p>para2</p>";
        $washed = $washer->wash($html);

        $this->assertEquals('<!-- html ignored --><!-- body ignored --><p>para1</p><!-- node type 8 --><p>para2</p>', $washed, "HTML comments - tags inside (#1489904)");
    }

    /**
     * Test fixing of invalid self-closing elements (#1489137)
     */
    function test_self_closing()
    {
        $html = "<textarea>test";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertRegExp('|<textarea>test</textarea>|', $washed, "Self-closing textarea (#1489137)");
    }

    /**
     * Test fixing of invalid closing tags (#1489446)
     */
    function test_closing_tag_attrs()
    {
        $html = "<a href=\"http://test.com\">test</a href>";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertRegExp('|</a>|', $washed, "Invalid closing tag (#1489446)");
    }

    /**
     * Test fixing of invalid lists nesting (#1488768)
     */
    function test_lists()
    {
        $data = array(
            array(
                "<ol><li>First</li><li>Second</li><ul><li>First sub</li></ul><li>Third</li></ol>",
                "<ol><li>First</li><li>Second<ul><li>First sub</li></ul></li><li>Third</li></ol>"
            ),
            array(
                "<ol><li>First<ul><li>First sub</li></ul></li></ol>",
                "<ol><li>First<ul><li>First sub</li></ul></li></ol>",
            ),
            array(
                "<ol><li>First<ol><li>First sub</li></ol></li></ol>",
                "<ol><li>First<ol><li>First sub</li></ol></li></ol>",
            ),
            array(
                "<ul><li>First</li><ul><li>First sub</li><ul><li>sub sub</li></ul></ul><li></li></ul>",
                "<ul><li>First<ul><li>First sub<ul><li>sub sub</li></ul></li></ul></li><li></li></ul>",
            ),
            array(
                "<ul><li>First</li><li>second</li><ul><ul><li>sub sub</li></ul></ul></ul>",
                "<ul><li>First</li><li>second<ul><ul><li>sub sub</li></ul></ul></li></ul>",
            ),
            array(
                "<ol><ol><ol></ol></ol></ol>",
                "<ol><ol><ol></ol></ol></ol>",
            ),
            array(
                "<div><ol><ol><ol></ol></ol></ol></div>",
                "<div><ol><ol><ol></ol></ol></ol></div>",
            ),
        );

        foreach ($data as $element) {
            rcube_washtml::fix_broken_lists($element[0]);

            $this->assertSame($element[1], $element[0], "Broken nested lists (#1488768)");
        }
    }

    /**
     * Test color style handling (#1489697)
     */
    function test_color_style()
    {
        $html = "<p style=\"font-size: 10px; color: rgb(241, 245, 218)\">a</p>";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertRegExp('|color: rgb\(241, 245, 218\)|', $washed, "Color style (#1489697)");
        $this->assertRegExp('|font-size: 10px|', $washed, "Font-size style");
    }

    /**
     * Test handling of unicode chars in style (#1489777)
     */
    function test_style_unicode()
    {
        $html = "<html><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
            <body><span style='font-family:\"新細明體\",\"serif\";color:red'>test</span></body></html>";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertRegExp('|style="font-family: \&quot;新細明體\&quot;,\&quot;serif\&quot;; color: red"|', $washed, "Unicode chars in style attribute - quoted (#1489697)");

        $html = "<html><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
            <body><span style='font-family:新細明體;color:red'>test</span></body></html>";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertRegExp('|style="font-family: 新細明體; color: red"|', $washed, "Unicode chars in style attribute (#1489697)");
    }

    /**
     * Test style item fixes
     */
    function test_style_wash()
    {
        $html = "<p style=\"line-height: 1; height: 10\">a</p>";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertRegExp('|line-height: 1;|', $washed, "Untouched line-height (#1489917)");
        $this->assertRegExp('|; height: 10px|', $washed, "Fixed height units");
    }

    /**
     * Test invalid style cleanup - XSS prevention (#1490227)
     */
    function test_style_wash_xss()
    {
        $html = "<img style=aaa:'\"/onerror=alert(1)//'>";
        $exp  = "<img style=\"aaa: '&quot;/onerror=alert(1)//'\" />";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertTrue(strpos($washed, $exp) !== false, "Style quotes XSS issue (#1490227)");

        $html = "<img style=aaa:'&quot;/onerror=alert(1)//'>";
        $exp  = "<img style=\"aaa: '&quot;/onerror=alert(1)//'\" />";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertTrue(strpos($washed, $exp) !== false, "Style quotes XSS issue (#1490227)");
    }
}
