<?php

namespace Tests\Browser\Components;

use App;
use Tests\Browser\Browser;
use Laravel\Dusk\Component;

class HtmlEditor extends Component
{
    const MODE_PLAIN = 'plain';
    const MODE_HTML  = 'html';

    public $id;

    /**
     * Class constructor
     */
    public function __construct($id)
    {
        $this->id = trim($id);
    }

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return '#' . $this->id;
    }

    /**
     * Assert that the browser page contains the component.
     *
     * @param Browser $browser
     *
     * @return void
     */
    public function assert($browser)
    {
        $browser->waitFor($this->selector() . '.html-editor');
    }

    /**
     * Get the element shortcuts for the component.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@plain-toolbar' => '.editor-toolbar',
            '@plain-body' => 'textarea',
            '@html-editor' => '.tox-tinymce',
            '@html-toolbar' => '.tox-tinymce .tox-editor-header',
            '@html-body' => 'iframe',
        ];
    }

    /**
     * Assert editor mode
     */
    public function assertMode($browser, $mode)
    {
        if ($mode == self::MODE_PLAIN) {
            $browser->assertVisible('@plain-toolbar')
                ->assertMissing('@html-body');
        }
        else {
            $browser->assertMissing('@plain-toolbar')
                ->assertVisible('@html-body');
        }
    }

    /**
     * Switch editor mode
     */
    public function switchMode($browser, $mode, $accept_warning = false)
    {
        if ($mode == self::MODE_HTML) {
            $browser->click('@plain-toolbar a.mce-i-html');
            if ($accept_warning) {
                $browser->waitForDialog()->acceptDialog();
            }
            $browser->waitFor('@html-body')->waitFor('@html-toolbar');
        }
        else {
            $browser->click('.tox-toolbar__group:first-child button');
            if ($accept_warning) {
                $browser->waitForDialog()->acceptDialog();
            }
            $browser->waitFor('@plain-body');
        }
    }
}
