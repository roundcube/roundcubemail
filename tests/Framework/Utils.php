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
            array('あいうえお@domain.com', 'Unicode char as address'),
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
            array('2001::'),
            array('2001::1'),
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
            array(':::1'),
            array('2001:::1'),
            array('2001::c4fe:5afe::1'),
            array(':c4fe:5afe:1'),
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
        $this->assertContains('#rcmbody {min-width:474px', $mod, 'Replace body selector nested in @media block (#5811)');
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

        $mod = rcube_utils::mod_css_styles("background:\\0075\\0072\\00006c( javascript:alert(&#039;xss&#039;) )", 'rcmbody');
        $this->assertEquals("/* evil! */", $mod, "Don't allow encoding quirks (2)");

        $mod = rcube_utils::mod_css_styles("background: \\75 \\72 \\6C ('/images/img.png')", 'rcmbody');
        $this->assertEquals("/* evil! */", $mod, "Don't allow encoding quirks (3)");

        $mod = rcube_utils::mod_css_styles("background: u\\r\\l('/images/img.png')", 'rcmbody');
        $this->assertEquals("/* evil! */", $mod, "Don't allow encoding quirks (4)");

        // position: fixed (#5264)
        $mod = rcube_utils::mod_css_styles(".test { position: fixed; }", 'rcmbody');
        $this->assertEquals("#rcmbody .test { position: absolute; }", $mod, "Replace position:fixed with position:absolute (0)");

        $mod = rcube_utils::mod_css_styles(".test { position:\nfixed; }", 'rcmbody');
        $this->assertEquals("#rcmbody .test { position: absolute; }", $mod, "Replace position:fixed with position:absolute (1)");

        $mod = rcube_utils::mod_css_styles(".test { position:/**/fixed; }", 'rcmbody');
        $this->assertEquals("#rcmbody .test { position: absolute; }", $mod, "Replace position:fixed with position:absolute (2)");

        // allow data URIs with images (#5580)
        $mod = rcube_utils::mod_css_styles("body { background-image: url(data:image/png;base64,123); }", 'rcmbody');
        $this->assertContains("#rcmbody { background-image: url(data:image/png;base64,123);", $mod, "Data URIs in url() allowed [1]");
        $mod = rcube_utils::mod_css_styles("body { background-image: url(data:image/png;base64,123); }", 'rcmbody', true);
        $this->assertContains("#rcmbody { background-image: url(data:image/png;base64,123);", $mod, "Data URIs in url() allowed [2]");
    }

    /**
     * rcube_utils::mod_css_styles()'s prefix argument handling
     */
    function test_mod_css_styles_prefix()
    {
        $css = '
            .one { font-size: 10pt; }
            .three.four { font-weight: bold; }
            #id1 { color: red; }
            #id2.class:focus { color: white; }
            .five:not(.test), { background: transparent; }
            div .six { position: absolute; }
            p > i { font-size: 120%; }
            div#some { color: yellow; }
            @media screen and (max-width: 699px) and (min-width: 520px) {
                li a.button { padding-left: 30px; }
            }
        ';
        $mod = rcube_utils::mod_css_styles($css, 'rc', true, 'test');

        $this->assertContains('#rc .testone', $mod);
        $this->assertContains('#rc .testthree.testfour', $mod);
        $this->assertContains('#rc #testid1', $mod);
        $this->assertContains('#rc #testid2.testclass:focus', $mod);
        $this->assertContains('#rc .testfive:not(.testtest)', $mod);
        $this->assertContains('#rc div .testsix', $mod);
        $this->assertContains('#rc p > i ', $mod);
        $this->assertContains('#rc div#testsome', $mod);
        $this->assertContains('#rc li a.testbutton', $mod);
    }

    function test_xss_entity_decode()
    {
        $mod = rcube_utils::xss_entity_decode("&lt;img/src=x onerror=alert(1)// </b>");
        $this->assertNotContains('<img', $mod, "Strip (encoded) tags from style node");

        $mod = rcube_utils::xss_entity_decode('#foo:after{content:"\003Cimg/src=x onerror=alert(2)>";}');
        $this->assertNotContains('<img', $mod, "Strip (encoded) tags from content property");

        $mod = rcube_utils::xss_entity_decode("background: u\\r\\00006c('/images/img.png')");
        $this->assertContains("url(", $mod, "Escape sequences resolving");

        // #5747
        $mod = rcube_utils::xss_entity_decode('<!-- #foo { content:css; } -->');
        $this->assertContains('#foo', $mod, "Strip HTML comments from content, but not the content");
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
        // this test depends on system timezone if not set
        date_default_timezone_set('UTC');

        $test = array(
            '1' => 1,
            '' => 0,
            'abc-555' => 0,
            '2013-04-22' => 1366588800,
            '2013/04/22' => 1366588800,
            '2013.04.22' => 1366588800,
            '22-04-2013' => 1366588800,
            '22/04/2013' => 1366588800,
            '22.04.2013' => 1366588800,
            '22.4.2013'  => 1366588800,
            '20130422'   => 1366588800,
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
            '01/30/1960' => '1960-01-30',
            '1960.12.11 01:02:00' => '1960-12-11',
        );

        foreach ($test as $datetime => $ts) {
            $result = rcube_utils::anytodatetime($datetime);
            $this->assertSame($ts, $result ? $result->format('Y-m-d') : false, "Error parsing date: $datetime");
        }

        $test = array(
            '12/11/2013 01:02:00' => '2013-11-12 01:02:00',
            '1960.12.11 01:02:00' => '1960-12-11 01:02:00',
        );

        foreach ($test as $datetime => $ts) {
            $result = rcube_utils::anytodatetime($datetime);
            $this->assertSame($ts, $result ? $result->format('Y-m-d H:i:s') : false, "Error parsing date: $datetime");
        }

        $test = array(
            'Sun, 4 Mar 2018 03:32:08 +0300 (MSK)' => '2018-03-04 03:32:08 +0300',
        );

        foreach ($test as $datetime => $ts) {
            $result = rcube_utils::anytodatetime($datetime);
            $this->assertSame($ts, $result ? $result->format('Y-m-d H:i:s O') : false, "Error parsing date: $datetime");
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
            '1520587800'         => '2018-03-09 11:30',  // unix timestamp conversion
            'Invalid date'       => false,
        );

        foreach ($test as $datetime => $ts) {
            $result = rcube_utils::anytodatetime($datetime, $tz);
            if ($result) $result->setTimezone($tz);  // move to target timezone for comparison
            $this->assertSame($ts, $result ? $result->format('Y-m-d H:i') : false, "Error parsing date: $datetime");
        }
    }

    /**
     * rcube:utils::format_datestr()
     */
    function test_format_datestr()
    {
        $test = array(
            array('abc-555', 'abc', 'abc-555'),
            array('2013-04-22', 'Y-m-d', '2013-04-22'),
            array('22/04/2013', 'd/m/Y', '2013-04-22'),
            array('4.22.2013', 'm.d.Y', '2013-04-22'),
        );

        foreach ($test as $data) {
            $result = rcube_utils::format_datestr($data[0], $data[1]);
            $this->assertSame($data[2], $result, "Error formatting date: " . $data[0]);
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
            'ßs'  => 'sss',
            'Xae' => 'xa',
            'Xoe' => 'xo',
            'Xue' => 'xu',
            '项目' => '项目',
        );

        // this test fails on PHP 5.3.3
        if (PHP_VERSION_ID > 50303) {
            $test['ß']  = '';
            $test['日'] = '';
        }

        foreach ($test as $input => $output) {
            $result = rcube_utils::normalize_string($input);
            $this->assertSame($output, $result, "Error normalizing '$input'");
        }
    }

    /**
     * rcube:utils::words_match()
     */
    function test_words_match()
    {
        $test = array(
            array('', 'test', false),
            array('test', 'test', true),
            array('test', 'none', false),
            array('test', 'test xyz', false),
            array('test xyz', 'test xyz', true),
            array('this is test', 'test', true),
            // try some binary content
            array('this is test ' . base64_decode('R0lGODlhDwAPAIAAAMDAwAAAACH5BAEAAAAALAAAAAAPAA8AQAINhI+py+0Po5y02otnAQA7'), 'test', true),
            array('this is test ' . base64_decode('R0lGODlhDwAPAIAAAMDAwAAAACH5BAEAAAAALAAAAAAPAA8AQAINhI+py+0Po5y02otnAQA7'), 'none', false),
        );

        foreach ($test as $idx => $params) {
            $result = rcube_utils::words_match($params[0], $params[1]);
            $this->assertSame($params[2], $result, "words_match() at index $idx");
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

    /**
     * rcube:utils::random_bytes()
     */
    function test_random_bytes()
    {
        $this->assertRegexp('/^[a-zA-Z0-9]{15}$/', rcube_utils::random_bytes(15));
        $this->assertSame(15, strlen(rcube_utils::random_bytes(15, true)));
        $this->assertSame(1, strlen(rcube_utils::random_bytes(1)));
        $this->assertSame(0, strlen(rcube_utils::random_bytes(0)));
        $this->assertSame(0, strlen(rcube_utils::random_bytes(-1)));
    }

    /**
     * Test-Cases for IDN to ASCII and IDN to UTF-8
     */
    function data_idn_convert()
    {

        /*
         * Check https://en.wikipedia.org/wiki/List_of_Internet_top-level_domains#Internationalized_brand_top-level_domains
         * and https://github.com/true/php-punycode/blob/master/tests/PunycodeTest.php for more Test-Data
         */

        return array(
            array('test@vermögensberater', 'test@xn--vermgensberater-ctb'),
            array('test@vermögensberatung', 'test@xn--vermgensberatung-pwb'),
            array('test@グーグル', 'test@xn--qcka1pmc'),
            array('test@谷歌', 'test@xn--flw351e'),
            array('test@中信', 'test@xn--fiq64b'),
            array('test@рф.ru', 'test@xn--p1ai.ru'),
            array('test@δοκιμή.gr', 'test@xn--jxalpdlp.gr'),
            array('test@gwóźdź.pl', 'test@xn--gwd-hna98db.pl'),
            array('рф.ru@рф.ru', 'рф.ru@xn--p1ai.ru'),
            array('vermögensberater', 'xn--vermgensberater-ctb'),
            array('vermögensberatung', 'xn--vermgensberatung-pwb'),
            array('グーグル', 'xn--qcka1pmc'),
            array('谷歌', 'xn--flw351e'),
            array('中信', 'xn--fiq64b'),
            array('рф.ru', 'xn--p1ai.ru'),
            array('δοκιμή.gr', 'xn--jxalpdlp.gr'),
            array('gwóźdź.pl', 'xn--gwd-hna98db.pl'),
        );

    }

    /**
     * Test idn_to_ascii
     *
     * @param string $decoded Decoded email address
     * @param string $encoded Encoded email address
     * @dataProvider data_idn_convert
     */
    function test_idn_to_ascii($decoded, $encoded)
    {
        $this->assertEquals(rcube_utils::idn_to_ascii($decoded), $encoded);
    }

    /**
     * Test idn_to_utf8
     *
     * @param string $decoded Decoded email address
     * @param string $encoded Encoded email address
     * @dataProvider data_idn_convert
     */
    function test_idn_to_utf8($decoded, $encoded)
    {
        $this->assertEquals(rcube_utils::idn_to_utf8($encoded), $decoded);
    }

    /**
     * Test idn_to_ascii with non-domain input (#6224)
     */
    function test_idn_to_ascii_special()
    {
        $this->assertEquals(rcube_utils::idn_to_ascii('H.S'), 'H.S');
        $this->assertEquals(rcube_utils::idn_to_ascii('d.-h.lastname'), 'd.-h.lastname');
    }
}
