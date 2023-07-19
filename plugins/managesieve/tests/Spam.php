<?php

class Managesieve_Spam extends ActionTestCase
{
    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../managesieve.php';
        include_once __DIR__ . '/../lib/Roundcube/rcube_sieve_engine.php';
        include_once __DIR__ . '/../lib/Roundcube/rcube_sieve_spam.php';
    }

    /**
     * Test vacation_form()
     */
    function test_vacation_form()
    {
        $rcube  = rcube::get_instance();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'managesieve');

        $plugin  = new managesieve($rcube->plugins);
        $spam = new rcube_sieve_spam($plugin);

        setProperty($spam, 'spam', ['list' => []]);
        setProperty($spam, 'exts', ['date', 'regex', 'vacation-seconds']);

        $result = $spam->forward_form([]);

        $this->assertTrue(strpos($result, '<form id="form"') === 0);
        $this->assertTrue(strpos($result, '<input type="hidden" name="_action" value="plugin.managesieve-spam">') !== false);
    }
}
