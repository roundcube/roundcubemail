<?php

/**
 * Test class to test rcmail_action_mail_search
 *
 * @package Tests
 */
class Actions_Mail_Search extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_search;

        $this->assertInstanceOf('rcmail_action', $object);
    }

    /**
     * Test searching mail (empty result)
     */
    function test_search_empty_result()
    {
        $action = new rcmail_action_mail_search;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'search');

        $this->assertTrue($action->checks());

        $_GET = [
            '_q'    => 'test',
            '_mbox' => 'INBOX',
        ];

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
            ->registerFunction('set_page')
            ->registerFunction('set_search_set')
            ->registerFunction('search', new rcube_result_index())
            ->registerFunction('get_search_set', [])
            ->registerFunction('get_search_set', [])
            ->registerFunction('get_pagesize', 10)
            ->registerFunction('get_pagesize', 10)
            ->registerFunction('get_folder', 'INBOX')
            ->registerFunction('list_messages', [])
            ->registerFunction('get_error_code', null)
            ->registerFunction('count', 0)
            ->registerFunction('get_quota', false);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('search', $result['action']);
        $this->assertSame(0, $result['env']['messagecount']);
        $this->assertSame(0, $result['env']['pagecount']);
        $this->assertSame(0, $result['env']['exists']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Search returned no matches.","notice",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_rowcount("Mailbox is empty","INBOX");') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_pagetitle("Roundcube Webmail :: Search result");') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_quota') !== false);
    }

    /**
     * Test searching mail (non-empty result)
     */
    function test_search_non_empty_result()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test search_input() method
     */
    function test_search_input()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test search_interval_criteria() method
     */
    function test_search_interval_criteria()
    {
        $this->markTestIncomplete();
    }
}
