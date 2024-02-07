<?php

/**
 * Test class to test rcmail_action_utils_html2text
 */
class Actions_Utils_Html2text extends ActionTestCase
{
    /**
     * Test for run()
     */
    public function test_run()
    {
        $object = new rcmail_action_utils_html2text();
        $html = '<p>test</p>';
        $object::$source = $this->createTempFile($html);

        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'utils', 'html2text');

        self::assertInstanceOf('rcmail_action', $object);
        self::assertTrue($object->checks());

        $this->runAndAssert($object, OutputHtmlMock::E_EXIT);

        self::assertSame('test', $output->output);
        self::assertSame(['Content-Type: text/plain; charset=UTF-8'], $output->headers);
    }
}
