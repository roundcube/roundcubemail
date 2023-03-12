<?php

/**
 * Test class to test rcmail_action_contacts_group_delete
 *
 * @package Tests
 */
class Actions_Contacts_Group_Delete extends ActionTestCase
{
    /**
     * Test error handling
     */
    function test_group_delete_errors()
    {
        $action = new rcmail_action_contacts_group_delete;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'group-delete');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Invalid group id
        $_POST = ['_source' => '0', '_gid' => 'unknown'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('group-delete', $result['action']);
        $this->assertSame('this.display_message("An error occurred while saving.","error",0);', trim($result['exec']));

        // Readonly addressbook
        $_POST = ['_source' => rcube_addressbook::TYPE_RECIPIENT, '_name' => 'test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('group-delete', $result['action']);
        $this->assertSame('this.display_message("This address source is read only.","warning",0);', trim($result['exec']));
    }

    /**
     * Test deleting a group
     */
    function test_group_delete_success()
    {
        $action = new rcmail_action_contacts_group_delete;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'group-delete');

        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $db     = rcmail::get_instance()->get_dbh();
        $query  = $db->query('SELECT * FROM `contactgroups` WHERE `user_id` = 1 AND `name` = \'test-group\'');
        $result = $db->fetch_assoc($query);
        $gid    = $result['contactgroup_id'];

        $_POST = ['_source' => '0', '_gid' => $gid];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('group-delete', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Group deleted successfully.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.remove_group_item({"source":"0","id":"' . $gid . '"});') !== false);

        $query  = $db->query('SELECT * FROM `contactgroups` WHERE `contactgroup_id` = ? AND `del` = 1', $gid);
        $result = $db->fetch_assoc($query);

        $this->assertTrue(!empty($result));
    }
}
