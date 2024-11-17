<?php

namespace Roundcube\Tests\Actions\Mail;

use rcmail_action as rcmail_action;
use rcmail_action_mail_viewsource as rcmail_action_mail_viewsource;
use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_mail_viewsource
 */
class ViewsourceTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_viewsource();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }
}
