<?php

/**
 * Test class to test messages decoding functions
 *
 * @package Tests
 */
class rcube_test_maildecode extends UnitTestCase
{
  private $app;

  function __construct()
  {
    $this->UnitTestCase('Mail headers decoding tests');
  }

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
    );

    foreach ($headers as $idx => $header) {
      $res = rcube_mime::decode_address_list($header);

      $this->assertEqual($results[$idx][0], count($res), "Rows number in result for header: " . $header);
      $this->assertEqual($results[$idx][1], $res[1]['name'], "Name part decoding for header: " . $header);
      $this->assertEqual($results[$idx][2], $res[1]['mailto'], "Email part decoding for header: " . $header);
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

      $this->assertEqual($item['out'], $res, "Header decoding for: " . $idx);
    }

  }
}
