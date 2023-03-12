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

    /**
     * Test for run()
     */
    function test_run()
    {
        $object = new rcmail_action_utils_text2html;
        $input = "test plain text input";
        $object::$source = $this->createTempFile($input);

        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'utils', 'text2html');

        $this->assertTrue($object->checks());

        $this->runAndAssert($object, OutputHtmlMock::E_EXIT);

        $this->assertSame('<div class="pre">test plain text input</div>', $output->output);
        $this->assertSame(['Content-Type: text/html; charset=UTF-8'], $output->headers);
    }
}
