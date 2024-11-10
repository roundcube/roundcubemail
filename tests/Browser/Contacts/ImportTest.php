<?php

namespace Roundcube\Tests\Browser\Contacts;

use PHPUnit\Framework\Attributes\Depends;
use Roundcube\Tests\Browser\Bootstrap;
use Roundcube\Tests\Browser\Components\App;
use Roundcube\Tests\Browser\Components\Dialog;
use Roundcube\Tests\Browser\TestCase;

class ImportTest extends TestCase
{
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        Bootstrap::init_db();
    }

    /**
     * Test basic elements of contacts import UI
     */
    public function testImportUI()
    {
        $this->browse(static function ($browser) {
            $browser->go('addressbook');

            $browser->clickToolbarMenuItem('import');

            $browser->with(new Dialog(), static function ($browser) {
                $browser->assertDialogTitle('Import contacts')
                    ->assertButton('mainaction.import', 'Import')
                    ->assertButton('cancel', 'Cancel');
            });

            $browser->withinFrame('.ui-dialog iframe', static function ($browser) {
                // check task and action
                $browser->with(new App(), static function ($browser) {
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
            $browser->with(new Dialog(), static function ($browser) {
                $browser->clickButton('cancel');
            });
        });
    }

    /**
     * Import contacts from a vCard file
     *
     * @depends testImportUI
     */
    #[Depends('testImportUI')]
    public function testImportProcess()
    {
        $this->browse(static function ($browser) {
            // Open the dialog again
            $browser->clickToolbarMenuItem('import');

            $browser->with(new Dialog(), static function ($browser) {
                $browser->assertDialogTitle('Import contacts')
                    ->clickButton('import');
            });

            // Submit the form with no file attached
            $browser->with(new Dialog(2), static function ($browser) {
                $browser->assertDialogTitle('Attention')
                    ->assertDialogContent('Please select a file')
                    ->assertButton('save.mainaction', 'OK')
                    ->pressESC();
            });

            $browser->with(new Dialog(), static function ($browser) {
                $browser->withinDialogFrame(static function ($browser) {
                    $browser->attach('.custom-file input', TESTS_DIR . 'data/contacts.vcf');
                })
                    ->clickButton('import')
                    ->withinDialogFrame(static function ($browser) {
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
    #[Depends('testImportProcess')]
    public function testImportResult()
    {
        $this->browse(static function ($browser) {
            // Open the dialog again
            $browser->click('#contacts-table tr:last-child');

            $browser->withinFrame('#contact-frame', static function ($browser) {
                $browser->waitFor('a.email'); // wait for iframe to load
                $browser->assertSeeIn('.names', 'Sylvester Stalone');
                $browser->assertSeeIn('a.email', 's.stalone@rambo.tv');
            });
        });
    }
}
