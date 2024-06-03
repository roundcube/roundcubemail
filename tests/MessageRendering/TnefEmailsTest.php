<?php

namespace Tests\MessageRendering;

/**
 * Test class to test "interesting" messages.
 */
class TnefEmailsTest extends MessageRenderingTestCase
{
    public function testTnefEmail1(): void
    {
        $domxpath = $this->renderMessage('631a672e15f742a98035f1cb7efe1f8db6310138@example.net');

        $this->assertSame('', $this->getBody($domxpath));

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(1, $attchNames, 'Attachments');
        $this->assertStringStartsWith('AUTHORS', $attchNames[0]->textContent);
    }

    public function testTnefEmail2(): void
    {
        $domxpath = $this->renderMessage('b6057653610f8041b120965652ff7f26a1a8f02d@example.net');

        $this->assertStringStartsWith('THE BILL OF RIGHTSAmendments 1-10 of the', $this->getBody($domxpath));

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(0, $attchNames, 'Attachments');
    }

    public function testTnefEmail3(): void
    {
        $domxpath = $this->renderMessage('cde7964538f283305609ec9146b4a80c121fd0ae@example.net');

        $bodyParagraphs = $domxpath->query('//div[@class="rcmBody"]/p');
        $this->assertCount(8, $bodyParagraphs, 'Body HTML paragraphs');
        $this->assertSame('Casdasdfasdfasd', $bodyParagraphs[0]->textContent);
        $this->assertSame('Casdasdfasdfasd', $bodyParagraphs[1]->textContent);
        $this->assertSame('Casdasdfasdfasd', $bodyParagraphs[2]->textContent);
        $this->assertSame('Casdasdfasdfasd', $bodyParagraphs[3]->textContent);
        $this->assertSame('Casdasdfasdfasd', $bodyParagraphs[4]->textContent);
        $this->assertSame(' ', $bodyParagraphs[5]->textContent);
        $this->assertSame('Casdasdfasdfasd', $bodyParagraphs[6]->textContent);
        $this->assertSame(' ', $bodyParagraphs[7]->textContent);

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(2, $attchNames, 'Attachments');
        $this->assertStringStartsWith('zappa_av1.jpg', $attchNames[0]->textContent);
        $this->assertStringStartsWith('bookmark.htm', $attchNames[1]->textContent);

        $inlineShownImages = $domxpath->query('//p[@class="image-attachment"]/span[@class="image-filename"]');
        $this->assertCount(1, $inlineShownImages, 'Inline shown images');
        $this->assertSame('zappa_av1.jpg', $inlineShownImages[0]->textContent);
    }
}
