<?php

namespace Tests\Browser\Mail;

class Compose extends \Tests\Browser\DuskTestCase
{
    public function testCompose()
    {
        $this->browse(function ($browser) {
            $this->go('mail');

            $browser->click('#taskmenu a.compose');

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
            $browser->with('#toolbar-menu', function($browser) {
                $browser->assertVisible('a.save.draft:not(.disabled)');
                $browser->assertVisible('a.attach:not(.disabled)');
                $browser->assertVisible('a.signature.disabled');
                $browser->assertVisible('a.responses:not(.disabled)');
                $browser->assertVisible('a.spellcheck:not(.disabled)');
            });

            // Task menu
            $browser->with('#taskmenu', function($browser) {
                $browser->assertVisible('a.compose:not(.disabled).selected');
                $browser->assertVisible('a.mail:not(.disabled):not(.selected)');
                $browser->assertVisible('a.contacts:not(.disabled):not(.selected)');
                $browser->assertVisible('a.settings:not(.disabled):not(.selected)');
                $browser->assertVisible('a.about:not(.disabled):not(.selected)');
                $browser->assertVisible('a.logout:not(.disabled):not(.selected)');
            });

            // Header inputs
            $browser->assertSeeIn('#layout-sidebar .header', 'Options and attachments');
            $browser->assertVisible('#compose-attachments');
            $browser->assertVisible('#_from');
            $browser->assertVisible('#compose-subject');
            $browser->assertInputValue('#compose-subject', '');

            // Mail body input
            $browser->assertVisible('#composebodycontainer.html-editor');
            $browser->assertVisible('#composebodycontainer > textarea');
        });
    }
}
