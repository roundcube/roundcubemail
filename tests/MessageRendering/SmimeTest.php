<?php

namespace Tests\MessageRendering;

/**
 * Test class to test S/MIME messages
 */
class SmimeTest extends MessageRenderingTestCase
{
    /**
     * Test that the content of an opaque signed message (smime-type=signed-data) is shown,
     * and its attachment is listed (#10398)
     */
    public function testSignedData()
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath('smime-signed-data@example.test');

        $this->assertSame('S/MIME opaque signed message', $this->getScrubbedSubject($domxpath));
        $this->assertSame('This message is signed, not encrypted. Grüße!', $this->getBody($domxpath));

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(1, $attchNames, 'Attachments');
        $this->assertStringStartsWith('notes.txt', $attchNames[0]->textContent);

        $this->assertCount(0, $domxpath->query('//span[@class="part-notice"]'), 'Notices');
    }

    /**
     * Test an opaque signed text message with the old application/x-pkcs7-mime type (#10398)
     */
    public function testSignedText()
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath('smime-signed-text@example.test');

        $this->assertSame('A signed plain text message.', $this->getBody($domxpath));
        $this->assertCount(0, $domxpath->query('//span[@class="attachment-name"]'), 'Attachments');
        $this->assertCount(0, $domxpath->query('//span[@class="part-notice"]'), 'Notices');
    }

    /**
     * Test that an encrypted message (smime-type=enveloped-data) is still shown as such
     */
    public function testEnvelopedData()
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath('smime-enveloped-data@example.test');

        $this->assertSame('This is an encrypted message and can not be displayed. Sorry!', $this->getBody($domxpath));

        $attchNames = $domxpath->query('//span[@class="attachment-name"]');
        $this->assertCount(1, $attchNames, 'Attachments');
        $this->assertStringStartsWith('smime.p7m', $attchNames[0]->textContent);
    }
}
