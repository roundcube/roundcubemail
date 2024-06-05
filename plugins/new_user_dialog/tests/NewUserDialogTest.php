<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class NewUserDialogTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \new_user_dialog($rcube->plugins);

        $this->assertInstanceOf('new_user_dialog', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}
