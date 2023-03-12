<?php

class HideBlockquote_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../hide_blockquote.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new hide_blockquote($rcube->plugins);

        $this->assertInstanceOf('hide_blockquote', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);

        $plugin->init();
    }

    /**
     * Test prefs_table() method
     */
    function test_prefs_table()
    {
        $rcube  = rcube::get_instance();
        $plugin = new hide_blockquote($rcube->plugins);

        $args = ['section' => 'mailview', 'blocks' => ['main' => ['options' => []]]];

        $result = $plugin->prefs_table($args);

        $this->assertSame(
            '<label for="hide_blockquote_limit">Hide citation when lines count is greater than</label>',
            $result['blocks']['main']['options']['hide_blockquote_limit']['title']
        );

        $this->assertSame(
            '<input name="_hide_blockquote_limit" id="hide_blockquote_limit" size="5" class="form-control" value="" type="text">',
            $result['blocks']['main']['options']['hide_blockquote_limit']['content']
        );
    }

    /**
     * Test prefs_save() method
     */
    function test_prefs_save()
    {
        $rcube  = rcube::get_instance();
        $plugin = new hide_blockquote($rcube->plugins);

        $_POST = [];
        $args  = ['section' => 'mailview', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertSame(0, $result['prefs']['hide_blockquote_limit']);

        $_POST = ['_hide_blockquote_limit' => '10'];
        $args  = ['section' => 'mailview', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertSame(10, $result['prefs']['hide_blockquote_limit']);
    }
}
