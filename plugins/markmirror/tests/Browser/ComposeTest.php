<?php

namespace Tests\Browser\Plugins\Markmirror;

use Roundcube\Tests\Browser\TestCase;

class ComposeTest extends TestCase
{
    public function testComposeUI()
    {
        $this->browse(static function ($browser) {
            $browser->go('mail');
            $browser->waitFor('#taskmenu .compose');
            $browser->click('#taskmenu .compose');
            $browser->waitFor('#compose-content .markmirror-start-button', 10);
            $browser->click('.markmirror-start-button');

            $browser->waitFor('#markmirror-container .cm-editor');
            $browser->assertVisible('#markmirror-container .cm-content');
            $browser->assertMissing('#markmirror-container #markmirror-preview');
            $browser->assertVisible('#markmirror-container .codemirror-toolbar');
            $browser->assertVisible('#markmirror-container .codemirror-toolbar .toolbar-button-quit');
            $browser->assertVisible('#markmirror-container .codemirror-toolbar .toolbar-button-bold');
            $browser->assertVisible('#markmirror-container .codemirror-toolbar .toolbar-button-italic');
            $browser->assertVisible('#markmirror-container .codemirror-toolbar .toolbar-button-blockquote');
            $browser->assertVisible('#markmirror-container .codemirror-toolbar .toolbar-button-help');
            $browser->assertVisible('#markmirror-container .codemirror-toolbar .toolbar-button-preview');
            $browser->assertElementsCount('#markmirror-container .codemirror-toolbar .clickable', 18);
            $browser->assertElementsCount('#markmirror-container .codemirror-toolbar .clickable.disabled', 0);

            // Test that clicking the preview button disables all but two toolbar buttons.
            $browser->click('#markmirror-container .codemirror-toolbar .toolbar-button-preview');
            $browser->assertVisible('#markmirror-container .codemirror-toolbar .toolbar-button-bold.disabled');
            $browser->assertElementsCount('#markmirror-container .codemirror-toolbar .clickable.disabled', 15);
            $browser->assertVisible('#markmirror-container #markmirror-preview');
            $browser->assertMissing('#markmirror-container .cm-content');

            // Test that clicking the preview button again re-enables all toolbar buttons.
            $browser->click('#markmirror-container .codemirror-toolbar .toolbar-button-preview');
            $browser->assertElementsCount('#markmirror-container .codemirror-toolbar .clickable.disabled', 0);
            $browser->assertMissing('#markmirror-container #markmirror-preview');
            $browser->assertVisible('#markmirror-container .cm-content');

            // Test the preview iframe.
            $browser->keys('#markmirror-container .cm-content', 'Hello, World!');
            $browser->click('#markmirror-container .codemirror-toolbar .toolbar-button-h1');
            $browser->assertSeeIn('#markmirror-container .cm-content .cm-line:first-child span:nth-child(1)', '#');
            $browser->assertSeeIn('#markmirror-container .cm-content .cm-line:first-child span:nth-child(2)', ' Hello, World!');
            $browser->click('#markmirror-container .codemirror-toolbar .toolbar-button-preview');
            $browser->withinFrame('#markmirror-container #markmirror-preview', static function ($browser) {
                $browser->assertSeeIn('h1', 'Hello, World!');
            });
            $browser->click('#markmirror-container .codemirror-toolbar .toolbar-button-preview');
            $browser->assertMissing('#markmirror-container #markmirror-preview');

            // Test that drafts are still plaintext/markdown.
            $browser->click('.header .menu .save.draft');
            $browser->waitForMessage('confirmation', 'Message saved to Drafts.');
            $browser->waitUntilMissing('#messagestack .confirmation', 10);
            // For some reason this only works if we click the button again.
            $browser->click('.header .menu .save.draft');
            $browser->waitForMessage('confirmation', 'Message saved to Drafts.');
            $browser->waitUntilMissing('#messagestack .confirmation', 10);
            $browser->click('#taskmenu .mail');
            if (!$browser->isDesktop()) {
                $browser->click('#messagelist-header .back-sidebar-button');
            }
            $browser->waitFor('#mailboxlist .mailbox.inbox.selected');
            $browser->waitUntilNotBusy();
            $browser->click('#mailboxlist .mailbox.drafts a');
            if ($browser->isDesktop()) {
                $browser->waitFor('#mailboxlist .mailbox.drafts.selected');
            }
            $browser->waitUntilNotBusy();
            $browser->waitFor('#messagelist .message:first-child');
            $browser->click('#messagelist .message:first-child');
            $browser->waitFor('#messagecontframe');
            $browser->withinFrame('#messagecontframe', static function ($browser) {
                $browser->waitFor('#message-content #messagebody');
                $browser->assertSeeIn('#message-content #messagebody', '# Hello, World!');
            });

            // Test that the editor starts automatically for a message that we had edited with it.
            $browser->withinFrame('#messagecontframe', static function ($browser) {
                $browser->press('#message-buttons .btn');
            });
            $browser->waitFor('#markmirror-container .cm-content');
            $browser->assertSeeIn('#markmirror-container .cm-content .cm-line:first-child', '# Hello, World!');

            // Test that sent messages are converted to HTML.
            $browser->keys('#compose_to input', 'user@example.com');
            $browser->keys('#compose_subject input', 'markmirror test');
            $browser->click('#compose-content .btn.send');
            if (!$browser->isDesktop()) {
                $browser->waitFor('#messagelist-header .back-sidebar-button');
                $browser->click('#messagelist-header .back-sidebar-button');
            }
            $browser->waitFor('#mailboxlist .mailbox.drafts.selected');
            $browser->waitUntilNotBusy();
            $browser->click('#mailboxlist .mailbox.sent a');
            if ($browser->isDesktop()) {
                $browser->waitFor('#mailboxlist .mailbox.sent.selected');
            }
            $browser->waitFor('#messagelist .message:first-child');
            $browser->click('#messagelist .message:first-child');
            $browser->waitFor('#messagecontframe');
            $browser->withinFrame('#messagecontframe', static function ($browser) {
                $browser->waitFor('#message-content #messagebody');
                $browser->assertSeeIn('#message-content #messagebody h1', 'Hello, World!');
            });
        });
    }
}
