<?php

class Help_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../help.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new help($rcube->plugins);

        $this->assertInstanceOf('help', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * Test help_metadata()
     */
    function test_help_metadata()
    {
        $rcube  = rcube::get_instance();
        $plugin = new help($rcube->plugins);

        $result = $plugin->help_metadata();

        $this->assertCount(3, $result);
        $this->assertMatchesRegularExpression('|\?_task=settings&_action=about&_framed=1$|', $result['about']);
        $this->assertSame('self', $result['license']);
        $this->assertSame('http://docs.roundcube.net/doc/help/1.1/en_US/', $result['index']);
    }
}
