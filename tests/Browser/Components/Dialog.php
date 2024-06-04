<?php

namespace Roundcube\Tests\Browser\Components;

use Facebook\WebDriver\WebDriverKeys;
use Laravel\Dusk\Component;
use Roundcube\Tests\Browser\Browser;

class Dialog extends Component
{
    public $num;

    /**
     * Class constructor
     */
    public function __construct($num = 1)
    {
        $this->num = $num ?: 1;
    }

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    #[\Override]
    public function selector()
    {
        // work with the specified dialog (in case there's more than one)
        $suffix = $this->num > 1 ? str_repeat(' + div + .ui-dialog', $this->num - 1) : '';
        return '.ui-dialog' . $suffix;
    }

    /**
     * Assert that the browser page contains the component.
     *
     * @param Browser $browser
     */
    #[\Override]
    public function assert($browser): void
    {
        $browser->waitFor($this->selector());
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
            '@title' => '.ui-dialog-titlebar',
            '@content' => '.ui-dialog-content',
            '@footer' => '.ui-dialog-buttonset',
        ];
    }

    /**
     * Assert dialog title
     */
    public function assertDialogTitle($browser, $title)
    {
        $browser->assertSeeIn('@title', $title);
    }

    /**
     * Assert dialog content (for simple text dialogs)
     */
    public function assertDialogContent($browser, $text)
    {
        $browser->assertSeeIn('@content', $text);
    }

    /**
     * Assert dialog button
     */
    public function assertButton($browser, $name, $label)
    {
        $selector = "@footer button.{$name}";
        $browser->assertVisible($selector)
            ->assertSeeIn($selector, $label);
    }

    /**
     * Click dialog button
     */
    public function clickButton($browser, $name, $expect_close = true)
    {
        $browser->click('@footer button.' . $name);

        if ($expect_close) {
            $browser->waitUntilMissing($this->selector());
        }
    }

    /**
     * Close dialog
     */
    public function closeDialog($browser)
    {
        $browser->click('@footer button.cancel, @footer button.close')
            ->waitUntilMissing($this->selector());
    }

    /**
     * Close dialog with ESC key
     */
    public function pressESC($browser)
    {
        $browser->driver->getKeyboard()->sendKeys(WebDriverKeys::ESCAPE);
        $browser->waitUntilMissing($this->selector());
    }

    /**
     * Execute code within dialog's iframe
     */
    public function withinDialogFrame($browser, $callback)
    {
        $browser->withinFrame('@content iframe', static function ($browser) use ($callback) {
            $browser->withinBody(static function ($browser) use ($callback) {
                $callback($browser);
            });
        });
    }
}
