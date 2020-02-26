<?php

namespace Tests\Browser\Components;

use Tests\Browser\Browser;
use PHPUnit\Framework\Assert;
use Laravel\Dusk\Component;

class RecipientInput extends Component
{
    public $selector;

    /**
     * Class constructor
     */
    public function __construct($selector)
    {
        $this->selector = trim($selector);
    }

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return $this->selector;
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
        $browser->waitFor($this->selector() . ' @input');
    }

    /**
     * Get the element shortcuts for the component.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@list' => 'ul.recipient-input',
            '@field' => '.input-group > input, input-group > textarea',
            '@input' => '@list input',
            '@add-contact' => 'a.add.recipient',
            '@add-header' => '.input-group-append:last-child a.add',
            '@recipient' => '@list li.recipient',
        ];
    }

    /**
     * Assert recipient box content
     */
    public function assertRecipient($browser, $num, $recipient)
    {
        $browser->ensurejQueryIsAvailable();
        $selector = $this->selector() . " ul.recipient-input li.recipient:nth-child($num)";
        $text = $browser->driver->executeScript("return \$('$selector').text()");

        Assert::assertSame($recipient, is_string($text) ? trim($text, ", ") : null);
    }
}
