<?php

namespace Roundcube\Tests\Rcmail;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcmail_resend_mail class
 */
class ResendMailTest extends TestCase
{
    /**
     * Test for header() method
     */
    public function test_headers()
    {
        $mail = new \rcmail_resend_mail();

        $result = $mail->headers();

        $this->assertSame([], $result);
    }
}
