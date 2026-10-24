<?php

namespace Roundcube\Tests\Actions\Contacts;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputHtmlMock;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_contacts_search
 */
class SearchTest extends ActionTestCase
{
    /**
     * Test search form request
     */
    public function test_run_search_form()
    {
        $action = new \rcmail_action_contacts_search();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', 'search');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        $_GET = ['_form' => 1];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('contactsearch', $output->template);
        $this->assertSame('', $output->getProperty('pagetitle')); // TODO: there should be a title
        $this->assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
    }

    /**
     * Test search request
     */
    public function test_run_quick_search()
    {
        $action = new \rcmail_action_contacts_search();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'contacts', 'search');

        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $_GET = ['_q' => 'George'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('search', $result['action']);
        $this->assertSame(1, $result['env']['pagecount']);
        $this->assertMatchesRegularExpression('/^[0-9a-z]{32}$/', $result['env']['search_request']);
        $this->assertContains(['add_contact_row', '4-0', ['name' => 'George Bush'], 'person', ['name' => 'George Bush', 'email' => 'g.bush@gov.com', 'ID' => '4-0']], $result['js_calls']);
        $this->assertContains(['set_rowcount', 'Contacts 1 to 1 of 1'], $result['js_calls']);
        $this->assertContains(['display_message', '1 contacts found.', 'confirmation', 0], $result['js_calls']);
        $this->assertContains(['unselect_directory'], $result['js_calls']);
        $this->assertContains(['enable_command', 'search-create', true], $result['js_calls']);
        $this->assertContains(['update_group_commands'], $result['js_calls']);
        $this->assertNotContains(['list_contacts_clear'], $result['js_calls']);
    }

    /**
     * Test search scope
     */
    public function test_run_quick_search_scope()
    {
        $action = new \rcmail_action_contacts_search();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'contacts', 'search');

        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $_GET = ['_q' => 'target'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains(['display_message', '2 contacts found.', 'confirmation', 0], $result['js_calls']);
        $this->assertContains(['add_contact_row', '7-0', ['name' => 'Target Personal'], 'person', ['name' => 'Target Personal', 'email' => 'target@personal.com', 'ID' => '7-0']], $result['js_calls']);
        $this->assertContains(['add_contact_row', '2-1', ['name' => 'target collected'], 'person', ['name' => 'target collected', 'email' => 'target@collected.com', 'ID' => '2-1']], $result['js_calls']);

        $_GET = ['_q' => 'target', '_scope' => '0'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains(['display_message', '1 contacts found.', 'confirmation', 0], $result['js_calls']);
        $this->assertContains(['add_contact_row', '7-0', ['name' => 'Target Personal'], 'person', ['name' => 'Target Personal', 'email' => 'target@personal.com', 'ID' => '7-0']], $result['js_calls']);

        $_GET = ['_q' => 'target', '_scope' => \rcube_addressbook::TYPE_RECIPIENT];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains(['display_message', '1 contacts found.', 'confirmation', 0], $result['js_calls']);
        $this->assertContains(['add_contact_row', '2-1', ['name' => 'target collected'], 'person', ['name' => 'target collected', 'email' => 'target@collected.com', 'ID' => '2-1']], $result['js_calls']);
    }

    /**
     * Test search request
     */
    public function test_run_search()
    {
        $action = new \rcmail_action_contacts_search();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'contacts', 'search');

        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $_POST = ['_adv' => '1', '_search_organization' => 'acme'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('search', $result['action']);
        $this->assertSame(1, $result['env']['pagecount']);
        $this->assertMatchesRegularExpression('/^[0-9a-z]{32}$/', $result['env']['search_request']);
        $this->assertContains(['add_contact_row', '1-0', ['name' => 'John Doe'], 'person', ['name' => 'John Doe', 'email' => 'johndoe@example.org', 'ID' => '1-0']], $result['js_calls']);
        $this->assertContains(['set_rowcount', 'Contacts 1 to 1 of 1'], $result['js_calls']);
        $this->assertContains(['display_message', '1 contacts found.', 'confirmation', 0], $result['js_calls']);
        $this->assertContains(['unselect_directory'], $result['js_calls']);
        $this->assertContains(['enable_command', 'search-create', true], $result['js_calls']);
        $this->assertContains(['update_group_commands'], $result['js_calls']);
        $this->assertContains(['list_contacts_clear'], $result['js_calls']);
    }

    public function test_run_search_scope()
    {
        $action = new \rcmail_action_contacts_search();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'contacts', 'search');

        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $_POST = ['_adv' => '1', '_search_email' => 'target'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains(['display_message', '2 contacts found.', 'confirmation', 0], $result['js_calls']);
        $this->assertContains(['add_contact_row', '7-0', ['name' => 'Target Personal'], 'person', ['name' => 'Target Personal', 'email' => 'target@personal.com', 'ID' => '7-0']], $result['js_calls']);
        $this->assertContains(['add_contact_row', '2-1', ['name' => 'target collected'], 'person', ['name' => 'target collected', 'email' => 'target@collected.com', 'ID' => '2-1']], $result['js_calls']);

        $_POST = ['_adv' => '1', '_search_email' => 'target', '_scope' => '0'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains(['display_message', '1 contacts found.', 'confirmation', 0], $result['js_calls']);
        $this->assertContains(['add_contact_row', '7-0', ['name' => 'Target Personal'], 'person', ['name' => 'Target Personal', 'email' => 'target@personal.com', 'ID' => '7-0']], $result['js_calls']);

        $_POST = ['_adv' => '1', '_search_email' => 'target', '_scope' => (string) \rcube_addressbook::TYPE_RECIPIENT];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains(['display_message', '1 contacts found.', 'confirmation', 0], $result['js_calls']);
        $this->assertContains(['add_contact_row', '2-1', ['name' => 'target collected'], 'person', ['name' => 'target collected', 'email' => 'target@collected.com', 'ID' => '2-1']], $result['js_calls']);
    }

    /**
     * Test saved search
     */
    public function test_run_saved_search()
    {
        $action = new \rcmail_action_contacts_search();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'contacts', 'search');

        $this->assertTrue($action->checks());

        self::initDB('searches');

        $db = \rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT * FROM `searches` WHERE `name` = \'test\'');
        $result = $db->fetch_assoc($query);
        $sid = $result['search_id'];

        self::initDB('contacts');

        $_GET = ['_sid' => $sid];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('search', $result['action']);
        $this->assertSame(1, $result['env']['pagecount']);
        $this->assertMatchesRegularExpression('/^[0-9a-z]{32}$/', $result['env']['search_request']);
        $this->assertContains(['add_contact_row', '4-0', ['name' => 'George Bush'], 'person', ['name' => 'George Bush', 'email' => 'g.bush@gov.com', 'ID' => '4-0']], $result['js_calls']);
        $this->assertContains(['set_rowcount', 'Contacts 1 to 1 of 1'], $result['js_calls']);
        $this->assertContains(['display_message', '1 contacts found.', 'confirmation', 0], $result['js_calls']);
        $this->assertNotContains(['unselect_directory'], $result['js_calls']);
        $this->assertNotContains(['enable_command', 'search-create', true], $result['js_calls']);
        $this->assertContains(['update_group_commands'], $result['js_calls']);
    }
}
