<?php

/**
 * Test class to test rcmail_action_utils_text2html
 *
 * @package Tests
 */
class Actions_Utils_Text2html extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_utils_text2html;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
