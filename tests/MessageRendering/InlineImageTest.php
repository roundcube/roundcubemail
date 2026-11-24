<?php

namespace Tests\MessageRendering;

/**
 * Test class to test simple messages.
 */
class InlineImageTest extends MessageRenderingTestCase
{
    public function testImageFromDataUri()
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath('trinity-eb9e559b-1926-4b09-990d-80e9da9a9c35-1723163091112@3c-app-mailcom-bs14');

        $this->assertSame('***SPAM***  wir gratulieren Ihnen recht herzlich.', $this->getScrubbedSubject($domxpath));

        $divElements = $domxpath->query('//div[@class="rcmBody"]/div/div');
        $this->assertCount(3, $divElements, 'Body HTML DIV elements');

        $this->assertSame('wir gratulieren Ihnen recht herzlich.', $divElements[0]->textContent);

        $img = $divElements[1]->firstChild->firstChild;
        $this->assertSame('img', $img->nodeName);
        $src = $img->attributes->getNamedItem('src')->textContent;
        $this->assertStringContainsString('?_task=mail&_action=get&_mbox=INBOX&_uid=', $src);
        $this->assertStringContainsString('&_part=2&_embed=1&_mimeclass=image', $src);

        $this->assertSame('v1signature', $divElements[2]->attributes->getNamedItem('class')->textContent);
        // This matches a non-breakable space.
        $this->assertMatchesRegularExpression('|^\x{00a0}$|u', $divElements[2]->textContent);

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(0, $attchNames, 'Attachments');
    }

    /**
     * Test that images get the MIME headers of their own part: the Content-Location
     * an image is referred to by, and an RFC2231 file name (which the IMAP server
     * may return unjoined in BODYSTRUCTURE)
     */
    public function testImagesMimeHeaders()
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath('inline-images-content-location@example.net');

        $this->assertSame('Inline images referred to by Content-Location', $this->getScrubbedSubject($domxpath));

        $images = $domxpath->query('//div[@class="rcmBody"]//img');
        $this->assertCount(2, $images, 'Body images');
        $this->assertStringContainsString('&_part=2&_embed=1&_mimeclass=image', $images[0]->attributes->getNamedItem('src')->textContent);
        $this->assertStringContainsString('&_part=3&_embed=1&_mimeclass=image', $images[1]->attributes->getNamedItem('src')->textContent);

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(1, $attchNames, 'Attachments');
        $this->assertSame('Șir lung de caractere în nume.png', $attchNames[0]->textContent);
    }
}
