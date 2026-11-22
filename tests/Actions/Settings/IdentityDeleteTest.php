<?php

namespace Roundcube\Tests\Actions\Settings;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_settings_identity_delete
 */
class IdentityDeleteTest extends ActionTestCase
{
    /**
     * Test deleting an identity
     */
    public function test_delete_identity()
    {
        $action = new \rcmail_action_settings_identity_delete();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'settings', 'delete-identity');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        self::initDB('identities');

        $db = \rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT * FROM `identities` WHERE `email` = ?', 'test@example.org');
        $result = $db->fetch_assoc($query);
        $iid = $result['identity_id'];

        $_POST = ['_iid' => $iid];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('delete-identity', $result['action']);
        $this->assertTrue(str_contains($result['exec'], 'this.display_message("Successfully deleted.","confirmation",0);'));
        $this->assertTrue(str_contains($result['exec'], 'this.remove_identity("' . $iid . '")'));

        $query = $db->query('SELECT * FROM `identities` WHERE `identity_id` = ?', $iid);
        $result = $db->fetch_assoc($query);

        $this->assertTrue(!empty($result['del']));

        // Test error handling
        $action = new \rcmail_action_settings_identity_delete();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'settings', 'delete-identity');

        $_POST = ['_iid' => 'unknown'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('delete-identity', $result['action']);
        $this->assertStringContainsString('this.display_message("An error occurred while saving.","error",0);', $result['exec']);
    }

    /**
     * Test that identities of another user do not reach plugins and are not deleted
     */
    public function test_delete_identity_of_another_user()
    {
        $rcmail = \rcmail::get_instance();
        $action = new \rcmail_action_settings_identity_delete();

        self::initDB('identities');

        $db = $rcmail->get_dbh();
        $db->query('DELETE FROM `users` WHERE `user_id` = 2');
        $db->query("INSERT INTO `users` (`user_id`, `username`, `mail_host`) VALUES (2, 'other@example.com', 'localhost')");
        $db->query("INSERT INTO `identities` (`user_id`, `name`, `standard`, `email`) VALUES (2, 'other', '1', 'other@example.com')");
        $db->query("INSERT INTO `identities` (`user_id`, `name`, `standard`, `email`) VALUES (2, 'other', '0', 'other@example.org')");
        $query = $db->query('SELECT `identity_id` FROM `identities` WHERE `user_id` = 2 AND `standard` = 0');
        $other = (string) $db->fetch_assoc($query)['identity_id'];
        $query = $db->query('SELECT `identity_id` FROM `identities` WHERE `email` = ?', 'test@example.org');
        $own = (string) $db->fetch_assoc($query)['identity_id'];

        $calls = [];
        $hook = static function ($args) use (&$calls) {
            $calls[] = $args['id'];
            return $args;
        };
        $rcmail->plugins->register_hook('identity_delete', $hook);

        try {
            foreach ([$other, "{$own},{$other}", '00'] as $iid) {
                $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'settings', 'delete-identity');
                $_POST = ['_iid' => $iid];

                $this->runAndAssert($action, OutputJsonMock::E_EXIT);

                $result = $output->getOutput();

                $this->assertStringContainsString('this.display_message("An error occurred while saving.","error",0);', $result['exec']);
            }
        } finally {
            $rcmail->plugins->unregister_hook('identity_delete', $hook);
        }

        $this->assertSame([], $calls);

        foreach ([$own, $other] as $iid) {
            $query = $db->query('SELECT `del` FROM `identities` WHERE `identity_id` = ?', $iid);
            $this->assertEmpty($db->fetch_assoc($query)['del']);
        }

        $db->query('DELETE FROM `identities` WHERE `user_id` = 2');
        $db->query('DELETE FROM `users` WHERE `user_id` = 2');
    }
}
