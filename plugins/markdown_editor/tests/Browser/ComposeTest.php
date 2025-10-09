<?php

namespace Tests\Browser\Plugins\MarkdownEditor;

use Roundcube\Tests\Browser\TestCase;

class ComposeTest extends TestCase
{
    public function testComposeUI()
    {
        $this->browse(static function ($browser) {
            $browser->go('mail');
            $browser->waitFor('#taskmenu .compose');
            $browser->click('#taskmenu .compose');
            $browser->waitFor('#compose-content .markdown-editor-start-button', 10);
            $browser->click('.markdown-editor-start-button');

            $browser->waitFor('#markdown-editor-container .cm-editor');
            $browser->assertVisible('#markdown-editor-container .cm-content');
            $browser->assertMissing('#markdown-editor-container #markdown-editor-preview');
            $browser->assertVisible('#markdown-editor-container .codemirror-toolbar');
            $browser->assertVisible('#markdown-editor-container .codemirror-toolbar .toolbar-button-quit');
            $browser->assertVisible('#markdown-editor-container .codemirror-toolbar .toolbar-button-bold');
            $browser->assertVisible('#markdown-editor-container .codemirror-toolbar .toolbar-button-italic');
            $browser->assertVisible('#markdown-editor-container .codemirror-toolbar .toolbar-button-blockquote');
            $browser->assertVisible('#markdown-editor-container .codemirror-toolbar .toolbar-button-help');
            $browser->assertVisible('#markdown-editor-container .codemirror-toolbar .toolbar-button-preview');
            $browser->assertElementsCount('#markdown-editor-container .codemirror-toolbar .clickable', 16);
            $browser->assertElementsCount('#markdown-editor-container .codemirror-toolbar .clickable.disabled', 0);

            // Test that clicking the preview button disables all but two toolbar buttons.
            $browser->click('#markdown-editor-container .codemirror-toolbar .toolbar-button-preview');
            $browser->assertVisible('#markdown-editor-container .codemirror-toolbar .toolbar-button-bold.disabled');
            $browser->assertElementsCount('#markdown-editor-container .codemirror-toolbar .clickable.disabled', 13);
            $browser->assertVisible('#markdown-editor-container #markdown-editor-preview');
            $browser->assertMissing('#markdown-editor-container .cm-content');

            // Test that clicking the preview button again re-enables all toolbar buttons.
            $browser->click('#markdown-editor-container .codemirror-toolbar .toolbar-button-preview');
            $browser->assertElementsCount('#markdown-editor-container .codemirror-toolbar .clickable.disabled', 0);
            $browser->assertMissing('#markdown-editor-container #markdown-editor-preview');
            $browser->assertVisible('#markdown-editor-container .cm-content');

            // Test the preview iframe.
            $browser->keys('#markdown-editor-container .cm-content', 'Hello, World!');
            $browser->click('#markdown-editor-container .codemirror-toolbar .toolbar-button-h1');
            $browser->assertSeeIn('#markdown-editor-container .cm-content .cm-line:first-child span:nth-child(1)', '#');
            $browser->assertSeeIn('#markdown-editor-container .cm-content .cm-line:first-child span:nth-child(2)', 'Hello, World!');
            $browser->click('#markdown-editor-container .codemirror-toolbar .toolbar-button-preview');
            $browser->withinFrame('#markdown-editor-container #markdown-editor-preview', static function ($browser) {
                $browser->assertSeeIn('h1', 'Hello, World!');
            });
            $browser->click('#markdown-editor-container .codemirror-toolbar .toolbar-button-preview');
            $browser->assertMissing('#markdown-editor-container #markdown-editor-preview');

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
            $browser->waitFor('#markdown-editor-container .cm-content');
            $browser->assertSeeIn('#markdown-editor-container .cm-content .cm-line:first-child', '# Hello, World!');

            // Test that sent messages are converted to HTML.
            $browser->keys('#compose_to input', 'user@example.com');
            $browser->keys('#compose_subject input', 'markdown-editor test');
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
