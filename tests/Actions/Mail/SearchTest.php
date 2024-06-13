<?php

namespace Roundcube\Tests\Actions\Mail;

use PHPUnit\Framework\Attributes\DataProvider;
use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_mail_search
 */
class SearchTest extends ActionTestCase
{
    /**
     * Test searching mail (empty result)
     */
    public function test_search_empty_result()
    {
        $action = new \rcmail_action_mail_search();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'mail', 'search');

        $this->assertTrue($action->checks());

        $_GET = [
            '_q' => 'test',
            '_mbox' => 'INBOX',
        ];

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('set_page')
            ->registerFunction('set_search_set')
            ->registerFunction('search', new \rcube_result_index())
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
    public function test_search_non_empty_result()
    {
        $action = new \rcmail_action_mail_search();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'mail', 'search');

        $_GET = [
            '_q' => 'test',
            '_mbox' => 'INBOX',
        ];

        $index = new \rcube_result_index('INBOX', 'SEARCH 10');

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('set_page')
            ->registerFunction('set_search_set')
            ->registerFunction('search')
            ->registerFunction('get_search_set', ['SEARCH HEADER SUBJECT test', $index, 'UTF-8', '', false])
            ->registerFunction('get_search_set', ['SEARCH HEADER SUBJECT test', $index, 'UTF-8', '', false])
            ->registerFunction('get_pagesize', 10)
            ->registerFunction('get_pagesize', 10)
            ->registerFunction('get_folder', 'INBOX')
            ->registerFunction('list_messages', [
                10 => \rcube_message_header::from_array([
                    'id' => 42,
                    'uid' => 10,
                    'subject' => 'test message',
                    'from' => 'test1@test.com',
                    'to' => 'Test <test2@test.com>',
                    'date' => 'Sun, 13 Mar 2022 17:08:18 +0100',
                    'size' => 889,
                    'content-type' => 'text/plain',
                ]),
            ])
            ->registerFunction('get_threading', false)
            ->registerFunction('get_threading', false)
            ->registerFunction('get_threading', false)
            ->registerFunction('count', 1)
            ->registerFunction('count', 1)
            ->registerFunction('count', 1)
            ->registerFunction('folder_data', [])
            ->registerFunction('get_quota', false);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('search', $result['action']);
        $this->assertSame(1, $result['env']['messagecount']);
        $this->assertSame(1, $result['env']['pagecount']);
        $this->assertSame(1, $result['env']['exists']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("1 messages found.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_rowcount("Messages 1 to 1 of 1","INBOX");') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_pagetitle("Roundcube Webmail :: Search result");') !== false);
    }

    /**
     * Test data for test_search_input()
     */
    public static function provide_search_input_cases(): iterable
    {
        $week = new \DateInterval('P1W');
        $weekDate = (new \DateTime('now', new \DateTimeZone('UTC')))->sub($week)->format('j-M-Y');

        return [
            [
                '',
                '',
            ],
            [
                'from:test',
                'HEADER FROM test',
            ],
            [
                'body:test',
                'BODY test',
            ],
            [
                'text:"test1 test2"',
                'TEXT "test1 test2"',
            ],
            [
                'test1 subject:test2',
                'HEADER SUBJECT test1 HEADER SUBJECT test2',
            ],
            [
                'cc:test1 bcc:test2',
                'HEADER CC test1 HEADER BCC test2',
            ],
            [
                'replyto:test1',
                'OR HEADER REPLY-TO test1 HEADER MAIL-REPLY-TO test1',
            ],
            [
                'followupto:test1',
                'OR HEADER FOLLOWUP-TO test1 HEADER MAIL-FOLLOWUP-TO test1',
            ],
            [
                'is:read IS:unread is:flaGGed is:Unflagged',
                'SEEN UNSEEN FLAGGED UNFLAGGED',
            ],
            [
                'is:unseen IS:seen is:Deleted is:Undeleted is:answered is:unanswered',
                'UNSEEN SEEN DELETED UNDELETED ANSWERED UNANSWERED',
            ],
            [
                'since:1w before:1w',
                "SINCE {$weekDate} BEFORE {$weekDate}",
            ],
            [
                'since:2022-12-31 before:2022-11-30',
                'SINCE 31-Dec-2022 BEFORE 30-Nov-2022',
            ],
            [
                'since:2022/1/1 before:2022/11/30',
                'SINCE 1-Jan-2022 BEFORE 30-Nov-2022',
            ],
            [
                'smaller:1KB larger:1M',
                'SMALLER 1024 LARGER 1048576',
            ],
            [
                '"from:test1"',
                'HEADER SUBJECT from:test1',
            ],
            [
                'text body from',
                'HEADER SUBJECT text HEADER SUBJECT body HEADER SUBJECT from',
            ],
            [
                '"text body from"',
                'HEADER SUBJECT "text body from"',
            ],
            [
                '"text body" from',
                'HEADER SUBJECT "text body" HEADER SUBJECT from',
            ],
            [
                ' to:"test1\" test2"    body:"test3  test4" "test5"',
                'HEADER TO "test1\" test2" BODY "test3  test4" HEADER SUBJECT test5',
            ],
            [
                ['test', 'from,to', 'UNSEEN', '-1W'],
                "UNSEEN BEFORE {$weekDate} OR HEADER FROM test HEADER TO test",
            ],
            // test OR-operator and AND-operator
            [
                '"OR"',
                'HEADER SUBJECT OR',
            ],
            [
                '"or" "OR"',
                'HEADER SUBJECT or HEADER SUBJECT OR',
            ],
            [
                'test1 "OR" test2',
                'HEADER SUBJECT test1 HEADER SUBJECT OR HEADER SUBJECT test2',
            ],
            [
                'test1 OR test2',
                'OR HEADER SUBJECT test1 HEADER SUBJECT test2',
            ],
            [
                'test1 OR test2 OR test3',
                'OR HEADER SUBJECT test1 OR HEADER SUBJECT test2 HEADER SUBJECT test3',
            ],
            [
                'from:test1 OR to:test2',
                'OR HEADER FROM test1 HEADER TO test2',
            ],
            [
                'replyto:test1 or from:test2',
                'OR OR HEADER REPLY-TO test1 HEADER MAIL-REPLY-TO test1 HEADER FROM test2',
            ],
            [
                'from:test1 body:test2 OR to:test3',
                'HEADER FROM test1 OR BODY test2 HEADER TO test3',
            ],
            [
                'or or or',
                '',
            ],
            [
                'or from:test1 body:test2 OR to:test3 or',
                'HEADER FROM test1 OR BODY test2 HEADER TO test3',
            ],
            [
                'from:test1 or or body:test2 OR to:test3',
                'OR HEADER FROM test1 OR BODY test2 HEADER TO test3',
            ],
            [
                'from:test1 and body:test2',
                'HEADER FROM test1 BODY test2',
            ],
            [
                'from:test1 and body:test2 or to:test3',
                'HEADER FROM test1 OR BODY test2 HEADER TO test3',
            ],
            // test negation
            [
                '-from:test1 and -body:test2',
                'NOT HEADER FROM test1 NOT BODY test2',
            ],
            [
                'from:-test1 and body:test2 or -to:test3',
                'HEADER FROM -test1 OR BODY test2 NOT HEADER TO test3',
            ],
            [
                '-since:1w -before:1w',
                "NOT SINCE {$weekDate} NOT BEFORE {$weekDate}",
            ],
            [
                '-smaller:1KB -larger:1M',
                'NOT SMALLER 1024 NOT LARGER 1048576',
            ],
            [
                '-"from:test1"',
                'NOT HEADER SUBJECT from:test1',
            ],
            [
                '"-from:test1"',
                'HEADER SUBJECT -from:test1',
            ],
        ];
    }

    /**
     * Test search_input() method
     *
     * @dataProvider provide_search_input_cases
     */
    #[DataProvider('provide_search_input_cases')]
    public function test_search_input($input, $output)
    {
        if (is_array($input)) {
            $result = call_user_func_array('rcmail_action_mail_search::search_input', $input);
        } else {
            $result = \rcmail_action_mail_search::search_input($input);
        }

        $this->assertSame($output, $result);
    }

    /**
     * Test data for test_search_interval_criteria()
     */
    public static function provide_search_interval_criteria_cases(): iterable
    {
        $week = new \DateInterval('P1W');
        $month = new \DateInterval('P1M');
        $year = new \DateInterval('P1Y');

        $utcTz = new \DateTimeZone('UTC');

        return [
            ['', null],
            ['1W', 'SINCE ' . (new \DateTime('now', $utcTz))->sub($week)->format('j-M-Y')],
            ['1M', 'SINCE ' . (new \DateTime('now', $utcTz))->sub($month)->format('j-M-Y')],
            ['1Y', 'SINCE ' . (new \DateTime('now', $utcTz))->sub($year)->format('j-M-Y')],
            ['-1W', 'BEFORE ' . (new \DateTime('now', $utcTz))->sub($week)->format('j-M-Y')],
            ['-1M', 'BEFORE ' . (new \DateTime('now', $utcTz))->sub($month)->format('j-M-Y')],
            ['-1Y', 'BEFORE ' . (new \DateTime('now', $utcTz))->sub($year)->format('j-M-Y')],
        ];
    }

    /**
     * Test search_interval_criteria() method
     *
     * @dataProvider provide_search_interval_criteria_cases
     */
    #[DataProvider('provide_search_interval_criteria_cases')]
    public function test_search_interval_criteria($input, $output)
    {
        $result = \rcmail_action_mail_search::search_interval_criteria($input);
        $this->assertSame($output, $result);
    }
}
