<?php

/**
 * Test class to test rcmail_action_settings_identity_delete
 *
 * @package Tests
 */
class Actions_Settings_IdentityDelete extends ActionTestCase
{
    /**
     * Test deleting an identity
     */
    function test_delete_identity()
    {
        $action = new rcmail_action_settings_identity_delete;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'delete-identity');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        self::initDB('identities');

        $db     = rcmail::get_instance()->get_dbh();
        $query  = $db->query('SELECT * FROM `identities` WHERE `email` = ?', 'test@example.org');
        $result = $db->fetch_assoc($query);
        $iid    = $result['identity_id'];

        $_POST = ['_iid' => $iid];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('delete-identity', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Successfully deleted.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.remove_identity("' . $iid . '")') !== false);

        $query  = $db->query('SELECT * FROM `identities` WHERE `identity_id` = ?', $iid);
        $result = $db->fetch_assoc($query);

        $this->assertTrue(!empty($result['del']));

        // Test error handling
        $action = new rcmail_action_settings_identity_delete;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'delete-identity');

        $_POST = ['_iid' => 'unknown'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('delete-identity', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("An error occurred while saving.","error",0);') !== false);
    }
}
