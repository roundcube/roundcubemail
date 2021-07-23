<?php

/**
 * Test class to test rcmail_action_contacts_undo
 *
 * @package Tests
 */
class Actions_Contacts_Undo extends ActionTestCase
{
    /**
     * Test contact undelete
     */
    function test_undo()
    {
        $action = new rcmail_action_contacts_undo;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'undo');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $db     = rcmail::get_instance()->get_dbh();
        $query  = $db->query('SELECT `contact_id` FROM `contacts` WHERE `user_id` = 1 LIMIT 1');
        $result = $db->fetch_assoc($query);
        $cid    = $result['contact_id'];
        $db->query('UPDATE `contacts` SET `del` = 1 WHERE `contact_id` = ' . $cid);

        $_SESSION['contact_undo'] = ['data' => [[$cid]]];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('undo', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'his.display_message("Contact(s) restored successfully.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.list_contacts()') !== false);

        $query  = $db->query('SELECT * FROM `contacts` WHERE `contact_id` = ' . $cid);
        $result = $db->fetch_assoc($query);

        $this->assertTrue(!empty($result));
        $this->assertTrue(empty($result['del']));
    }
}
