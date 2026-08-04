<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class ExampleAddressbookTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \example_addressbook($rcube->plugins);

        $this->assertInstanceOf('example_addressbook', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);

        $plugin->init();
    }

    /**
     * Test address_sources()
     */
    public function test_address_sources()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \example_addressbook($rcube->plugins);

        $result = $plugin->address_sources(['sources' => []]);

        $this->assertSame('static', $result['sources']['static']['id']);
    }

    /**
     * Test search()
     */
    public function test_search()
    {
        $backend = new \example_addressbook_backend('static');

        // "Jane" matches only the Jane Example record
        $result = $backend->search(['name'], 'Jane');

        $this->assertInstanceOf('rcube_result_set', $result);
        $this->assertCount(1, $result->records);
        $this->assertSame(1, $result->count);
        $this->assertSame('112', $result->records[0]['ID']);
    }
}
