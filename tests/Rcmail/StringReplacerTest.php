<?php

namespace Roundcube\Tests\Rcmail;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Roundcube\Tests\invokeMethod;

/**
 * Test class to test rcmail_string_replacer class
 */
class StringReplacerTest extends TestCase
{
    /**
     * Test for mailto_callback() method
     */
    public function test_mailto_callback()
    {
        $replacer = new \rcmail_string_replacer();

        $result = invokeMethod($replacer, 'mailto_callback', [['email@address.com', 'email@address.com']]);

        $this->assertMatchesRegularExpression($replacer->pattern, $result);

        $result = invokeMethod($replacer, 'mailto_callback', [['address.com', 'address.com']]);

        $this->assertSame('address.com', $result);
    }

    /**
     * Test e-mail addresses and mailto: URIs with the compose handler (#10428)
     *
     * @dataProvider provide_replace_cases
     */
    #[DataProvider('provide_replace_cases')]
    public function test_replace($input, $output)
    {
        $replacer = new \rcmail_string_replacer();
        $result = $replacer->resolve($replacer->replace($input));

        $this->assertSame($output, $result);
    }

    /**
     * Data for test_replace()
     */
    public static function provide_replace_cases(): iterable
    {
        return [
            ['user@example.com?subject=Hi',
                '<a href="mailto:user@example.com" onclick="return rcmail.command(\'compose\',\'user@example.com\',this)">user@example.com</a>?subject=Hi',
            ],
            ['mailto:user@example.com?body=http://example.com/x',
                '<a href="mailto:user@example.com?body=http://example.com/x" onclick="return rcmail.command(\'compose\',\'user@example.com?body=http://example.com/x\',this)">'
                . 'mailto:user@example.com?body=http://example.com/x</a>',
            ],
            ['MAILTO:user@example.com?subject=a\b&cc=other@example.org',
                '<a href="mailto:user@example.com?subject=a\b&amp;cc=other@example.org" onclick="return rcmail.command(\'compose\',\'user@example.com?subject=a\\\b&amp;cc=other@example.org\',this)">'
                . 'MAILTO:user@example.com?subject=a\b&amp;cc=other@example.org</a>',
            ],
            ['mailto:user@example.com?subject=x\'+alert(1)+\'',
                '<a href="mailto:user@example.com?subject=x" onclick="return rcmail.command(\'compose\',\'user@example.com?subject=x\',this)">'
                . 'mailto:user@example.com?subject=x</a>\'+alert(1)+\'',
            ],
            // invalid address: no mailto: link, but the link in the query is still found
            ['mailto:user@example?body=http://example.com/x', 'mailto:user@example?body=<a href="http://example.com/x">http://example.com/x</a>'],
            ['git clone https://user@example.com/team/repo.git', 'git clone <a href="https://user">https://user</a>@example.com/team/repo.git'],
        ];
    }
}
