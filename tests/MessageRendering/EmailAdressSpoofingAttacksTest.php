<?php

namespace Tests\MessageRendering;

/**
 * Test class to test simple messages.
 */
class EmailAdressSpoofingAttacksTest extends MessageRenderingTestCase
{
    /**
     * Test that two text mime-parts with disposition "attachment" are shown as
     * attachments.
     */
    public function testEmailAdressSpoofingAttacks()
    {
        $domxpath = $this->runAndGetHtmlOutputDomxpath('1176148a1ed73947311a08a3fc11264e64d8f775eae596e5fa660cfd1684a5e6@example.net');
        $this->assertSame('Email address spoofing attacks', $this->getScrubbedSubject($domxpath));

        $suspiciousAddressWarnings = $domxpath->query('//span[@class="suspicious-address-warning"]');
        // It should be present three times: two times for the phishing attack in the From header (once in the header
        // summary element, once in the header details element), one time for the homograph attack in the To header.
        $this->assertCount(3, $suspiciousAddressWarnings, 'Sender phishing warning');
        $expectedMsg = 'This message contains suspicious email addresses that may be fraudulent.';
        $this->assertSame($expectedMsg, $suspiciousAddressWarnings[0]->attributes->getNamedItem('title')->textContent);
        $this->assertSame($expectedMsg, $suspiciousAddressWarnings[1]->attributes->getNamedItem('title')->textContent);
        $this->assertSame($expectedMsg, $suspiciousAddressWarnings[2]->attributes->getNamedItem('title')->textContent);
    }
}
