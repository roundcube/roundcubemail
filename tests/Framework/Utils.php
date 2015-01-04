<?php

/**
 * Test class to test rcube_utils class
 *
 * @package Tests
 */
class Framework_Utils extends PHPUnit_Framework_TestCase
{

    /**
     * Valid email addresses for test_valid_email()
     */
    function data_valid_email()
    {
        return array(
            array('email@domain.com', 'Valid email'),
            array('firstname.lastname@domain.com', 'Email contains dot in the address field'),
            array('email@subdomain.domain.com', 'Email contains dot with subdomain'),
            array('firstname+lastname@domain.com', 'Plus sign is considered valid character'),
            array('email@[123.123.123.123]', 'Square bracket around IP address'),
            array('email@[IPv6:::1]', 'Square bracket around IPv6 address (1)'),
            array('email@[IPv6:::1.2.3.4]', 'Square bracket around IPv6 address (2)'),
            array('email@[IPv6:2001:2d12:c4fe:5afe::1]', 'Square bracket around IPv6 address (3)'),
            array('"email"@domain.com', 'Quotes around email is considered valid'),
            array('1234567890@domain.com', 'Digits in address are valid'),
            array('email@domain-one.com', 'Dash in domain name is valid'),
            array('_______@domain.com', 'Underscore in the address field is valid'),
            array('email@domain.name', '.name is valid Top Level Domain name'),
            array('email@domain.co.jp', 'Dot in Top Level Domain name also considered valid (use co.jp as example here)'),
            array('firstname-lastname@domain.com', 'Dash in address field is valid'),
            array('test@xn--e1aaa0cbbbcacac.xn--p1ai', 'IDNA domain'),
        );
    }

    /**
     * Invalid email addresses for test_invalid_email()
     */
    function data_invalid_email()
    {
        return array(
            array('plainaddress', 'Missing @ sign and domain'),
            array('#@%^%#$@#$@#.com', 'Garbage'),
            array('@domain.com', 'Missing username'),
            array('Joe Smith <email@domain.com>', 'Encoded html within email is invalid'),
            array('email.domain.com', 'Missing @'),
            array('email@domain@domain.com', 'Two @ sign'),
            array('.email@domain.com', 'Leading dot in address is not allowed'),
            array('email.@domain.com', 'Trailing dot in address is not allowed'),
            array('email..email@domain.com', 'Multiple dots'),
            array('あいうえお@domain.com', 'Unicode char as address'),
            array('email@domain.com (Joe Smith)', 'Text followed email is not allowed'),
            array('email@domain', 'Missing top level domain (.com/.net/.org/etc)'),
            array('email@-domain.com', 'Leading dash in front of domain is invalid'),
//            array('email@domain.web', '.web is not a valid top level domain'),
            array('email@123.123.123.123', 'IP address without brackets'),
            array('email@2001:2d12:c4fe:5afe::1', 'IPv6 address without brackets'),
            array('email@IPv6:2001:2d12:c4fe:5afe::1', 'IPv6 address without brackets (2)'),
            array('email@[111.222.333.44444]', 'Invalid IP format'),
            array('email@[111.222.255.257]', 'Invalid IP format (2)'),
            array('email@[.222.255.257]', 'Invalid IP format (3)'),
            array('email@[::1]', 'Invalid IPv6 format (1)'),
            array('email@[IPv6:2001:23x2:1]', 'Invalid IPv6 format (2)'),
            array('email@[IPv6:1111:2222:33333::4444:5555]', 'Invalid IPv6 format (3)'),
            array('email@[IPv6:1111::3333::4444:5555]', 'Invalid IPv6 format (4)'),
            array('email@domain..com', 'Multiple dot in the domain portion is invalid'),
        );
    }

    /**
     * @dataProvider data_valid_email
     */
    function test_valid_email($email, $title)
    {
        $this->assertTrue(rcube_utils::check_email($email, false), $title);
    }

    /**
     * @dataProvider data_invalid_email
     */
    function test_invalid_email($email, $title)
    {
        $this->assertFalse(rcube_utils::check_email($email, false), $title);
    }

    /**
     * Valid IP addresses for test_valid_ip()
     */
    function data_valid_ip()
    {
        return array(
            array('0.0.0.0'),
            array('123.123.123.123'),
            array('::'),
            array('::1'),
            array('::1.2.3.4'),
            array('2001:2d12:c4fe:5afe::1'),
        );
    }

    /**
     * Valid IP addresses for test_invalid_ip()
     */
    function data_invalid_ip()
    {
        return array(
            array(''),
            array(0),
            array('123.123.123.1234'),
            array('1.1.1.1.1'),
            array('::1.2.3.260'),
            array('::1.0'),
            array('2001::c4fe:5afe::1'),
        );
    }

    /**
     * @dataProvider data_valid_ip
     */
    function test_valid_ip($ip)
    {
        $this->assertTrue(rcube_utils::check_ip($ip));
    }

