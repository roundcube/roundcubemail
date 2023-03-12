<?php

namespace Tests\Browser\Mail;

use Tests\Browser\Components\App;
use Tests\Browser\Components\Popupmenu;

class MailTest extends \Tests\Browser\TestCase
{
    public function testMailUI()
    {
        $this->browse(function ($browser) {
            $browser->go('mail');

            // check task
            $browser->with(new App(), function ($browser) {
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
        $this->browse(function ($browser) {
            $browser->go('mail');

            $browser->clickToolbarMenuItem('more');

            $browser->with(new Popupmenu('message-menu'), function ($browser) {
                // Note: These are button class names, not action names
                $active = ['import'];
                $disabled = ['print', 'download', 'edit.asnew', 'source', 'move', 'copy', 'extwin'];
                $hidden = [];

                if ($browser->isPhone()) {
                    $hidden = ['print', 'extwin'];
                    $disabled = array_diff($disabled, $hidden);
                }

                $browser->assertMenuState($active, $disabled, $hidden);
                $browser->closeMenu();
            });
        });
    }
}
