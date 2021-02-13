<?php

/**
 * Test class to test rcmail_action_contacts_group_rename
 *
 * @package Tests
 */
class Actions_Contacts_Group_Rename extends ActionTestCase
{
    /**
     * Test error handling
     */
    function test_group_rename_errors()
    {
        $action = new rcmail_action_contacts_group_rename;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'group-rename');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Invalid group id
        $_POST = ['_source' => '0', '_gid' => 'unknown', '_name' => ''];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('group-rename', $result['action']);
        $this->assertSame('this.display_message("An error occurred while saving.","error",0);', trim($result['exec']));

        // Readonly addressbook
        $_POST = ['_source' => rcube_addressbook::TYPE_RECIPIENT, '_gid' => 'aaa', '_name' => 'new-test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('group-rename', $result['action']);
        $this->assertSame('this.display_message("This address source is read only.","warning",0);', trim($result['exec']));
    }

    /**
     * Test renaming a group
     */
    function test_group_rename_success()
    {
        $action = new rcmail_action_contacts_group_rename;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'group-rename');

        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $db     = rcmail::get_instance()->get_dbh();
        $query  = $db->query('SELECT * FROM `contactgroups` WHERE `user_id` = 1 AND `name` = \'test-group\'');
        $result = $db->fetch_assoc($query);
        $gid    = $result['contactgroup_id'];

        $_POST = ['_source' => '0', '_gid' => $gid, '_name' => 'new-name'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('group-rename', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Group renamed successfully.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.update_contact_group({"source":"0","id":"' . $gid . '","name":"new-name"') !== false);

        $query  = $db->query('SELECT * FROM `contactgroups` WHERE `contactgroup_id` = ? AND `name` = ?', $gid, 'new-name');
        $result = $db->fetch_assoc($query);

        $this->assertTrue(!empty($result));
    }

}