    /**
     * @dataProvider data_invalid_ip
     */
    function test_invalid_ip($ip)
    {
        $this->assertFalse(rcube_utils::check_ip($ip));
    }

    /**
     * Data for test_rep_specialchars_output()
     */
    function data_rep_specialchars_output()
    {
        return array(
            array('', '', 'abc', 'abc'),
            array('', '', '?', '?'),
            array('', '', '"', '&quot;'),
            array('', '', '<', '&lt;'),
            array('', '', '>', '&gt;'),
            array('', '', '&', '&amp;'),
            array('', '', '&amp;', '&amp;amp;'),
            array('', '', '<a>', '&lt;a&gt;'),
            array('', 'remove', '<a>', ''),
        );
    }

    /**
     * Test for rep_specialchars_output
     * @dataProvider data_rep_specialchars_output
     */
    function test_rep_specialchars_output($type, $mode, $str, $res)
    {
        $result = rcube_utils::rep_specialchars_output(
            $str, $type ? $type : 'html', $mode ? $mode : 'strict');

        $this->assertEquals($result, $res);
    }

    /**
     * rcube_utils::mod_css_styles()
     */
    function test_mod_css_styles()
    {
        $css = file_get_contents(TESTS_DIR . 'src/valid.css');
        $mod = rcube_utils::mod_css_styles($css, 'rcmbody');

        $this->assertRegExp('/#rcmbody\s+\{/', $mod, "Replace body style definition");
        $this->assertRegExp('/#rcmbody h1\s\{/', $mod, "Prefix tag styles (single)");
        $this->assertRegExp('/#rcmbody h1, #rcmbody h2, #rcmbody h3, #rcmbody textarea\s+\{/', $mod, "Prefix tag styles (multiple)");
        $this->assertRegExp('/#rcmbody \.noscript\s+\{/', $mod, "Prefix class styles");

        $css = file_get_contents(TESTS_DIR . 'src/media.css');
        $mod = rcube_utils::mod_css_styles($css, 'rcmbody');

        $this->assertContains('#rcmbody table[class=w600]', $mod, 'Replace styles nested in @media block');
        $this->assertContains('#rcmbody {width:600px', $mod, 'Replace body selector nested in @media block');
    }

    /**
     * rcube_utils::mod_css_styles()
     */
    function test_mod_css_styles_xss()
    {
        $mod = rcube_utils::mod_css_styles("body.main2cols { background-image: url('../images/leftcol.png'); }", 'rcmbody');
        $this->assertEquals("/* evil! */", $mod, "No url() values allowed");

        $mod = rcube_utils::mod_css_styles("@import url('http://localhost/somestuff/css/master.css');", 'rcmbody');
        $this->assertEquals("/* evil! */", $mod, "No import statements");

        $mod = rcube_utils::mod_css_styles("left:expression(document.body.offsetWidth-20)", 'rcmbody');
        $this->assertEquals("/* evil! */", $mod, "No expression properties");

        $mod = rcube_utils::mod_css_styles("left:exp/*  */ression( alert(&#039;xss3&#039;) )", 'rcmbody');
        $this->assertEquals("/* evil! */", $mod, "Don't allow encoding quirks");

        $mod = rcube_utils::mod_css_styles("background:\\0075\\0072\\006c( javascript:alert(&#039;xss&#039;) )", 'rcmbody');
        $this->assertEquals("/* evil! */", $mod, "Don't allow encoding quirks (2)");
    }

    /**
     * Check rcube_utils::explode_quoted_string()
     */
    function test_explode_quoted_string()
    {
        $data = array(
            '"a,b"' => array('"a,b"'),
            '"a,b","c,d"' => array('"a,b"','"c,d"'),
            '"a,\\"b",d' => array('"a,\\"b"', 'd'),
        );

        foreach ($data as $text => $res) {
            $result = rcube_utils::explode_quoted_string(',', $text);
            $this->assertSame($res, $result);
        }
    }

    /**
     * Check rcube_utils::explode_quoted_string() compat. with explode()
     */
    function test_explode_quoted_string_compat()
    {
        $data = array('', 'a,b,c', 'a', ',', ',a');

        foreach ($data as $text) {
            $result = rcube_utils::explode_quoted_string(',', $text);
            $this->assertSame(explode(',', $text), $result);
        }
    }

    /**
     * rcube_utils::get_boolean()
     */
    function test_get_boolean()
    {
        $input = array(
            false, 'false', '0', 'no', 'off', 'nein', 'FALSE', '', null,
        );

        foreach ($input as $idx => $value) {
            $this->assertFalse(rcube_utils::get_boolean($value), "Invalid result for $idx test item");
        }

        $input = array(
            true, 'true', '1', 1, 'yes', 'anything', 1000,
        );

        foreach ($input as $idx => $value) {
            $this->assertTrue(rcube_utils::get_boolean($value), "Invalid result for $idx test item");
        }
    }

