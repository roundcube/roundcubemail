<?php

/**
 * Test class to test rcmail_action_contacts_group_create
 */
class Actions_Contacts_Group_Create extends ActionTestCase
{
    /**
     * Test error handling
     */
    public function test_group_create_errors()
    {
        $action = new rcmail_action_contacts_group_create();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'group-create');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        // Unset group name
        $_POST = ['_source' => '0', '_name' => ''];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('group-create', $result['action']);
        self::assertSame('this.display_message("An error occurred while saving.","error",0);', trim($result['exec']));

        // Readonly addressbook
        $_POST = ['_source' => rcube_addressbook::TYPE_RECIPIENT, '_name' => 'test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('group-create', $result['action']);
        self::assertSame('this.display_message("This address source is read only.","warning",0);', trim($result['exec']));
    }

    /**
     * Test creating a group
     */
    public function test_group_create_success()
    {
        $action = new rcmail_action_contacts_group_create();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'group-create');

        self::assertTrue($action->checks());

        self::initDB('contacts');

        $_POST = ['_source' => '0', '_name' => 'test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('group-create', $result['action']);
        self::assertTrue(strpos($result['exec'], 'this.display_message("Group created successfully.","confirmation",0);') !== false);
        self::assertTrue(strpos($result['exec'], 'this.insert_contact_group({"source":"0","id":"2","name":"test"});') !== false);

        $db = rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT * FROM `contactgroups` WHERE `user_id` = 1 AND `name` = \'test\'');
        $result = $db->fetch_assoc($query);

        self::assertTrue(!empty($result));
    }
}
