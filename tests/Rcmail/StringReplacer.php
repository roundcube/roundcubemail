<?php

/**
 * Test class to test rcmail_string_replacer class
 *
 * @package Tests
 */
class Rcmail_RcmailStringReplacer extends PHPUnit\Framework\TestCase
{
    /**
     * Test for mailto_callback() method
     */
    function test_mailto_callback()
    {
        $replacer = new rcmail_string_replacer();

        $result = invokeMethod($replacer, 'mailto_callback', [['email@address.com', 'email@address.com']]);

        $this->assertMatchesRegularExpression($replacer->pattern, $result);

        $result = invokeMethod($replacer, 'mailto_callback', [['address.com', 'address.com']]);

        $this->assertSame('address.com', $result);
    }
}
