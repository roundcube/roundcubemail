<?php

class Markasjunk_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../markasjunk.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new markasjunk($rcube->plugins);

        $this->assertInstanceOf('markasjunk', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * Test driver loading
     */
    function test_init_driver()
    {
        $rcube  = rcube::get_instance();
        $plugin = new markasjunk($rcube->plugins);

        $drivers = ['amavis_blacklist', 'cmd_learn', 'dir_learn', 'edit_headers', 'email_learn',
            'jsevent', 'sa_blacklist', 'sa_detach'
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
