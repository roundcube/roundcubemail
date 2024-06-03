<?php

namespace Roundcube\Mail\Plugins\Tests;

use Roundcube\Mail\Tests\ActionTestCase;

class ManagesieveForwardTest extends ActionTestCase
{
    /**
     * Test vacation_form()
     */
    public function test_vacation_form()
    {
        $rcube = \rcube::get_instance();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'settings', 'managesieve');

        $plugin = new \managesieve($rcube->plugins);
        $forward = new \rcube_sieve_forward($plugin);

        setProperty($forward, 'forward', ['list' => []]);
        setProperty($forward, 'exts', ['date', 'regex', 'vacation-seconds']);

        $result = $forward->forward_form([]);

        $this->assertTrue(strpos($result, '<form id="form"') === 0);
        $this->assertTrue(strpos($result, '<input type="hidden" name="_action" value="plugin.managesieve-forward">') !== false);
    }
}