    /**
     * rcube:utils::file2class()
     */
    function test_file2class()
    {
        $test = array(
            array('', '', 'unknown'),
            array('text', 'text', 'text'),
            array('image/png', 'image.png', 'image png'),
        );

        foreach ($test as $v) {
            $result = rcube_utils::file2class($v[0], $v[1]);
            $this->assertSame($v[2], $result);
        }
    }

    /**
     * rcube:utils::strtotime()
     */
    function test_strtotime()
    {
        $test = array(
            '1' => 1,
            '' => 0,
            'abc-555' => 0,
            '2013-04-22' => 1366581600,
            '2013/04/22' => 1366581600,
            '2013.04.22' => 1366581600,
            '22-04-2013' => 1366581600,
            '22/04/2013' => 1366581600,
            '22.04.2013' => 1366581600,
            '22.4.2013'  => 1366581600,
            '20130422'   => 1366581600,
            '2013/06/21 12:00:00 UTC' => 1371816000,
            '2013/06/21 12:00:00 Europe/Berlin' => 1371808800,
        );

        foreach ($test as $datetime => $ts) {
            $result = rcube_utils::strtotime($datetime);
            $this->assertSame($ts, $result, "Error parsing date: $datetime");
        }
    }

    /**
     * rcube:utils::anytodatetime()
     */
    function test_anytodatetime()
    {
        $test = array(
            '2013-04-22' => '2013-04-22',
            '2013/04/22' => '2013-04-22',
            '2013.04.22' => '2013-04-22',
            '22-04-2013' => '2013-04-22',
            '22/04/2013' => '2013-04-22',
            '22.04.2013' => '2013-04-22',
            '04/22/2013' => '2013-04-22',
            '22.4.2013'  => '2013-04-22',
            '20130422'   => '2013-04-22',
            '1900-10-10' => '1900-10-10',
            '01-01-1900' => '1900-01-01',
            '01/30/1960' => '1960-01-30'
        );

        foreach ($test as $datetime => $ts) {
            $result = rcube_utils::anytodatetime($datetime);
            $this->assertSame($ts, $result ? $result->format('Y-m-d') : false, "Error parsing date: $datetime");
        }
    }

    /**
     * rcube:utils::anytodatetime()
     */
    function test_anytodatetime_timezone()
    {
        $tz = new DateTimeZone('Europe/Helsinki');
        $test = array(
            'Jan 1st 2014 +0800' => '2013-12-31 18:00',  // result in target timezone
            'Jan 1st 14 45:42'   => '2014-01-01 00:00',  // force fallback to rcube_utils::strtotime()
            'Jan 1st 2014 UK'    => '2014-01-01 00:00',
            'Invalid date'       => false,
        );

        foreach ($test as $datetime => $ts) {
            $result = rcube_utils::anytodatetime($datetime, $tz);
            if ($result) $result->setTimezone($tz);  // move to target timezone for comparison
            $this->assertSame($ts, $result ? $result->format('Y-m-d H:i') : false, "Error parsing date: $datetime");
        }
    }

    /**
     * rcube:utils::tokenize_string()
     */
    function test_tokenize_string()
    {
        $test = array(
            ''        => array(),
            'abc d'   => array('abc'),
            'abc de'  => array('abc','de'),
            'äàé;êöü-xyz' => array('äàé','êöü','xyz'),
            '日期格式' => array('日期格式'),
        );

        foreach ($test as $input => $output) {
            $result = rcube_utils::tokenize_string($input);
            $this->assertSame($output, $result);
        }
    }

    /**
     * rcube:utils::normalize_string()
     */
    function test_normalize_string()
    {
        $test = array(
            ''        => '',
            'abc def' => 'abc def',
            'ÇçäâàåæéêëèïîìÅÉöôòüûùÿøØáíóúñÑÁÂÀãÃÊËÈÍÎÏÓÔõÕÚÛÙýÝ' => 'ccaaaaaeeeeiiiaeooouuuyooaiounnaaaaaeeeiiioooouuuyy',
            'ąáâäćçčéęëěíîłľĺńňóôöŕřśšşťţůúűüźžżýĄŚŻŹĆ' => 'aaaaccceeeeiilllnnooorrsssttuuuuzzzyaszzc',
            'ß'   => '',
            'ßs'  => 'sss',
            'Xae' => 'xa',
            'Xoe' => 'xo',
            'Xue' => 'xu',
            '项目' => '项目',
            '日'   => '',  // FIXME: this should not be stripped although minlen = 2
        );

        foreach ($test as $input => $output) {
            $result = rcube_utils::normalize_string($input);
            $this->assertSame($output, $result, "Error normalizing '$input'");
        }
    }

    /**
     * rcube:utils::is_absolute_path()
     */
    function test_is_absolute_path()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $test = array(
                '' => false,
                "C:\\" => true,
                'some/path' => false,
            );
        }
        else {
            $test = array(
                '' => false,
                '/path' => true,
                'some/path' => false,
            );
        }

        foreach ($test as $input => $output) {
            $result = rcube_utils::is_absolute_path($input);
            $this->assertSame($output, $result);
        }
    }
}
