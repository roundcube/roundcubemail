<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_output class
 */
class Framework_Output extends TestCase
{
    /**
     * Test get_edit_field()
     */
    public function test_get_edit_field()
    {
        $out = rcube_output::get_edit_field('test', 'value');

        self::assertSame('<input name="_test" class="ff_test" type="text" value="value">', $out);

        $_POST['_test'] = 'testv';
        $out = rcube_output::get_edit_field('test', 'value');

        self::assertSame('<input name="_test" class="ff_test" type="text" value="testv">', $out);

        $out = rcube_output::get_edit_field('test', 'value', ['class' => 'a'], 'checkbox');

        self::assertSame('<input class="a ff_test" name="_test" value="1" type="checkbox">', $out);

        $out = rcube_output::get_edit_field('test', 'value', ['class' => 'a'], 'textarea');

        self::assertSame('<textarea class="a ff_test" name="_test">testv</textarea>', $out);

        $out = rcube_output::get_edit_field('test', 'value', ['class' => 'a'], 'select');

        self::assertSame('<select class="a ff_test" name="_test">' . "\n" . '<option value="">---</option></select>', $out);

        $_POST['_test'] = 'tt';
        $attr = ['options' => ['tt' => 'oo']];
        $out = rcube_output::get_edit_field('test', 'value', $attr, 'select');

        self::assertSame('<select name="_test" class="ff_test">' . "\n"
            . '<option value="">---</option><option value="tt" selected="selected">oo</option></select>',
            $out
        );
    }

    /**
     * Test json_serialize()
     */
    public function test_json_serialize()
    {
        self::assertSame('""', rcube_output::json_serialize(''));
        self::assertSame('[]', rcube_output::json_serialize([]));
        self::assertSame('10', rcube_output::json_serialize(10));
        self::assertSame('{"test":"test"}', rcube_output::json_serialize(['test' => 'test']));

        // Test non-utf-8 input
        self::assertSame('{"ab":"ab"}', rcube_output::json_serialize(["a\x8cb" => "a\x8cb"]));
    }
}
