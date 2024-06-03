<?php

namespace Roundcube\Mail\Tests\Actions\Utils;

use Roundcube\Mail\Tests\ActionTestCase;

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

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
