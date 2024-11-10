<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

use function Roundcube\Tests\getProperty;
use function Roundcube\Tests\invokeMethod;
use function Roundcube\Tests\setProperty;

class MarkasjunkTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \markasjunk($rcube->plugins);

        $this->assertInstanceOf('markasjunk', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * Test driver loading
     */
    public function test_init_driver()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \markasjunk($rcube->plugins);

        $drivers = ['amavis_blacklist', 'cmd_learn', 'dir_learn', 'edit_headers', 'email_learn',
            'jsevent', 'sa_blacklist', 'sa_detach',
        ];

        setProperty($plugin, 'rcube', $rcube);

        foreach ($drivers as $driver_name) {
            $rcube->config->set('markasjunk_learning_driver', $driver_name);

            invokeMethod($plugin, '_init_driver');

            $driver = getProperty($plugin, 'driver');
            $this->assertInstanceOf("markasjunk_{$driver_name}", $driver);
        }
    }
}
