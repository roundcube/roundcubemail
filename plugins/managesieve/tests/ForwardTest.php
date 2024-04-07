<?php

class Managesieve_Forward extends ActionTestCase
{
    /**
     * Test vacation_form()
     */
    public function test_vacation_form()
    {
        $rcube = rcube::get_instance();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'managesieve');

        $plugin = new managesieve($rcube->plugins);
        $forward = new rcube_sieve_forward($plugin);

        setProperty($forward, 'forward', ['list' => []]);
        setProperty($forward, 'exts', ['date', 'regex', 'vacation-seconds']);

        $result = $forward->forward_form([]);

        self::assertTrue(strpos($result, '<form id="form"') === 0);
        self::assertTrue(strpos($result, '<input type="hidden" name="_action" value="plugin.managesieve-forward">') !== false);
    }
}
