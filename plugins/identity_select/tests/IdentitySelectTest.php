<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

use function Roundcube\Tests\invokeMethod;

class IdentitySelectTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \identity_select($rcube->plugins);

        $this->assertInstanceOf('identity_select', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * Test get_email_from_header() method
     */
    public function test_get_email_from_header()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \identity_select($rcube->plugins);
        $message = new \stdClass();

        $headers = [
            'Delivered-To' => 'user@example.com',
            'Received' => 'from github.com ([10.48.109.45]) by smtp.github.com (Postfix) with ESMTPA id 8C9B4E0075'
                . ' for <john@domain.tld>; Sat, 28 Nov 2020 22:45:44 -0800 (PST)',
        ];
        $message->headers = \rcube_message_header::from_array($headers);
        $result = invokeMethod($plugin, 'get_email_from_header', [$message, 'Delivered-To']);
        $this->assertSame(['user@example.com'], $result);
        $result = invokeMethod($plugin, 'get_email_from_header', [$message, 'Received']);
        $this->assertSame(['john@domain.tld'], $result);

        $headers = [
            'Received' => [
                'from mail.aliasprovider.com (mail.aliasprovider.com [198.51.100.20]) by smtp.example.com (Postfix) with ESMTP id 555AAA777'
                    . ' for chris.smith@example.com; Sun, 10 May 2026 16:00:01 -0400 (EDT)',
                'from mail.external.com (mail.external.com [203.0.113.45]) by mail.aliasprovider.com (Postfix) with ESMTP id 444BBB666'
                    . ' for bob.smith@example.com; Sun, 10 May 2026 16:00:00 -0400 (EDT)',
                'from client.mail.com (client.mail.com [192.0.2.10]) by mail.external.com (Postfix) with ESMTP id 333CCC555'
                    . ' for sales@example.com; Sun, 10 May 2026 15:59:58 -0400 (EDT)',
            ],
        ];
        $message->headers = \rcube_message_header::from_array($headers);
        $result = invokeMethod($plugin, 'get_email_from_header', [$message, 'Received']);
        $this->assertSame(['sales@example.com', 'bob.smith@example.com', 'chris.smith@example.com'], $result);

        $headers = [
            'Received' => 'from mail.aliasprovider.com (mail.aliasprovider.com [198.51.100.20]) by smtp.example.com (Postfix) with ESMTP id 555AAA777'
                . ' for <josé.müller@example.com>; Sun, 10 May 2026 16:30:01 -0400 (EDT)',
        ];
        $message->headers = \rcube_message_header::from_array($headers);
        $result = invokeMethod($plugin, 'get_email_from_header', [$message, 'Received']);
        $this->assertSame(['josé.müller@example.com'], $result);
    }
}
