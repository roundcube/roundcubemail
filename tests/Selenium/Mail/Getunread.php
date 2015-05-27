<?php

class Selenium_Mail_Getunread extends Selenium_Test
{
    protected $msgcount = 0;

    protected function setUp()
    {
        parent::setUp();

        bootstrap::init_imap();
        bootstrap::purge_mailbox('INBOX');

        // import email messages
        foreach (glob(TESTS_DIR . 'Selenium/data/mail/list_*.eml') as $f) {
            bootstrap::import_message($f, 'INBOX');
            $this->msgcount++;
        }
    }

    public function testGetunread()
    {
        $this->go('mail');

        $res = $this->ajaxResponse('getunread', "rcmail.http_request('getunread')");
        $this->assertEquals('getunread', $res['action']);

        $env = $this->get_env();
        $this->assertEquals($env['unread_counts']['INBOX'], $this->msgcount);

        $li = $this->byCssSelector('.folderlist li.inbox');
        $this->assertHasClass('unread', $li);

        $badge = $this->byCssSelector('.folderlist li.inbox span.unreadcount');
        $this->assertEquals(strval($this->msgcount), $this->getText($badge));
    }
}
