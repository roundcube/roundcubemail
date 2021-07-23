<?php

/**
 * Test class to test rcmail_action_contacts_qrcode
 *
 * @package Tests
 */
class Actions_Contacts_Qrcode extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_contacts_qrcode;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'qrcode');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['HTTP/1.0 404 Contact not found'], $output->headers);
        $this->assertSame('', $result);

        if (!function_exists('imagecreate')) {
            $this->markTestSkipped();
        }

        $db      = rcmail::get_instance()->get_dbh();
        $query   = $db->query('SELECT `contact_id` FROM `contacts` WHERE `user_id` = 1 LIMIT 1');
        $contact = $db->fetch_assoc($query);

        $_GET = ['_cid' => $contact['contact_id'], '_source' => '0'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('Content-Type: image/png', $output->headers[0]);
        $this->assertRegExp('/^\x89\x50\x4E\x47/', $result);
    }
}
