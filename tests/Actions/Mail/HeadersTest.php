<?php

namespace Roundcube\Tests\Actions\Mail;

use rcmail_action;
use rcmail_action_mail_headers;
use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_mail_headers
 */
class HeadersTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_headers();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }
}
