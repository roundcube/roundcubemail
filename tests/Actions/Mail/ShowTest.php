<?php

namespace Roundcube\Tests\Actions\Mail;

use Roundcube\Tests\ActionTestCase;

use function Roundcube\Tests\invokeMethod;

/**
 * Test class to test rcmail_action_mail_show
 */
class ShowTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_show();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }

    /**
     * Test prepare_part_body() method
     */
    public function test_prepare_part_body()
    {
        // HTML sample (from #9911) focusing on body attributes and styles
        $html = <<<'EOF'
            <html lang="en">
                <head>
                    <style>
                        @media (min-width: 600px) {
                            .body_class_name { color: red; }
                        }
                    </style>
                </head>
                <body class="body_class_name" id="bod">
                    <p>Test</p>
                </body>
            </html>
            EOF;

        $action = new \rcmail_action_mail_show();

        $body = invokeMethod($action, 'prepare_part_body', [$html, $this->get_html_part()]);
        $xpath = self::parseHtml($body);

        $this->assertCount(1, $xpath->query('/html/div'));
        $this->assertSame('message-htmlpart1', $xpath->query('/html/div')->item(0)->getAttribute('id'));
        $this->assertSame('message-htmlpart', $xpath->query('/html/div')->item(0)->getAttribute('class'));

        $this->assertCount(1, $xpath->query('/html/div/style'));
        $this->assertSame('text/css', $xpath->query('/html/div/style')->item(0)->getAttribute('type'));
        $this->assertSame(
            '@media (min-width: 600px) { #message-htmlpart1 .v1body_class_name { color: red; } }',
            trim(preg_replace('/(\s{2,}|\n)/', ' ', $xpath->query('/html/div/style')->item(0)->textContent))
        );

        $this->assertCount(1, $xpath->query('/html/div/div'));
        $this->assertSame('v1bod', $xpath->query('/html/div/div')->item(0)->getAttribute('id'));
        $this->assertSame('v1body_class_name', $xpath->query('/html/div/div')->item(0)->getAttribute('class'));
        $this->assertSame('Test', $xpath->query('/html/div/div/p')->item(0)->textContent);

        // Invoking the method again (for the next part) use a different ID/prefix
        $body = invokeMethod($action, 'prepare_part_body', [$html, $this->get_html_part()]);
        $xpath = self::parseHtml($body);

        $this->assertCount(1, $xpath->query('/html/div'));
        $this->assertSame('message-htmlpart2', $xpath->query('/html/div')->item(0)->getAttribute('id'));
        $this->assertSame('message-htmlpart', $xpath->query('/html/div')->item(0)->getAttribute('class'));

        $this->assertCount(1, $xpath->query('/html/div/style'));
        $this->assertSame(
            '@media (min-width: 600px) { #message-htmlpart2 .v2body_class_name { color: red; } }',
            trim(preg_replace('/(\s{2,}|\n)/', ' ', $xpath->query('/html/div/style')->item(0)->textContent))
        );

        $this->assertCount(1, $xpath->query('/html/div/div'));
        $this->assertSame('v2bod', $xpath->query('/html/div/div')->item(0)->getAttribute('id'));
        $this->assertSame('v2body_class_name', $xpath->query('/html/div/div')->item(0)->getAttribute('class'));
    }

    /**
     * Helper method to create a HTML message part object
     */
    protected function get_html_part($body = null)
    {
        $part = new \rcube_message_part();
        $part->ctype_primary = 'text';
        $part->ctype_secondary = 'html';
        $part->body = $body ? file_get_contents(TESTS_DIR . $body) : null;
        $part->replaces = [];

        return $part;
    }
}
