<?php

namespace Tests\MessageRendering;

/**
 * Test class to test simple messages.
 */
class BasicMessagesTest extends MessageRenderingTestCase
{
    /**
     * Test that two text mime-parts with disposition "attachment" are shown as
     * attachments.
     */
    public function testList00()
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath('99839b8ec12482419372f1edafa9de75@woodcrest.local');
        $this->assertSame('Lines', $this->getScrubbedSubject($domxpath));

        $this->assertStringStartsWith('Plain text message body.', $this->getBody($domxpath));

        $attchElems = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(2, $attchElems, 'Attachments');
        $this->assertStringStartsWith('lines.txt', $attchElems[0]->textContent);
        $this->assertStringStartsWith('lines_lf.txt', $attchElems[1]->textContent);
    }

    /**
     * Test that one inline image is not shown as attachment.
     */
    public function testList01()
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath('3ef8a0120cd7dc2fd776468c8515e29a@domain.tld');

        $this->assertSame('Test HTML with local and remote image', $this->getScrubbedSubject($domxpath));

        $this->assertSame("Attached image: \nRemote image:", $this->getBody($domxpath));

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(0, $attchNames, 'Attachments');
    }

    /**
     * Test that text parts are shown and also listed as attachments, and that
     * filenames are properly listed.
     */
    public function testFilename()
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath('de75@tester.local');

        $this->assertSame('Attachment filename encoding', $this->getScrubbedSubject($domxpath));

        $msgParts = $domxpath->query('//div[@class="message-part"]');
        $this->assertCount(3, $msgParts, 'Message text parts');

        $this->assertSame("foo\nbar\ngna", $msgParts[0]->textContent);
        $this->assertSame('潦੯慢ੲ湧', $msgParts[1]->textContent);
        $this->assertSame("foo\nbar\ngna", $msgParts[2]->textContent);

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(6, $attchNames, 'Attachments');

        $this->assertSame('A011.txt', $attchNames[0]->textContent);
        $this->assertSame('A012.txt', $attchNames[1]->textContent);
        $this->assertSame('A014.txt', $attchNames[2]->textContent);
        $this->assertSame('żółć.png', $attchNames[3]->textContent);
        $this->assertSame('żółć.png', $attchNames[4]->textContent);
        $this->assertSame('very very very very long very very very very long ćććććć very very very long name.txt', $attchNames[5]->textContent);
    }
}
