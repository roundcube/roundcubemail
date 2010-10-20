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

    $this->app = rcmail::get_instance();
    $this->app->imap_init(false);
  }

  /**
   * Test decoding of single e-mail address strings
   * Uses rcube_imap::decode_address_list()
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
    );

    $results = array(
        0  => array('', 'test@domain.tld'),
        1  => array('', 'test@domain.tld'),
        2  => array('Test', 'test@domain.tld'),
        3  => array('Test Test', 'test@domain.tld'),
        4  => array('Test Test', 'test@domain.tld'),
        5  => array('Test Test', 'test@domain.tld'),
        6  => array('Test Test', 'test@domain.tld'),
        7  => array('Test " Test', 'test@domain.tld'),
        8  => array('Test<Test', 'test@domain.tld'),
        9  => array('Test', 'test@domain.tld'),
        10 => array('Test', 'test@domain.tld'),
    );

    foreach ($headers as $idx => $header) {
      $res = $this->app->imap->decode_address_list($header);

      $this->assertEqual(1, count($res), "Rows number in result for header: " . $header);
      $this->assertEqual($results[$idx][0], $res[1]['name'], "Name part decoding for header: " . $header);
      $this->assertEqual($results[$idx][1], $res[1]['mailto'], "Name part decoding for header: " . $header);
    }
  }

}
