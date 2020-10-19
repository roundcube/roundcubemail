<?php

/**
 * Test class to test rcube_washtml class
 *
 * @package Tests
 */
class Framework_Washtml extends PHPUnit\Framework\TestCase
{
    /**
     * A helper method to remove comments added by rcube_washtml
     */
    function cleanupResult($html)
    {
        return preg_replace('/<!-- [a-z]+ (ignored|not allowed) -->/', '', $html);
    }


    /**
     * Test the elimination of some XSS vulnerabilities
     */
    function test_html_xss()
    {
        // #1488850
        $html = '<a href="data:text/html,&lt;script&gt;alert(document.cookie)&lt;/script&gt;">Firefox</a>'
            .'<a href="vbscript:alert(document.cookie)">Internet Explorer</a></p>'
            .'<A href="data:text/html,&lt;script&gt;alert(document.cookie)&lt;/script&gt;">Firefox</a>'
            .'<A HREF="vbscript:alert(document.cookie)">Internet Explorer</a>'
            .'<a href="data:application/xhtml+xml;base64,PGh0bW">CLICK ME</a>'; // #6896

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertNotRegExp('/data:text/', $washed, "Remove data:text/html links");
        $this->assertNotRegExp('/vbscript:/', $washed, "Remove vbscript: links");
        $this->assertNotRegExp('/data:application/', $washed, "Remove data:application links");
    }

    /**
     * Test fixing of invalid href
     */
    function test_href()
    {
        $html = "<p><a href=\"\nhttp://test.com\n\">Firefox</a><a href=\"domain.com\">Firefox</a>";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertRegExp('|href="http://test\.com"|', $washed, "Link href with newlines (#1488940)");
        $this->assertRegExp('|href="http://domain\.com"|', $washed, "Link href with no protocol (#7454)");
    }

    /**
     * Test XSS in area's href (#5240)
     */
    function test_href_area()
    {
        $html = '<p><area href="data:text/html,&lt;script&gt;alert(document.cookie)&lt;/script&gt;">'
            . '<area href="vbscript:alert(document.cookie)">Internet Explorer</p>'
            . '<area href="javascript:alert(document.domain)" shape=default>'
            . '<p><AREA HREF="data:text/html,&lt;script&gt;alert(document.cookie)&lt;/script&gt;">'
            . '<Area href="vbscript:alert(document.cookie)">Internet Explorer</p>'
            . '<area HREF="javascript:alert(document.domain)" shape=default>';

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertNotRegExp('/data:text/', $washed, "data:text/html in area href");
        $this->assertNotRegExp('/vbscript:/', $washed, "vbscript: in area href");
        $this->assertNotRegExp('/javascript:/', $washed, "javascript: in area href");
    }

    /**
     * Test removing of object tag, but keeping innocent children
     */
    function test_object()
    {
        $html = "<div>\n<object data=\"move.swf\" type=\"application/x-shockwave-flash\">\n"
               ."<param name=\"foo\" value=\"bar\">\n"
               ."<p>This alternative text should survive</p>"
               ."</object>\n</div>";
        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertNotRegExp('/<\/?object/', $washed, "Remove object tag");
        $this->assertNotRegExp('/<param/', $washed, "Remove param tag");
        $this->assertRegExp('/<p>/', $washed, "Keep embedded tags");
    }

