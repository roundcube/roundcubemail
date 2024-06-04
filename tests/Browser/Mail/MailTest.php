<?php

namespace Roundcube\Tests\Browser\Mail;

use Roundcube\Tests\Browser\Components\App;
use Roundcube\Tests\Browser\Components\Popupmenu;
use Roundcube\Tests\Browser\TestCase;

class MailTest extends TestCase
{
    public function testMailUI()
    {
        $this->browse(static function ($browser) {
            $browser->go('mail');

            // check task
            $browser->with(new App(), static function ($browser) {
                $browser->assertEnv('task', 'mail');
                // these objects should be there always
                $browser->assertObjects([
                    'qsearchbox',
                    'mailboxlist',
                    'messagelist',
                    'quotadisplay',
                    'search_filter',
                    'countdisplay',
                ]);
            });

            if (!$browser->isDesktop()) {
                $browser->click('.back-sidebar-button');
            }

            $browser->assertSeeIn('#layout-sidebar .header', TESTS_USER);

            // Folders list
            $browser->assertVisible('#mailboxlist li.mailbox.inbox.selected');

            if (!$browser->isDesktop()) {
                $browser->click('.back-list-button');
            }

            // Mail preview frame
            if (!$browser->isPhone()) {
                $browser->assertVisible('#messagecontframe');
            }

            // Toolbar menu
            $browser->assertToolbarMenu(
                ['more'], // active items
                ['reply', 'reply-all', 'forward', 'delete', 'markmessage'], // inactive items
            );

            // Task menu
            $browser->assertTaskMenu('mail');
        });
    }

    /**
     * Test message menu
     */
    public function testMessageMenu()
    {
        $this->browse(static function ($browser) {
            $browser->go('mail');

            $browser->clickToolbarMenuItem('more', null, false);

            $browser->with(new Popupmenu('message-menu'), static function ($browser) {
                // Note: These are button class names, not action names
                $active = ['import'];
                $disabled = ['print', 'download', 'edit.asnew', 'source', 'move', 'copy', 'extwin'];
                $hidden = [];

                if ($browser->isPhone()) {
                    $hidden = ['print', 'extwin'];
                    $disabled = array_diff($disabled, $hidden);
                }

                $browser->assertMenuState($active, $disabled, $hidden)
                    ->closeMenu();
            });
        });
    }
}
