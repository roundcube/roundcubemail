<?php

namespace Tests\Browser\Components;

use Tests\Browser\Browser;
use Laravel\Dusk\Component;

class Popupmenu extends Component
{
    public $id;

    /**
     * Class constructor
     */
    public function __construct($id)
    {
        $this->id = $id;
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
        $browser->waitFor($this->selector());
    }

    /**
     * Get the element shortcuts for the component.
     *
     * @return array
     */
    public function elements()
    {
        return [
        ];
    }

    /**
     * Assert popup menu state
     */
    public function assertMenuState($browser, $active, $disabled = [], $missing = [])
    {
        foreach ($active as $option) {
            // Print action is disabled on phones
            if ($option == 'print' && $browser->isPhone()) {
                $browser->assertMissing("a.print");
            }
            else {
                $browser->assertVisible("a.{$option}:not(.disabled)");
            }
        }

        foreach ($disabled as $option) {
            if ($option == 'print' && $browser->isPhone()) {
                $browser->assertMissing("a.print");
            }
            else {
                $browser->assertVisible("a.{$option}.disabled");
            }
        }

        foreach ($missing as $option) {
            $browser->assertMissing("a.{$option}");
        }
    }

    /**
     * Close popup menu
     */
    public function closeMenu($browser)
    {
        // hide the menu back
        $browser->withinBody(function ($browser) {
            $browser->script("window.UI.menu_hide('{$this->id}')");
            $browser->waitUntilMissing($this->selector());
            if ($browser->isPhone()) {
                // FIXME: For some reason sometimes .popover-overlay does not close,
                //        we have to remove it manually
                $browser->script(
                    "Array.from(document.getElementsByClassName('popover-overlay')).forEach(function(elem) { elem.parentNode.removeChild(elem); })"
                );
            }
        });
    }

    /**
     * Select popup menu item
     */
    public function clickMenuItem($browser, $name, $dropdown_action = null)
    {
        $selector = "a.{$name}" . ($dropdown_action ? " + a.dropdown" : '');

        $browser->click($selector);

        if ($dropdown_action) {
            $popup_id = $browser->attribute($selector, 'data-popup');
            $browser->withinBody(function ($browser) use ($popup_id, $dropdown_action) {
                $browser->click("#{$popup_id} li a.{$dropdown_action}");
            });
        }

        $this->closeMenu($browser);
    }
}
