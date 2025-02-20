<?php

namespace Tests\MessageRendering;

/**
 * Test class to test "interesting" messages.
 */
class SingleImageNoTextTest extends MessageRenderingTestCase
{
    /**
     * Test that of a multipart/mixed message which contains only one
     * image, that image is shown.
     */
    public function testShowMultipartMixedSingleImageToo(): void
    {
        $this->markTestSkipped('TBD: test for fixing GH issue 9443');
        // This next comment line prevents phpstan from reporting this as
        // unreachable code (technically it is right, but that's on purpose
        // here...).
        // @phpstan-ignore-next-line
        $domxpath = $this->renderMessage('XXXXXXXXXXXXX@mx01.lytzenitmail.dk');

        $this->assertSame('Not OK', $this->getScrubbedSubject($domxpath));

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(1, $attchNames, 'Attachments');
        $this->assertStringStartsWith('Resized_20240427_200026(1).jpeg', $attchNames[0]->textContent);

        $shownImages = $domxpath->query('//span[@class="image-filename"]');
        $this->assertCount(1, $attchNames, 'Shown images');
        $this->assertSame('Resized_20240427_200026(1).jpeg', $shownImages[0]->textContent);
    }
}
