<?php

/**
 * Test class to test rcube_mime class
 *
 * @package Tests
 */
class Framework_Mime extends PHPUnit_Framework_TestCase
{

    /**
     * Test decoding of single e-mail address strings
     * Uses rcube_mime::decode_address_list()
     */
    function test_decode_single_address()
    {
        $headers = array(
            0  => 'test@domain.tld',
            1  => '<test@domain.tld>',
            2  => 'Test <test@domain.tld>',
            3  => 'Test Test <test@domain.tld>',
            4  => 'Test Test<test@domain.tld>',
            5  => '"Test Test" <test@domain.tld>',
            6  => '"Test Test"<test@domain.tld>',
            7  => '"Test \\" Test" <test@domain.tld>',
            8  => '"Test<Test" <test@domain.tld>',
            9  => '=?ISO-8859-1?B?VGVzdAo=?= <test@domain.tld>',
            10 => '=?ISO-8859-1?B?VGVzdAo=?=<test@domain.tld>', // #1487068
            // comments in address (#1487673)
            11 => 'Test (comment) <test@domain.tld>',
            12 => '"Test" (comment) <test@domain.tld>',
            13 => '"Test (comment)" (comment) <test@domain.tld>',
            14 => '(comment) <test@domain.tld>',
            15 => 'Test <test@(comment)domain.tld>',
            16 => 'Test Test ((comment)) <test@domain.tld>',
            17 => 'test@domain.tld (comment)',
            18 => '"Test,Test" <test@domain.tld>',
            // 1487939
            19 => 'Test <"test test"@domain.tld>',
            20 => '<"test test"@domain.tld>',
            21 => '"test test"@domain.tld',
            // invalid (#1489092)
            22 => '"John Doe @ SomeBusinessName" <MAILER-DAEMON>',
        );

        $results = array(
            0  => array(1, '', 'test@domain.tld'),
            1  => array(1, '', 'test@domain.tld'),
            2  => array(1, 'Test', 'test@domain.tld'),
            3  => array(1, 'Test Test', 'test@domain.tld'),
            4  => array(1, 'Test Test', 'test@domain.tld'),
            5  => array(1, 'Test Test', 'test@domain.tld'),
            6  => array(1, 'Test Test', 'test@domain.tld'),
            7  => array(1, 'Test " Test', 'test@domain.tld'),
            8  => array(1, 'Test<Test', 'test@domain.tld'),
            9  => array(1, 'Test', 'test@domain.tld'),
            10 => array(1, 'Test', 'test@domain.tld'),
            11 => array(1, 'Test', 'test@domain.tld'),
            12 => array(1, 'Test', 'test@domain.tld'),
            13 => array(1, 'Test (comment)', 'test@domain.tld'),
            14 => array(1, '', 'test@domain.tld'),
            15 => array(1, 'Test', 'test@domain.tld'),
            16 => array(1, 'Test Test', 'test@domain.tld'),
            17 => array(1, '', 'test@domain.tld'),
            18 => array(1, 'Test,Test', 'test@domain.tld'),
            19 => array(1, 'Test', '"test test"@domain.tld'),
            20 => array(1, '', '"test test"@domain.tld'),
            21 => array(1, '', '"test test"@domain.tld'),
            // invalid (#1489092)
            22 => array(1, 'John Doe @ SomeBusinessName', 'MAILER-DAEMON'),
        );

        foreach ($headers as $idx => $header) {
            $res = rcube_mime::decode_address_list($header);

            $this->assertEquals($results[$idx][0], count($res), "Rows number in result for header: " . $header);
            $this->assertEquals($results[$idx][1], $res[1]['name'], "Name part decoding for header: " . $header);
            $this->assertEquals($results[$idx][2], $res[1]['mailto'], "Email part decoding for header: " . $header);
        }
    }

