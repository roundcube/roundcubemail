<?php

namespace Roundcube\Tests\Actions\Contacts;

use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_contacts_index
 */
class IndexTest extends ActionTestCase
{
    /**
     * Test run() method in HTTP mode
     */
    public function test_run_http()
    {
        $action = new \rcmail_action_contacts_index();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', '');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        // self::initDB('contacts');

        $action->run();

        $this->assertSame([], $output->headers);
        $this->assertNull($output->getOutput());

        $sources = $output->get_env('address_sources');

        $this->assertCount(3, $sources);
        $this->assertSame('Personal Addresses', $sources[0]['name']);
        $this->assertSame('Collected Recipients', $sources[1]['name']);
        $this->assertSame('Trusted Senders', $sources[2]['name']);
        $this->assertSame('Contacts', $output->getProperty('pagetitle'));
    }

    /**
     * Test run() method in AJAX mode
     */
    public function test_run_ajax()
    {
        $action = new \rcmail_action_contacts_index();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'contacts', 'list');

        $this->assertTrue($action->checks());

        // self::initDB('contacts');

        $action->run();

        $this->assertSame([], $output->headers);
        $this->assertNull($output->getOutput());
        $this->assertNull($output->get_env('address_sources'));
        $this->assertSame('', $output->getProperty('pagetitle'));
    }

    /**
     * Test contact_source() method
     */
    public function test_contact_source()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test set_sourcename() method
     */
    public function test_set_sourcename()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test directory_list() method
     */
    public function test_directory_list()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test savedsearch_list() method
     */
    public function test_savedsearch_list()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test contact_groups() method
     */
    public function test_contact_groups()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test contacts_list() method
     */
    public function test_contacts_list()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test contacts_list_title() method
     */
    public function test_contacts_list_title()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rowcount_display() method
     */
    public function test_rowcount_display()
    {
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', '');
        $result = \rcmail_action_contacts_index::rowcount_display([]);

        $this->assertSame('<span id="rcmcountdisplay">Loading...</span>', $result);
    }

    /**
     * Test get_rowcount_text() method
     */
    public function test_get_rowcount_text()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test get_type_label() method
     */
    public function test_get_type_label()
    {
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', '');
        $result = \rcmail_action_contacts_index::get_type_label('home');

        $this->assertSame('Home', $result);
    }

    /**
     * Test contact_form() method
     */
    public function test_contact_form()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test contact_photo() method
     */
    public function test_contact_photo()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test search_update() method
     */
    public function test_search_update()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test get_cids() method
     */
    public function test_get_cids()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test source_selector() method
     */
    public function test_source_selector()
    {
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', '');

        $result = \rcmail_action_contacts_index::source_selector([]);
        $expected = '<span>Personal Addresses<input type="hidden" name="_source" value="0"></span>';

        $this->assertSame($expected, $result);

        // TODO: Test more
    }
}
