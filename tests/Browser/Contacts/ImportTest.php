<?php

namespace Tests\Browser\Contacts;

use Tests\Browser\Components\App;
use Tests\Browser\Components\Dialog;

class ImportTest extends \Tests\Browser\TestCase
{
    public static function setUpBeforeClass(): void
    {
        \bootstrap::init_db();
    }

    /**
     * Test basic elements of contacts import UI
     */
    public function testImportUI()
    {
        $this->browse(function ($browser) {
            $browser->go('addressbook');

            $browser->clickToolbarMenuItem('import');

            $browser->with(new Dialog(), function ($browser) {
                $browser->assertDialogTitle('Import contacts')
                    ->assertButton('mainaction.import', 'Import')
                    ->assertButton('cancel', 'Cancel');
            });

            $browser->withinFrame('.ui-dialog iframe', function ($browser) {
                // check task and action
                $browser->with(new App(), function ($browser) {
                    $browser->assertEnv('task', 'addressbook');
                    $browser->assertEnv('action', 'import');
                    // these objects should be there always
                    $browser->assertObjects(['importform']);
                });

                $browser->assertSee('You can upload');
                $browser->assertVisible('#rcmImportForm');
                $browser->assertVisible('#rcmImportForm select');
                $browser->assertVisible('#rcmImportForm .custom-switch');
                // FIXME: selecting the file input directly does not work
                $browser->assertVisible('#rcmImportForm .custom-file');
                $browser->assertSelected('#rcmImportForm select', 0);
            });

            // Close the dialog
            $browser->with(new Dialog(), function ($browser) {
                $browser->clickButton('cancel');
            });
        });
    }

    /**
     * Import contacts from a vCard file
     *
     * @depends testImportUI
     */
    public function testImportProcess()
    {
        $this->browse(function ($browser) {
            // Open the dialog again
            $browser->clickToolbarMenuItem('import');

            $browser->with(new Dialog(), function ($browser) {
                $browser->assertDialogTitle('Import contacts')
                    ->clickButton('import');
            });

            // Submit the form with no file attached
            $browser->with(new Dialog(2), function ($browser) {
                $browser->assertDialogTitle('Attention')
                    ->assertDialogContent('Please select a file')
                    ->assertButton('save.mainaction', 'OK')
                    ->pressESC();
            });

            $browser->with(new Dialog(), function ($browser) {
                $browser->withinDialogFrame(function ($browser) {
                        $browser->attach('.custom-file input', TESTS_DIR . 'data/contacts.vcf');
                    })
                    ->clickButton('import')
                    ->withinDialogFrame(function ($browser) {
                        $browser->waitForText('Successfully imported 2 contacts:');
                    })
                    ->closeDialog();
            });

            // Expected existing contacts + imported
            $browser->waitFor('#contacts-table tr')
                ->assertElementsCount('#contacts-table tbody tr', 4)
                ->assertSeeIn('#rcmcountdisplay', '1 – 4 of 4');
        });
    }

    /**
     * Test imported contact
     *
     * @depends testImportProcess
     */
    public function testImportResult()
    {
        $this->browse(function ($browser) {
            // Open the dialog again
            $browser->click('#contacts-table tr:last-child');

            $browser->withinFrame('#contact-frame', function ($browser) {
                $browser->waitFor('a.email'); // wait for iframe to load
                $browser->assertSeeIn('.names', 'Sylvester Stalone');
                $browser->assertSeeIn('a.email', 's.stalone@rambo.tv');
            });
        });
    }
}
