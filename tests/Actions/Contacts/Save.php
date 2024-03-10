<?php

/**
 * Test class to test rcmail_action_contacts_save
 */
class Actions_Contacts_Save extends ActionTestCase
{
    /**
     * Test pre-checks
     */
    public function test_run_prechecks()
    {
        $action = new rcmail_action_contacts_save();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'save');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        // reload
        $_GET = ['_reload' => 1];

        $action->run();

        self::assertNull($output->getOutput());
        self::assertNull($output->getProperty('message'));
        self::assertSame('add', rcmail::get_instance()->action);

        // readonly addressbook
        $_GET = ['_source' => rcube_addressbook::TYPE_RECIPIENT];

        $action->run();

        self::assertNull($output->getOutput());
        self::assertSame('contactreadonly', $output->getProperty('message'));
        self::assertSame('add', rcmail::get_instance()->action);

        // empty $_POST
        $_POST = ['_source' => '0'];

        $action->run();

        self::assertNull($output->getOutput());
        self::assertSame('nonamewarning', $output->getProperty('message'));
        self::assertSame('add', rcmail::get_instance()->action);
    }

    /**
     * Test saving a new contact
     */
    public function test_run_new_contact()
    {
        $action = new rcmail_action_contacts_save();
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

        self::assertSame('iframe', $output->template);
        self::assertSame('successfullysaved', $output->getProperty('message'));
        self::assertTrue(stripos($result, '<!DOCTYPE html>') === 0);

        $db = rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT `contact_id` FROM `contacts` WHERE `email` = ?', 'test@user.com');
        $contact = $db->fetch_assoc($query);

        self::assertTrue(!empty($contact));
    }

    /**
     * Test editing a contact
     */
    public function test_run_existing_contact()
    {
        self::markTestIncomplete();
    }

    /**
     * Test process_input() method
     */
    public function test_process_input()
    {
        self::markTestIncomplete();
    }
}
