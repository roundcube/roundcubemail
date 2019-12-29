<?php

namespace Tests\Browser;

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\SupportsChrome;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

abstract class DuskTestCase extends TestCase
{
    use ProvidesBrowser,
        SupportsChrome;

    protected $app;
    protected static $phpProcess;


    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startWebServer();
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions())->addArguments([
            '--disable-gpu',
            '--headless',
            '--window-size=1280,720',
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }

    /**
     * Set up the test run
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->app = \rcmail::get_instance();

        Browser::$baseUrl = 'http://localhost:8000';
        Browser::$storeScreenshotsAt = __DIR__ . '/screenshots';
        Browser::$storeConsoleLogAt = __DIR__ . '/console';

        // Purge screenshots from the last test run
        $pattern = sprintf('failure-%s_%s-*',
            str_replace("\\", '_', get_class($this)),
            $this->getName(false)
        );

        try {
            $files = Finder::create()->files()->in(__DIR__ . '/screenshots')->name($pattern);
            foreach ($files as $file) {
                @unlink($file->getRealPath());
            }
        }
        catch (\Symfony\Component\Finder\Exception\DirectoryNotFoundException $e) {
            // ignore missing screenshots directory
        }
    }

    /**
     * Assert specified rcmail.env value
     */
    protected function assertEnvEquals($key, $expected)
    {
        $this->assertEquals($expected, $this->getEnv($key));
    }

    /**
     * Get content of rcmail.env entry
     */
    protected function getEnv($key)
    {
        $this->browse(function (Browser $browser) use ($key, &$result) {
            $result = $browser->script("return rcmail.env['$key']");
            $result = $result[0];
        });

        return $result;
    }

    /**
     * Get HTML IDs of defined buttons for specified Roundcube action
     */
    protected function getButtons($action)
    {
        $this->browse(function (Browser $browser) use ($action, &$buttons) {
            $buttons = $browser->script("return rcmail.buttons['$action']");
            $buttons = $buttons[0];
        });

        if (is_array($buttons)) {
            foreach ($buttons as $idx => $button) {
                $buttons[$idx] = $button['id'];
            }
        }

        return (array) $buttons;
    }

    /**
     * Return names of defined gui_objects
     */
    protected function getObjects()
    {
        $this->browse(function (Browser $browser) use (&$objects) {
            $objects = $browser->script("var i, r = []; for (i in rcmail.gui_objects) r.push(i); return r");
            $objects = $objects[0];
        });

        return (array) $objects;
    }

    /**
     * Log in the test user
     */
    protected function doLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->type('_user', TESTS_USER);
            $browser->type('_pass', TESTS_PASS);
            $browser->click('button[type="submit"]');

            // wait after successful login
            //$browser->waitForReload();
            $browser->waitUntil('!rcmail.busy');
        });
    }

    /**
     * Visit specified task/action with logon if needed
     */
    protected function go($task = 'mail', $action = null, $login = true)
    {
        $this->browse(function (Browser $browser) use ($task, $action, $login) {
            $browser->visit("/?_task=$task&_action=$action");

            // check if we have a valid session
            if ($login && $this->getEnv('task') == 'login') {
                $this->doLogin();
            }
        });
    }

    /**
     * Starts PHP server.
     */
    protected static function startWebServer()
    {
        $path = realpath(__DIR__ . '/../../public_html');
        $cmd = ['php', '-S', 'localhost:8000'];

        static::$phpProcess = new Process($cmd, null, []);
        static::$phpProcess->setWorkingDirectory($path);
        static::$phpProcess->start();

        static::afterClass(function () {
            static::$phpProcess->stop();
        });
    }
}
