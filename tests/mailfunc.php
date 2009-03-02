<?php

/**
 * Test class to test steps/mail/func.inc functions
 *
 * @package Tests
 */
class rcube_test_mailfunc extends UnitTestCase
{

  function __construct()
  {
    $this->UnitTestCase('Mail body rendering tests');
    
    // simulate environment to successfully include func.inc
    $GLOBALS['RCMAIL'] = $RCMAIL = rcmail::get_instance();
    $GLOBALS['OUTPUT'] = $OUTPUT = $RCMAIL->load_gui();
    $RCMAIL->action = 'spell';
    $IMAP = $RCMAIL->imap;
    
    require_once 'steps/mail/func.inc';
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
    $html = rcmail_print_body($part, array('safe' => false));

    $this->assertPattern('/src="'.$part->replaces['ex1.jpg'].'"/', $html, "Replace reference to inline image");
    $this->assertPattern('#background="./program/blocked.gif"#', $html, "Replace external background image");
    $this->assertNoPattern('/ex3.jpg/', $html, "No references to external images");
    $this->assertNoPattern('/<meta [^>]+>/', $html, "No meta tags allowed");
    $this->assertNoPattern('/<style [^>]+>/', $html, "No style tags allowed");
    $this->assertNoPattern('/<form [^>]+>/', $html, "No form tags allowed");
    $this->assertPattern('/Subscription form/', $html, "Include <form> contents");
    $this->assertPattern('/<!-- input not allowed -->/', $html, "No input elements allowed");
    $this->assertPattern('/<a[^>]+ target="_blank">/', $html, "Set target to _blank");
    $this->assertTrue($GLOBALS['REMOTE_OBJECTS'], "Remote object detected");
    
    // render HTML in safe mode
    $html2 = rcmail_print_body($part, array('safe' => true));
    
    $this->assertPattern('/<style [^>]+>/', $html2, "Allow styles in safe mode");
    $this->assertPattern('#src="http://evilsite.net/mailings/ex3.jpg"#', $html2, "Allow external images in HTML (safe mode)");
    $this->assertPattern("#url\('http://evilsite.net/newsletter/image/bg/bg-64.jpg'\)#", $html2, "Allow external images in CSS (safe mode)");
  }

  /**
   * Test the elimination of some trivial XSS vulnerabilities
   */
  function test_html_xss()
  {
    $part = $this->get_html_part('src/htmlxss.txt');
    $washed = rcmail_print_body($part, array('safe' => true));

    $this->assertNoPattern('/src="skins/', $washed, "Remove local references");
    $this->assertNoPattern('/\son[a-z]+/', $wahsed, "Remove on* attributes");
    $this->assertNoPattern('/alert/', $wahsed, "Remove alerts");
  }

  /**
   * Test HTML sanitization to fix the CSS Expression Input Validation Vulnerability
   * reported at http://www.securityfocus.com/bid/26800/
   */
  function test_html_xss2()
  {
    $part = $this->get_html_part('src/BID-26800.txt');
    $washed = rcmail_print_body($part, array('safe' => true));

    $this->assertNoPattern('/alert|expression|javascript|xss/', $washed, "Remove evil style blocks");
    $this->assertNoPattern('/font-style:italic/', $washed, "Allow valid styles");
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
    
    $this->assertPattern('/<a href="mailto:nobody@roundcube.net" onclick="return rcmail.command\(\'compose\',\'nobody@roundcube.net\',this\)">nobody@roundcube.net<\/a>/', $html, "Mailto links with onclick");
    $this->assertPattern('#<a href="http://www.apple.com/legal/privacy/" target="_blank">http://www.apple.com/legal/privacy/</a>#', $html, "Links with target=_blank");
  }

}

?>