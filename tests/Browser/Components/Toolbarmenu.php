<?php

namespace Tests\Browser\Components;

use Tests\Browser\Browser;
use Laravel\Dusk\Component;

class Toolbarmenu extends Component
{
    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return '#toolbar-menu';
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
        if ($browser->isPhone()) {
            $browser->assertPresent($this->selector());
        }
        else {
            $browser->assertVisible($this->selector());
        }
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
     * Assert toolbar menu state
     */
    public function assertMenuState($browser, $active, $disabled = [], $missing = [])
    {
        // On phone the menu is invisible, open it
        if ($browser->isPhone()) {
            $browser->withinBody(function ($browser) {
                // As we might be in a list or content "view" we have to find
                // currently visible menu button, and click it
                foreach ($browser->elements('.toolbar-menu-button') as $button) {
                    if ($button->isDisplayed()) {
                        $button->click();
                    }
                }

                $browser->waitFor($this->selector());
            });
        }

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

        $this->closeMenu($browser);
    }

    /**
     * Close toolbar menu (on phones)
     */
    public function closeMenu($browser)
    {
        // hide the menu back
        if ($browser->isPhone()) {
            $browser->withinBody(function ($browser) {
                $browser->script("window.UI.menu_hide('toolbar-menu')");
                $browser->waitUntilMissing($this->selector())->pause(150);
                // FIXME: For some reason sometimes .popover-overlay does not close,
                //        we have to remove it manually
                $browser->script(
                    "Array.from(document.getElementsByClassName('popover-overlay')).forEach(function(elem) { elem.parentNode.removeChild(elem); })"
                );
            });
        }
    }

    /**
     * Select toolbar menu item
     */
    public function clickMenuItem($browser, $name, $dropdown_action = null)
    {
        if ($browser->isPhone()) {
            $browser->withinBody(function ($browser) {
                // Click (visible) menu button
                foreach ($browser->elements('.toolbar-menu-button') as $button) {
                    if ($button->isDisplayed()) {
                        $button->click();
                    }
                }
                $browser->waitFor($this->selector());
            });
        }

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
