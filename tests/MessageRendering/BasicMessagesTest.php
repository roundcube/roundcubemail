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
        $domxpath = $this->renderMessage('99839b8ec12482419372f1edafa9de75@woodcrest.local');
        $this->assertSame('Lines', $this->getScrubbedSubject($domxpath));

        $bodyParts = $domxpath->query('//iframe[contains(@class, "framed-message-part")]');
        $this->assertCount(1, $bodyParts, 'Message body parts');

        $this->assertIframedContent($bodyParts[0], 1, "Plain text message body.\n\n--\u{00A0}\nDeveloper of Free Software\nSent with Roundcube Webmail - roundcube.net");

        $attchElems = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(2, $attchElems, 'Attachments');
        $this->assertStringStartsWith('lines.txt', $attchElems[0]->textContent);
        $this->assertStringStartsWith('lines_lf.txt', $attchElems[1]->textContent);

        $shownImages = $domxpath->query('//span[@class="image-filename"]');
        $this->assertCount(0, $shownImages, 'Shown images');
    }

    /**
     * Test that one inline image is not shown as attachment.
     */
    public function testList01()
    {
        $domxpath = $this->renderMessage('3ef8a0120cd7dc2fd776468c8515e29a@domain.tld');

        $this->assertSame('Test HTML with local and remote image', $this->getScrubbedSubject($domxpath));

        $bodyParts = $domxpath->query('//iframe[contains(@class, "framed-message-part")]');
        $this->assertCount(1, $bodyParts, 'Message body parts');
        $this->assertIframedContent($bodyParts[0], '2.1', "Attached image: \nRemote image:");

        $attchElems = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(0, $attchElems, 'Attachments');

        $shownImages = $domxpath->query('//span[@class="image-filename"]');
        $this->assertCount(0, $shownImages, 'Shown images');
    }

    /**
     * Test that text parts are shown and also listed as attachments, and that
     * filenames are properly listed.
     */
    public function testFilename()
    {
        $domxpath = $this->renderMessage('de75@tester.local');

        $this->assertSame('Attachment filename encoding', $this->getScrubbedSubject($domxpath));

        $bodyParts = $domxpath->query('//iframe[contains(@class, "framed-message-part")]');
        $this->assertCount(3, $bodyParts, 'Message body parts');

        $this->assertIframedContent($bodyParts[0], '1', "foo\nbar\ngna");
        // TODO: This fails, because the rendered body content is wrong – why?
        $this->assertIframedContent($bodyParts[1], '2', '潦੯慢ੲ湧');

        $this->assertIframedContent($bodyParts[2], '6', "foo\nbar\ngna");

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(6, $attchNames, 'Attachments');

        $this->assertSame('A011.txt', $attchNames[0]->textContent);
        $this->assertSame('A012.txt', $attchNames[1]->textContent);
        $this->assertSame('A014.txt', $attchNames[2]->textContent);
        $this->assertSame('żółć.png', $attchNames[3]->textContent);
        $this->assertSame('żółć.png', $attchNames[4]->textContent);
        $this->assertSame('very very very very long very very very very long ćććććć very very very long name.txt', $attchNames[5]->textContent);

        $shownImages = $domxpath->query('//span[@class="image-filename"]');
        $this->assertCount(2, $shownImages, 'Shown images');
        $this->assertSame('żółć.png', $shownImages[0]->textContent);
        $this->assertSame('żółć.png', $shownImages[1]->textContent);
    }
}
