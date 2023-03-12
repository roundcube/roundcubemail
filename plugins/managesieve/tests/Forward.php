<?php

class Managesieve_Forward extends ActionTestCase
{
    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../managesieve.php';
        include_once __DIR__ . '/../lib/Roundcube/rcube_sieve_engine.php';
        include_once __DIR__ . '/../lib/Roundcube/rcube_sieve_forward.php';
    }

    /**
     * Test vacation_form()
     */
    function test_vacation_form()
    {
        $rcube  = rcube::get_instance();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'managesieve');

        $plugin  = new managesieve($rcube->plugins);
        $forward = new rcube_sieve_forward($plugin);

        setProperty($forward, 'forward', ['list' => []]);
        setProperty($forward, 'exts', ['date', 'regex', 'vacation-seconds']);

        $result = $forward->forward_form([]);

        $this->assertTrue(strpos($result, '<form id="form"') === 0);
        $this->assertTrue(strpos($result, '<input type="hidden" name="_action" value="plugin.managesieve-forward">') !== false);
    }
}
