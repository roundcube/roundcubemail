<?php

namespace Tests\Browser\Mail;

use Tests\Browser\Components\App;

class ComposeTest extends \Tests\Browser\TestCase
{
    public function testCompose()
    {
        $this->browse(function ($browser) {
            $browser->go('mail');

            $browser->clickTaskMenuItem('compose');

            // check task and action
            $browser->with(new App(), function ($browser) {
                $browser->assertEnv('task', 'mail');
                $browser->assertEnv('action', 'compose');

                // these objects should be there always
                $browser->assertObjects([
                    'qsearchbox',
                    'addressbookslist',
                    'contactslist',
                    'messageform',
                    'attachmentlist',
                    'filedrop',
                    'uploadform'
                ]);
            });

            // Toolbar menu
            $browser->assertToolbarMenu(
                ['save.draft', 'responses', 'spellcheck'], // active items
                ['signature'], // inactive items
            );

            if ($browser->isPhone()) {
                $browser->assertToolbarMenu(['options'], []);
            }
            else {
                $browser->assertToolbarMenu(['attach'], []);
                $browser->assertMissing('#toolbar-menu a.options');
            }

            // Task menu
            $browser->assertTaskMenu('compose');

            // Header inputs
            $browser->assertVisible('#_from');
            $browser->assertVisible('#compose-subject');
            $browser->assertInputValue('#compose-subject', '');

            // Mail body input
            $browser->assertVisible('#composebodycontainer.html-editor');
            $browser->assertVisible('#composebodycontainer > textarea');

            if ($browser->isPhone()) {
                $browser->clickToolbarMenuItem('options');
            }

            // Compose options
            $browser->assertSeeIn('#layout-sidebar .header', 'Options and attachments');
            $browser->assertVisible('#compose-attachments');
        });
    }
}
