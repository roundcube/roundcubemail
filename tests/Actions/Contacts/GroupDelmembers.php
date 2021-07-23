<?php

/**
 * Test class to test rcmail_action_contacts_group_delmembers
 *
 * @package Tests
 */
class Actions_Contacts_Group_Delmembers extends ActionTestCase
{
    /**
     * Test error handling
     */
    function test_group_delmembers_errors()
    {
        $action = new rcmail_action_contacts_group_delmembers;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'del-members');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Invalid group id
        $_POST = ['_source' => '0', '_gid' => 'unknown'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('del-members', $result['action']);
        $this->assertSame('this.display_message("An error occurred while saving.","error",0);', trim($result['exec']));

        // Readonly addressbook
        $_POST = ['_source' => rcube_addressbook::TYPE_RECIPIENT, '_gid' => 'test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('del-members', $result['action']);
        $this->assertSame('this.display_message("This address source is read only.","warning",0);', trim($result['exec']));
    }

    /**
     * Test deleting a group member
     */
    function test_group_delmembers_success()
    {
        $action = new rcmail_action_contacts_group_delmembers;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'del-members');

        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $db     = rcmail::get_instance()->get_dbh();
        $query  = $db->query('SELECT * FROM `contactgroups` WHERE `user_id` = 1 AND `name` = \'test-group\'');
        $result = $db->fetch_assoc($query);
        $gid    = $result['contactgroup_id'];
        $query  = $db->query('SELECT * FROM `contacts` WHERE `user_id` = 1 LIMIT 1');
        $result = $db->fetch_assoc($query);
        $cid    = $result['contact_id'];
        $db->query('INSERT INTO `contactgroupmembers` (`contactgroup_id`, `contact_id`) VALUES (?, ?)', $gid, $cid);

        $_POST = ['_source' => '0', '_gid' => $gid, '_cid' => $cid];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('del-members', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Successfully removed contacts from this group.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.remove_group_contacts({"source":"0","gid":"' . $gid . '"});') !== false);

        $query  = $db->query('SELECT * FROM `contactgroupmembers` WHERE `contactgroup_id` = ? AND `contact_id` = ?', $gid, $cid);
        $result = $db->fetch_assoc($query);

        $this->assertTrue(empty($result));
    }
}
