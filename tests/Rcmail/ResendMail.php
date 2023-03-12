<?php

/**
 * Test class to test rcmail_resend_mail class
 *
 * @package Tests
 */
class Rcmail_RcmailResendMail extends PHPUnit\Framework\TestCase
{
    /**
     * Test for header() method
     */
    function test_headers()
    {
        $mail = new rcmail_resend_mail();

        $result = $mail->headers();

        $this->assertSame([], $result);
    }
}
