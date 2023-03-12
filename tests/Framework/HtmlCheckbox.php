<?php

/**
 * Test class to test html_checkbox class
 *
 * @package Tests
 */
class Framework_HtmlCheckbox extends PHPUnit\Framework\TestCase
{

    /**
     * Class constructor
     */
    function test_checked_state()
    {
        $input = new html_checkbox(['value' => 1]);

        $this->assertSame('<input value="1" type="checkbox">', $input->show(0));
        $this->assertSame('<input value="1" type="checkbox">', $input->show('0'));
        $this->assertSame('<input value="1" checked="checked" type="checkbox">', $input->show(1));
        $this->assertSame('<input value="1" checked="checked" type="checkbox">', $input->show('1'));
        $this->assertSame('<input value="1" checked="checked" type="checkbox">', $input->show(true));
        // test that the checked state does not "leak"
        $this->assertSame('<input value="1" type="checkbox">', $input->show(0));
    }
}
