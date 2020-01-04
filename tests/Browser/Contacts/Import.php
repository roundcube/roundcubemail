<?php

namespace Tests\Browser\Contacts;

use Tests\Browser\Components\App;

class Import extends \Tests\Browser\TestCase
{
    /**
     * Test basic elements of contacts import UI
     */
    public function testImportUI()
    {
        \bootstrap::init_db();

        $this->browse(function ($browser) {
            $browser->go('addressbook');

            $browser->clickToolbarMenuItem('import');

            $browser->assertSeeIn('.ui-dialog-title', 'Import contacts');
            $browser->assertVisible('.ui-dialog button.mainaction.import');
            $browser->assertVisible('.ui-dialog button.cancel');

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
            $browser->click('.ui-dialog button.cancel');
            $browser->assertMissing('.ui-dialog');
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
            $browser->assertSeeIn('.ui-dialog-title', 'Import contacts');

            // Submit the form with no file attached
            $browser->click('.ui-dialog button.mainaction');
            $browser->waitForText('Attention');
            $browser->assertSee('Please select a file');
            $browser->driver->getKeyboard()->sendKeys(\Facebook\WebDriver\WebDriverKeys::ESCAPE);
            $this->assertCount(1, $browser->elements('.ui-dialog'));

            $browser->withinFrame('.ui-dialog iframe', function ($browser) {
                $browser->attach('.custom-file input', TESTS_DIR . 'data/contacts.vcf');
            });

            $browser->click('.ui-dialog button.mainaction');

            $browser->withinFrame('.ui-dialog iframe', function ($browser) {
                $browser->waitForText('Successfully imported 2 contacts:');
            });

            // Close the dialog
            $browser->click('.ui-dialog button.cancel');

            // Expected existing contacts + imported
            $browser->waitFor('#contacts-table tr');
            $this->assertCount(4, $browser->elements('#contacts-table tbody tr'));
            $browser->assertSeeIn('#rcmcountdisplay', '1 – 4 of 4');
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
