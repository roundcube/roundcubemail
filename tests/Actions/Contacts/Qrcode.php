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

        $type = $action->check_support();

        if (!$type) {
            $this->markTestSkipped();
        }

        $db      = rcmail::get_instance()->get_dbh();
        $query   = $db->query('SELECT `contact_id` FROM `contacts` WHERE `user_id` = 1 AND `name` = \'Jon Snow\'');
        $contact = $db->fetch_assoc($query);

        $_GET = ['_cid' => $contact['contact_id'], '_source' => '0'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        if ($type == 'image/png') {
            $this->assertSame('Content-Type: image/png', $output->headers[0]);
            $this->assertMatchesRegularExpression('/^\x89\x50\x4E\x47/', $result);
        }
        else {
            $this->assertSame('Content-Type: image/svg+xml', $output->headers[0]);
            $this->assertMatchesRegularExpression('/^<\?xml/', $result);
            $this->assertMatchesRegularExpression('/<svg /', $result);
            $this->assertMatchesRegularExpression('/<rect /', $result);
        }
    }
}
