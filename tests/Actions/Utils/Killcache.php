<?php

/**
 * Test class to test rcmail_action_utils_killcache
 */
class Actions_Utils_Killcache extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_utils_killcache();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
