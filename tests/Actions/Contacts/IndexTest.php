<?php

/**
 * Test class to test rcmail_action_contacts_index
 */
class Actions_Contacts_Index extends ActionTestCase
{
    /**
     * Test run() method in HTTP mode
     */
    public function test_run_http()
    {
        $action = new rcmail_action_contacts_index();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', '');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        // self::initDB('contacts');

        $action->run();

        self::assertSame([], $output->headers);
        self::assertNull($output->getOutput());

        $sources = $output->get_env('address_sources');

        self::assertCount(3, $sources);
        self::assertSame('Personal Addresses', $sources[0]['name']);
        self::assertSame('Collected Recipients', $sources[1]['name']);
        self::assertSame('Trusted Senders', $sources[2]['name']);
        self::assertSame('Contacts', $output->getProperty('pagetitle'));
    }

    /**
     * Test run() method in AJAX mode
     */
    public function test_run_ajax()
    {
        $action = new rcmail_action_contacts_index();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'list');

        self::assertTrue($action->checks());

        // self::initDB('contacts');

        $action->run();

        self::assertSame([], $output->headers);
        self::assertNull($output->getOutput());
        self::assertNull($output->get_env('address_sources'));
        self::assertSame('', $output->getProperty('pagetitle'));
    }

    /**
     * Test contact_source() method
     */
    public function test_contact_source()
    {
        self::markTestIncomplete();
    }

    /**
     * Test set_sourcename() method
     */
    public function test_set_sourcename()
    {
        self::markTestIncomplete();
    }

    /**
     * Test directory_list() method
     */
    public function test_directory_list()
    {
        self::markTestIncomplete();
    }

    /**
     * Test savedsearch_list() method
     */
    public function test_savedsearch_list()
    {
        self::markTestIncomplete();
    }

    /**
     * Test contact_groups() method
     */
    public function test_contact_groups()
    {
        self::markTestIncomplete();
    }

    /**
     * Test contacts_list() method
     */
    public function test_contacts_list()
    {
        self::markTestIncomplete();
    }

    /**
     * Test contacts_list_title() method
     */
    public function test_contacts_list_title()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rowcount_display() method
     */
    public function test_rowcount_display()
    {
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', '');
        $result = rcmail_action_contacts_index::rowcount_display([]);

        self::assertSame('<span id="rcmcountdisplay">Loading...</span>', $result);
    }

    /**
     * Test get_rowcount_text() method
     */
    public function test_get_rowcount_text()
    {
        self::markTestIncomplete();
    }

    /**
     * Test get_type_label() method
     */
    public function test_get_type_label()
    {
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', '');
        $result = rcmail_action_contacts_index::get_type_label('home');

        self::assertSame('Home', $result);
    }

    /**
     * Test contact_form() method
     */
    public function test_contact_form()
    {
        self::markTestIncomplete();
    }

    /**
     * Test contact_photo() method
     */
    public function test_contact_photo()
    {
        self::markTestIncomplete();
    }

    /**
     * Test search_update() method
     */
    public function test_search_update()
    {
        self::markTestIncomplete();
    }

    /**
     * Test get_cids() method
     */
    public function test_get_cids()
    {
        self::markTestIncomplete();
    }

    /**
     * Test source_selector() method
     */
    public function test_source_selector()
    {
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', '');

        $result = rcmail_action_contacts_index::source_selector([]);
        $expected = '<span>Personal Addresses<input type="hidden" name="_source" value="0"></span>';

        self::assertSame($expected, $result);

        // TODO: Test more
    }
}
