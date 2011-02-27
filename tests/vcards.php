<?php

/**
 * Unit tests for class rcube_vcard
 *
 * @package Tests
 */
class rcube_test_vcards extends UnitTestCase
{

  function __construct()
  {
    $this->UnitTestCase('Vcard encoding/decoding tests');
  }
  
  function _srcpath($fn)
  {
    return realpath(dirname(__FILE__) . '/src/' . $fn);
  }
  
  function test_parse_one()
  {
    $vcard = new rcube_vcard(file_get_contents($this->_srcpath('apple.vcf')));
    
    $this->assertEqual(true, $vcard->business, "Identify as business record");
    $this->assertEqual("Apple Computer AG", $vcard->displayname, "FN => displayname");
    $this->assertEqual("", $vcard->firstname, "No person name set");
  }

  function test_parse_two()
  {
    $vcard = new rcube_vcard(file_get_contents($this->_srcpath('johndoe.vcf')), null);
    
    $this->assertEqual(false, $vcard->business, "Identify as private record");
    $this->assertEqual("John Doë", $vcard->displayname, "Decode according to charset attribute");
    $this->assertEqual("roundcube.net", $vcard->organization, "Test organization field");
    $this->assertEqual(2, count($vcard->email), "List two e-mail addresses");
    $this->assertEqual("roundcube@gmail.com", $vcard->email[0], "Use PREF e-mail as primary");
  }
  
  function test_import()
  {
    $input = file_get_contents($this->_srcpath('apple.vcf'));
    $input .= file_get_contents($this->_srcpath('johndoe.vcf'));
    
    $vcards = rcube_vcard::import($input);
    
    $this->assertEqual(2, count($vcards), "Detected 2 vcards");
    $this->assertEqual("Apple Computer AG", $vcards[0]->displayname, "FN => displayname");
    $this->assertEqual("John Doë", $vcards[1]->displayname, "Displayname with correct charset");
    
    // http://trac.roundcube.net/ticket/1485542
    $vcards2 = rcube_vcard::import(file_get_contents($this->_srcpath('thebat.vcf')));
    $this->assertEqual("Iksiñski", $vcards2[0]->surname, "Detect charset in encoded values");
  }
  
  function test_encodings()
  {
      $input = file_get_contents($this->_srcpath('utf-16_sample.vcf'));
      
      $vcards = rcube_vcard::import($input);
      $this->assertEqual("Ǽgean ĽdaMonté", $vcards[0]->displayname, "Decoded from UTF-16");
  }
  
}
