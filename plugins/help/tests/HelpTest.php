<?php

use PHPUnit\Framework\TestCase;

class Help_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new help($rcube->plugins);

        self::assertInstanceOf('help', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * Test help_metadata()
     */
    public function test_help_metadata()
    {
        $rcube = rcube::get_instance();
        $plugin = new help($rcube->plugins);

        $result = $plugin->help_metadata();

        self::assertCount(3, $result);
        self::assertMatchesRegularExpression('|\?_task=settings&_action=about&_framed=1$|', $result['about']);
        self::assertSame('self', $result['license']);
        self::assertSame('http://docs.roundcube.net/doc/help/1.1/en_US/', $result['index']);
    }
}
