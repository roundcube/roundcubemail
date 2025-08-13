<?php

namespace Roundcube\Tests\Actions\Mail;

use Roundcube\Tests\ActionTestCase;

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
     * Test prepare_html_body() method
     */
    public function test_prepare_html_body()
    {
        $action = new \rcmail_action_mail_compose();

        $html = <<<'EOF'
            <html>
                <head>
                    <style>
                        @media (min-width: 600px) {
                            .body_class_name { color: red; }
                        }
                    </style>
                </head>
                <body class="body_class_name" id="bod">
                    <p>Broken CSS selector</p>
                </body>
            </html>
            EOF;

        $body = $action->prepare_html_body($html);
        $xpath = self::parseHtml($body);

        $this->assertCount(1, $xpath->query('//style'));
        $this->assertSame('text/css', $xpath->query('//style')->item(0)->getAttribute('type'));
        $this->assertSame(
            '@media (min-width: 600px) { .v1body_class_name { color: red; } }',
            trim(preg_replace('/(\s{2,}|\n)/', ' ', $xpath->query('//style')->item(0)->textContent))
        );

        $this->assertCount(1, $xpath->query('//div'));
        $this->assertSame('v1bod', $xpath->query('//div')->item(0)->getAttribute('id'));
        $this->assertSame('rcmBody v1body_class_name', $xpath->query('//div')->item(0)->getAttribute('class'));
        $this->assertCount(1, $xpath->query('//div/p'));
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
}
