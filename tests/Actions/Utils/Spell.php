<?php

/**
 * Test class to test rcmail_action_utils_spell
 *
 * @package Tests
 */
class Actions_Utils_Spell extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_utils_spell;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
