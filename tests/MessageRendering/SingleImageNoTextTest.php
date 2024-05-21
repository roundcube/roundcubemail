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
    public function test_show_multipart_mixed_single_image_too()
    {
        $this->markTestIncomplete('TBD: test for fixing GH issue 9443');

        $domxpath = $this->run_and_get_html_output_domxpath('XXXXXXXXXXXXX@mx01.lytzenitmail.dk');

        $this->assertSame('Not OK', $this->getScrubbedSubject($domxpath));

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(1, $attchNames, 'Attachments');
        $this->assertStringStartsWith('Resized_20240427_200026(1).jpeg', $attchNames[0]->textContent);
    }
}
