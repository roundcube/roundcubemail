<?php

namespace Tests\MessageRendering;

/**
 * Test class to test simple messages.
 */
class MarkdownTest extends MessageRenderingTestCase
{
    public function testMarkdownContent()
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath('e40d23f5d4b928f1536699b0723fa4a84ef3467d76ecbcdc361e8c394c6675a3@example.net');

        $this->assertSame('Markdown', $this->getScrubbedSubject($domxpath));

        $msgParts = $domxpath->query('//div[@class="message-part"]');
        $this->assertCount(1, $msgParts, 'Message text parts');

        $paragraphs = $domxpath->query('//div[@class="message-part"]//p');
        $this->assertCount(2, $paragraphs);

        $html = $paragraphs[0]->ownerDocument->saveHTML($paragraphs[0]);
        $this->assertSame('<p><strong>Hello!</strong></p>', $html);

        $html = $paragraphs[1]->ownerDocument->saveHTML($paragraphs[1]);
        $this->assertSame("<p>I'm <em>really</em> happy that you're <em>reading</em> this!</p>", $html);

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(0, $attchNames, 'Attachments');
    }

    public function testPlaintextAndMarkdownContent()
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath('60fb477df7365015fea1b6adc4e85d3dec0571f3260d609768f3427e6bfc8f61@example.net');

        $this->assertSame('Plaintext and markdown', $this->getScrubbedSubject($domxpath));

        $msgParts = $domxpath->query('//div[@class="message-part"]');
        $this->assertCount(2, $msgParts, 'Message text parts');

        $this->assertSame('Please read the attached markdown file.', $msgParts[0]->textContent);

        $paragraphs = $domxpath->query('//div[@class="message-part"]//p');
        $this->assertCount(2, $paragraphs);

        $html = $paragraphs[0]->ownerDocument->saveHTML($paragraphs[0]);
        $this->assertSame('<p><strong>Hello!</strong></p>', $html);

        $html = $paragraphs[1]->ownerDocument->saveHTML($paragraphs[1]);
        $this->assertSame("<p>I'm <em>really</em> happy that you're <em>reading</em> this!</p>", $html);

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(1, $attchNames, 'Attachments');
        $this->assertSame('test.md', $attchNames[0]->textContent);
    }

    public function testPlaintextWithMarkdownAttachment()
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath('76fc626530d3253af13591c298d887acb801b440cdf3458da1882d667b8220aa@example.net');

        $this->assertSame('Plaintext with markdown attachment', $this->getScrubbedSubject($domxpath));

        $msgParts = $domxpath->query('//div[@class="message-part"]');
        $this->assertCount(1, $msgParts, 'Message text parts');

        $this->assertSame('Please read the attached markdown file.', $msgParts[0]->textContent);

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(1, $attchNames, 'Attachments');
        $this->assertSame('test.md', $attchNames[0]->textContent);
    }
}
