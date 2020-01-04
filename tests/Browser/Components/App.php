<?php

namespace Tests\Browser\Components;

use Laravel\Dusk\Component as BaseComponent;
use PHPUnit\Framework\Assert;
use Tests\Browser\Browser;

class App extends BaseComponent
{
    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return '';
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
        // Assume the app (window.rcmail) is always available
        // we can't assert that before we visit the page
        // i.e. you will not be able to use gotoAction()
        // method if you try to assert that fact.
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
     * Assert value of rcmail.env entry
     */
    public function assertEnv($browser, string $key, $expected)
    {
        Assert::assertEquals($expected, $this->getEnv($browser, $key));
    }

    /**
     * Assert existence of defined gui_objects
     */
    public function assertObjects($browser, array $names)
    {
        $objects = $this->getObjects($browser);

        foreach ($names as $object_name) {
            Assert::assertContains($object_name, $objects);
        }
    }

    /**
     * Return rcmail.env entry
     */
    public function getEnv($browser, $key)
    {
        $result = $browser->script("return rcmail.env['$key']");
        $result = $result[0];

        return $result;
    }

    /**
     * Return names of defined gui_objects
     */
    public function getObjects($browser)
    {
        $objects = $browser->script("var i, r = []; for (i in rcmail.gui_objects) r.push(i); return r");
        $objects = $objects[0];

        return (array) $objects;
    }

    /**
     * Visit specified task/action with logon if needed
     */
    public function gotoAction($browser, $task = 'mail', $action = null, $login = true)
    {
        $browser->visit("/?_task={$task}&_action={$action}");

        // check if we have a valid session
        if ($login && $this->getEnv($browser, 'task') == 'login') {
            $this->doLogin($browser);
        }
    }

    /**
     * Log in the test user
     */
    protected function doLogin($browser)
    {
        $browser->type('_user', TESTS_USER);
        $browser->type('_pass', TESTS_PASS);
        $browser->click('button[type="submit"]');

        // wait after successful login
        $browser->waitUntil('!rcmail.busy');
    }
}