    /**
     * Test decoding of header values
     * Uses rcube_mime::decode_mime_string()
     */
    function test_header_decode_qp()
    {
        $test = array(
            // #1488232: invalid character "?"
            'quoted-printable (1)' => array(
                'in'  => '=?utf-8?Q?Certifica=C3=A7=C3=A3??=',
                'out' => 'Certifica=C3=A7=C3=A3?',
            ),
            'quoted-printable (2)' => array(
                'in'  => '=?utf-8?Q?Certifica=?= =?utf-8?Q?C3=A7=C3=A3?=',
                'out' => 'Certifica=C3=A7=C3=A3',
            ),
            'quoted-printable (3)' => array(
                'in'  => '=?utf-8?Q??= =?utf-8?Q??=',
                'out' => '',
            ),
            'quoted-printable (4)' => array(
                'in'  => '=?utf-8?Q??= a =?utf-8?Q??=',
                'out' => ' a ',
            ),
            'quoted-printable (5)' => array(
                'in'  => '=?utf-8?Q?a?= =?utf-8?Q?b?=',
                'out' => 'ab',
            ),
            'quoted-printable (6)' => array(
                'in'  => '=?utf-8?Q?   ?= =?utf-8?Q?a?=',
                'out' => '   a',
            ),
            'quoted-printable (7)' => array(
                'in'  => '=?utf-8?Q?___?= =?utf-8?Q?a?=',
                'out' => '   a',
            ),
        );

        foreach ($test as $idx => $item) {
            $res = rcube_mime::decode_mime_string($item['in'], 'UTF-8');
            $res = quoted_printable_encode($res);

            $this->assertEquals($item['out'], $res, "Header decoding for: " . $idx);
        }
    }

    /**
     * Test format=flowed unfolding
     */
    function test_format_flowed()
    {
        $raw = file_get_contents(TESTS_DIR . 'src/format-flowed-unfolded.txt');
        $flowed = file_get_contents(TESTS_DIR . 'src/format-flowed.txt');

        $this->assertEquals($flowed, rcube_mime::format_flowed($raw, 80), "Test correct folding and space-stuffing");
    }

    /**
     * Test format=flowed unfolding
     */
    function test_unfold_flowed()
    {
        $flowed = file_get_contents(TESTS_DIR . 'src/format-flowed.txt');
        $unfolded = file_get_contents(TESTS_DIR . 'src/format-flowed-unfolded.txt');

        $this->assertEquals($unfolded, rcube_mime::unfold_flowed($flowed), "Test correct unfolding of quoted lines");
    }

    /**
     * Test wordwrap()
     */
    function test_wordwrap()
    {
        $samples = array(
            array(
                array("aaaa aaaa\n           aaaa"),
                "aaaa aaaa\n           aaaa",
            ),
            array(
                array("123456789 123456789 123456789 123", 29),
                "123456789 123456789 123456789\n123",
            ),
            array(
                array("123456789   3456789 123456789", 29),
                "123456789   3456789 123456789",
            ),
            array(
                array("123456789 123456789 123456789   123", 29),
                "123456789 123456789 123456789\n  123",
            ),
            array(
                array("abc", 1, "\n", true),
                "a\nb\nc",
            ),
            array(
                array("ąść", 1, "\n", true, 'UTF-8'),
                "ą\nś\nć",
            ),
            array(
                array(">abc\n>def", 2, "\n", true),
                ">abc\n>def",
            ),
            array(
                array("abc def", 3, "-"),
                "abc-def",
            ),
            array(
                array("----------------------------------------------------------------------------------------\nabc                        def123456789012345", 76),
                "----------------------------------------------------------------------------------------\nabc                        def123456789012345",
            ),
            array(
                array("-------\nabc def", 5),
                "-------\nabc\ndef",
            ),
            array(
                array("http://xx.xxx.xx.xxx:8080/addressbooks/roundcubexxxxx%40xxxxxxxxxxxxxxxxxxxxxxx.xx.xx/testing/", 70),
                "http://xx.xxx.xx.xxx:8080/addressbooks/roundcubexxxxx%40xxxxxxxxxxxxxxxxxxxxxxx.xx.xx/testing/",
            ),
        );

        foreach ($samples as $sample) {
            $this->assertEquals($sample[1], call_user_func_array(array('rcube_mime', 'wordwrap'), $sample[0]), "Test text wrapping");
        }
    }

}
