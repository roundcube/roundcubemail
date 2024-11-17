<?php

namespace Roundcube\Tests\Actions\Mail;

use rcmail_action as rcmail_action;
use rcmail_action_mail_show as rcmail_action_mail_show;
use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_mail_show
 */
class ShowTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_show();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }
}
