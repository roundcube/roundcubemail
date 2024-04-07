<?php

/**
 * Test class to test rcmail_action_contacts_mailto
 */
class Actions_Contacts_Mailto extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new rcmail_action_contacts_mailto();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'mailto');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        self::initDB('contacts');

        $db = rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT `contact_id` FROM `contacts` WHERE `email` = ?', 'johndoe@example.org');
        $result = $db->fetch_assoc($query);
        $cid = $result['contact_id'];

        $_POST = ['_cid' => $cid, '_source' => '0'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('mailto', $result['action']);
        self::assertTrue(strpos($result['exec'], 'this.open_compose_step({"_mailto":"') !== false);

        preg_match('/_mailto":"([0-9a-z]+)/', $result['exec'], $m);

        self::assertSame('John+Doe+%3Cjohndoe%40example.org%3E', $_SESSION['mailto'][$m[1]]);
    }
}
