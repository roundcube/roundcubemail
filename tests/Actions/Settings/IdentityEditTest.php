<?php

namespace Roundcube\Tests\Actions\Settings;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputHtmlMock;

/**
 * Test class to test rcmail_action_settings_identity_edit
 */
class IdentityEditTest extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new \rcmail_action_settings_identity_edit();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'settings', 'edit-identity');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        self::initDB('identities');

        $db = \rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT * FROM `identities` WHERE `standard` = 1 LIMIT 1');
        $identity = $db->fetch_assoc($query);

        $_GET = ['_iid' => $identity['identity_id']];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('identityedit', $output->template);
        $this->assertSame('Edit identity', $output->getProperty('pagetitle'));
        $this->assertSame($identity['identity_id'], $output->get_env('iid'));
        $this->assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        $this->assertTrue(str_contains($result, "rcmail.gui_object('editform', 'form')"));
        $this->assertTrue(str_contains($result, 'test@example.com'));

        // TODO: Test error handling
    }

    /**
     * Test identity_form() method
     */
    public function test_identity_form()
    {
        $action = new \rcmail_action_settings_identity_edit();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'settings', 'edit-identity');

        self::initDB('identities');

        $result = $action->identity_form([]);

        $this->assertTrue(str_contains($result, '<form id="identityImageUpload"'));
        $this->assertTrue(str_contains($result, '<legend>Settings</legend>'));
    }
}
