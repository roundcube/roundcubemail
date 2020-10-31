<?php

/**
 * Test class to test rcmail_action_contacts_index
 *
 * @package Tests
 */
class Actions_Contacts_Index extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_index;

        $this->assertInstanceOf('rcmail_action', $object);
    }

    /**
     * Test run() method in HTTP mode
     */
    function test_run_http()
    {
        $action = new rcmail_action_contacts_index;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', '');

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
    function test_run_ajax()
    {
        $action = new rcmail_action_contacts_index;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'list');

        $this->assertTrue($action->checks());

        // self::initDB('contacts');

        $action->run();

        $this->assertSame([], $output->headers);
        $this->assertNull($output->getOutput());
        $this->assertNull($output->get_env('address_sources'));
        $this->assertSame('', $output->getProperty('pagetitle'));
    }
}
