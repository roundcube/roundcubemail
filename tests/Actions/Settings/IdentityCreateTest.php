<?php

namespace Roundcube\Tests\Actions\Settings;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputHtmlMock;

/**
 * Test class to test rcmail_action_settings_identity_create
 */
class IdentityCreateTest extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new \rcmail_action_settings_identity_create();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'settings', 'add-identity');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('identityedit', $output->template);
        $this->assertSame('Add identity', $output->getProperty('pagetitle'));
        $this->assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        $this->assertTrue(strpos($result, "rcmail.gui_object('editform', 'form')") !== false);
    }
}
