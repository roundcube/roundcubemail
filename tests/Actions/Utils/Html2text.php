<?php

/**
 * Test class to test rcmail_action_utils_html2text
 *
 * @package Tests
 */
class Actions_Utils_Html2text extends ActionTestCase
{
    /**
     * Test for run()
     */
    function test_run()
    {
        $object = new rcmail_action_utils_html2text;
        $html = "<p>test</p>";
        $object::$source = $this->createTempFile($html);

        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'utils', 'html2text');

        $this->assertInstanceOf('rcmail_action', $object);
        $this->assertTrue($object->checks());

        $this->runAndAssert($object, OutputHtmlMock::E_EXIT);

        $this->assertSame('test', $output->output);
        $this->assertSame(['Content-Type: text/plain; charset=UTF-8'], $output->headers);
    }
}
