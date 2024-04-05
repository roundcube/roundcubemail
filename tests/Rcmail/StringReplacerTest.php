<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcmail_string_replacer class
 */
class Rcmail_RcmailStringReplacer extends TestCase
{
    /**
     * Test for mailto_callback() method
     */
    public function test_mailto_callback()
    {
        $replacer = new rcmail_string_replacer();

        $result = invokeMethod($replacer, 'mailto_callback', [['email@address.com', 'email@address.com']]);

        $this->assertMatchesRegularExpression($replacer->pattern, $result);

        $result = invokeMethod($replacer, 'mailto_callback', [['address.com', 'address.com']]);

        $this->assertSame('address.com', $result);
    }
}
