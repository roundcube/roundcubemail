<?php

/**
 * Test class to test rcmail_action_contacts_show
 *
 * @package Tests
 */
class Actions_Contacts_Show extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_contacts_show;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'show');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $db      = rcmail::get_instance()->get_dbh();
        $query   = $db->query('SELECT `contact_id` FROM `contacts` WHERE `user_id` = 1 LIMIT 1');
        $contact = $db->fetch_assoc($query);

        $_GET = [
            '_cid'    => $contact['contact_id'],
            '_source' => '0'
        ];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('contact', $output->template);
        $this->assertSame('', $output->getProperty('pagetitle'));
        $this->assertSame($contact['contact_id'], $output->get_env('cid'));
        $this->assertSame(false, $output->get_env('readonly'));
        $this->assertSame('Personal Addresses', $output->get_env('sourcename'));
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
    }

    /**
     * Test contact_head() method
     */
    function test_contact_head()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test contact_details() method
     */
    function test_contact_details()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test render_email_value() method
     */
    function test_render_email_value()
    {
        $input = 'test@<email.tld';
        $expected = '<a href="mailto:test@&lt;email.tld" onclick="return rcmail.command(\'compose\',\'test@&lt;email.tld\',this)"'
            . ' title="Compose mail to" class="email">test@&lt;email.tld</a>';
        $this->assertSame($expected, rcmail_action_contacts_show::render_email_value($input));
    }

    /**
     * Test render_phone_value() method
     */
    function test_render_phone_value()
    {
        $input = '+48-123<456';
        $expected = '<a href="tel:+48-123456" class="phone">+48-123&lt;456</a>';
        $this->assertSame($expected, rcmail_action_contacts_show::render_phone_value($input));
    }

    /**
     * Test render_url_value() method
     */
    function test_render_url_value()
    {
        $input = 'http://test/<123';
        $expected = '<a href="http://test/&lt;123" target="_blank" class="url">http://test/&lt;123</a>';
        $this->assertSame($expected, rcmail_action_contacts_show::render_url_value($input));
    }

    /**
     * Test contact_record_groups() method
     */
    function test_contact_record_groups()
    {
        $this->markTestIncomplete();
    }
}
