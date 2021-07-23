<?php

/**
 * Test class to test rcube_output class
 *
 * @package Tests
 */
class Framework_Output extends PHPUnit\Framework\TestCase
{
    /**
     * Test get_edit_field()
     */
    function test_get_edit_field()
    {
        $out = rcube_output::get_edit_field('test', 'value');

        $this->assertSame('<input name="_test" class="ff_test" type="text" value="value">', $out);

        $_POST['_test'] = 'testv';
        $out = rcube_output::get_edit_field('test', 'value');

        $this->assertSame('<input name="_test" class="ff_test" type="text" value="testv">', $out);

        $out = rcube_output::get_edit_field('test', 'value', ['class' => 'a'], 'checkbox');

        $this->assertSame('<input class="a ff_test" name="_test" value="1" type="checkbox">', $out);

        $out = rcube_output::get_edit_field('test', 'value', ['class' => 'a'], 'textarea');

        $this->assertSame('<textarea class="a ff_test" name="_test">testv</textarea>', $out);

        $out = rcube_output::get_edit_field('test', 'value', ['class' => 'a'], 'select');

        $this->assertSame('<select class="a ff_test" name="_test">' . "\n" . '<option value="">---</option></select>', $out);

        $_POST['_test'] = 'tt';
        $attr = ['options' => ['tt' => 'oo']];
        $out  = rcube_output::get_edit_field('test', 'value', $attr, 'select');

        $this->assertSame('<select name="_test" class="ff_test">' . "\n"
            . '<option value="">---</option><option value="tt" selected="selected">oo</option></select>',
            $out
        );
    }

    /**
     * Test json_serialize()
     */
    function test_json_serialize()
    {
        $this->assertSame('""', rcube_output::json_serialize(''));
        $this->assertSame('[]', rcube_output::json_serialize([]));
        $this->assertSame('10', rcube_output::json_serialize(10));
        $this->assertSame('{"test":"test"}', rcube_output::json_serialize(['test' => 'test']));

        // Test non-utf-8 input
        $this->assertSame('{"ab":"ab"}', rcube_output::json_serialize(["a\x8cb" => "a\x8cb"]));
    }
}
