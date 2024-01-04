<?php

/**
 * Test class to test rcmail_action_utils_spell
 */
class Actions_Utils_Spell extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_utils_spell();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
