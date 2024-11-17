<?php

namespace Roundcube\Tests\Actions\Mail;

use rcmail_action as rcmail_action;
use rcmail_action_mail_getunread as rcmail_action_mail_getunread;
use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_mail_getunread
 */
class GetunreadTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_getunread();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }
}
