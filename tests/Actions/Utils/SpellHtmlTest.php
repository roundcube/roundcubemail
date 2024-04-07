<?php

/**
 * Test class to test rcmail_action_utils_spell_html
 */
class Actions_Utils_SpellHtml extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_utils_spell_html();

        self::assertInstanceOf('rcmail_action', $object);
    }
}
