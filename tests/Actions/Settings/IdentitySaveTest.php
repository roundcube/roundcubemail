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
