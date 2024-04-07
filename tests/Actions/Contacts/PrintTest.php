<?php

/**
 * Test class to test rcmail_action_contacts_print
 */
class Actions_Contacts_Print extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new rcmail_action_contacts_print();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'print');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        self::initDB('contacts');

        $db = rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT `contact_id` FROM `contacts` WHERE `user_id` = 1 LIMIT 1');
        $contact = $db->fetch_assoc($query);

        $_GET = ['_cid' => $contact['contact_id'], '_source' => '0'];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame('contactprint', $output->template);
        self::assertSame('', $output->getProperty('pagetitle')); // TODO: there should be a title
        self::assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
    }

    /**
     * Test contact_head() method
     */
    public function test_contact_head()
    {
        self::markTestIncomplete();
    }

    /**
     * Test contact_details() method
     */
    public function test_contact_details()
    {
        self::markTestIncomplete();
    }
}
