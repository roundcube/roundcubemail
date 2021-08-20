<?php

class Managesieve_Vacation extends ActionTestCase
{
    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../managesieve.php';
        include_once __DIR__ . '/../lib/Roundcube/rcube_sieve_engine.php';
        include_once __DIR__ . '/../lib/Roundcube/rcube_sieve_vacation.php';
    }

    /**
     * Test vacation_form()
     */
    function test_vacation_form()
    {
        $rcube  = rcube::get_instance();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'managesieve');

        $plugin   = new managesieve($rcube->plugins);
        $vacation = new rcube_sieve_vacation($plugin);

        setProperty($vacation, 'vacation', ['list' => []]);
        setProperty($vacation, 'exts', ['date', 'regex', 'vacation-seconds']);

        $result = $vacation->vacation_form([]);

        $this->assertSame('H:i', $output->get_env('time_format'));
        $this->assertTrue(strpos($result, '<form id="form"') === 0);
        $this->assertTrue(strpos($result, '<input type="hidden" name="_action" value="plugin.managesieve-vacation">') !== false);
    }

    function test_build_regexp_tests()
    {
        $error    = null;
        $vacation = new rcube_sieve_vacation(true);
        $tests    = invokeMethod($vacation, 'build_regexp_tests', ['2014-02-20', '2014-03-05', &$error]);

        $this->assertCount(2, $tests);
        $this->assertSame('header', $tests[0]['test']);
        $this->assertSame('regex', $tests[0]['type']);
        $this->assertSame('received', $tests[0]['arg1']);
        $this->assertSame('(20|21|22|23|24|25|26|27|28) Feb 2014', $tests[0]['arg2']);
        $this->assertSame('header', $tests[1]['test']);
        $this->assertSame('regex', $tests[1]['type']);
        $this->assertSame('received', $tests[1]['arg1']);
        $this->assertSame('([ 0]1|[ 0]2|[ 0]3|[ 0]4|[ 0]5) Mar 2014', $tests[1]['arg2']);

        $tests = invokeMethod($vacation, 'build_regexp_tests', ['2014-02-20', '2014-01-05', &$error]);

        $this->assertSame(null, $tests);
        $this->assertSame('managesieve.invaliddateformat', $error);
    }

    function test_parse_regexp_tests()
    {
        $tests = [
            [
                'test' => 'header',
                'type' => 'regex',
                'arg1' => 'received',
                'arg2' => '(20|21|22|23|24|25|26|27|28) Feb 2014',
            ],
            [
                'test' => 'header',
                'type' => 'regex',
                'arg1' => 'received',
                'arg2' => '([ 0]1|[ 0]2|[ 0]3|[ 0]4|[ 0]5) Mar 2014',
            ]
        ];

        $vacation = new rcube_sieve_vacation(true);
        $result   = invokeMethod($vacation, 'parse_regexp_tests', [$tests]);

        $this->assertCount(2, $result);
        $this->assertSame('20 Feb 2014', $result['from']);
        $this->assertSame('05 Mar 2014', $result['to']);
    }
}
