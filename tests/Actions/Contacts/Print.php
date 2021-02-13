<?php

/**
 * Test class to test rcmail_action_contacts_print
 *
 * @package Tests
 */
class Actions_Contacts_Print extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_contacts_print;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'print');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $db      = rcmail::get_instance()->get_dbh();
        $query   = $db->query('SELECT `contact_id` FROM `contacts` WHERE `user_id` = 1 LIMIT 1');
        $contact = $db->fetch_assoc($query);

        $_GET = ['_cid' => $contact['contact_id'], '_source' => '0'];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('contactprint', $output->template);
        $this->assertSame('', $output->getProperty('pagetitle')); // TODO: there should be a title
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
    }

    /**
     * Test contact_head() method
     */
    function test_contact_head()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test contact_details() method
     */
    function test_contact_details()
    {
        $this->markTestIncomplete();
    }
}
