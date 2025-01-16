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
    public function testShowMultipartMixedSingleImageToo()
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath('XXXXXXXXXXXXX@mx01.lytzenitmail.dk');

        $this->assertSame('Not OK', $this->getScrubbedSubject($domxpath));

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(1, $attchNames, 'Attachments');
        $this->assertStringStartsWith('Resized_20240427_200026(1).jpeg', $attchNames[0]->textContent);
    }
}
