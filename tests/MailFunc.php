<?php

/**
 * Test class to test steps/mail/func.inc functions
 *
 * @package Tests
 */
class MailFunc extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        // simulate environment to successfully include func.inc
        $GLOBALS['RCMAIL'] = $RCMAIL = rcmail::get_instance();
        $GLOBALS['OUTPUT'] = $OUTPUT = $RCMAIL->load_gui();
        $RCMAIL->action = 'autocomplete';
        $RCMAIL->storage_init(false);

        require_once INSTALL_PATH . 'program/steps/mail/func.inc';

        $GLOBALS['EMAIL_ADDRESS_PATTERN'] = $EMAIL_ADDRESS_PATTERN;
    }

    /**
     * Helper method to create a HTML message part object
     */
    function get_html_part($body)
    {
        $part = new rcube_message_part;
        $part->ctype_primary = 'text';
        $part->ctype_secondary = 'html';
        $part->body = file_get_contents(TESTS_DIR . $body);
        $part->replaces = array();
        return $part;
    }


    /**
     * Test sanitization of a "normal" html message
     */
    function test_html()
    {
        $part = $this->get_html_part('src/htmlbody.txt');
        $part->replaces = array('ex1.jpg' => 'part_1.2.jpg', 'ex2.jpg' => 'part_1.2.jpg');

        // render HTML in normal mode
        $html = rcmail_html4inline(rcmail_print_body($part, array('safe' => false)), 'foo');

        $this->assertRegExp('/src="'.$part->replaces['ex1.jpg'].'"/', $html, "Replace reference to inline image");
        $this->assertRegExp('#background="./program/resources/blocked.gif"#', $html, "Replace external background image");
        $this->assertNotRegExp('/ex3.jpg/', $html, "No references to external images");
        $this->assertNotRegExp('/<meta [^>]+>/', $html, "No meta tags allowed");
        //$this->assertNoPattern('/<style [^>]+>/', $html, "No style tags allowed");
        $this->assertNotRegExp('/<form [^>]+>/', $html, "No form tags allowed");
        $this->assertRegExp('/Subscription form/', $html, "Include <form> contents");
        $this->assertRegExp('/<!-- link ignored -->/', $html, "No external links allowed");
        $this->assertRegExp('/<a[^>]+ target="_blank"/', $html, "Set target to _blank");
        $this->assertTrue($GLOBALS['REMOTE_OBJECTS'], "Remote object detected");

        // render HTML in safe mode
        $html2 = rcmail_html4inline(rcmail_print_body($part, array('safe' => true)), 'foo');

        $this->assertRegExp('/<style [^>]+>/', $html2, "Allow styles in safe mode");
        $this->assertRegExp('#src="http://evilsite.net/mailings/ex3.jpg"#', $html2, "Allow external images in HTML (safe mode)");
        $this->assertRegExp("#url\('?http://evilsite.net/newsletter/image/bg/bg-64.jpg'?\)#", $html2, "Allow external images in CSS (safe mode)");
        $css = '<link rel="stylesheet" .+_u=tmp-[a-z0-9]+\.css.+_action=modcss';
        $this->assertRegExp('#'.$css.'#Ui', $html2, "Filter (anonymized) external styleseehts with utils/modcss.inc");
    }

    /**
     * Test the elimination of some trivial XSS vulnerabilities
     */
    function test_html_xss()
    {
        $part = $this->get_html_part('src/htmlxss.txt');
        $washed = rcmail_print_body($part, array('safe' => true));

        $this->assertNotRegExp('/src="skins/', $washed, "Remove local references");
        $this->assertNotRegExp('/\son[a-z]+/', $washed, "Remove on* attributes");

        $html = rcmail_html4inline($washed, 'foo');
        $this->assertNotRegExp('/onclick="return rcmail.command(\'compose\',\'xss@somehost.net\',this)"/', $html, "Clean mailto links");
        $this->assertNotRegExp('/alert/', $html, "Remove alerts");
    }

    /**
     * Test HTML sanitization to fix the CSS Expression Input Validation Vulnerability
     * reported at http://www.securityfocus.com/bid/26800/
     */
    function test_html_xss2()
    {
        $part = $this->get_html_part('src/BID-26800.txt');
        $washed = rcmail_html4inline(rcmail_print_body($part, array('safe' => true)), 'dabody', '', $attr, true);

        $this->assertNotRegExp('/alert|expression|javascript|xss/', $washed, "Remove evil style blocks");
        $this->assertNotRegExp('/font-style:italic/', $washed, "Allow valid styles");
    }

    /**
     * Test the elimination of some XSS vulnerabilities
     */
    function test_html_xss3()
    {
        // #1488850
        $html = '<p><a href="data:text/html,&lt;script&gt;alert(document.cookie)&lt;/script&gt;">Firefox</a>'
            .'<a href="vbscript:alert(document.cookie)">Internet Explorer</a></p>';
        $washed = rcmail_wash_html($html, array('safe' => true), array());

        $this->assertNotRegExp('/data:text/', $washed, "Remove data:text/html links");
        $this->assertNotRegExp('/vbscript:/', $washed, "Remove vbscript: links");
    }

    /**
     * Test washtml class on non-unicode characters (#1487813)
     */
    function test_washtml_utf8()
    {
        $part = $this->get_html_part('src/invalidchars.html');
        $washed = rcmail_print_body($part);

        $this->assertRegExp('/<p>символ<\/p>/', $washed, "Remove non-unicode characters from HTML message body");
    }

    /**
     * Test links pattern replacements in plaintext messages
     */
    function test_plaintext()
    {
        $part = new rcube_message_part;
        $part->ctype_primary = 'text';
        $part->ctype_secondary = 'plain';
        $part->body = quoted_printable_decode(file_get_contents(TESTS_DIR . 'src/plainbody.txt'));
        $html = rcmail_print_body($part, array('safe' => true));

        $this->assertRegExp('/<a href="mailto:nobody@roundcube.net" onclick="return rcmail.command\(\'compose\',\'nobody@roundcube.net\',this\)">nobody@roundcube.net<\/a>/', $html, "Mailto links with onclick");
        $this->assertRegExp('#<a rel="noreferrer" target="_blank" href="http://www.apple.com/legal/privacy">http://www.apple.com/legal/privacy</a>#', $html, "Links with target=_blank");
        $this->assertRegExp('#\\[<a rel="noreferrer" target="_blank" href="http://example.com/\\?tx\\[a\\]=5">http://example.com/\\?tx\\[a\\]=5</a>\\]#', $html, "Links with square brackets");
    }

    /**
     * Test mailto links in html messages
     */
    function test_mailto()
    {
        $part = $this->get_html_part('src/mailto.txt');

        // render HTML in normal mode
        $html = rcmail_html4inline(rcmail_print_body($part, array('safe' => false)), 'foo');

        $mailto = '<a href="mailto:me@me.com?subject=this is the subject&amp;body=this is the body"'
            .' onclick="return rcmail.command(\'compose\',\'me@me.com?subject=this is the subject&amp;body=this is the body\',this)" rel="noreferrer">e-mail</a>';

        $this->assertRegExp('|'.preg_quote($mailto, '|').'|', $html, "Extended mailto links");
    }

    /**
     * Test the elimination of HTML comments
     */
    function test_html_comments()
    {
        $part = $this->get_html_part('src/htmlcom.txt');
        $washed = rcmail_print_body($part, array('safe' => true));

        // #1487759
        $this->assertRegExp('|<p>test1</p>|', $washed, "Buggy HTML comments");
        // but conditional comments (<!--[if ...) should be removed
        $this->assertNotRegExp('|<p>test2</p>|', $washed, "Conditional HTML comments");
    }

    /**
     * Test URI base resolving in HTML messages
     */
    function test_resolve_base()
    {
        $html = file_get_contents(TESTS_DIR . 'src/htmlbase.txt');
        $html = rcube_washtml::resolve_base($html);

        $this->assertRegExp('|src="http://alec\.pl/dir/img1\.gif"|', $html, "URI base resolving [1]");
        $this->assertRegExp('|src="http://alec\.pl/dir/img2\.gif"|', $html, "URI base resolving [2]");
        $this->assertRegExp('|src="http://alec\.pl/img3\.gif"|', $html, "URI base resolving [3]");

        // base resolving exceptions
        $this->assertRegExp('|src="cid:theCID"|', $html, "URI base resolving exception [1]");
        $this->assertRegExp('|src="http://other\.domain\.tld/img3\.gif"|', $html, "URI base resolving exception [2]");
    }
}
