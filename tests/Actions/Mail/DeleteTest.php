<?php

namespace Roundcube\Mail\Tests\Actions\Mail;

/**
 * Test class to test rcmail_action_mail_delete
 */
class DeleteTest extends \ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_delete();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
