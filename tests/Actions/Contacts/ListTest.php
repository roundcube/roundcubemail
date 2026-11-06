<?php

namespace Roundcube\Tests\Actions\Contacts;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_contacts_list
 */
class ListTest extends ActionTestCase
{
    /**
     * Test listing contacts
     */
    public function test_list()
    {
        $action = new \rcmail_action_contacts_list();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'contacts', 'list');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('list', $result['action']);
        $this->assertSame(1, $result['env']['pagecount']);

        $commands = $result['js_calls'];

        $this->assertCount(9, $commands);
        $this->assertSame(['set_group_prop', null], $commands[0]);
        $this->assertSame(['set_rowcount', 'Contacts 1 to 7 of 7'], $commands[1]);

        $db = \rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT `contact_id` FROM `contacts` WHERE `email` = \'g.bush@gov.com\'');
        $id = $db->fetch_assoc($query)['contact_id'];

        $this->assertSame(
            ['add_contact_row', $id, ['name' => 'George Bush'], 'person', ['name' => 'George Bush', 'email' => 'g.bush@gov.com', 'ID' => $id]],
            $commands[2]
        );
    }
}
