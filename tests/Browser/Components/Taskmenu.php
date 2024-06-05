<?php

namespace Roundcube\Tests\Browser\Components;

use Laravel\Dusk\Component;
use Roundcube\Tests\Browser\Browser;

class Taskmenu extends Component
{
    protected $options = ['compose', 'mail', 'contacts', 'settings', 'about', 'logout'];

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    #[\Override]
    public function selector()
    {
        return '#taskmenu';
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
     * Assert Taskmenu state
     */
    public function assertMenuState(Browser $browser, $selected)
    {
        // On phone the menu is invisible, open it
        if ($browser->isPhone()) {
            $browser->withinBody(function ($browser) {
                $browser->click('.task-menu-button');
                $browser->waitFor($this->selector());
            });
        }

        foreach ($this->options as $option) {
            $browser->assertVisible("a.{$option}:not(.disabled)" . ($selected == $option ? '.selected' : ':not(.selected)'));
        }

        // hide the menu back
        if ($browser->isPhone()) {
            $browser->withinBody(function ($browser) {
                $browser->click('.popover a.button.cancel');
                $browser->waitUntilMissingOrStale($this->selector());
            });
        }
    }

    /**
     * Select Taskmenu item
     */
    public function clickMenuItem(Browser $browser, $name)
    {
        if ($browser->isPhone()) {
            $browser->withinBody(static function ($browser) {
                $browser->click('.task-menu-button');
            });
        }

        $browser->click("a.{$name}");

        if ($browser->isPhone()) {
            $browser->withinBody(function ($browser) {
                $browser->waitUntilMissingOrStale($this->selector());
            });
        }
    }
}
