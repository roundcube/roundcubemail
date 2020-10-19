<?php

/**
 * Test class to test rcmail_action_utils_html2text
 *
 * @package Tests
 */
class Actions_Utils_Html2text extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_utils_html2text;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
