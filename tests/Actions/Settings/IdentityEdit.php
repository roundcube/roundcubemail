<?php

/**
 * Test class to test rcmail_action_settings_identity_edit
 *
 * @package Tests
 */
class Actions_Settings_IdentityEdit extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_settings_identity_edit;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'edit-identity');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        self::initDB('identities');

        $db       = rcmail::get_instance()->get_dbh();
        $query    = $db->query('SELECT * FROM `identities` WHERE `standard` = 1 LIMIT 1');
        $identity = $db->fetch_assoc($query);

        $_GET = ['_iid' => $identity['identity_id']];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('identityedit', $output->template);
        $this->assertSame('Edit identity', $output->getProperty('pagetitle'));
        $this->assertSame($identity['identity_id'], $output->get_env('iid'));
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
        $this->assertTrue(strpos($result, "rcmail.gui_object('editform', 'form')") !== false);
        $this->assertTrue(strpos($result, "test@example.com") !== false);

        // TODO: Test error handling
    }

    /**
     * Test identity_form() method
     */
    function test_identity_form()
    {
        $action = new rcmail_action_settings_identity_edit;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'edit-identity');

        self::initDB('identities');

        $result = $action->identity_form([]);

        $this->assertTrue(strpos($result, '<form id="identityImageUpload"') !== false);
        $this->assertTrue(strpos($result, '<legend>Settings</legend>') !== false);
    }
}
