<?php

/**
 * Test class to test rcmail_action_contacts_edit
 */
class Actions_Contacts_Edit extends ActionTestCase
{
    /**
     * Test run() method in edit mode
     */
    public function test_run_edit_mode()
    {
        $action = new rcmail_action_contacts_edit();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'edit');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        self::initDB('contacts');

        $db = rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT `contact_id` FROM `contacts` WHERE `user_id` = 1 LIMIT 1');
        $contact = $db->fetch_assoc($query);

        $_GET = [
            '_cid' => $contact['contact_id'],
            '_source' => '0',
        ];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame('contactedit', $output->template);
        self::assertSame('Edit contact', $output->getProperty('pagetitle'));
        self::assertSame($contact['contact_id'], $output->get_env('cid'));
        self::assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        self::assertTrue(strpos($result, "rcmail.gui_object('contactphoto', 'contactpic');") !== false);
    }

    /**
     * Test run() method in add mode
     */
    public function test_run_add_mode()
    {
        self::markTestIncomplete();
    }

    /**
     * Test contact_edithead() method
     */
    public function test_contact_edithead()
    {
        self::markTestIncomplete();
    }

    /**
     * Test contact_editform() method
     */
    public function test_contact_editform()
    {
        self::markTestIncomplete();
    }

    /**
     * Test get_form_tags() method
     */
    public function test_get_form_tags()
    {
        self::markTestIncomplete();
    }

    /**
     * Test upload_photo_form() method
     */
    public function test_upload_photo_form()
    {
        self::markTestIncomplete();
    }

    /**
     * Test photo_drop_area() method
     */
    public function test_photo_drop_area()
    {
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'edit');
        $result = rcmail_action_contacts_edit::photo_drop_area([]);

        self::assertNull($output->get_env('filedrop'));

        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'edit');
        $result = rcmail_action_contacts_edit::photo_drop_area(['id' => 'test']);

        $scripts = $output->getProperty('scripts');
        $filedrop = $output->get_env('filedrop');

        self::assertSame("rcmail.gui_object('filedrop', 'test');", trim($scripts['head']));
        self::assertSame('upload-photo', $filedrop['action']);
        self::assertSame('_photo', $filedrop['fieldname']);
        self::assertSame(1, $filedrop['single']);
        self::assertSame('^image/.+', $filedrop['filter']);
    }
}
