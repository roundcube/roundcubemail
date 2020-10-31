<?php

/**
 * Test class to test rcmail_action_contacts_edit
 *
 * @package Tests
 */
class Actions_Contacts_Edit extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_edit;

        $this->assertInstanceOf('rcmail_action', $object);
    }

    /**
     * Test run() method in edit mode
     */
    function test_run_edit()
    {
        $action = new rcmail_action_contacts_edit;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'edit');

        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $db      = rcmail::get_instance()->get_dbh();
        $query   = $db->query('SELECT `contact_id` FROM `contacts` WHERE `user_id` = 1 LIMIT 1');
        $contact = $db->fetch_assoc($query);

        $_GET = [
            '_cid'    => $contact['contact_id'],
            '_source' => '0'
        ];

        try {
            $action->run();
        }
        catch (Exception $e) {
            $this->assertSame(OutputHtmlMock::E_EXIT, $e->getCode());
        }

        $result = $output->getOutput();

        $this->assertSame('contactedit', $output->template);
        $this->assertSame('Edit contact', $output->getProperty('pagetitle'));
        $this->assertSame($contact['contact_id'], $output->get_env('cid'));
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
        $this->assertTrue(strpos($result, "rcmail.gui_object('contactphoto', 'contactpic');") !== false);
    }
}
