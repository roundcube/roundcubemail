<?php

namespace Roundcube\Tests\Actions\Mail;

use Roundcube\Tests\ActionTestCase;

use function Roundcube\Tests\invokeMethod;
use function Roundcube\Tests\setProperty;

/**
 * Test class to test rcmail_action_mail_compose
 */
class ComposeTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_compose();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }

    /**
     * Test quote_text() method
     */
    public function test_quote_text()
    {
        $action = new \rcmail_action_mail_compose();

        $this->assertSame('> ', $action->quote_text(''));

        $result = $action->quote_text("test1\ntest2");
        $expected = "> test1\n> test2";

        $this->assertSame($expected, $result);

        $result = $action->quote_text("> test1\n> test2");
        $expected = ">> test1\n>> test2";

        $this->assertSame($expected, $result);
    }

    /**
     * Invoke prepare_html_body() with a given compose mode and HTML body
     */
    private function invoke_prepare_html_body($mode, $body)
    {
        $object = new \rcmail_action_mail_compose();

        setProperty($object, 'COMPOSE', ['mode' => $mode], \rcmail_action_mail_compose::class);
        setProperty($object, 'MESSAGE', (object) ['is_safe' => true], \rcmail_action_mail_compose::class);
        setProperty($object, 'CID_MAP', [], \rcmail_action_mail_compose::class);

        return invokeMethod($object, 'prepare_html_body', [$body], \rcmail_action_mail_compose::class);
    }

    /**
     * Test that "Edit as New" (MODE_EDIT) does not wrap the body in a container
     * element. Wrapping accumulates extra <div> tags on every edit/send cycle (#9919).
     */
    public function test_prepare_html_body_edit_mode_has_no_container()
    {
        $result = $this->invoke_prepare_html_body(\rcmail_sendmail::MODE_EDIT, '<p>Hello world</p>');

        $this->assertStringNotContainsString('editbody', $result);
        $this->assertStringNotContainsString('<div', $result);
        $this->assertStringContainsString('Hello world', $result);
    }

    /**
     * Test that reply mode still wraps the (quoted) body in a container element,
     * so the #9919 fix does not regress style isolation for replies.
     */
    public function test_prepare_html_body_reply_mode_has_container()
    {
        $result = $this->invoke_prepare_html_body(\rcmail_sendmail::MODE_REPLY, '<p>Hello world</p>');

        $this->assertStringContainsString('replybody', $result);
        $this->assertStringContainsString('Hello world', $result);
    }

    /**
     * Test that "Edit as New" (MODE_EDIT) does not wrap the body in a <div> even
     * when the stored message's <body> tag carries a style attribute (as added by
     * send.php's default_font/default_font_size wrapping on every send). Without
     * this, the body callback still forwards the style attribute into a new <div>
     * on every edit/send cycle, so the accumulation from #9919 continues, just
     * without the "editbody" id (#9919).
     */
    public function test_prepare_html_body_edit_mode_strips_body_style()
    {
        $body = '<html><head></head><body style="font-size: 10pt; font-family: Verdana,Geneva,sans-serif;">'
            . "\r\n<p>Hello world</p></body></html>";

        $result = $this->invoke_prepare_html_body(\rcmail_sendmail::MODE_EDIT, $body);

        $this->assertStringNotContainsString('<div', $result);
        $this->assertStringContainsString('Hello world', $result);
    }
}
