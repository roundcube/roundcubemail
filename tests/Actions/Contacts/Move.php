<?php

/**
 * Test class to test rcmail_action_contacts_move
 *
 * @package Tests
 */
class Actions_Contacts_Move extends ActionTestCase
{
    /**
     * Test moving of a single contact
     */
    function test_move_contact()
    {
        $action = new rcmail_action_contacts_move;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'move');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $db      = rcmail::get_instance()->get_dbh();
        $query   = $db->query('SELECT * FROM `collected_addresses` WHERE `email` = ?', 'test@collected.eu');
        $contact = $db->fetch_assoc($query);
        $cid     = $contact['address_id'];

        $_POST = ['_cid' => $cid, '_to' => '0', '_source' => rcube_addressbook::TYPE_RECIPIENT];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('move', $result['action']);
        $this->assertSame(0, $result['env']['pagecount']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Successfully moved 1 contacts.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_rowcount("No contacts found.");') !== false);

        $query  = $db->query('SELECT * FROM `contacts` WHERE `email` = ?', 'test@collected.eu');
        $result = $db->fetch_assoc($query);

        $this->assertTrue(!empty($result));

        $query  = $db->query('SELECT * FROM `collected_addresses` WHERE `email` = ?', 'test@collected.eu');
        $result = $db->fetch_assoc($query);

        $this->assertTrue(empty($result));
    }

    /**
     * Test moving a contact to a group
     */
    function test_move_contact_to_group()
    {
        // Test error handling, test moving to a group, test moving multiple contacts
        $this->markTestIncomplete();
    }
}
