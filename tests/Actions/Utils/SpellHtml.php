<?php

/**
 * Test class to test rcmail_action_utils_spell_html
 *
 * @package Tests
 */
class Actions_Utils_SpellHtml extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_utils_spell_html;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
