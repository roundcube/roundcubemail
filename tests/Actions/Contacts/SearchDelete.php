<?php

/**
 * Test class to test rcmail_action_contacts_search_delete
 *
 * @package Tests
 */
class Actions_Contacts_Search_Delete extends ActionTestCase
{
    /**
     * Test error handling
     */
    function test_search_delete_errors()
    {
        $action = new rcmail_action_contacts_search_delete;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'search-delete');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $_POST = ['_sid' => 'unknown'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('search-delete', $result['action']);
        $this->assertSame('this.display_message("Could not delete saved search.","error",0);', trim($result['exec']));
    }

    /**
     * Test deleting a saved-search
     */
    function test_search_create_success()
    {
        $action = new rcmail_action_contacts_search_delete;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'search-delete');

        $this->assertTrue($action->checks());

        self::initDB('searches');

        $db     = rcmail::get_instance()->get_dbh();
        $query  = $db->query('SELECT * FROM `searches` WHERE `name` = \'test\'');
        $result = $db->fetch_assoc($query);
        $sid    = $result['search_id'];

        $_POST = ['_sid' => $sid];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('search-delete', $result['action']);
        $this->assertSame(0, $result['env']['pagecount']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Saved search deleted successfully.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.remove_search_item("' . $sid . '")') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_rowcount("No contacts found.");') !== false);

        $query  = $db->query('SELECT * FROM `searches` WHERE `name` = \'test\'');
        $result = $db->fetch_assoc($query);

        $this->assertTrue(empty($result));
    }
}
