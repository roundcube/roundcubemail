<?php

/**
 * Test class to test rcmail_action_contacts_undo
 */
class Actions_Contacts_Undo extends ActionTestCase
{
    /**
     * Test contact undelete
     */
    public function test_undo()
    {
        $action = new rcmail_action_contacts_undo();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'undo');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        self::initDB('contacts');

        $db = rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT `contact_id` FROM `contacts` WHERE `user_id` = 1 LIMIT 1');
        $result = $db->fetch_assoc($query);
        $cid = $result['contact_id'];
        $db->query('UPDATE `contacts` SET `del` = 1 WHERE `contact_id` = ' . $cid);

        $_SESSION['contact_undo'] = ['data' => [[$cid]]];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('undo', $result['action']);
        self::assertTrue(strpos($result['exec'], 'his.display_message("Contact(s) restored successfully.","confirmation",0);') !== false);
        self::assertTrue(strpos($result['exec'], 'this.list_contacts()') !== false);

        $query = $db->query('SELECT * FROM `contacts` WHERE `contact_id` = ' . $cid);
        $result = $db->fetch_assoc($query);

        self::assertTrue(!empty($result));
        self::assertTrue(empty($result['del']));
    }
}
