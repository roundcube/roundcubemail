<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for class rcube_vcard
 */
class VCardTest extends TestCase
{
    public function _srcpath($fn)
    {
        return realpath(__DIR__ . '/../src/' . $fn);
    }

    public function test_parse_one()
    {
        $vcard = new \rcube_vcard(file_get_contents($this->_srcpath('apple.vcf')));

        $this->assertTrue($vcard->business, 'Identify as business record');
        $this->assertSame('Apple Computer AG', $vcard->displayname, 'FN => displayname');
        $this->assertSame('', $vcard->firstname, 'No person name set');
    }

    public function test_parse_two()
    {
        $vcard = new \rcube_vcard(file_get_contents($this->_srcpath('johndoe.vcf')), null);

        $this->assertFalse($vcard->business, 'Identify as private record');
        $this->assertSame('John Doë', $vcard->displayname, 'Decode according to charset attribute');
        $this->assertSame('roundcube.net', $vcard->organization, 'Test organization field');
        $this->assertCount(2, $vcard->email, 'List two e-mail addresses');
        $this->assertSame('roundcube@gmail.com', $vcard->email[0], 'Use PREF e-mail as primary');
    }

    /**
     * Make sure MOBILE phone is returned as CELL (as specified in standard)
     */
    public function test_parse_three()
    {
        $vcard = new \rcube_vcard(file_get_contents($this->_srcpath('johndoe.vcf')), null);

        $vcf = $vcard->export();
        $this->assertMatchesRegularExpression('/TEL;CELL:\+987654321/', $vcf, 'Return CELL instead of MOBILE (import)');

        $vcard = new \rcube_vcard();
        $vcard->set('phone', '+987654321', 'MOBILE');

        $vcf = $vcard->export();
        $this->assertMatchesRegularExpression('/TEL;TYPE=cell:\+987654321/', $vcf, 'Return CELL instead of MOBILE (set)');
    }

    /**
     * Backslash escaping test (#1488896)
     */
    public function test_parse_four()
    {
        $vcard = "BEGIN:VCARD\nVERSION:3.0\nN:last\\;;first\\\\;middle\\\\\\;\\\\;prefix;\nFN:test\nEND:VCARD";
        $vcard = new \rcube_vcard($vcard, null);
        $vcard = $vcard->get_assoc();

        $this->assertSame('last;', $vcard['surname'], 'Decode backslash character');
        $this->assertSame('first\\', $vcard['firstname'], 'Decode backslash character');
        $this->assertSame('middle\;\\', $vcard['middlename'], 'Decode backslash character');
        $this->assertSame('prefix', $vcard['prefix'], 'Decode backslash character');
    }

    /**
     * Backslash parsing test (#1489085)
     */
    public function test_parse_five()
    {
        $vcard = "BEGIN:VCARD\nVERSION:3.0\nN:last\\\\\\a;fir\\nst\nURL:http\\://domain.tld\nEND:VCARD";
        $vcard = new \rcube_vcard($vcard, null);
        $vcard = $vcard->get_assoc();

        $this->assertSame('last\a', $vcard['surname'], 'Decode dummy backslash character');
        $this->assertSame("fir\nst", $vcard['firstname'], 'Decode backslash character');
        $this->assertSame('http://domain.tld', $vcard['website:other'][0], 'Decode dummy backslash character');
    }

    /**
     * Some Apple vCard quirks (#1489993)
     */
    public function test_parse_six()
    {
        $vcard = new \rcube_vcard("BEGIN:VCARD\n"
            . "VERSION:3.0\n"
            . "N:;;;;\n"
            . "FN:Apple Computer AG\n"
            . "ITEM1.ADR;type=WORK;type=pref:;;Birgistrasse 4a;Wallisellen-Zürich;;8304;Switzerland\n"
            . "PHOTO;ENCODING=B:aHR0cDovL3Rlc3QuY29t\n"
            . 'END:VCARD'
        );

        $result = $vcard->get_assoc();

        $this->assertCount(1, $result['address:work'], 'ITEM1.-prefixed entry');
    }

    /**
     * Extra whitespace at start of continuation line (#9593/1).
     */
    public function test_parse_continuation_line_with_initial_whitespace()
    {
        $vcard_string = <<<'EOF'
            BEGIN:VCARD
            VERSION:3.0
            N:Doe;Jane;;;
            FN:Jane Doe
            NOTE:an
              example
            END:VCARD
            EOF;

        $vcard = new \rcube_vcard(str_replace("\n", "\r\n", $vcard_string) . "\r\n");

        $result = $vcard->get_assoc();

        $this->assertSame('an example', $result['notes'][0]);
    }

