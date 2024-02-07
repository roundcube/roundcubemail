<?php

/**
 * Test class to test rcmail_action_contacts_search_create
 */
class Actions_Contacts_Search_Create extends ActionTestCase
{
    /**
     * Test error handling
     */
    public function test_search_create_errors()
    {
        $action = new rcmail_action_contacts_search_create();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'search-create');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        // Unset group name
        $_POST = ['_name' => ''];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('search-create', $result['action']);
        self::assertSame('this.display_message("Could not create saved search.","error",0);', trim($result['exec']));
    }

    /**
     * Test creating a saved-search
     */
    public function test_search_create_success()
    {
        $action = new rcmail_action_contacts_search_create();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'search-create');

        self::assertTrue($action->checks());

        self::initDB('searches');

        $_POST = ['_search' => 'fakeid', '_name' => 'test2'];
        $_SESSION['contact_search_params'] = [
            'id' => 'fakeid',
            'data' => ['*', 'bush'],
        ];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('search-create', $result['action']);
        self::assertTrue(strpos($result['exec'], 'this.display_message("Saved search created successfully.","confirmation",0);') !== false);
        self::assertTrue(strpos($result['exec'], 'this.insert_saved_search("test2",') !== false);

        $db = rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT * FROM `searches` WHERE `name` = \'test2\'');
        $result = $db->fetch_assoc($query);

        self::assertTrue(!empty($result));
    }
}
