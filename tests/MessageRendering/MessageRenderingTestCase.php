<?php

namespace Tests\MessageRendering;

use Masterminds\HTML5;
use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\ExitException;

/**
 * Class to base actual test classes on, which test specific message rendering.
 */
class MessageRenderingTestCase extends ActionTestCase
{
    /**
     * Get the body from the document, trimmed from surrounding whitespace.
     */
    protected function getBody(\DOMXPath $domxpath): string
    {
        $bodyElem = $domxpath->query('//div[@id="messagebody"]');
        $this->assertCount(1, $bodyElem, 'Message body');
        return trim($bodyElem[0]->textContent);
    }

    /**
     * Get the subject from the document, stripped by the prefix "Subject: ",
     * the suffix "Open in new window", and trimmed from surrounding whitespace.
     */
    protected function getScrubbedSubject(\DOMXPath $domxpath): string
    {
        $subjectElem = $domxpath->query('//h2[@class="subject"][1]');
        $subject = preg_replace('/^\s*Subject:\s*(.*)\s*Open in new window$/', '$1', trim($subjectElem[0]->textContent));
        return trim($subject);
    }

    /**
     * Execute run() to render the message with the given $msgId.
     *
     * This is useful to check how rcmail_action_mail_show() renders messages.
     * It requires a running dovecot to fetch the messages from. Messages need
     * to be placed as individual files in
     * `tests/src/emails/test@example/Mail/cur/`.
     */
    protected function runAndGetHtmlOutputDomxpath(string $msgId): \DOMXPath
    {
        $imap_host = getenv('RC_CONFIG_IMAP_HOST') ?: 'tls://localhost:143';
        $rcmail = \rcmail::get_instance();
        // We need to overwrite the storage object, else storage_init() just
        // returns the cached one (which might be a StorageMock instance).
        $mockStorage = $rcmail->storage = null;
        $rcmail->storage_init();
        // Login our test user so we can fetch messages from the imap server.
        $rcmail->login('test-message-rendering@localhost', 'pass', $imap_host);
        $storage = $rcmail->get_storage();
        $storage->set_options(['all_headers' => true]);
        // We need to set the folder, else no message can be fetched.
        $storage->set_folder('INBOX');
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'mail', 'preview');
        // TODO: Why do we need to set the skin manually?
        $output->set_skin('elastic');

        $action = new \rcmail_action_mail_show();
        $this->assertTrue($action->checks());

        $messagesList = $storage->list_messages();

        // Find the UID of the wanted message.
        $messageUid = null;
        foreach ($messagesList as $messageHeaders) {
            if ($messageHeaders->get('message-id') === "<{$msgId}>") {
                $messageUid = $messageHeaders->uid;
                break;
            }
        }
        if ($messageUid === null) {
            throw new \Exception("No message found in messages list with Message-Id '{$msgId}'");
        }

        // Prepare and trigger the rendering.
        $_GET = ['_uid' => $messageUid];
        $html = '';
        try {
            $action->run();
        } catch (ExitException $e) {
            $html = $output->getOutput();
        }

        // Reset the storage to the mocked one most other tests expect.
        $rcmail->storage = $mockStorage;

        // disabled_html_ns=true is a workaround for the performance issue
        // https://github.com/Masterminds/html5-php/issues/181
        $html5 = new HTML5(['disable_html_ns' => true]);
        return new \DOMXPath($html5->loadHTML($html));
    }
}
