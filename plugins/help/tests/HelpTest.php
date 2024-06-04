<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class HelpTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \help($rcube->plugins);

        $this->assertInstanceOf('help', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * Test help_metadata()
     */
    public function test_help_metadata()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \help($rcube->plugins);

        $result = $plugin->help_metadata();

        $this->assertCount(3, $result);
        $this->assertMatchesRegularExpression('|\?_task=settings&_action=about&_framed=1$|', $result['about']);
        $this->assertSame('self', $result['license']);
        $this->assertSame('http://docs.roundcube.net/doc/help/1.1/en_US/', $result['index']);
    }
}
