<?php

namespace Tests\Browser\Mail;

use Tests\Browser\Components\Toolbarmenu;

class ListTest extends \Tests\Browser\TestCase
{
    protected static $msgcount = 0;

    public static function setUpBeforeClass(): void
    {
        \bootstrap::init_imap(true);
        \bootstrap::purge_mailbox('INBOX');

        // import email messages
        foreach (glob(TESTS_DIR . 'data/mail/list_??.eml') as $f) {
            \bootstrap::import_message($f, 'INBOX');
            self::$msgcount++;
        }
    }

    public function testList()
    {
        $this->browse(function ($browser) {
            $browser->go('mail');

            $browser->waitUntilNotBusy()
                ->assertElementsCount('#messagelist tbody tr', self::$msgcount);

            // check message list
            $browser->assertVisible('#messagelist tbody tr:first-child.unread');

            $this->assertEquals('Test HTML with local and remote image',
                $browser->text('#messagelist tbody tr:first-child span.subject'));

            // Note: This element icon has width=0, use assertPresent() not assertVisible()
            $browser->assertPresent('#messagelist tbody tr:first-child span.msgicon.unread');

            // List toolbar menu
            $browser->assertVisible('#layout-list .header a.toolbar-button.refresh:not(.disabled)');

            if ($browser->isDesktop()) {
                $browser->with('#toolbar-list-menu', function ($browser) {
                    $browser->assertVisible('a.select:not(.disabled)');
                    $browser->assertVisible('a.options:not(.disabled)');

                    $imap = \bootstrap::get_storage();
                    if ($imap->get_threading()) {
                        $browser->assertVisible('a.threads:not(.disabled)');
                    }
                    else {
                        $browser->assertMissing('a.threads');
                    }
                });
            }
            else if ($browser->isTablet()) {
                $browser->click('.toolbar-list-button')
                    ->waitFor('#toolbar-list-menu');

                $browser->with('#toolbar-list-menu', function ($browser) {
                    $browser->assertVisible('a.select:not(.disabled)');
                    $browser->assertVisible('a.options:not(.disabled)');

                    $imap = \bootstrap::get_storage();
                    if ($imap->get_threading()) {
                        $browser->assertVisible('a.threads:not(.disabled)');
                    }
                    else {
                        $browser->assertMissing('a.threads');
                    }
                });

                $browser->click(); // hide the popup menu
            }
            else { // phone
                // On phones list options are in the toolbar menu
                $browser->with(new Toolbarmenu(), function ($browser) {
                    $active  = ['select', 'options'];
                    $missing = [];
                    $imap = \bootstrap::get_storage();

                    if ($imap->get_threading()) {
                        $active[] = 'threads';
                    }
                    else {
                        $missing[] = 'threads';
                    }

                    $browser->assertMenuState($active, [], $missing);
                });
            }
        });
    }

    /**
     * @depends testList
     */
    public function testListSelection()
    {
        $this->browse(function ($browser) {
            if ($browser->isPhone()) {
                $browser->with(new Toolbarmenu(), function ($browser) {
                    $browser->clickMenuItem('select');
                });
            }
            else if ($browser->isTablet()) {
                $browser->click('.toolbar-list-button');
                $browser->click('#toolbar-list-menu a.select');
            }
            else {
                $browser->click('#toolbar-list-menu a.select');
                $browser->assertFocused('#toolbar-list-menu a.select');
            }

            // Popup menu content
            $browser->waitFor('#listselect-menu');
            $browser->with('#listselect-menu', function($browser) {
                $browser->assertVisible('a.selection:not(.disabled)');
                $browser->assertVisible('a.select.all:not(.disabled)');
                $browser->assertVisible('a.select.page:not(.disabled)');
                $browser->assertVisible('a.select.unread:not(.disabled)');
                $browser->assertVisible('a.select.flagged:not(.disabled)');
                $browser->assertVisible('a.select.invert:not(.disabled)');
                $browser->assertVisible('a.select.none:not(.disabled)');
            });

            // Close the menu(s) by clicking the page body
            $browser->click();
            $browser->waitUntilMissing('#listselect-menu');

            // TODO: Test selection actions
        });
    }
}
