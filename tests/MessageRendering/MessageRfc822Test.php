<?php

namespace Tests\MessageRendering;

/**
 * Test class to test simple messages.
 */
class MessageRfc822Test extends MessageRenderingTestCase
{
    public function testRfc822Part(): void
    {
        $domxpath = $this->renderMessage('4052c2097de93825c1be040270d98e47@example.net');
        $this->assertSame('Fwd: Lines', $this->getScrubbedSubject($domxpath));

        $parts = $domxpath->query('//div[@id="messagebody"]/div');
        $this->assertCount(5, $parts);
        $this->assertSame('message-prefix', $parts[0]->attributes->getNamedItem('class')->textContent);
        $this->assertEmpty($parts[0]->textContent);
        $this->assertSame('message-part', $parts[1]->attributes->getNamedItem('class')->textContent);
        $this->assertSame('Loading data...', $parts[1]->textContent);
        $this->assertSame('message-partheaders', $parts[2]->attributes->getNamedItem('class')->textContent);
        // The content of this part is tested in more detail below.
        $this->assertNotEmpty($parts[2]->textContent);
        $this->assertSame('message-prefix', $parts[3]->attributes->getNamedItem('class')->textContent);
        $this->assertEmpty($parts[3]->textContent);
        $this->assertSame('message-part', $parts[4]->attributes->getNamedItem('class')->textContent);
        $this->assertSame('Loading data...', $parts[4]->textContent);

        $contentParts = $domxpath->query('//iframe[contains(@class, "framed-message-part")]');
        $this->assertIframedContent($contentParts[0], '1', 'Check the forwarded message');
        $this->assertIframedContent($contentParts[1], '2.1', "Plain text message body.\n\n--\u{00A0}\nDeveloper of Free Software\nSent with Roundcube Webmail - roundcube.net");

        $msgRfc822Subject = $domxpath->query('//div[@class="message-partheaders"]//td[@class="header subject"]');
        $this->assertCount(1, $msgRfc822Subject);
        $this->assertSame('Lines', $msgRfc822Subject[0]->textContent);

        $msgRfc822From = $domxpath->query('//div[@class="message-partheaders"]//td[@class="header from"]');
        $this->assertCount(1, $msgRfc822From);
        $this->assertSame('Thomas B.', $msgRfc822From[0]->textContent);

        $msgRfc822To = $domxpath->query('//div[@class="message-partheaders"]//td[@class="header to"]');
        $this->assertCount(1, $msgRfc822To);
        $this->assertSame('Tom Tester', $msgRfc822To[0]->textContent);

        $msgRfc822Cc = $domxpath->query('//div[@class="message-partheaders"]//td[@class="header cc"]');
        $this->assertCount(1, $msgRfc822Cc);
        $this->assertSame('test1@domain.tld, test2@domain.tld, test3@domain.tld, test4@domain.tld, test5@domain.tld, test6@domain.tld, test7@domain.tld, test8@domain.tld, test9@domain.tld, test10@domain.tld, test11@domain.tld, test12@domain.tld', $msgRfc822Cc[0]->textContent);

        $msgRfc822ReplyTo = $domxpath->query('//div[@class="message-partheaders"]//td[@class="header mail-reply-to"]');
        $this->assertCount(1, $msgRfc822ReplyTo);
        $this->assertSame('hello@roundcube.net', $msgRfc822ReplyTo[0]->textContent);

        $msgRfc822Date = $domxpath->query('//div[@class="message-partheaders"]//td[@class="header date"]');
        $this->assertCount(1, $msgRfc822Date);
        // Using a RegExp here, because the result is different depending on the timezone of the testing environment.
        $this->assertMatchesRegularExpression('/2014-05-2[234]{1} \d{2}:\d{2}/', $msgRfc822Date[0]->textContent);
    }
}
