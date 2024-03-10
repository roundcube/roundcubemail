<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_smtp class
 */
class Framework_Smtp extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_smtp();

        $this->assertInstanceOf('rcube_smtp', $object, 'Class constructor');
    }

    /**
     * Test preparing headers
     */
    public function test_prepare_headers()
    {
        $smtp = new rcube_smtp();

        $headers = [
            'Subject' => 'Test',
            'From' => '"John Doe" <john@domain.tld>',
            'Received' => 'from github.com ([10.48.109.45]) by smtp.github.com (Postfix) with ESMTPA id 8C9B4E0075'
                . ' for <john@domain.tld>; Sat, 28 Nov 2020 22:45:44 -0800 (PST)',
        ];

        $result = invokeMethod($smtp, '_prepare_headers', [$headers]);

        $this->assertCount(2, $result);
        $this->assertSame('john@domain.tld', $result[0]);
        $this->assertSame(
            'Received: from github.com ([10.48.109.45]) by smtp.github.com (Postfix) with ESMTPA id 8C9B4E0075'
                . " for <john@domain.tld>; Sat, 28 Nov 2020 22:45:44 -0800 (PST)\r\n"
                . "Subject: Test\r\n"
                . "From: \"John Doe\" <john@domain.tld>\r\n",
            $result[1]
        );
    }

    /**
     * Test preparing unicode headers.
     *
     * When either the sender or any recipient requires EAI, then it's
     * okay to send UTF-8 in headers (without RFC2047 encoding, that
     * is). This test tests that Roundcube chooses plain UTF-8 in the
     * Subject, in a the display-name of an address, and in the
     * message-id.
     */
    function test_prepare_unicode_headers()
    {
        $smtp = new rcube_smtp;

        $headers = [
            'Subject' => 'भारत',
            'From' => '"भारत" <भारत@भारत.भारत>'
        ];

        $result = invokeMethod($smtp, '_prepare_headers', [$headers]);

        $this->assertCount(2, $result);
        $this->assertSame('भारत@भारत.भारत', $result[0]);
        $this->assertSame(
            "Subject: भारत\r\nFrom: \"भारत\" <भारत@भारत.भारत>\r\n",
            $result[1]
        );
    }

    /**
     * Test parsing email address input
     */
    public function test_parse_rfc822()
    {
        $smtp = new rcube_smtp();
        $input = 'test@test1.com, "test" <test@test2.pl>, "test@test3.eu" <test@test3.uk>';
        $result = invokeMethod($smtp, '_parse_rfc822', [$input]);

        $this->assertSame(['test@test1.com', 'test@test2.pl', 'test@test3.uk'], $result);
    }
}
