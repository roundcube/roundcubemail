<?php

/**
 * Unit tests for class rcube_vcard
 *
 * @package Tests
 */
class Framework_VCard extends PHPUnit_Framework_TestCase
{

    function _srcpath($fn)
    {
        return realpath(__DIR__ . '/../src/' . $fn);
    }

    function test_parse_one()
    {
        $vcard = new rcube_vcard(file_get_contents($this->_srcpath('apple.vcf')));

        $this->assertTrue($vcard->business, "Identify as business record");
        $this->assertEquals("Apple Computer AG", $vcard->displayname, "FN => displayname");
        $this->assertEquals("", $vcard->firstname, "No person name set");
    }

    function test_parse_two()
    {
        $vcard = new rcube_vcard(file_get_contents($this->_srcpath('johndoe.vcf')), null);

        $this->assertFalse($vcard->business, "Identify as private record");
        $this->assertEquals("John Doë", $vcard->displayname, "Decode according to charset attribute");
        $this->assertEquals("roundcube.net", $vcard->organization, "Test organization field");
        $this->assertCount(2, $vcard->email, "List two e-mail addresses");
        $this->assertEquals("roundcube@gmail.com", $vcard->email[0], "Use PREF e-mail as primary");
    }

    /**
     * Make sure MOBILE phone is returned as CELL (as specified in standard)
     */
    function test_parse_three()
    {
        $vcard = new rcube_vcard(file_get_contents($this->_srcpath('johndoe.vcf')), null);

        $vcf = $vcard->export();
        $this->assertRegExp('/TEL;CELL:\+987654321/', $vcf, "Return CELL instead of MOBILE (import)");

        $vcard = new rcube_vcard();
        $vcard->set('phone', '+987654321', 'MOBILE');

        $vcf = $vcard->export();
        $this->assertRegExp('/TEL;TYPE=CELL:\+987654321/', $vcf, "Return CELL instead of MOBILE (set)");
    }

    /**
     * Backslash escaping test (#1488896)
     */
    function test_parse_four()
    {
        $vcard = "BEGIN:VCARD\nVERSION:3.0\nN:last\\;;first\\\\;middle\\\\\\;\\\\;prefix;\nFN:test\nEND:VCARD";
        $vcard = new rcube_vcard($vcard, null);
        $vcard = $vcard->get_assoc();

        $this->assertEquals("last;", $vcard['surname'], "Decode backslash character");
        $this->assertEquals("first\\", $vcard['firstname'], "Decode backslash character");
        $this->assertEquals("middle\\;\\", $vcard['middlename'], "Decode backslash character");
        $this->assertEquals("prefix", $vcard['prefix'], "Decode backslash character");
    }

    /**
     * Backslash parsing test (#1489085)
     */
    function test_parse_five()
    {
        $vcard = "BEGIN:VCARD\nVERSION:3.0\nN:last\\\\\\a;fir\\nst\nURL:http\\://domain.tld\nEND:VCARD";
        $vcard = new rcube_vcard($vcard, null);
        $vcard = $vcard->get_assoc();

        $this->assertEquals("last\\a", $vcard['surname'], "Decode dummy backslash character");
        $this->assertEquals("fir\nst", $vcard['firstname'], "Decode backslash character");
        $this->assertEquals("http://domain.tld", $vcard['website:other'][0], "Decode dummy backslash character");
    }

    /**
     * Some Apple vCard quirks (#1489993)
     */
    function test_parse_six()
    {
        $vcard = new rcube_vcard("BEGIN:VCARD\n"
            . "VERSION:3.0\n"
            . "N:;;;;\n"
            . "FN:Apple Computer AG\n"
            . "ITEM1.ADR;type=WORK;type=pref:;;Birgistrasse 4a;Wallisellen-Zürich;;8304;Switzerland\n"
            . "PHOTO;ENCODING=B:aHR0cDovL3Rlc3QuY29t\n"
            . "END:VCARD"
        );

        $result = $vcard->get_assoc();

        $this->assertCount(1, $result['address:work'], "ITEM1.-prefixed entry");
    }

    function test_import()
    {
        $input = file_get_contents($this->_srcpath('apple.vcf'));
        $input .= file_get_contents($this->_srcpath('johndoe.vcf'));

        $vcards = rcube_vcard::import($input);

        $this->assertCount(2, $vcards, "Detected 2 vcards");
        $this->assertEquals("Apple Computer AG", $vcards[0]->displayname, "FN => displayname");
        $this->assertEquals("John Doë", $vcards[1]->displayname, "Displayname with correct charset");

        // https://github.com/roundcube/roundcubemail/issues/1934
        $vcards2 = rcube_vcard::import(file_get_contents($this->_srcpath('thebat.vcf')));
        $this->assertEquals("Iksiñski", $vcards2[0]->surname, "Detect charset in encoded values");
    }

    function test_import_photo_encoding()
    {
        $input = file_get_contents($this->_srcpath('photo.vcf'));

        $vcards = rcube_vcard::import($input);
        $vcard = $vcards[0]->get_assoc();

        $this->assertCount(1, $vcards, "Detected 1 vcard");

        // ENCODING=b case (#1488683)
        $this->assertEquals("/9j/4AAQSkZJRgABAQA", substr(base64_encode($vcard['photo']), 0, 19), "Photo decoding");
        $this->assertEquals("Müller", $vcard['surname'], "Unicode characters");

        $input = str_replace('ENCODING=b:', 'ENCODING=base64;jpeg:', $input);

        $vcards = rcube_vcard::import($input);
        $vcard = $vcards[0]->get_assoc();

        // ENCODING=base64 case (#1489977)
        $this->assertEquals("/9j/4AAQSkZJRgABAQA", substr(base64_encode($vcard['photo']), 0, 19), "Photo decoding");

        $input = str_replace('PHOTO;ENCODING=base64;jpeg:', 'PHOTO:data:image/jpeg;base64,', $input);

        $vcards = rcube_vcard::import($input);
        $vcard = $vcards[0]->get_assoc();

        // vcard4.0 "PHOTO:data:image/jpeg;base64," case (#1489977)
        $this->assertEquals("/9j/4AAQSkZJRgABAQA", substr(base64_encode($vcard['photo']), 0, 19), "Photo decoding");
    }

    function test_encodings()
    {
        $input = file_get_contents($this->_srcpath('utf-16_sample.vcf'));

        $vcards = rcube_vcard::import($input);
        $this->assertEquals("Ǽgean ĽdaMonté", $vcards[0]->displayname, "Decoded from UTF-16");
    }

    /**
     * Skipping empty values (#6564)
     */
    function test_parse_skip_empty()
    {
        $vcard = new rcube_vcard("BEGIN:VCARD\n"
            . "VERSION:3.0\n"
            . "N:;;;;\n"
            . "FN:Test\n"
            . "TEL;TYPE=home:67890\n"
            . "TEL;TYPE=CELL:\n"
            . "ADR;TYPE=home:;;street;city;state;zip;country\n"
            . "END:VCARD"
        );

        $result = $vcard->get_assoc();

        $this->assertCount(1, $result['phone:home'], "TYPE=home entry exists");
        $this->assertTrue(!isset($result['phone:mobile']), "TYPE=CELL entry ignored");
        $this->assertCount(5, $result['address:home'][0], "ADR with some fields missing");
        $this->assertEquals($result['address:home'][0]['zipcode'], 'zip', "ADR with some fields missing (1)");
        $this->assertEquals($result['address:home'][0]['street'], 'street', "ADR with some fields missing (2)");
    }
}
