<?php

namespace Roundcube\Tests\Actions\Settings;

use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_settings_identity_save
 */
class IdentitySaveTest extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_identity_edit()
    {
        $action = new \rcmail_action_settings_identity_save();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'settings', 'save-identity');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        self::initDB('identities');

        $db = \rcmail::get_instance()->get_dbh();
        $query = $db->query('SELECT * FROM `identities` WHERE `standard` = 1 LIMIT 1');
        $identity = $db->fetch_assoc($query);

        // Test successful identity update

        $_POST = [
            '_iid' => $identity['identity_id'],
            '_name' => 'new-name',
            '_email' => 'new@example.com',
            '_standard' => '1',
            '_signature' => 'test',
        ];

        $action->run();

        $this->assertSame('edit-identity', \rcmail::get_instance()->action);
        $this->assertSame('successfullysaved', $output->getProperty('message'));

        $query = $db->query('SELECT * FROM `identities` WHERE `identity_id` = ?', $identity['identity_id']);
        $identity = $db->fetch_assoc($query);

        $this->assertSame('new-name', $identity['name']);
        $this->assertSame('new@example.com', $identity['email']);
        $this->assertSame('test', $identity['signature']);
        $this->assertSame(1, (int) $identity['standard']);
    }

    /**
     * Test that an identity of another user does not reach plugins and is not changed
     */
    public function test_identity_of_another_user()
    {
        $rcmail = \rcmail::get_instance();
        $action = new \rcmail_action_settings_identity_save();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'settings', 'save-identity');

        self::initDB('identities');

        $db = $rcmail->get_dbh();
        $db->query('DELETE FROM `users` WHERE `user_id` = 2');
        $db->query("INSERT INTO `users` (`user_id`, `username`, `mail_host`) VALUES (2, 'other@example.com', 'localhost')");
        $db->query("INSERT INTO `identities` (`user_id`, `name`, `standard`, `email`) VALUES (2, 'other', '1', 'other@example.com')");
        $query = $db->query('SELECT `identity_id` FROM `identities` WHERE `user_id` = 2');
        $other = (string) $db->fetch_assoc($query)['identity_id'];
        $query = $db->query('SELECT `identity_id` FROM `identities` WHERE `user_id` = 1 AND `standard` = 1');
        $own = (string) $db->fetch_assoc($query)['identity_id'];

        $calls = [];
        $hook = static function ($args) use (&$calls) {
            $calls[] = $args['id'];
            return $args;
        };
        $rcmail->plugins->register_hook('identity_update', $hook);

        try {
            foreach ([0, 2, 3] as $level) {
                $rcmail->config->set('identities_level', $level);

                $_POST = [
                    '_iid' => $other,
                    '_name' => 'changed',
                    '_email' => 'changed@example.com',
                    '_signature' => 'changed',
                ];

                $action->run();

                $this->assertSame('errorsaving', $output->getProperty('message'));
                $this->assertSame('edit-identity', $rcmail->action);
            }

            // The own identity still reaches the plugin and is saved
            $rcmail->config->set('identities_level', 0);
            $_POST = ['_iid' => $own, '_name' => 'own', '_email' => 'test@example.com', '_standard' => '1'];

            $action->run();

            $this->assertSame('successfullysaved', $output->getProperty('message'));
        } finally {
            $rcmail->plugins->unregister_hook('identity_update', $hook);
            $rcmail->config->set('identities_level', 0);
        }

        $this->assertSame([$own], $calls);

        $query = $db->query('SELECT * FROM `identities` WHERE `identity_id` = ?', $other);
        $identity = $db->fetch_assoc($query);

        $this->assertSame('other', $identity['name']);
        $this->assertSame('other@example.com', $identity['email']);
        $this->assertSame('', (string) $identity['signature']);

        $db->query('DELETE FROM `identities` WHERE `user_id` = 2');
        $db->query('DELETE FROM `users` WHERE `user_id` = 2');
    }

    /**
     * Test run() method for a new identity
     */
    public function test_new_identity()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test run() method errors handling
     */
    public function test_run_errors()
    {
        $this->markTestIncomplete();
    }
}
