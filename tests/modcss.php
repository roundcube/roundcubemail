<?php

/**
 * Test class to test rcmail_mod_css_styles and XSS vulnerabilites
 *
 * @package Tests
 */
class rcube_test_modcss extends UnitTestCase
{

  function __construct()
  {
    $this->UnitTestCase('CSS modification and vulnerability tests');
  }
  
  function test_modcss()
  {
    $css = file_get_contents(TESTS_DIR . 'src/valid.css');
    $mod = rcmail_mod_css_styles($css, 'rcmbody');

    $this->assertPattern('/#rcmbody\s+\{/', $mod, "Replace body style definition");
    $this->assertPattern('/#rcmbody h1\s\{/', $mod, "Prefix tag styles (single)");
    $this->assertPattern('/#rcmbody h1, #rcmbody h2, #rcmbody h3, #rcmbody textarea\s+\{/', $mod, "Prefix tag styles (multiple)");
    $this->assertPattern('/#rcmbody \.noscript\s+\{/', $mod, "Prefix class styles");
  }
  
  function test_xss()
  {
    $mod = rcmail_mod_css_styles("body.main2cols { background-image: url('../images/leftcol.png'); }", 'rcmbody');
    $this->assertEqual("/* evil! */", $mod, "No url() values allowed");
    
    $mod = rcmail_mod_css_styles("@import url('http://localhost/somestuff/css/master.css');", 'rcmbody');
    $this->assertEqual("/* evil! */", $mod, "No import statements");
    
    $mod = rcmail_mod_css_styles("left:expression(document.body.offsetWidth-20)", 'rcmbody');
    $this->assertEqual("/* evil! */", $mod, "No expression properties");
    
    $mod = rcmail_mod_css_styles("left:exp/*  */ression( alert(&#039;xss3&#039;) )", 'rcmbody');
    $this->assertEqual("/* evil! */", $mod, "Don't allow encoding quirks");
    
    $mod = rcmail_mod_css_styles("background:\\0075\\0072\\006c( javascript:alert(&#039;xss&#039;) )", 'rcmbody');
    $this->assertEqual("/* evil! */", $mod, "Don't allow encoding quirks (2)");
  }
  
}
