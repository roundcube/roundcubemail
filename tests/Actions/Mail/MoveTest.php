<?php

namespace Roundcube\Tests\Actions\Mail;

use rcmail_action;
use rcmail_action_mail_move;
use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_mail_move
 */
class MoveTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_move();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }
}
