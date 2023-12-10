<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverKeys;
use PHPUnit\Framework\Assert;
use Tests\Browser\Components;

/**
 * Laravel Dusk Browser extensions
 */
class Browser extends \Laravel\Dusk\Browser
{
    /**
     * Assert number of (visible) elements
     */
    public function assertElementsCount($selector, $expected_count, $visible = true)
    {
        $elements = $this->elements($selector);
        $count = count($elements);

        if ($visible) {
            foreach ($elements as $element) {
                if (!$element->isDisplayed()) {
                    $count--;
                }
            }
        }

        Assert::assertEquals($expected_count, $count);

        return $this;
    }

    /**
     * Assert specified rcmail.env value
     */
    public function assertEnvEquals($key, $expected)
    {
        $this->assertEquals($expected, $this->getEnv($key));

        return $this;
    }

    /**
     * Assert specified checkbox state
     */
    public function assertCheckboxState($selector, $state)
    {
        if ($state) {
            $this->assertChecked($selector);
        }
        else {
            $this->assertNotChecked($selector);
        }

        return $this;
    }

    /**
     * Assert that the given element has specified class assigned.
     */
    public function assertHasClass($selector, $class_name)
    {
        $fullSelector = $this->resolver->format($selector);
        $element      = $this->resolver->findOrFail($selector);
        $classes      = explode(' ', (string) $element->getAttribute('class'));

        Assert::assertContains($class_name, $classes);

        return $this;
    }

    /**
     * Assert Task menu state
     */
    public function assertTaskMenu($selected)
    {
        $this->with(new Components\Taskmenu(), function ($browser) use ($selected) {
            $browser->assertMenuState($selected);
        });

        return $this;
    }

    /**
     * Assert toolbar menu state
     */
    public function assertToolbarMenu($active, $disabled = [], $missing = [])
    {
        $this->with(new Components\Toolbarmenu(), function ($browser) use ($active, $disabled, $missing) {
            $browser->assertMenuState($active, $disabled, $missing);
        });

        return $this;
    }

    /**
     * Close toolbar menu (on phones)
     */
    public function closeToolbarMenu()
    {
        $this->with(new Components\Toolbarmenu(), function ($browser) {
            $browser->closeMenu();
        });

        return $this;
    }

    /**
     * Select taskmenu item
     */
    public function clickTaskMenuItem($name)
    {
        $this->with(new Components\Taskmenu(), function ($browser) use ($name) {
            $browser->clickMenuItem($name);
        });

        return $this;
    }

    /**
     * Select toolbar menu item
     */
    public function clickToolbarMenuItem($name, $dropdown_action = null)
    {
        $this->with(new Components\Toolbarmenu(), function ($browser) use ($name, $dropdown_action) {
            $browser->clickMenuItem($name, $dropdown_action);
        });

        return $this;
    }

    /**
     * Shortcut to click an element while holding CTRL key
     */
    public function ctrlClick($selector)
    {
        $this->driver->getKeyboard()->pressKey(WebDriverKeys::LEFT_CONTROL);
        $this->element($selector)->click();
        $this->driver->getKeyboard()->releaseKey(WebDriverKeys::LEFT_CONTROL);

        return $this;
    }

    /**
     * Visit specified task/action with logon if needed
     */
    public function go($task = 'mail', $action = null, $login = true)
    {
        $this->with(new Components\App(), function ($browser) use ($task, $action, $login) {
            $browser->gotoAction($task, $action, $login);
        });

        return $this;
    }

    /**
     * Check if in Phone mode
     */
    public static function isPhone()
    {
        return getenv('TESTS_MODE') == 'phone';
    }

    /**
     * Check if in Tablet mode
     */
    public static function isTablet()
    {
        return getenv('TESTS_MODE') == 'tablet';
    }

    /**
     * Check if in Desktop mode
     */
    public static function isDesktop()
    {
        return !self::isPhone() && !self::isTablet();
    }

    /**
     * Handler for actions that expect to open a new window
     *
     * @param callable $callback Function to execute with Browser object as argument
     *
     * @return array Main window handle and new window handle
     */
    public function openWindow($callback)
    {
        $current_window = $this->driver->getWindowHandle();
        $before_handles = $this->driver->getWindowHandles();

        $callback($this);

        $after_handles = $this->driver->getWindowHandles();
        $new_window    = array_first(array_diff($after_handles, $before_handles));

        return [$current_window, $new_window];
    }

    /**
     * Change state of the Elastic's pretty checkbox
     */
    public function setCheckboxState($selector, $state)
    {
        // Because you can't operate on the original checkbox directly
        $this->ensurejQueryIsAvailable();

        if ($state) {
            $run = "if (!element.prev().is(':checked')) element.click()";
        }
        else {
            $run = "if (element.prev().is(':checked')) element.click()";
        }

        $this->script(
            "var element = jQuery('$selector')[0] || jQuery('input[name=$selector]')[0];"
            ."element = jQuery(element).next('.custom-control-label'); $run;"
        );

        return $this;
    }

    /**
     * Returns content of a downloaded file
     */
    public function readDownloadedFile($filename)
    {
        $filename = TESTS_DIR . "downloads/$filename";

        // Give the browser a chance to finish download
        if (!file_exists($filename)) {
            sleep(2);
        }

        Assert::assertFileExists($filename);

        return file_get_contents($filename);
    }

    /**
     * Removes downloaded file
     */
    public function removeDownloadedFile($filename)
    {
        @unlink(TESTS_DIR . "downloads/$filename");

        return $this;
    }

    /**
     * Close UI (notice/confirmation/loading/error/warning) message
     */
    public function closeMessage($type)
    {
        $selector = '#messagestack > div.' . $type;

        $this->click($selector);

        return $this;
    }

    /**
     * Wait until the UI is unlocked
     */
    public function waitUntilNotBusy()
    {
        $this->waitUntil("!rcmail.busy");

        return $this;
    }

    /**
     * Wait for UI (notice/confirmation/loading/error/warning) message
     * and assert it's text
     */
    public function waitForMessage($type, $text)
    {
        $selector = '#messagestack > div.' . $type;

        $this->waitFor($selector)->assertSeeIn($selector, $text);

        return $this;
    }

    /**
     * Execute code within body context.
     * Useful to execute code that selects elements outside of a component context
     */
    public function withinBody($callback)
    {
        if ($this->resolver->prefix != 'body') {
            $orig_prefix = $this->resolver->prefix;
            $this->resolver->prefix = 'body';
        }

        call_user_func($callback, $this);

        if (isset($orig_prefix)) {
            $this->resolver->prefix = $orig_prefix;
        }

        return $this;
    }
}
