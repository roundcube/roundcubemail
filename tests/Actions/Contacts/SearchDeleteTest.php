<?php

/**
 * Test class to test rcmail_action_contacts_search_delete
 */
class Actions_Contacts_Search_Delete extends ActionTestCase
{
    /**
     * Test error handling
     */
    public function test_search_delete_errors()
    {
        $action = new rcmail_action_contacts_search_delete();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'search-delete');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $_POST = ['_sid' => 'unknown'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('search-delete', $result['action']);
        self::assertSame('this.display_message("Could not delete saved search.","error",0);', trim($result['exec']));
    }

    /**
     * Test deleting a saved-search
     */
    public function test_search_create_success()
    {
        $action = new rcmail_action_contacts_search_delete();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'search-delete');

        self::assertTrue($action->checks());

        self::initDB('searches');

        $db = rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT * FROM `searches` WHERE `name` = \'test\'');
        $result = $db->fetch_assoc($query);
        $sid = $result['search_id'];

        $_POST = ['_sid' => $sid];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('search-delete', $result['action']);
        self::assertSame(0, $result['env']['pagecount']);
        self::assertTrue(strpos($result['exec'], 'this.display_message("Saved search deleted successfully.","confirmation",0);') !== false);
        self::assertTrue(strpos($result['exec'], 'this.remove_search_item("' . $sid . '")') !== false);
        self::assertTrue(strpos($result['exec'], 'this.set_rowcount("No contacts found.");') !== false);

        $query = $db->query('SELECT * FROM `searches` WHERE `name` = \'test\'');
        $result = $db->fetch_assoc($query);

        self::assertTrue(empty($result));
    }
}
