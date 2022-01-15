<?php

namespace Tests\Browser\Mail;

use Tests\Browser\Components\App;
use Tests\Browser\Components\Popupmenu;

class PrintTest extends \Tests\Browser\TestCase
{
    public static function setUpBeforeClass(): void
    {
        \bootstrap::init_imap(true);
        \bootstrap::purge_mailbox('INBOX');

        // import email messages
        foreach (glob(TESTS_DIR . 'data/mail/list_00.eml') as $f) {
            \bootstrap::import_message($f, 'INBOX');
        }
    }

    /**
     * Test Print action
     */
    public function testPrint()
    {
        $this->browse(function ($browser) {
            $browser->go('mail');

            $browser->waitFor('#messagelist tbody tr:first-child')
                ->ctrlClick('#messagelist tbody tr:first-child');

            $browser->clickToolbarMenuItem('more');

            $browser->with(new Popupmenu('message-menu'), function ($browser) use (&$current_window, &$new_window) {
                if ($browser->isPhone()) {
                    $browser->assertMissing('a.print');
                    $this->markTestSkipped();
                }

                list($current_window, $new_window) = $browser->openWindow(function ($browser) {
                    $browser->clickMenuItem('print');
                });
            });

            $browser->driver->switchTo()->window($new_window);

            $browser->with(new App(), function ($browser) {
                $browser->assertEnv([
                        'task' => 'mail',
                        'action' => 'print',
                ]);
            });

            $browser->assertVisible('img.contactphoto')
                ->assertSeeIn('.subject', 'Lines')
                ->assertSeeIn('.message-part div.pre', 'Plain text message body.')
                ->assertVisible('.message-part div.pre .sig')
                // Tests "more recipients" link
                ->with('.header-headers .header.cc', function ($browser) {
                    $browser->assertSee('test10@domain.tld')
                        ->assertDontSee('test11@domain.tld')
                        ->assertSeeIn('a.morelink', '2 more...')
                        ->assertElementsCount('span.adr', 10)
                        ->click('a.morelink')
                        ->assertElementsCount('span.adr', 12)
                        ->assertSee('test12@domain.tld');
                });

            $browser->driver->close();
            $browser->driver->switchTo()->window($current_window);
        });
    }
}
