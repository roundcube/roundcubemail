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

            $browser->assertSeeIn('#layout-sidebar .header', TESTS_USER);

            // Folders list
            $browser->assertVisible('#mailboxlist li.mailbox.inbox.selected');

            // Mail preview frame
            $browser->assertVisible('#messagecontframe');

            // Toolbar menu
            $browser->with('#toolbar-menu', function($browser) {
                $browser->assertMissing('a.compose'); // this is always hidden button
                $browser->assertVisible('a.reply.disabled');
                $browser->assertVisible('a.reply-all.disabled');
                $browser->assertVisible('a.forward.disabled');
                $browser->assertVisible('a.delete.disabled');
                $browser->assertVisible('a.markmessage.disabled');
                $browser->assertVisible('a.more:not(.disabled)');
            });

            // Task menu
            $browser->with('#taskmenu', function($browser) {
                $browser->assertVisible('a.compose:not(.disabled):not(.selected)');
                $browser->assertVisible('a.mail.selected');
                $browser->assertVisible('a.contacts:not(.disabled):not(.selected)');
                $browser->assertVisible('a.settings:not(.disabled):not(.selected)');
                $browser->assertVisible('a.about:not(.disabled):not(.selected)');
                $browser->assertVisible('a.logout:not(.disabled):not(.selected)');
            });
        });
    }
}
