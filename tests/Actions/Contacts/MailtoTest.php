<?php

namespace Roundcube\Tests\Actions\Contacts;

use rcmail as rcmail;
use rcmail_action as rcmail_action;
use rcmail_action_contacts_mailto as rcmail_action_contacts_mailto;
use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_contacts_mailto
 */
class MailtoTest extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new \rcmail_action_contacts_mailto();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'contacts', 'mailto');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $db = \rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT `contact_id` FROM `contacts` WHERE `email` = ?', 'johndoe@example.org');
        $result = $db->fetch_assoc($query);
        $cid = $result['contact_id'];

        $_POST = ['_cid' => $cid, '_source' => '0'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('mailto', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.open_compose_step({"_mailto":"') !== false);

        preg_match('/_mailto":"([0-9a-z]+)/', $result['exec'], $m);

        $this->assertSame('John+Doe+%3Cjohndoe%40example.org%3E', $_SESSION['mailto'][$m[1]]);
    }
}
