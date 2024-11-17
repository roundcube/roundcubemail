<?php

namespace Roundcube\Tests\Actions\Mail;

use rcmail_action as rcmail_action;
use rcmail_action_mail_delete as rcmail_action_mail_delete;
use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_mail_delete
 */
class DeleteTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_delete();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }
}
