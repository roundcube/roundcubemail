<?php

/**
 * Test class to test rcmail_action_contacts_qrcode
 */
class Actions_Contacts_Qrcode extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new rcmail_action_contacts_qrcode();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'qrcode');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['HTTP/1.0 404 Contact not found'], $output->headers);
        self::assertSame('', $result);

        $type = $action->check_support();

        if (!$type) {
            self::markTestSkipped();
        }

        $db = rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT `contact_id` FROM `contacts` WHERE `user_id` = 1 AND `name` = \'Jon Snow\'');
        $contact = $db->fetch_assoc($query);

        $_GET = ['_cid' => $contact['contact_id'], '_source' => '0'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        if ($type == 'image/png') {
            self::assertSame('Content-Type: image/png', $output->headers[0]);
            self::assertMatchesRegularExpression('/^\x89\x50\x4E\x47/', $result);
        } else {
            self::assertSame('Content-Type: image/svg+xml', $output->headers[0]);
            self::assertMatchesRegularExpression('/^<\?xml/', $result);
            self::assertMatchesRegularExpression('/<svg /', $result);
            self::assertMatchesRegularExpression('/<rect /', $result);
        }
    }
}
