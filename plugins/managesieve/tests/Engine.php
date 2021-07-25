<?php

class Managesieve_Engine extends ActionTestCase
{
    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../managesieve.php';
        include_once __DIR__ . '/../lib/Roundcube/rcube_sieve_engine.php';
        include_once __DIR__ . '/../lib/Roundcube/rcube_sieve_vacation.php';
    }

    /**
     * Test filter_form()
     */
    function test_filter_form()
    {
        $rcube  = rcube::get_instance();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'managesieve');

        // Set expected storage function calls/results
        $rcube->storage
            ->registerFunction('list_folders_subscribed', [
                    'INBOX',
                    'Test',
            ])
            ->registerFunction('mod_folder', 'Test')
            ->registerFunction('mod_folder', 'Test')
            ->registerFunction('folder_attributes', []);

        $plugin = new managesieve($rcube->plugins);
        $engine = new rcube_sieve_engine($plugin);

        setProperty($engine, 'exts', ['copy', 'currentdate', 'date', 'duplicate',
            'editheader', 'enotify', 'envelope', 'fileinto', 'imap4flags', 'index',
            'mime', 'regex', 'reject', 'relational', 'spamtest', 'subaddress',
            'vacation', 'vacation-seconds', 'variables']);

        $result = $engine->filter_form([]);

        $this->assertFalse($output->get_env('rule_disabled'));
        $this->assertTrue(strpos($result, '<form name="filterform"') === 0);
        $this->assertTrue(strpos($result, '<input type="hidden" name="_action" value="plugin.managesieve-save">') !== false);
        $this->assertTrue(strpos($result, '<div id="rules">') !== false);
        $this->assertTrue(strpos($result, '<div id="actions">') !== false);

        // TODO: Test it for real
    }

    /**
     * Data sets for strip_value() test
     */
    function data_strip_value()
    {
        return [
            ['', ['']],
            [' test ', [' test ', true, false]],
            ['test', [' test ', false, true]],
            ['test', ['test<p>']],
            ['test<p>', ['test<p>', true]],
            [['test1', 'test2'], [['test1<p>', 'test2<p>'], false]],
            [['test1<p>', 'test2<p>'], [['test1<p>', 'test2<p>'], true]],
        ];
    }

    /**
     * Test strip_value()
     *
     * @dataProvider data_strip_value
     */
    function test_strip_value($expected, $args)
    {
        $rcube  = rcube::get_instance();

        $plugin = new managesieve($rcube->plugins);
        $engine = new rcube_sieve_engine($plugin);

        $this->assertSame($expected, invokeMethod($engine, 'strip_value', $args));
    }

    /**
     * Test list_input()
     */
    function test_list_input()
    {
        $rcube  = rcube::get_instance();

        $plugin = new managesieve($rcube->plugins);
        $engine = new rcube_sieve_engine($plugin);

        $args     = [1, 'n', '<p>'];
        $expected = '<textarea data-type="list" name="_n[1]" style="display:none" id="n1">&lt;p&gt;</textarea>';

        $this->assertSame($expected, invokeMethod($engine, 'list_input', $args));
    }
}
