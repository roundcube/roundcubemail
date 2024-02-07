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

        self::assertInstanceOf('rcube_smtp', $object, 'Class constructor');
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

        self::assertCount(2, $result);
        self::assertSame('john@domain.tld', $result[0]);
        self::assertSame(
            'Received: from github.com ([10.48.109.45]) by smtp.github.com (Postfix) with ESMTPA id 8C9B4E0075'
                . " for <john@domain.tld>; Sat, 28 Nov 2020 22:45:44 -0800 (PST)\r\n"
                . "Subject: Test\r\n"
                . "From: \"John Doe\" <john@domain.tld>\r\n",
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

        self::assertSame(['test@test1.com', 'test@test2.pl', 'test@test3.uk'], $result);
    }
}
