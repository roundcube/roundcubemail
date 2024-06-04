<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_shared functions
 */
class BootstrapTest extends TestCase
{
    /**
     * bootstrap.php: asciiwords()
     */
    public function test_asciiwords()
    {
        $this->assertSame('abc.123', asciiwords('abc%.123', false));
        $this->assertSame('abc-123', asciiwords('abc%.123', true, '-'));
    }

    /**
     * bootstrap.php: in_array_nocase()
     */
    public function test_in_array_nocase()
    {
        $haystack = ['Test'];
        $needle = 'test';
        $result = in_array_nocase($needle, $haystack);

        $this->assertTrue($result, 'Invalid in_array_nocase() result (Array)');

        $result = in_array_nocase($needle, null);

        $this->assertFalse($result, 'Invalid in_array_nocase() result (null)');
    }

    /**
     * bootstrap.php: parse_bytes()
     */
    public function test_parse_bytes()
    {
        $data = [
            '0' => 0,
            '1' => 1,
            '1024' => 1024,
            ' 10 ' => 10,

            '2k' => 2 * 1024,
            '2m' => 2 * 1024 * 1024,
            '2g' => 2 * 1024 * 1024 * 1024,
            '2t' => 2 * 1024 * 1024 * 1024 * 1024,

            '2 k' => 2 * 1024,
            '2kb' => 2 * 1024,
            '2kB' => 2 * 1024,
            '2KiB' => 2 * 1024,
            '2 m' => 2 * 1024 * 1024,
            '2TB' => 2 * 1024 * 1024 * 1024 * 1024,

            '2.5k' => (int) round(2.5 * 1024),
            '0.01 MiB' => (int) round(0.01 * 1024 * 1024),

            '' => false,
            '-1' => false,
            '1 1' => false,
            '1BB' => false,
            '1MM' => false,
        ];

        foreach ($data as $value => $expected) {
            $result = parse_bytes($value);
            $this->assertSame($expected, $result, "Invalid parse_bytes() result for {$value}");
        }

        $this->assertFalse(parse_bytes(null));
        $this->assertSame(0, parse_bytes(0));
        $this->assertSame(10, parse_bytes(10.1));
    }

    /**
     * bootstrap.php: slashify()
     */
    public function test_slashify()
    {
        $data = [
            'test' => 'test/',
            'test/' => 'test/',
            '' => '/',
            '\\' => '\/',
        ];

        foreach ($data as $value => $expected) {
            $result = slashify($value);
            $this->assertSame($expected, $result, "Invalid slashify() result for {$value}");
        }
    }

    /**
     * bootstrap.php: unslashify()
     */
    public function test_unslashify()
    {
        $data = [
            'test' => 'test',
            'test/' => 'test',
            '/' => '',
            '\/' => '\\',
            'test/test' => 'test/test',
            'test//' => 'test',
            '/test//' => '/test',
        ];

        foreach ($data as $value => $expected) {
            $result = unslashify($value);
            $this->assertSame($expected, $result, "Invalid unslashify() result for {$value}");
        }
    }

    /**
     * bootstrap.php: get_offset_sec()
     */
    public function test_get_offset_sec()
    {
        $data = [
            '1s' => 1,
            '1m' => 1 * 60,
            '1h' => 1 * 60 * 60,
            '1d' => 1 * 60 * 60 * 24,
            '1w' => 1 * 60 * 60 * 24 * 7,
            '1y' => 1,
            '100' => 100,
        ];

        foreach ($data as $value => $expected) {
            $result = get_offset_sec($value);
            $this->assertSame($expected, $result, "Invalid get_offset_sec() result for {$value}");
        }
    }

    /**
     * bootstrap.php: array_keys_recursive()
     */
    public function test_array_keys_recursive()
    {
        $input = [
            'one' => [
                'two' => [
                    'three' => [],
                    'four' => 'something',
                ],
            ],
            'five' => 'test',
        ];

        $result = array_keys_recursive($input);
        $input_str = 'one,two,three,four,five';
        $result_str = implode(',', $result);

        $this->assertSame($input_str, $result_str, 'Invalid array_keys_recursive() result');
    }

    /**
     * bootstrap.php: array_first()
     */
    public function test_array_first()
    {
        $this->assertNull(array_first([]));
        $this->assertNull(array_first(false));
        $this->assertNull(array_first('test'));
        $this->assertSame('test', array_first(['test']));

        $input = ['test1', 'test2'];
        next($input);
        $this->assertSame('test1', array_first($input));
    }

    /**
     * bootstrap.php: abbreviate_string()
     */
    public function test_abbreviate_string()
    {
        $data = [
            // expected, string, maxlength, placeholder, $ending
            ['', '', 10, '...', false],
            ['1234.90abc', '1234567890abc', 10, '.', false],
            ['żćżć.12345', 'żćżćżćżć12345', 10, '.', false],
            // TODO: more cases
        ];

        foreach ($data as $set) {
            $result = abbreviate_string($set[1], $set[2], $set[3], $set[4]);
            $this->assertSame($set[0], $result);
        }
    }

    /**
     * bootstrap.php: format_email()
     */
    public function test_format_email()
    {
        $data = [
            '' => '',
            'test' => 'test',
            'test@test.tld' => 'test@test.tld',
            'test@[127.0.0.1]' => 'test@[127.0.0.1]',
            'TEST@TEST.TLD' => 'TEST@test.tld',
        ];

        foreach ($data as $value => $expected) {
            $result = format_email($value);
            $this->assertSame($expected, $result, "Invalid format_email() result for {$value}");
        }
    }

    /**
     * bootstrap.php: format_email_recipient()
     */
    public function test_format_email_recipient()
    {
        $data = [
            '' => [''],
            'test' => ['test'],
            'test@test.tld' => ['test@test.tld'],
            'test@[127.0.0.1]' => ['test@[127.0.0.1]'],
            'TEST@TEST.TLD' => ['TEST@TEST.TLD'],
            'TEST <test@test.tld>' => ['test@test.tld', 'TEST'],
            '"TEST\"" <test@test.tld>' => ['test@test.tld', 'TEST"'],
        ];

        foreach ($data as $expected => $value) {
            $result = format_email_recipient($value[0], $value[1] ?? null);
            $this->assertSame($expected, $result, 'Invalid format_email_recipient()');
        }
    }

    /**
     * bootstrap.php: is_ascii()
     */
    public function test_is_ascii()
    {
        $result = is_ascii('0123456789');
        $this->assertTrue($result, 'Valid ASCII (numbers)');

        $result = is_ascii('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
        $this->assertTrue($result, 'Valid ASCII (letters)');

        $result = is_ascii(" !\"#\$%&'()*+,-./:;<=>?@[\\^_`{|}~");
        $this->assertTrue($result, 'Valid ASCII (special characters)');

        $result = is_ascii("\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F"
            . "\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F");
        $this->assertTrue($result, 'Valid ASCII (control characters)');

        $result = is_ascii("\n", false);
        $this->assertFalse($result, 'Valid ASCII (control characters)');

        $result = is_ascii('ż');
        $this->assertFalse($result, 'Invalid ASCII (UTF-8 character)');

        $result = is_ascii('ż', false);
        $this->assertFalse($result, 'Invalid ASCII (UTF-8 character [2])');
    }

    /**
     * bootstrap.php: version_parse()
     */
    public function test_version_parse()
    {
        $this->assertSame('0.9.0', version_parse('0.9-stable'));
        $this->assertSame('0.9.99', version_parse('0.9-git'));
    }
}
