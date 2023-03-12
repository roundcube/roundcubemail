<?php

/**
 * Test class to test rcmail_action_contacts_group_addmembers
 *
 * @package Tests
 */
class Actions_Contacts_Group_Addmembers extends ActionTestCase
{
    /**
     * Test error handling
     */
    function test_group_addmembers_errors()
    {
        $action = new rcmail_action_contacts_group_addmembers;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'add-members');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Invalid group id
        $_POST = ['_source' => '0', '_gid' => 'unknown'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('add-members', $result['action']);
        $this->assertSame('this.display_message("No group assignments changed.","notice",0);', trim($result['exec']));

        // Readonly addressbook
        $_POST = ['_source' => rcube_addressbook::TYPE_RECIPIENT, '_gid' => 'test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('add-members', $result['action']);
        $this->assertSame('this.display_message("This address source is read only.","warning",0);', trim($result['exec']));
    }

    /**
     * Test adding a group member
     */
    function test_group_addmembers_success()
    {
        $action = new rcmail_action_contacts_group_addmembers;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'add-members');

        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $db     = rcmail::get_instance()->get_dbh();
        $query  = $db->query('SELECT * FROM `contactgroups` WHERE `user_id` = 1 AND `name` = \'test-group\'');
        $result = $db->fetch_assoc($query);
        $gid    = $result['contactgroup_id'];
        $query  = $db->query('SELECT * FROM `contacts` WHERE `user_id` = 1 LIMIT 1');
        $result = $db->fetch_assoc($query);
        $cid    = $result['contact_id'];

        $_POST = ['_source' => '0', '_gid' => $gid, '_cid' => $cid];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('add-members', $result['action']);
        $this->assertSame('this.display_message("Successfully added the contacts to this group.","confirmation",0);', trim($result['exec']));

        $query  = $db->query('SELECT * FROM `contactgroupmembers` WHERE `contactgroup_id` = ? AND `contact_id` = ?', $gid, $cid);
        $result = $db->fetch_assoc($query);

        $this->assertTrue(!empty($result));
    }
}
