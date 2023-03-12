<?php

/**
 * Test class to test rcube_spoofchecker class
 *
 * @package Tests
 */
class Framework_Spoofchecker extends PHPUnit\Framework\TestCase
{
    /**
     * Test data for test_check()
     */
    function data_check()
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
     * @dataProvider data_check
     */
    function test_check($email, $expected)
    {
        $this->assertSame($expected, rcube_spoofchecker::check($email));
    }
}
