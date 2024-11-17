<?php

namespace Roundcube\Tests\Actions\Mail;

use rcmail_action;
use rcmail_action_mail_check_recent;
use Roundcube\Tests\ActionTestCase;

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

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }
}
