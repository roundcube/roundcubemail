<?php

namespace Roundcube\Tests\Actions\Mail;

use rcmail_action;
use rcmail_action_mail_list;
use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_mail_list
 */
class ListTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_list();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }
}
