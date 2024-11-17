<?php

namespace Roundcube\Tests\Actions\Mail;

use rcmail_action as rcmail_action;
use rcmail_action_mail_folder_purge as rcmail_action_mail_folder_purge;
use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_mail_folder_purge
 */
class FolderPurgeTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_folder_purge();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }
}
