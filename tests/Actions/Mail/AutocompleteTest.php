<?php

namespace Roundcube\Mail\Tests\Actions\Mail;

use Roundcube\Mail\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_mail_autocomplete
 */
class AutocompleteTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_autocomplete();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
