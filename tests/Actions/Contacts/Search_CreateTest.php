<?php

namespace Roundcube\Tests\Actions\Contacts;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_contacts_search_create
 */
class Search_CreateTest extends ActionTestCase
{
    /**
     * Test error handling
     */
    public function test_search_create_errors()
    {
        $action = new \rcmail_action_contacts_search_create();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'contacts', 'search-create');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        // Unset group name
        $_POST = ['_name' => ''];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('search-create', $result['action']);
        $this->assertSame([['display_message', 'Could not create saved search.', 'error', 0]], $result['js_calls']);
    }

    /**
     * Test creating a saved-search
     */
    public function test_search_create_success()
    {
        $action = new \rcmail_action_contacts_search_create();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'contacts', 'search-create');

        $this->assertTrue($action->checks());

        self::initDB('searches');

        $_POST = ['_search' => 'fakeid', '_name' => 'test2'];
        $_SESSION['contact_search_params'] = [
            'id' => 'fakeid',
            'data' => ['*', 'bush'],
            'scope' => null,
        ];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('search-create', $result['action']);
        $this->assertContains(['display_message', 'Saved search created successfully.', 'confirmation', 0], $result['js_calls']);

        $db = \rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT * FROM `searches` WHERE `name` = \'test2\'');
        $search = $db->fetch_assoc($query);

        $this->assertTrue(!empty($search));
        $this->assertContains(['insert_saved_search', 'test2', $search['search_id']], $result['js_calls']);
    }
}
