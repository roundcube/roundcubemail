<?php

/**
 * Test class to test rcmail_action_contacts_search
 */
class Actions_Contacts_Search extends ActionTestCase
{
    /**
     * Test search form request
     */
    public function test_run_search_form()
    {
        $action = new rcmail_action_contacts_search();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'search');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $_GET = ['_form' => 1];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame('contactsearch', $output->template);
        self::assertSame('', $output->getProperty('pagetitle')); // TODO: there should be a title
        self::assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
    }

    /**
     * Test search request
     */
    public function test_run_quick_search()
    {
        $action = new rcmail_action_contacts_search();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'search');

        self::assertTrue($action->checks());

        self::initDB('contacts');

        $_GET = ['_q' => 'George'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('search', $result['action']);
        self::assertSame(1, $result['env']['pagecount']);
        self::assertMatchesRegularExpression('/^[0-9a-z]{32}$/', $result['env']['search_request']);
        self::assertTrue(strpos($result['exec'], 'this.add_contact_row') !== false);
        self::assertTrue(strpos($result['exec'], 'this.set_rowcount("Contacts 1 to 1 of 1");') !== false);
        self::assertTrue(strpos($result['exec'], 'this.display_message("1 contacts found.","confirmation",0);') !== false);
        self::assertTrue(strpos($result['exec'], 'this.unselect_directory();') !== false);
        self::assertTrue(strpos($result['exec'], 'this.enable_command("search-create",true);') !== false);
        self::assertTrue(strpos($result['exec'], 'this.update_group_commands()') !== false);
    }

    /**
     * Test search request
     */
    public function test_run_search()
    {
        // TODO: Search using saved search, or using the form
        self::markTestIncomplete();
    }

    /**
     * Test contact_search_form() method
     */
    public function test_contact_search_form()
    {
        self::markTestIncomplete();
    }
}
