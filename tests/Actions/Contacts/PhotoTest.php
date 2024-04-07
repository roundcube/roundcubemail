<?php

/**
 * Test class to test rcmail_action_contacts_photo
 */
class Actions_Contacts_Photo extends ActionTestCase
{
    /**
     * Test run() method - no photo case
     */
    public function test_no_photo()
    {
        $action = new rcmail_action_contacts_photo();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'photo');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: image/gif'], $output->headers);
        self::assertSame(base64_decode(rcmail_output::BLANK_GIF, true), $result);

        $_GET = ['_error' => 1];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['HTTP/1.0 204 Photo not found'], $output->headers);
        self::assertSame('', $result);
    }

    /**
     * Test run() method - a contact with real photo
     */
    public function test_photo()
    {
        self::markTestIncomplete();
    }
}
