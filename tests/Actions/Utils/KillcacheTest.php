<?php

namespace Roundcube\Tests\Actions\Utils;

use rcmail_action as rcmail_action;
use rcmail_action_utils_killcache as rcmail_action_utils_killcache;
use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_utils_killcache
 */
class KillcacheTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_utils_killcache();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }
}