    public function test_import()
    {
        $input = file_get_contents($this->_srcpath('apple.vcf'));
        $input .= file_get_contents($this->_srcpath('johndoe.vcf'));

        $vcards = \rcube_vcard::import($input);

        $this->assertCount(2, $vcards, 'Detected 2 vcards');
        $this->assertSame('Apple Computer AG', $vcards[0]->displayname, 'FN => displayname');
        $this->assertSame('John Doë', $vcards[1]->displayname, 'Displayname with correct charset');

        // https://github.com/roundcube/roundcubemail/issues/1934
        $vcards2 = \rcube_vcard::import(file_get_contents($this->_srcpath('thebat.vcf')));
        $this->assertSame('Iksi=F1ski', quoted_printable_encode($vcards2[0]->surname));

        $vcards[0]->reset();
        // TODO: Test reset() method
    }

    public function test_import_photo_encoding()
    {
        $input = file_get_contents($this->_srcpath('photo.vcf'));

        $vcards = \rcube_vcard::import($input);

        $this->assertCount(1, $vcards, 'Detected 1 vcard');

        $vcard = $vcards[0]->get_assoc();

        // ENCODING=b case (#1488683)
        $this->assertSame('/9j/4AAQSkZJRgABAQA', substr(base64_encode($vcard['photo']), 0, 19), 'Photo decoding');
        $this->assertSame('Müller', $vcard['surname'], 'Unicode characters');

        $input = str_replace('ENCODING=b:', 'ENCODING=base64;jpeg:', $input);

        $vcards = \rcube_vcard::import($input);
        $vcard = $vcards[0]->get_assoc();

        // ENCODING=base64 case (#1489977)
        $this->assertSame('/9j/4AAQSkZJRgABAQA', substr(base64_encode($vcard['photo']), 0, 19), 'Photo decoding');

        $input = str_replace('PHOTO;ENCODING=base64;jpeg:', 'PHOTO:data:image/jpeg;base64,', $input);

        $vcards = \rcube_vcard::import($input);
        $vcard = $vcards[0]->get_assoc();

        // vcard4.0 "PHOTO:data:image/jpeg;base64," case (#1489977)
        $this->assertSame('/9j/4AAQSkZJRgABAQA', substr(base64_encode($vcard['photo']), 0, 19), 'Photo decoding');
    }

    public function test_encodings()
    {
        $input = file_get_contents($this->_srcpath('utf-16_sample.vcf'));

        $vcards = \rcube_vcard::import($input);
        $this->assertSame('Ǽgean ĽdaMonté', $vcards[0]->displayname, 'Decoded from UTF-16');
    }

    /**
     * Skipping empty values (#6564)
     */
    public function test_parse_skip_empty()
    {
        $vcard = new \rcube_vcard("BEGIN:VCARD\n"
            . "VERSION:3.0\n"
            . "N:;;;;\n"
            . "FN:Test\n"
            . "TEL;TYPE=home:67890\n"
            . "TEL;TYPE=CELL:\n"
            . "ADR;TYPE=home:;;street;city;state;zip;country\n"
            . 'END:VCARD'
        );

        $result = $vcard->get_assoc();

        $this->assertCount(1, $result['phone:home'], 'TYPE=home entry exists');
        $this->assertTrue(!isset($result['phone:mobile']), 'TYPE=CELL entry ignored');
        $this->assertCount(5, $result['address:home'][0], 'ADR with some fields missing');
        $this->assertSame($result['address:home'][0]['zipcode'], 'zip', 'ADR with some fields missing (1)');
        $this->assertSame($result['address:home'][0]['street'], 'street', 'ADR with some fields missing (2)');
    }

    /**
     * Support BDAT in YYYYMMRR format
     */
    public function test_bday_v4()
    {
        $vcard = "BEGIN:VCARD\nVERSION:4.0\nN:last\\;;first\\\\;middle\\\\\\;\\\\;prefix;\nFN:test\nBDAY:19800202\nEND:VCARD";
        $vcard = new \rcube_vcard($vcard, null);
        $vcard = $vcard->get_assoc();

        $this->assertSame('1980-02-02', $vcard['birthday'][0]);
    }

    /**
     * Test required fields in output (#8771)
     */
    public function test_required_fields()
    {
        $vcard = new \rcube_vcard();
        $result = $vcard->export();

        $this->assertSame($result, "BEGIN:VCARD\r\nVERSION:3.0\r\nFN:\r\nN:;;;;\r\nEND:VCARD");
    }
}
