<?php

/**
 * Test class to test rcmail_action_contacts_copy
 *
 * @package Tests
 */
class Actions_Contacts_Copy extends ActionTestCase
{
    /**
     * Test copying pre-check errors
     */
    function test_copy_pre_check_errors()
    {
        $action = new rcmail_action_contacts_copy;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'copy');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Missing target addressbook
        $_POST = [
            '_cid'    => 1,
            '_source' => '0',
        ];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('copy', $result['action']);
        $this->assertSame('this.display_message("Could not copy any contacts.","error",0);', trim($result['exec']));

        // target = source
        $_POST['_to'] = '0';

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('copy', $result['action']);
        $this->assertSame('this.display_message("Could not copy any contacts.","error",0);', trim($result['exec']));

        // target readonly
        $_POST['_to'] = rcube_addressbook::TYPE_RECIPIENT;

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('copy', $result['action']);
        $this->assertSame('this.display_message("Could not copy any contacts.","error",0);', trim($result['exec']));

        // Non-existing contact
        $_POST = [
            '_cid'    => 100,
            '_source' => rcube_addressbook::TYPE_RECIPIENT,
            '_to'     => '0',
        ];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('copy', $result['action']);
        $this->assertSame('this.display_message("Could not copy any contacts.","error",0);', trim($result['exec']));
    }

    /**
     * Test successful copying a contact
     */
    function test_copy_success()
    {
        $action = new rcmail_action_contacts_copy;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'copy');

        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $rcmail = rcmail::get_instance();
        $source = $rcmail->get_address_book(rcube_addressbook::TYPE_RECIPIENT);
        $cid    = $rcmail->contact_create(['email' => 'test@recipient.com'], $source);

        // Missing target addressbook
        $_POST = [
            '_cid'    => $cid,
            '_source' => rcube_addressbook::TYPE_RECIPIENT,
            '_to'     => '0',
        ];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('copy', $result['action']);
        $this->assertSame('this.display_message("Successfully copied 1 contacts.","confirmation",0);', trim($result['exec']));

        // Check that the contact has been really added to the contacts db
        $db     = $rcmail->get_dbh();
        $query  = $db->query('SELECT count(*) AS cnt FROM `contacts` WHERE `user_id` = 1 AND `email` = ?', 'test@recipient.com');
        $result = $db->fetch_assoc($query);

        $this->assertSame('1', $result['cnt']);
    }

    /**
     * Test copying a contact with group assignment
     */
    function test_copy_with_group()
    {
        $this->markTestIncomplete();
    }
}
