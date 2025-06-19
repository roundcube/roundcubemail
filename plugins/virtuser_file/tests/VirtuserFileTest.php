<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class VirtuserFileTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \virtuser_file($rcube->plugins);

        $this->assertInstanceOf('virtuser_file', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * Test email lookup
     */
    public function test_virtuals()
    {
        $rcube = \rcube::get_instance();
        $rcube->config->set('virtuser_file', realpath(__DIR__ . '/src/virtuals'));
        $plugin = new \virtuser_file($rcube->plugins);
        $plugin->init();

        $email = $plugin->user2email(['user' => 'alias-2']);
        $user = $plugin->email2user(['email' => 'email2@domain.com']);

        $this->assertSame('email2@domain.com', $email['email'][0]);
        $this->assertSame('alias-2', $user['user']);
    }

    /**
     * Test opensmtpd format
     */
    public function test_opensmtpd()
    {
        $rcube = \rcube::get_instance();
        $rcube->config->set('virtuser_file', realpath(__DIR__ . '/src/opensmtpd'));
        $plugin = new \virtuser_file($rcube->plugins);
        $plugin->init();

        $email = $plugin->user2email(['user' => 'alias-2']);
        $user = $plugin->email2user(['email' => 'email2@domain.com']);

        $this->assertSame('email2@domain.com', $email['email'][0]);
        $this->assertSame('alias-2', $user['user']);
    }
}
