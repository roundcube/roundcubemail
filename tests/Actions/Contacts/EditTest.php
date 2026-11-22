<?php

namespace Roundcube\Tests\Actions\Contacts;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputHtmlMock;

use function Roundcube\Tests\getHTMLNodes;

/**
 * Test class to test rcmail_action_contacts_edit
 */
class EditTest extends ActionTestCase
{
    /**
     * Test run() method in edit mode
     */
    public function test_run_edit_mode()
    {
        $action = new \rcmail_action_contacts_edit();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', 'edit');

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

        $this->assertSame('contactedit', $output->template);
        $this->assertSame('Edit contact', $output->getProperty('pagetitle'));
        $this->assertSame($contact['contact_id'], $output->get_env('cid'));
        $this->assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        $this->assertTrue(str_contains($result, "rcmail.gui_object('contactphoto', 'contactpic');"));
    }

    /**
     * Test run() method in edit mode with a contact photo URL, external photos not allowed
     */
    public function test_run_edit_mode_photo_url()
    {
        $action = new \rcmail_action_contacts_edit();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', 'edit');

        self::initDB('contacts');
        $cid = PhotoTest::import_contact('url.photo@example.com', 'PHOTO;VALUE=uri:' . PhotoTest::PHOTO_URL);

        \rcmail::get_instance()->config->set('show_photo_urls', false);

        $_GET = ['_cid' => $cid, '_source' => '0'];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();
        $img = getHTMLNodes($result, "//div[@id='contactpic']/img");
        $field = getHTMLNodes($result, "//input[@id='ff_photo']");

        $this->assertCount(1, $img);
        $this->assertSame($output->get_env('photo_placeholder'), $img[0]->getAttribute('src'));
        $this->assertStringNotContainsString('photos.example.com', $result);
        // The stored photo is kept when the form is saved ('-del-' would delete it)
        $this->assertCount(1, $field);
        $this->assertSame('', $field[0]->getAttribute('value'));
    }

    /**
     * Test run() method in add mode
     */
    public function test_run_add_mode()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test contact_edithead() method
     */
    public function test_contact_edithead()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test contact_editform() method
     */
    public function test_contact_editform()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test get_form_tags() method
     */
    public function test_get_form_tags()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test upload_photo_form() method
     */
    public function test_upload_photo_form()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test photo_drop_area() method
     */
    public function test_photo_drop_area()
    {
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', 'edit');
        $result = \rcmail_action_contacts_edit::photo_drop_area([]);

        $this->assertNull($output->get_env('filedrop'));

        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', 'edit');
        $result = \rcmail_action_contacts_edit::photo_drop_area(['id' => 'test']);

        $scripts = $output->getProperty('scripts');
        $filedrop = $output->get_env('filedrop');

        $this->assertSame("rcmail.gui_object('filedrop', 'test');", trim($scripts['head']));
        $this->assertSame('upload-photo', $filedrop['action']);
        $this->assertSame('_photo', $filedrop['fieldname']);
        $this->assertSame(1, $filedrop['single']);
        $this->assertSame('^image/.+', $filedrop['filter']);
    }
}
