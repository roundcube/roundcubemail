<?php

/**
 * Test class to test rcmail_action_mail_search
 *
 * @package Tests
 */
class Actions_Mail_Search extends ActionTestCase
{
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
        self::initStorage();
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
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'search');

        $result = rcmail_action_mail_search::search_input('', 'subject,from', 'base', 'INBOX');

        $this->assertSame([], $result[0]);
        $this->assertSame('', $result[1]);

        $result = rcmail_action_mail_search::search_input('test', 'subject,from', 'base', 'INBOX');

        $this->assertSame(['subject' => 'HEADER SUBJECT', 'from' => 'HEADER FROM'], $result[0]);
        $this->assertSame('test', $result[1]);

        $result = rcmail_action_mail_search::search_input('test', null, 'base', 'INBOX');

        $this->assertSame(['subject' => 'HEADER SUBJECT'], $result[0]);
        $this->assertSame('test', $result[1]);

        $result = rcmail_action_mail_search::search_input('body:test', 'subject,from', 'base', 'INBOX');

        $this->assertSame(['body' => 'BODY'], $result[0]);
        $this->assertSame('test', $result[1]);

        $result = rcmail_action_mail_search::search_input('test', 'from,invalid entry', 'base', 'INBOX');

        $this->assertSame(['from' => 'HEADER FROM'], $result[0]);
        $this->assertSame('test', $result[1]);
    }

    /**
     * Test search_interval_criteria() method
     */
    function test_search_interval_criteria()
    {
        $this->markTestIncomplete();
    }
}
