<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_spoofchecker class
 */
class SpoofcheckerTest extends TestCase
{
    /**
     * Test data for test_check()
     */
    public static function provide_check_cases(): iterable
    {
        return [
            // Valid:
            ['test@paypal.com', false],
            ['postbаnk@gmail.com', false], // ignore spoofed local part
            ['мон.мон', false],

            // Suspicious:
            ['test@Рaypal.com', true],
            ['test@postbаnk.com', true],
            ['aaa.мон', true],

            // TODO: Non-working as expected:
            // ['test@paypa1.com', true],
            // ['test@paypal' . "\xe2\x80\xa8" . '.com', true],
            // ['test@paypal' . "\xe2\x80\x8b" . '.com', true],
            // ['adoḅe.com', true], // ???????
        ];
    }

    /**
     * @dataProvider provide_check_cases
     */
    #[DataProvider('provide_check_cases')]
    public function test_check($email, $expected)
    {
        $this->assertSame($expected, \rcube_spoofchecker::check($email));
    }
}
