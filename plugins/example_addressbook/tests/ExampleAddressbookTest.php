<?php

use PHPUnit\Framework\TestCase;

class ExampleAddressbook_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new example_addressbook($rcube->plugins);

        self::assertInstanceOf('example_addressbook', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);

        $plugin->init();
    }

    /**
     * Test address_sources()
     */
    public function test_address_sources()
    {
        $rcube = rcube::get_instance();
        $plugin = new example_addressbook($rcube->plugins);

        $result = $plugin->address_sources(['sources' => []]);

        self::assertSame('static', $result['sources']['static']['id']);
    }
}
