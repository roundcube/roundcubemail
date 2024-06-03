<?php

namespace Tests\MessageRendering;

/**
 * Test class to test "interesting" messages.
 */
class SingleAttachedImageNoTextTest extends MessageRenderingTestCase
{
    /**
     * Test that of a multipart/mixed message which contains only one
     * image, that image is shown. (GitHub issue #9443)
     */
    public function testShowMultipartMixedSingleImageToo(): void
    {
        $domxpath = $this->renderMessage('XXXXXXXXXXXXX@mx01.lytzenitmail.dk');

        $this->assertSame('Not OK', $this->getScrubbedSubject($domxpath));

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(1, $attchNames, 'Attachments');
        $this->assertStringStartsWith('Resized_20240427_200026(1).jpeg', $attchNames[0]->textContent);
    }

    /**
     * Test that an image, that has a Content-ID, which is not accompanied by
     * an HTML part (and thus is not referred to), is shown as attachment.
     * (GitHub issue #9565)
     */
    public function testShowUnreferredToImagesWithContentId(): void
    {
        $domxpath = $this->renderMessage('yyy@mail.gmail.com');

        $this->assertSame('test', $this->getScrubbedSubject($domxpath));

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(1, $attchNames, 'Attachments');
        $this->assertStringStartsWith('тест.jpg', $attchNames[0]->textContent);
    }

    /**
     * Test that an image, that has a Content-ID, but is not referred to in the
     * accompanying HTML-part, is shown as attachment. (GitHub issue #9685)
     */
    public function testShowUnreferredToImagesWithContentIdInMultipartAlternative(): void
    {
        $domxpath = $this->renderMessage('2ef37d1124655807449f5e405cdd4834b79fb026@example.net');

        $this->assertSame('Multipart/alternative with attached but unreferenced image', $this->getScrubbedSubject($domxpath));

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(1, $attchNames, 'Attachments');
        $this->assertStringStartsWith('Stg Aiki - SB - 22-23 Fév 2025.jpg', $attchNames[0]->textContent);
    }
}
