<?php

namespace Tests\MessageRendering;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class to test simple messages.
 */
class InlineImageTest extends MessageRenderingTestCase
{
    /**
     * Test that an image referenced from the HTML part is listed when the
     * message is displayed as plain text, as the body does not show it then.
     */
    public function testImageFromDataUriInPlainTextView()
    {
        $rcmail = \rcmail::get_instance();
        $preferHtml = $rcmail->config->get('prefer_html');
        $rcmail->config->set('prefer_html', false);

        try {
            $domxpath = $this->runAndGetHtmlOutputDomxpath('trinity-eb9e559b-1926-4b09-990d-80e9da9a9c35-1723163091112@3c-app-mailcom-bs14');
        } finally {
            $rcmail->config->set('prefer_html', $preferHtml);
        }

        $this->assertCount(0, $domxpath->query('//div[@id="messagebody"]//img[not(@class="image-thumbnail")]'), 'Body images');
        $this->assertCount(1, $domxpath->query('//span[@class="attachment-name"]'), 'Attachments');
        $this->assertCount(1, $domxpath->query('//p[@class="image-attachment"]'), 'Images below the body');
    }

    /**
     * Test that images referenced from an HTML part inside a
     * multipart/alternative are shown in the body only (#10347).
     *
     * @dataProvider provide_ImagesReferencedFromMultipartAlternative_cases
     */
    #[DataProvider('provide_ImagesReferencedFromMultipartAlternative_cases')]
    public function testImagesReferencedFromMultipartAlternative($msgId, $parts, $attachments)
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath($msgId);

        $imgs = $domxpath->query('//div[@class="rcmBody"]//img');
        $this->assertCount(count($parts), $imgs, 'Body images');
        foreach ($parts as $i => $part) {
            $src = $imgs[$i]->attributes->getNamedItem('src')->textContent;
            $this->assertStringContainsString("&_part={$part}&_embed=1&_mimeclass=image", $src);
        }

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(count($attachments), $attchNames, 'Attachments');
        foreach ($attachments as $i => $name) {
            $this->assertStringStartsWith($name, $attchNames[$i]->textContent);
        }

        $this->assertCount(0, $domxpath->query('//p[@class="image-attachment"]'), 'Images below the body');
    }

    /**
     * Test data for testImagesReferencedFromMultipartAlternative()
     */
    public static function provide_ImagesReferencedFromMultipartAlternative_cases(): iterable
    {
        return [
            // multipart/related: multipart/alternative, image (e.g. Gmail, Outlook)
            ['10347-related-alternative@example.net', ['2'], []],
            // multipart/mixed: multipart/alternative, images (e.g. Yahoo Mail)
            ['10347-mixed-alternative@example.net', ['2', '3'], []],
            // multipart/mixed: multipart/related (multipart/alternative, image), file (e.g. Outlook)
            ['10347-mixed-related-alternative@example.net', ['1.2'], ['notes.txt']],
        ];
    }

    /**
     * Test that an image the HTML part refers to only where washing removes the
     * reference (image-set) is listed below the body, with or without inline_images.
     */
    public function testImageWithRemovedReference()
    {
        $rcmail = \rcmail::get_instance();
        $inlineImages = $rcmail->config->get('inline_images');

        foreach ([true, false] as $inline) {
            $rcmail->config->set('inline_images', $inline);

            try {
                $domxpath = $this->runAndGetHtmlOutputDomxpath('removed-reference@example.net');
            } finally {
                $rcmail->config->set('inline_images', $inlineImages);
            }

            $imgs = $domxpath->query('//div[@class="rcmBody"]//img');
            $this->assertCount(1, $imgs, 'Body images');
            $this->assertStringContainsString('&_part=2&_embed=1&_mimeclass=image', $imgs[0]->attributes->getNamedItem('src')->textContent);
            $this->assertCount(0, $domxpath->query('//span[@class="attachment-name"]'), 'Attachments');

            $below = $domxpath->query('//p[@class="image-attachment"]');
            $this->assertCount(1, $below, 'Images below the body');
            $this->assertStringNotContainsString('display:none', $below[0]->getAttribute('style'));
            $this->assertCount($inline ? 1 : 0, $domxpath->query('.//img[@class="image-thumbnail"]', $below[0]), 'Thumbnail');
            $this->assertStringContainsString('&_part=3&_download=1', $domxpath->query('.//a[@class="download"]', $below[0])[0]->attributes->getNamedItem('href')->textContent);
            $this->assertSame(['3' => 'image/png'], $rcmail->output->get_env('attachments'));
        }
    }

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
}
