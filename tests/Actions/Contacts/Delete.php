<?php

/**
 * Test class to test rcmail_action_contacts_delete
 *
 * @package Tests
 */
class Actions_Contacts_Delete extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_delete;

        $this->assertInstanceOf('rcmail_action', $object);
    }

    /**
     * Test deleting of a single existing contact
     */
    function test_delete_single_existing_contact()
    {
        $action = new rcmail_action_contacts_delete;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'delete');

        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $db     = rcmail::get_instance()->get_dbh();
        $query  = $db->query('SELECT `contact_id` FROM `contacts` WHERE `user_id` = 1 LIMIT 1');
        $result = $db->fetch_assoc($query);

        $_POST = [
            '_cid'    => $result['contact_id'],
            '_source' => '0'
        ];

        try {
            $action->run();
        }
        catch (Exception $e) {
            $this->assertSame(OutputJsonMock::E_EXIT, $e->getCode());
        }

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('delete', $result['action']);
        $this->assertSame(1, $result['env']['pagecount']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Contact(s) deleted successfully.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_rowcount("Contacts 1 to 5 of 5")') !== false);
    }
}
