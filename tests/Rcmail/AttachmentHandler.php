<?php

/**
 * Test class to test rcmail_attachment_handler class
 *
 * @package Tests
 */
class Rcmail_RcmailAttachmentHandler extends ActionTestCase
{

    /**
     * Test rcmail_action::svg_filter()
     */
    function test_svg_filter()
    {
        $svg = '<svg><a xlink:href="javascript:alert(1)"><text x="20" y="20">XSS</text></a></svg>';
        $exp = '<svg><a><text x="20" y="20">XSS</text></a></svg>';

        $out = rcmail_attachment_handler::svg_filter($svg);

        $this->assertSame($exp, $out);
    }
}
