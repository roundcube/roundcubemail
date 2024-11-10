<?php

namespace Roundcube\Plugins\Tests;

use Roundcube\Tests\ActionTestCase;

class AdditionalMessageHeadersTest extends ActionTestCase
{
    /**
     * Test the plugin
     */
    public function test_plugin()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \additional_message_headers($rcube->plugins);

        $this->assertInstanceOf('additional_message_headers', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);

        $plugin->init();

        $args = ['message' => new \Mail_mime()];

        $result = $plugin->message_headers($args);

        $this->assertSame("MIME-Version: 1.0\r\n", $result['message']->txtHeaders());

        $rcube->config->set('additional_message_headers', ['X-Test' => 'Test']);

        $result = $plugin->message_headers($args);

        $this->assertSame("MIME-Version: 1.0\r\nX-Test: Test\r\n", $result['message']->txtHeaders());
    }
}
