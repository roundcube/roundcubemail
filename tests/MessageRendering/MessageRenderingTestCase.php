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
    protected function renderIframedBodyContent(array $params): \DOMXPath
    {
        return $this->runActionAndGetHtmlOutputDomxpath('get', function ($storage) use ($params) {
            // rcmail_action_mail_get calls rcmail_attachment_handler, which
            // closes one layer of output buffering, probably because index.php
            // always starts one and rcmail_attachment_handler wants to close
            // that. In order to avoid our tests being "risky" and thus marked
            // as problematic, we need to simulate the opened output buffer
            // layer.
            ob_start();
            $action = new \rcmail_action_mail_get();
            $this->assertTrue($action->checks());
            $_GET = $params;
            $action->run();
        });
    }

    protected function renderMessage(string $msgId): \DOMXPath
    {
        return $this->runActionAndGetHtmlOutputDomxpath('preview', function ($storage) use ($msgId) {
            $action = new \rcmail_action_mail_show();
            $this->assertTrue($action->checks());

            $messagesList = $storage->list_messages();

            // Find the UID of the wanted message.
            $messageUid = null;
            // TODO: find a method that scales better.
            foreach ($messagesList as $messageHeaders) {
                if ($messageHeaders->get('message-id') === "<{$msgId}>") {
                    $messageUid = $messageHeaders->uid;
                    break;
                }
            }
            if ($messageUid === null) {
                throw new \Exception("No message found in messages list with Message-Id '{$msgId}'");
            }

            $_GET = ['_uid' => $messageUid];
            $action->run();
        });
    }

    protected function runActionAndGetHtmlOutputDomxpath(string $urlActionParam, \Closure $callback): \DOMXPath
    {
        $imap_host = getenv('RC_CONFIG_IMAP_HOST') ?: 'tls://localhost:143';
        $rcmail = \rcmail::get_instance();
        // We need to overwrite the storage object, else storage_init() just
        // returns the cached one (which might be a StorageMock instance).
        $mockStorage = $rcmail->storage;
        $rcmail->storage = null;
        $rcmail->storage_init();
        // Login our test user so we can fetch messages from the imap server.
        $rcmail->login('test-message-rendering@localhost', 'pass', $imap_host);
        $storage = $rcmail->get_storage();
        $storage->set_options(['all_headers' => true]);
        // We need to set the folder, else no message can be fetched.
        $storage->set_folder('INBOX');

        $this->initOutput(\rcmail_action::MODE_HTTP, 'mail', $urlActionParam);
        // TODO: Why do we need to set the skin manually?
        $rcmail->output->set_skin('elastic');

        // Prepare and trigger the rendering.
        $html = '';
        try {
            $callback($storage);
        } catch (ExitException $e) {
            // phpstan complains that the output classes of the production code
            // don't provide that method – which is true, but not relevant
            // here.
            // @phpstan-ignore-next-line
            $html = $rcmail->output->getOutput();
        }

        // Reset the storage to the mocked one most other tests expect.
        $rcmail->storage = $mockStorage;

        // disabled_html_ns=true is a workaround for the performance issue
        // https://github.com/Masterminds/html5-php/issues/181
        $html5 = new HTML5(['disable_html_ns' => true]);
        return new \DOMXPath($html5->loadHTML($html));
    }

    protected function getSrcParams(\DOMNode $elem): array
    {
        $src = $elem->attributes->getNamedItem('src')->textContent;
        $url = parse_url($src, \PHP_URL_QUERY);
        parse_str($url, $params);
        return $params;
    }

    protected function getIframedContent(array $params): string
    {
        $domxpath_body = $this->renderIframedBodyContent($params);
        $bodyElem = $domxpath_body->query('//body');
        $this->assertCount(1, $bodyElem, 'Message body');
        return $bodyElem[0]->textContent;
    }

    protected function assertIframedContent(\DOMNode $elem, string $partId, string $content): void
    {
        $params = $this->getSrcParams($elem);
        $this->assertSrcUrlParams($params, $partId);
        $body = $this->getIframedContent($params);
        $this->assertSame($content, trim($body));
    }

    private function assertSrcUrlParams(array $params, string $partId): void
    {
        $this->assertSame('mail', $params['_task']);
        $this->assertSame('get', $params['_action']);
        $this->assertSame('INBOX', $params['_mbox']);
        $this->assertMatchesRegularExpression('/^\d+$/', $params['_uid']);
        $this->assertSame($partId, $params['_part']);
    }
}
