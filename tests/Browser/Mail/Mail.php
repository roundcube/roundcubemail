<?php

namespace Tests\Browser\Mail;

class Mail extends \Tests\Browser\DuskTestCase
{
    public function testMailUI()
    {
        $this->browse(function ($browser) {
            $this->go('mail');

            // check task
            $this->assertEnvEquals('task', 'mail');

            $objects = $this->getObjects();

            // these objects should be there always
            $this->assertContains('qsearchbox', $objects);
            $this->assertContains('mailboxlist', $objects);
            $this->assertContains('messagelist', $objects);
            $this->assertContains('quotadisplay', $objects);
            $this->assertContains('search_filter', $objects);
            $this->assertContains('countdisplay', $objects);

            if (!$this->isDesktop()) {
                $browser->click('.back-sidebar-button');
            }

            $browser->assertSeeIn('#layout-sidebar .header', TESTS_USER);

            // Folders list
            $browser->assertVisible('#mailboxlist li.mailbox.inbox.selected');

            if (!$this->isDesktop()) {
                $browser->click('.back-list-button');
            }

            // Mail preview frame
            if (!$this->isPhone()) {
                $browser->assertVisible('#messagecontframe');
            }

            // Toolbar menu
            $this->assertToolbarMenu(
                ['more'], // active items
                ['reply', 'reply-all', 'forward', 'delete', 'markmessage'], // inactive items
            );

            // Task menu
            $this->assertTaskMenu('mail');
        });
    }
}
