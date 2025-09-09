<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;
use Roundcube\Tests\OutputHtmlMock;

/**
 * Test class to test rcube_output class
 */
class OutputTest extends TestCase
{
    /**
     * Test download_headers()
     */
    public function test_download_headers()
    {
        $output = new OutputHtmlMock();

        // Basic (empty) case
        $output->reset();
        $output->download_headers('test');

        $this->assertCount(3, $output->headers);
        $this->assertContains('Content-Disposition: attachment; filename="test"', $output->headers);
        $this->assertContains('Content-Type: application/octet-stream', $output->headers);
        $this->assertContains('Content-Security-Policy: default-src \'none\'; img-src \'self\'', $output->headers);

        // Test handling of filename*
        $output->reset();
        $output->download_headers('test ? test');

        $this->assertCount(3, $output->headers);
        $this->assertContains('Content-Disposition: attachment; filename="test _ test"; filename*=' . RCUBE_CHARSET . "''" . rawurlencode('test ? test'), $output->headers);
        $this->assertContains('Content-Type: application/octet-stream', $output->headers);
        $this->assertContains('Content-Security-Policy: default-src \'none\'; img-src \'self\'', $output->headers);

        // Invalid content type
        $output->reset();
        $params = ['type' => 'invalid'];
        $output->download_headers('test', $params);

        $this->assertCount(3, $output->headers);
        $this->assertContains('Content-Type: application/octet-stream', $output->headers);

        // Test inline disposition with type_charset
        $output->reset();
        $params = ['disposition' => 'inline', 'type' => 'text/plain', 'type_charset' => 'ISO-8859-1'];
        $output->download_headers('test', $params);

        $this->assertCount(3, $output->headers);
        $this->assertContains('Content-Disposition: inline; filename="test"', $output->headers);
        $this->assertContains('Content-Type: text/plain; charset=ISO-8859-1', $output->headers);

        // Insecure content-type elimination for inline mode
        $types = [
            'application/ecmascript',
            'application/javascript',
            'application/javascript-1.2',
            'application/x-javascript',
            'application/x-jscript',
            'application/xml',
            'application/xhtml+xml',
            'text/javascript',
            'text/xml',
            'text/unknown',
        ];

        foreach ($types as $type) {
            $output->reset();
            $params = ['type' => $type, 'disposition' => 'inline'];
            $output->download_headers('test', $params);

            $this->assertContains('Content-Type: text/plain; charset=' . RCUBE_CHARSET, $output->headers, "Case:{$type}");
        }

        // TODO: More test cases
        $this->markTestIncomplete();
    }

    /**
     * Test get_edit_field()
     */
    public function test_get_edit_field()
    {
        $out = \rcube_output::get_edit_field('test', 'value');

        $this->assertSame('<input name="_test" class="ff_test" type="text" value="value">', $out);

        $_POST['_test'] = 'testv';
        $out = \rcube_output::get_edit_field('test', 'value');

        $this->assertSame('<input name="_test" class="ff_test" type="text" value="testv">', $out);

        $out = \rcube_output::get_edit_field('test', 'value', ['class' => 'a'], 'checkbox');

        $this->assertSame('<input class="a ff_test" name="_test" value="1" type="checkbox">', $out);

        $out = \rcube_output::get_edit_field('test', 'value', ['class' => 'a'], 'textarea');

        $this->assertSame('<textarea class="a ff_test" name="_test">testv</textarea>', $out);

        $out = \rcube_output::get_edit_field('test', 'value', ['class' => 'a'], 'select');

        $this->assertSame('<select class="a ff_test" name="_test">' . "\n" . '<option value="">---</option></select>', $out);

        $_POST['_test'] = 'tt';
        $attr = ['options' => ['tt' => 'oo']];
        $out = \rcube_output::get_edit_field('test', 'value', $attr, 'select');

        $this->assertSame('<select name="_test" class="ff_test">' . "\n"
            . '<option value="">---</option><option value="tt" selected="selected">oo</option></select>',
            $out
        );
    }

    /**
     * Test json_serialize()
     */
    public function test_json_serialize()
    {
        $this->assertSame('""', \rcube_output::json_serialize(''));
        $this->assertSame('[]', \rcube_output::json_serialize([]));
        $this->assertSame('10', \rcube_output::json_serialize(10));
        $this->assertSame('{"test":"test"}', \rcube_output::json_serialize(['test' => 'test']));

        // Test non-utf-8 input
        $this->assertSame('{"ab":"ab"}', \rcube_output::json_serialize(["a\x8cb" => "a\x8cb"]));
    }
}
