<?php

namespace Tests\MessageRendering;

/**
 * Test class to test simple messages.
 */
class InlineImageTest extends MessageRenderingTestCase
{
    public function testImageFromDataUri(): void
    {
        $domxpath = $this->renderMessage('trinity-eb9e559b-1926-4b09-990d-80e9da9a9c35-1723163091112@3c-app-mailcom-bs14');

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
}
