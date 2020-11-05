<?php

/**
 * Test class to test rcmail_action_contacts_save
 *
 * @package Tests
 */
class Actions_Contacts_Save extends ActionTestCase
{
    /**
     * Test pre-checks
     */
    function test_run_prechecks()
    {
        $action = new rcmail_action_contacts_save;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'save');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // reload
        $_GET = ['_reload' => 1];

        $action->run();

        $this->assertSame(null, $output->getOutput());
        $this->assertSame(null, $output->getProperty('message'));
        $this->assertSame('add', rcmail::get_instance()->action);

        // readonly addressbook
        $_GET = ['_source' => rcube_addressbook::TYPE_RECIPIENT];

        $action->run();

        $this->assertSame(null, $output->getOutput());
        $this->assertSame('contactreadonly', $output->getProperty('message'));
        $this->assertSame('add', rcmail::get_instance()->action);

        // empty $_POST
        $_POST = ['_source' => '0'];

        $action->run();

        $this->assertSame(null, $output->getOutput());
        $this->assertSame('nonamewarning', $output->getProperty('message'));
        $this->assertSame('add', rcmail::get_instance()->action);
    }

    /**
     * Test saving a new contact
     */
    function test_run_new_contact()
    {
        $action = new rcmail_action_contacts_save;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'save');

        self::initDB('contacts');

        $_POST = [
            '_source' => '0',
            '_firstname' => 'Alec',
            '_surname' => 'Test',
            '_subtype_email' => ['home'],
            '_email' => ['test@user.com'],
        ];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('iframe', $output->template);
        $this->assertSame('successfullysaved', $output->getProperty('message'));
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);

        $db      = rcmail::get_instance()->get_dbh();
        $query   = $db->query('SELECT `contact_id` FROM `contacts` WHERE `email` = ?', 'test@user.com');
        $contact = $db->fetch_assoc($query);

        $this->assertTrue(!empty($contact));
    }

    /**
     * Test editing a contact
     */
    function test_run_existing_contact()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test process_input() method
     */
    function test_process_input()
    {
        $this->markTestIncomplete();
    }
}
