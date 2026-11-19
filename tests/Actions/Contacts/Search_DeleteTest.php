<?php

namespace Roundcube\Tests\Actions\Contacts;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_contacts_search_delete
 */
class Search_DeleteTest extends ActionTestCase
{
    /**
     * Test error handling
     */
    public function test_search_delete_errors()
    {
        $action = new \rcmail_action_contacts_search_delete();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'contacts', 'search-delete');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        $_POST = ['_sid' => 'unknown'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('search-delete', $result['action']);
        $this->assertSame([['display_message', 'Could not delete saved search.', 'error', 0]], $result['js_calls']);
    }

    /**
     * Test deleting a saved-search
     */
    public function test_search_create_success()
    {
        $action = new \rcmail_action_contacts_search_delete();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'contacts', 'search-delete');

        $this->assertTrue($action->checks());

        self::initDB('searches');

        $db = \rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT * FROM `searches` WHERE `name` = \'test\'');
        $result = $db->fetch_assoc($query);
        $sid = $result['search_id'];

        $_POST = ['_sid' => $sid];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('search-delete', $result['action']);
        $this->assertSame(0, $result['env']['pagecount']);
        $this->assertContains(['display_message', 'Saved search deleted successfully.', 'confirmation', 0], $result['js_calls']);
        $this->assertContains(['remove_search_item', $sid], $result['js_calls']);
        $this->assertContains(['set_rowcount', 'No contacts found.'], $result['js_calls']);

        $query = $db->query('SELECT * FROM `searches` WHERE `name` = \'test\'');
        $result = $db->fetch_assoc($query);

        $this->assertTrue(empty($result));
    }
}
