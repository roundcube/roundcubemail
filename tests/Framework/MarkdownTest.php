<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_markdown class
 */
class MarkdownTest extends TestCase
{
    public function test_to_html()
    {
        // A test string that includes syntax from GitHub Flavoured Markdown.
        $source = <<<'END'
            # A headline

            Hello there!

            ---------

            * one
            * two
            * three

            ~not~
            END;

        $expected = <<<'END'
            <h1>A headline</h1>
            <p>Hello there!</p>
            <hr />
            <ul>
            <li>one</li>
            <li>two</li>
            <li>three</li>
            </ul>
            <p><del>not</del></p>

            END;

        $markdown = \rcube_markdown::to_html($source);
        $this->assertSame($expected, $markdown);
    }
}
