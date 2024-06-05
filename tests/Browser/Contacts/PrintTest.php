<?php

namespace Roundcube\Tests\Browser\Contacts;

use Roundcube\Tests\Browser\Bootstrap;
use Roundcube\Tests\Browser\Components\App;
use Roundcube\Tests\Browser\TestCase;

class PrintTest extends TestCase
{
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        Bootstrap::init_db();
    }

    /**
     * Test Print action
     */
    public function testPrint()
    {
        $this->browse(function ($browser) {
            $browser->go('addressbook');

            $browser->waitFor('#contacts-table tbody tr:first-child')
                ->ctrlClick('#contacts-table tbody tr:first-child');

            [$current_window, $new_window] = $browser->openWindow(function ($browser) {
                if ($browser->isPhone()) {
                    $this->markTestSkipped();
                }

                $browser->clickToolbarMenuItem('print');
            });

            $browser->driver->switchTo()->window($new_window);

            $browser->with(new App(), static function ($browser) {
                $browser->assertEnv([
                    'task' => 'addressbook',
                    'action' => 'print',
                ]);
            });

            $browser->assertVisible('#contactphoto img')
                ->assertSeeIn('#contacthead .firstname', 'John')
                ->assertSeeIn('#contacthead .surname', 'Doe')
                ->assertSeeIn('#contacttabs fieldset:first-child legend', 'Properties')
                ->assertSeeIn('#contacttabs', 'johndoe@example.org');

            $browser->driver->close();
            $browser->driver->switchTo()->window($current_window);
        });
    }
}
