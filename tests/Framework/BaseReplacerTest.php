<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_base_replacer class
 */
class BaseReplacerTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_base_replacer('test');

        $this->assertInstanceOf(\rcube_base_replacer::class, $object, 'Class constructor');
    }

    /**
     * Test replace()
     */
    public function test_replace()
    {
        $base = 'http://thisshouldntbetheurl.bob.com/';
        $html = '<A href=http://shouldbethislink.com>Test URL</A>';

        $replacer = new \rcube_base_replacer($base);
        $response = $replacer->replace($html);

        $this->assertSame('<A href="http://shouldbethislink.com">Test URL</A>', $response);
    }

    /**
     * Data for absolute_url() test
     */
    public static function provide_absolute_url_cases(): iterable
    {
        return [
            ['', 'http://test', 'http://test/'],
            ['http://test', 'http://anything', 'http://test'],
            ['cid:test', 'http://anything', 'cid:test'],
            ['/test', 'http://test', 'http://test/test'],
            ['./test', 'http://test', 'http://test/test'],
            ['../test1', 'http://test/test2', 'http://test1'],
            ['../test1', 'http://test/test2/', 'http://test/test1'],
        ];
    }

    /**
     * Test absolute_url()
     *
     * @dataProvider provide_absolute_url_cases
     */
    #[DataProvider('provide_absolute_url_cases')]
    public function test_absolute_url($path, $base, $expected)
    {
        $replacer = new \rcube_base_replacer('test');
        $result = $replacer->absolute_url($path, $base);

        $this->assertSame($expected, $result);
    }
}
