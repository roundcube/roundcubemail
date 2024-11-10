<?php

namespace Roundcube\Tests\Actions\Mail;

use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_mail_list_contacts
 */
class ListContactsTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_list_contacts();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }
}
