<?php

namespace Tests\Browser\Mail;

class Compose extends \Tests\Browser\DuskTestCase
{
    public function testCompose()
    {
        $this->browse(function ($browser) {
            $this->go('mail');

            $this->clickTaskMenuItem('compose');

            // check task and action
            $this->assertEnvEquals('task', 'mail');
            $this->assertEnvEquals('action', 'compose');

            $objects = $this->getObjects();

            // these objects should be there always
            $this->assertContains('qsearchbox', $objects);
            $this->assertContains('addressbookslist', $objects);
            $this->assertContains('contactslist', $objects);
            $this->assertContains('messageform', $objects);
            $this->assertContains('attachmentlist', $objects);
            $this->assertContains('filedrop', $objects);
            $this->assertContains('uploadform', $objects);

            // Toolbar menu
            $this->assertToolbarMenu(
                ['save.draft', 'responses', 'spellcheck'], // active items
                ['signature'], // inactive items
            );

            if ($this->isPhone()) {
                $this->assertToolbarMenu(['options'], []);
            }
            else {
                $this->assertToolbarMenu(['attach'], []);
                $browser->assertMissing('#toolbar-menu a.options');
            }

            // Task menu
            $this->assertTaskMenu('compose');

            // Header inputs
            $browser->assertVisible('#_from');
            $browser->assertVisible('#compose-subject');
            $browser->assertInputValue('#compose-subject', '');

            // Mail body input
            $browser->assertVisible('#composebodycontainer.html-editor');
            $browser->assertVisible('#composebodycontainer > textarea');

            if ($this->isPhone()) {
                $this->clickToolbarMenuItem('options');
            }

            // Compose options
            $browser->assertSeeIn('#layout-sidebar .header', 'Options and attachments');
            $browser->assertVisible('#compose-attachments');
        });
    }
}