    /**
     * Test handling HTML comments
     */
    function test_comments()
    {
        $washer = new rcube_washtml;

        $html   = "<!--[if gte mso 10]><p>p1</p><!--><p>p2</p>";
        $washed = $this->cleanupResult($washer->wash($html));

        $this->assertEquals('<p>p2</p>', $washed, "HTML conditional comments (#1489004)");

        $html   = "<!--TestCommentInvalid><p>test</p>";
        $washed = $this->cleanupResult($washer->wash($html));

        $this->assertEquals('<p>test</p>', $washed, "HTML invalid comments (#1487759)");

        $html   = "<p>para1</p><!-- comment --><p>para2</p>";
        $washed = $this->cleanupResult($washer->wash($html));

        $this->assertEquals('<p>para1</p><p>para2</p>', $washed, "HTML comments - simple comment");

        $html   = "<p>para1</p><!-- <hr> comment --><p>para2</p>";
        $washed = $this->cleanupResult($washer->wash($html));

        $this->assertEquals('<p>para1</p><p>para2</p>', $washed, "HTML comments - tags inside (#1489904)");

        $html   = "<p>para1</p><!-- comment => comment --><p>para2</p>";
        $washed = $this->cleanupResult($washer->wash($html));

        $this->assertEquals('<p>para1</p><p>para2</p>', $washed, "HTML comments - bracket inside");

        $html   = "<p><!-- span>1</span -->\n<span>2</span>\n<!-- >3</span --><span>4</span></p>";
        $washed = $this->cleanupResult($washer->wash($html));

        $this->assertEquals("<p>\n<span>2</span>\n<span>4</span></p>", $washed, "HTML comments (#6464)");
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
     * Test deprecated body attributes (#7109)
     */
    function test_style_body_attrs()
    {
        $html = "<html><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
            <body bgcolor=\"#fff\" text=\"#000\" background=\"#test\" link=\"#111\" alink=\"#222\" vlink=\"#333\">
            </body></html>";

        $washer = new rcube_washtml(array('html_elements' => array('body')));
        $washed = $washer->wash($html);

        $this->assertRegExp('|bgcolor="#fff"|', $washed, "Body bgcolor attribute");
        $this->assertRegExp('|text="#000"|', $washed, "Body text attribute");
        $this->assertRegExp('|background="#test"|', $washed, "Body background attribute");
        $this->assertRegExp('|link="#111"|', $washed, "Body link attribute");
        $this->assertRegExp('|alink="#222"|', $washed, "Body alink attribute");
        $this->assertRegExp('|vlink="#333"|', $washed, "Body vlink attribute");
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

        $html     = "<div style=\"padding: 0px\n   20px;border:1px solid #000;\"></div>";
        $expected = "<div style=\"padding: 0px 20px; border: 1px solid #000\"></div>";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertTrue(strpos($washed, $expected) !== false, "White-space and new-line characters handling");
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

    /**
     * Test SVG cleanup
     */
    function test_wash_svg()
    {
        $svg = '<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg version="1.1" baseProfile="full" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" viewBox="0 0 100 100">
  <polygon id="triangle" points="0,0 0,50 50,0" fill="#009900" stroke="#004400" onmouseover="alert(1)" />
  <text x="50" y="68" font-size="48" fill="#FFF" text-anchor="middle"><![CDATA[410]]></text>
  <script type="text/javascript">
    alert(document.cookie);
  </script>
  <text x="10" y="25" >An example text</text>
  <a xlink:href="http://www.w.pl"><rect width="100%" height="100%" /></a>
  <foreignObject xlink:href="data:text/xml,%3Cscript xmlns=\'http://www.w3.org/1999/xhtml\'%3Ealert(1)%3C/script%3E"/>
  <set attributeName="onmouseover" to="alert(1)"/>
  <animate attributeName="onunload" to="alert(1)"/>
  <animate attributeName="xlink:href" begin="0" from="javascript:alert(1)" />
</svg>';

        $exp = '<svg xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" version="1.1" baseProfile="full" viewBox="0 0 100 100">
  <polygon id="triangle" points="0,0 0,50 50,0" fill="#009900" stroke="#004400" x-washed="onmouseover" />
  <text x="50" y="68" font-size="48" fill="#FFF" text-anchor="middle">410</text>
  <!-- script not allowed -->
  <text x="10" y="25">An example text</text>
  <a xlink:href="http://www.w.pl"><rect width="100%" height="100%" /></a>
  <!-- foreignObject ignored -->
  <set attributeName="onmouseover" x-washed="to" />
  <animate attributeName="onunload" x-washed="to" />
  <animate attributeName="xlink:href" begin="0" x-washed="from" />
</svg>';

        $washer = new rcube_washtml;
        $washed = $washer->wash($svg);

        $this->assertSame($washed, $exp, "SVG content");
    }

    /**
     * Test cases for SVG tests
     */
    function data_wash_svg_tests()
    {
        $svg1 = "<svg id='x' width='100' height='100'><a xlink:href='javascript:alert(1)'><rect x='0' y='0' width='100' height='100' /></a></svg>";

        return [
            [
                '<head xmlns="&quot;&gt;&lt;script&gt;alert(document.domain)&lt;/script&gt;"><svg></svg></head>',
                '<svg xmlns="http://www.w3.org/1999/xhtml"></svg>'
            ],
            [
                '<head xmlns="&quot; onload=&quot;alert(document.domain)">Hello victim!<svg></svg></head>',
                'Hello victim!<svg xmlns="http://www.w3.org/1999/xhtml"></svg>'
            ],
            [
                '<p>Hello victim!<svg xmlns="&quot; onload=&quot;alert(document.domain)"></svg></p>',
                '<p>Hello victim!<svg /></p>'
            ],
            [
                '<html><p>Hello victim!<svg xmlns="&quot; onload=&quot;alert(document.domain)"></svg></p>',
                '<p>Hello victim!<svg xmlns="http://www.w3.org/1999/xhtml"></svg></p>'
            ],
            [
                '<svg xmlns="&quot; onload=&quot;alert(document.domain)" />',
                '<svg xmlns="&quot; onload=&quot;alert(document.domain)" />'
            ],
            [
                '<html><svg xmlns="&quot; onload=&quot;alert(document.domain)" />',
                '<svg xmlns="http://www.w3.org/1999/xhtml"></svg>'
            ],
            [
                '<svg><a xlink:href="javascript:alert(1)"><text x="20" y="20">XSS</text></a></svg>',
                '<svg><a x-washed="xlink:href"><text x="20" y="20">XSS</text></a></svg>'
            ],
            [
                '<html><svg><a xlink:href="javascript:alert(1)"><text x="20" y="20">XSS</text></a></svg>',
                '<svg xmlns="http://www.w3.org/1999/xhtml"><a x-washed="xlink:href"><text x="20" y="20">XSS</text></a></svg>'
            ],
            [
                '<svg><animate xlink:href="#xss" attributeName="href" values="javascript:alert(1)" />'
                    . '<a id="xss"><text x="20" y="20">XSS</text></a></svg>',
                '<svg><!-- animate blocked --><a id="xss"><text x="20" y="20">XSS</text></a></svg>',
            ],
            [
                '<html><svg><animate xlink:href="#xss" attributeName="href" values="javascript:alert(1)" />'
                    . '<a id="xss"><text x="20" y="20">XSS</text></a></svg>',
                '<svg xmlns="http://www.w3.org/1999/xhtml">'
                    . '<!-- animate blocked --><a id="xss"><text x="20" y="20">XSS</text></a></svg>',
            ],
            [
                '<svg><animate xlink:href="#xss" attributeName="href" from="javascript:alert(1)" to="1" />'
                    . '<a id="xss"><text x="20" y="20">XSS</text></a></svg>',
                '<svg><!-- animate blocked --><a id="xss"><text x="20" y="20">XSS</text></a></svg>',
            ],
            [
                '<svg><set xlink:href="#xss" attributeName="href" from="?" to="javascript:alert(1)" />'
                    . '<a id="xss"><text x="20" y="20">XSS</text></a></svg>',
                '<svg><!-- set blocked --><a id="xss"><text x="20" y="20">XSS</text></a></svg>',
            ],
            [
                '<svg><animate xlink:href="#xss" attributename="href" dur="5s" repeatCount="indefinite" keytimes="0;0;1" values="https://portswigger.net?;javascript:alert(1);0" />'
                    . '<a id="xss"><text x="20" y="20">XSS</text></a></svg>',
                '<svg><!-- animate blocked --><a id="xss"><text x="20" y="20">XSS</text></a></svg>',
            ],
            [
                "<svg><use href=\"data:image/svg+xml,&lt;svg id='x' xmlns='http://www.w3.org/2000/svg' "
                    . "xmlns:xlink='http://www.w3.org/1999/xlink' width='100' height='100'&gt;&lt;a xlink:href='javascript:alert(1)'&gt;"
                    . "&lt;rect x='0' y='0' width='100' height='100' /&gt;&lt;/a&gt;&lt;/svg&gt;\"></use></svg>",
                "<svg><use href=\"data:image/svg+xml;base64,PHN2ZyB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53"
                    . "My5vcmcvMTk5OS94bGluayIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiBpZD0ie"
                    . "CIgd2lkdGg9IjEwMCIgaGVpZ2h0PSIxMDAiPjxhIHgtd2FzaGVkPSJ4bGluazpocmVmIj48cmVjdC"
                    . "B4PSIwIiB5PSIwIiB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgLz48L2E+PC9zdmc+\" /></svg>"
            ],
            [
                "<svg><use href=\"data:image/svg+xml;base64," . base64_encode($svg1) . "\"></use></svg>",
                "<svg><use href=\"data:image/svg+xml;base64,PHN2ZyBpZD0ieCIgd2lkdGg9IjEwMCIgaGVpZ2h"
                    . "0PSIxMDAiPjxhIHgtd2FzaGVkPSJ4bGluazpocmVmIj48cmVjdCB4PSIwIiB5PSIwIiB3aWR0aD0"
                    . "iMTAwIiBoZWlnaHQ9IjEwMCIgLz48L2E+PC9zdmc+\" /></svg>"
            ],
            [
                '<svg><script href="data:text/javascript,alert(1)" /><text x="20" y="20">XSS</text></svg>',
                '<svg><text x="20" y="20">XSS</text></svg>'
            ],
        ];
    }

    /**
     * Test SVG cleanup
     *
     * @dataProvider data_wash_svg_tests
     */
    function test_wash_svg_tests($input, $expected)
    {
        $washer = new rcube_washtml;
        $washed = $washer->wash($input);

        $this->assertSame($expected, $this->cleanupResult($washed), "SVG content");
    }

    /**
     * Test cases for various XSS issues
     */
    function data_wash_xss_tests()
    {
        return [
            [
                '<html><base href="javascript:/a/-alert(1)///////"><a href="../lol/safari.html">test</a>',
                '<body><a x-washed="href">test</a></body>'
            ],
            [
                '<html><math><x href="javascript:alert(1)">blah</x>',
                '<body><math>blah</math></body>'
            ],
            [
                '<html><a href="j&#x61vascript:alert(1)">XSS</a>',
                '<body><a x-washed="href">XSS</a></body>'
            ],
            [
                '<html><a href="&#x6a avascript:alert(1)">XSS</a>',
                '<body><a x-washed="href">XSS</a></body>'
            ],
            [
                '<html><a href="&#x6a avascript:alert(1)">XSS</a>',
                '<body><a x-washed="href">XSS</a></body>'
            ],
            [
                '<html><body background="javascript:alert(1)">',
                '<body x-washed="background"></body>'
            ],
            [
                '<html><math href="javascript:alert(location);"><mi>clickme</mi></math>',
                '<body><math x-washed="href"><mi>clickme</mi></math></body>',
            ],
            [
                '<html><math><mstyle href="javascript:alert(location);"><mi>clickme</mi></mstyle></math>',
                '<body><math><mstyle x-washed="href"><mi>clickme</mi></mstyle></math></body>',
            ],
            [
                '<html><math><msubsup href="javascript:alert(location);"><mi>clickme</mi></msubsup></math>',
                '<body><math><msubsup x-washed="href"><mi>clickme</mi></msubsup></math></body>',
            ],
            [
                '<html><math><ms HREF="javascript:alert(location);">clickme</ms></math>',
                '<body><math><ms x-washed="href">clickme</ms></math></body>',
            ],
        ];
    }

    /**
     * Test various XSS issues
     *
     * @dataProvider data_wash_xss_tests
     */
    function test_wash_xss_tests($input, $expected)
    {
        $washer = new rcube_washtml(['allow_remote' => true, 'html_elements' => ['body']]);
        $washed = $washer->wash($input);

        $this->assertSame($expected, $this->cleanupResult($washed), "XSS issues");
    }

    /**
     * Test position:fixed cleanup - (#5264)
     */
    function test_style_wash_position_fixed()
    {
        $html = "<img style='position:fixed' /><img style=\"position:/**/ fixed; top:10px\" />";
        $exp  = "<img style=\"position: absolute\" /><img style=\"position: absolute; top: 10px\" />";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertTrue(strpos($washed, $exp) !== false, "Position:fixed (#5264)");
    }

    /**
     * Test MathML cleanup
     */
    function test_wash_mathml()
    {
        $mathml = '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>
            <math><semantics>
                <mrow>
                    <msub><mi>I</mi><mi>D</mi></msub>
                    <mo>=</mo>
                    <mfrac><mn>1</mn><mn>2</mn></mfrac>
                    <msub><mi>k</mi><mi>n</mi></msub>
                    <mfrac><mi>W</mi><mi>L</mi></mfrac>
                    <mo stretchy="false">(</mo>
                    <msub><mi>V</mi><mrow><mi>G</mi><mi>S</mi></mrow></msub>
                    <mo>-</mo><msub><mi>V</mi><mi>t</mi></msub><msup>
                    <mo stretchy="false">)</mo><mn>2</mn></msup>
                </mrow>
                <annotation encoding="TeX">I_D = \frac{1}{2} k_n \frac{W}{L} (V_{GS}-V_t)^2</annotation>
            </semantics></math>
            </body></html>';

        $exp = '<!-- html ignored --><!-- head ignored --><!-- meta ignored --><!-- body ignored -->
            <math><semantics>
                <mrow>
                    <msub><mi>I</mi><mi>D</mi></msub>
                    <mo>=</mo>
                    <mfrac><mn>1</mn><mn>2</mn></mfrac>
                    <msub><mi>k</mi><mi>n</mi></msub>
                    <mfrac><mi>W</mi><mi>L</mi></mfrac>
                    <mo stretchy="false">(</mo>
                    <msub><mi>V</mi><mrow><mi>G</mi><mi>S</mi></mrow></msub>
                    <mo>-</mo><msub><mi>V</mi><mi>t</mi></msub><msup>
                    <mo stretchy="false">)</mo><mn>2</mn></msup>
                </mrow>
                <annotation encoding="TeX">I_D = \frac{1}{2} k_n \frac{W}{L} (V_{GS}-V_t)^2</annotation>
            </semantics></math>';

        $washer = new rcube_washtml;
        $washed = $washer->wash($mathml);

        // remove whitespace between tags
        $washed = preg_replace('/>[\s\r\n\t]+</', '><', $washed);
        $exp    = preg_replace('/>[\s\r\n\t]+</', '><', $exp);

        $this->assertSame(trim($washed), trim($exp), "MathML content");
    }

    /**
     * Test external links in src of input/video elements (#5583)
     */
    function test_src_wash()
    {
        $html = "<input type=\"image\" src=\"http://TRACKING_URL/\">";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertTrue($washer->extlinks);
        $this->assertNotContains('TRACKING', $washed, "Src attribute of <input> tag (#5583)");

        $html = "<video src=\"http://TRACKING_URL/\">";

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertTrue($washer->extlinks);
        $this->assertNotContains('TRACKING', $washed, "Src attribute of <video> tag (#5583)");
    }

    /**
     * Test external links
     */
    function test_extlinks()
    {
        $html = array(
            array("<link href=\"http://TRACKING_URL/\">", true),
            array("<link href=\"src:abc\">", false),
            array("<img src=\"http://TRACKING_URL/\">", true),
            array("<img src=\"data:image\">", false),
            array('<p style="backgr\\ound-image: \\ur\\l(\'http://TRACKING_URL\')"></p>', true),
        );

        foreach ($html as $item) {
            $washer = new rcube_washtml;
            $washed = $washer->wash($item[0]);

            $this->assertSame($item[1], $washer->extlinks);
        }

        foreach ($html as $item) {
            $washer = new rcube_washtml(array('allow_remote' => true));
            $washed = $washer->wash($item[0]);

            $this->assertFalse($washer->extlinks);
        }
    }

    function test_textarea_content_escaping()
    {
        $html = '<textarea><p style="x:</textarea><img src=x onerror=alert(1)>">';

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertNotContains('onerror=alert(1)>', $washed);
        $this->assertContains('&lt;p style=&quot;x:', $washed);
    }

    /**
     * Test css_prefix feature
     */
    function test_css_prefix()
    {
        $washer = new rcube_washtml(array('css_prefix' => 'test'));

        $html   = '<p id="my-id">'
            . '<label for="my-other-id" class="my-class1 my-class2">test</label>'
            . '<a href="#my-id">link</a>'
            . '</p>';
        $washed = $washer->wash($html);

        $this->assertContains('id="testmy-id"', $washed);
        $this->assertContains('for="testmy-other-id"', $washed);
        $this->assertContains('href="#testmy-id"', $washed);
        $this->assertContains('class="testmy-class1 testmy-class2"', $washed);
    }

    /**
     * Test removing xml tag
     */
    function test_xml_tag()
    {
        $html = '<p><?xml:namespace prefix = "xsl" /></p>';

        $washer = new rcube_washtml;
        $washed = $this->cleanupResult($washer->wash($html));

        $this->assertSame($washed, '<p></p>');

        $html = '<?xml encoding="UTF-8"><html><body>HTML</body></html>';

        $washer = new rcube_washtml;
        $washed = $this->cleanupResult($washer->wash($html));

        $this->assertSame($washed, 'HTML');
    }

    /**
     * Test missing main HTML hierarchy tags (#6713)
     */
    function test_missing_tags()
    {
        $washer = new rcube_washtml();

        $html   = '<head></head>First line<br />Second line';
        $washed = $washer->wash($html);

        $this->assertContains('First line', $washed);

        $html   = 'First line<br />Second line';
        $washed = $washer->wash($html);

        $this->assertContains('First line', $washed);

        $html   = '<html>First line<br />Second line</html>';
        $washed = $washer->wash($html);

        $this->assertContains('>First line', $washed);

        $html   = '<html><head></head>First line<br />Second line</html>';
        $washed = $washer->wash($html);

        $this->assertContains('First line', $washed);

        // Not really valid HTML, but because its common in email world
        // and because it works with DOMDocument, we make sure its supported
        $html   = 'First line<br /><html><body>Second line';
        $washed = $washer->wash($html);

        $this->assertContains('First line', $washed);

        $html   = 'First line<br /><html>Second line';
        $washed = $washer->wash($html);

        $this->assertContains('First line', $washed);
    }

    /**
     * Test CDATA cleanup
     */
    function test_cdata()
    {
        $html = '<p><![CDATA[<script>alert(document.cookie)</script>]]></p>';

        $washer = new rcube_washtml;
        $washed = $washer->wash($html);

        $this->assertTrue(strpos($washed, '<script>') === false, "CDATA content");
    }
}
