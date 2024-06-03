<?php

namespace Roundcube\Mail\Tests\Actions\Mail;

use Roundcube\Mail\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_mail_check_recent
 */
class CheckRecentTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_check_recent();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
