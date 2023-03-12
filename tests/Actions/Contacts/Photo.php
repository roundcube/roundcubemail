<?php

/**
 * Test class to test rcmail_action_contacts_photo
 *
 * @package Tests
 */
class Actions_Contacts_Photo extends ActionTestCase
{
    /**
     * Test run() method - no photo case
     */
    function test_no_photo()
    {
        $action = new rcmail_action_contacts_photo;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'photo');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: image/gif'], $output->headers);
        $this->assertSame(base64_decode(rcmail_output::BLANK_GIF), $result);

        $_GET = ['_error' => 1];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['HTTP/1.0 204 Photo not found'], $output->headers);
        $this->assertSame('', $result);
    }

    /**
     * Test run() method - a contact with real photo
     */
    function test_photo()
    {
        $this->markTestIncomplete();
    }
}
