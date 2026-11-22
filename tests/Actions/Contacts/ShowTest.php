<?php

namespace Roundcube\Tests\Actions\Contacts;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputHtmlMock;

use function Roundcube\Tests\getHTMLNodes;

/**
 * Test class to test rcmail_action_contacts_show
 */
class ShowTest extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new \rcmail_action_contacts_show();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', 'show');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $db = \rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT `contact_id` FROM `contacts` WHERE `user_id` = 1 LIMIT 1');
        $contact = $db->fetch_assoc($query);

        $_GET = [
            '_cid' => $contact['contact_id'],
            '_source' => '0',
        ];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('contact', $output->template);
        $this->assertSame('', $output->getProperty('pagetitle'));
        $this->assertSame($contact['contact_id'], $output->get_env('cid'));
        $this->assertFalse($output->get_env('readonly'));
        $this->assertSame('Personal Addresses', $output->get_env('sourcename'));
        $this->assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
    }

    /**
     * Test run() method with a contact photo URL
     */
    public function test_run_photo_url()
    {
        $action = new \rcmail_action_contacts_show();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', 'show');
        $rcmail = \rcmail::get_instance();

        self::initDB('contacts');
        $cid = PhotoTest::import_contact('url.photo@example.com', 'PHOTO;VALUE=uri:' . PhotoTest::PHOTO_URL);

        $_GET = ['_cid' => $cid, '_source' => '0'];

        // External photos not allowed: the placeholder is shown, like for a contact without a photo
        $rcmail->config->set('show_photo_urls', false);
        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();
        $img = getHTMLNodes($result, "//div[@id='contactpic']/img");

        $this->assertCount(1, $img);
        $this->assertSame($output->get_env('photo_placeholder'), $img[0]->getAttribute('src'));
        $this->assertStringNotContainsString('photos.example.com', $result);

        // External photos allowed: the photo is loaded from the URL
        $rcmail->config->set('show_photo_urls', true);
        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $img = getHTMLNodes($output->getOutput(), "//div[@id='contactpic']/img");

        $this->assertCount(1, $img);
        $this->assertSame(PhotoTest::PHOTO_URL, $img[0]->getAttribute('src'));

        $rcmail->config->set('show_photo_urls', false);
    }

    /**
     * Test contact_head() method
     */
    public function test_contact_head()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test contact_details() method
     */
    public function test_contact_details()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test render_email_value() method
     */
    public function test_render_email_value()
    {
        $input = 'test@<email.tld';
        $expected = '<a href="mailto:test@&lt;email.tld" onclick="return rcmail.command(\'compose\',\'test@&lt;email.tld\',this)"'
            . ' title="Compose mail to" class="email">test@&lt;email.tld</a>';
        $this->assertSame($expected, \rcmail_action_contacts_show::render_email_value($input));
    }

    /**
     * Test render_phone_value() method
     */
    public function test_render_phone_value()
    {
        $input = '+48-123<456';
        $expected = '<a href="tel:+48-123456" class="phone">+48-123&lt;456</a>';
        $this->assertSame($expected, \rcmail_action_contacts_show::render_phone_value($input));
    }

    /**
     * Test render_url_value() method
     */
    public function test_render_url_value()
    {
        $input = 'http://test/<123';
        $expected = '<a href="http://test/&lt;123" target="_blank" rel="noopener" class="url">http://test/&lt;123</a>';
        $this->assertSame($expected, \rcmail_action_contacts_show::render_url_value($input));
    }

    /**
     * Test contact_record_groups() method
     */
    public function test_contact_record_groups()
    {
        $this->markTestIncomplete();
    }
}
