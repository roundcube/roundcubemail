<?php

namespace Roundcube\Tests\Browser\Components;

use Laravel\Dusk\Component;
use Roundcube\Tests\Browser\Browser;

class Toolbarmenu extends Component
{
    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    #[\Override]
    public function selector()
    {
        return '#toolbar-menu';
    }

    /**
     * Assert that the browser page contains the component.
     *
     * @param Browser $browser
     */
    #[\Override]
    public function assert($browser): void
    {
        if ($browser->isPhone()) {
            $browser->assertPresent($this->selector());
        } else {
            $browser->assertVisible($this->selector());
        }
    }

    /**
     * Get the element shortcuts for the component.
     *
     * @return array
     */
    #[\Override]
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
        $this->openMenu($browser);

        foreach ($active as $option) {
            // Print action is disabled on phones
            if ($option == 'print' && $browser->isPhone()) {
                $browser->assertMissing('a.print');
            } else {
                $browser->assertVisible("a.{$option}:not(.disabled)");
            }
        }

        foreach ($disabled as $option) {
            if ($option == 'print' && $browser->isPhone()) {
                $browser->assertMissing('a.print');
            } else {
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
                $browser->script("var elem; while(elem = \$('.popover.show .popover-header a.button:visible')[0]) \$(elem).click();");
                $browser->waitUntilMissingOrStale($this->selector());
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
    public function clickMenuItem($browser, $name, $dropdown_action = null, $close = true)
    {
        $this->openMenu($browser);

        $selector = "a.{$name}" . ($dropdown_action ? ' + a.dropdown' : '');

        $browser->click($selector);

        if ($dropdown_action) {
            $popup_id = $browser->attribute($selector, 'data-popup');
            $browser->withinBody(static function ($browser) use ($popup_id, $dropdown_action) {
                $browser->click("#{$popup_id} li a.{$dropdown_action}");
            });
        }

        // Make sure the menu is closed on mobile
        if ($close) {
            $this->closeMenu($browser);
        }
    }

    /**
     * Open toolbar menu (on phones)
     */
    public function openMenu($browser)
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
    }
}
