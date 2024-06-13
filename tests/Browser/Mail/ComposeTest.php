<?php

namespace Roundcube\Tests\Browser\Mail;

use Facebook\WebDriver\WebDriverKeys;
use PHPUnit\Framework\Attributes\Depends;
use Roundcube\Tests\Browser\Bootstrap;
use Roundcube\Tests\Browser\Components\App;
use Roundcube\Tests\Browser\Components\HtmlEditor;
use Roundcube\Tests\Browser\Components\RecipientInput;
use Roundcube\Tests\Browser\TestCase;

class ComposeTest extends TestCase
{
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        Bootstrap::init_db();
    }

    public function testCompose()
    {
        $this->browse(static function ($browser) {
            $browser->go('mail');

            $browser->clickTaskMenuItem('compose');

            // check task and action
            $browser->with(new App(), static function ($browser) {
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
                    'uploadform',
                ]);
            });

            // Toolbar menu
            $browser->assertToolbarMenu(
                ['save.draft', 'responses', 'spellcheck'], // active items
                ['signature'], // inactive items
            );

            if ($browser->isPhone()) {
                $browser->assertToolbarMenu(['options'], []);
            } else {
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

            if ($browser->isPhone()) {
                $browser->click('#layout-sidebar a.back-content-button');
            }
        });
    }

    /**
     * @depends testCompose
     */
    #[Depends('testCompose')]
    public function testPlainEditor()
    {
        // Test for #7230: Shift+PageUp text selection
        // and copy-pasting with keyboard
        $this->browse(static function ($browser) {
            $browser->with(new HtmlEditor('composebodycontainer'), static function ($browser) {
                $browser->assertMode(HtmlEditor::MODE_PLAIN)
                    ->type('@plain-body', "line1\nline2\n")
                    ->keys('@plain-body', [WebDriverKeys::SHIFT, WebDriverKeys::PAGE_UP])
                    ->keys('@plain-body', [WebDriverKeys::CONTROL, 'c'])
                    ->keys('@plain-body', [WebDriverKeys::CONTROL, 'x'])
                    ->keys('@plain-body', [WebDriverKeys::CONTROL, 'v'])
                    ->keys('@plain-body', [WebDriverKeys::CONTROL, 'v'])
                    ->assertValue('@plain-body', "line1\nline2\nline1\nline2\n");
            });
        });

        // Test switching to HTML and back
        $this->browse(static function ($browser) {
            $browser->with(new HtmlEditor('composebodycontainer'), static function ($browser) {
                $browser->switchMode(HtmlEditor::MODE_HTML, true)
                    ->switchMode(HtmlEditor::MODE_PLAIN)
                    ->assertValue('@plain-body', "line1\nline2\nline1\nline2")
                    ->switchMode(HtmlEditor::MODE_HTML, false);
            });
        });
    }

    /**
     * @depends testCompose
     */
    #[Depends('testCompose')]
    public function testRecipientInput()
    {
        // Test for #7231: Recipient input bug when using click
        // to select a contact from autocomplete list
        $this->browse(static function ($browser) {
            $browser->with(new RecipientInput('#compose_to'), static function ($browser) {
                $browser->type('@input', 'johndoe@e')
                    ->withinBody(static function ($browser) {
                        $browser->whenAvailable('#rcmKSearchpane', static function ($browser) {
                            $browser->click('li:first-child');
                        });
                    })
                    ->waitFor('@recipient')
                    ->assertElementsCount('@recipient', 1)
                    ->assertRecipient(1, 'John Doe <johndoe@example.org>');
            });
        });
    }
}
