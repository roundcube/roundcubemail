<?php

/**
 * Test class to test rcmail_output_html class
 *
 * @package Tests
 */
class Framework_OutputHtml extends PHPUnit_Framework_TestCase
{

    /**
     * Data for test_conditions()
     */
    function data_conditions()
    {
        return array(
            array("<roundcube:if condition='1' />A<roundcube:endif />", "A"),
            array("<roundcube:if condition='0' />A<roundcube:else />B<roundcube:endif />", "B"),
            array("<roundcube:if condition='0' />A<roundcube:elseif condition='1' />B<roundcube:else />C<roundcube:endif />", "B"),
            array("<roundcube:if condition='1' /><roundcube:if condition='0' />A<roundcube:else />B<roundcube:endif />C<roundcube:else />D<roundcube:endif />", "BC"),
            array("<roundcube:if condition='1' /><roundcube:if condition='1' />A<roundcube:else />B<roundcube:endif />C<roundcube:else />D<roundcube:endif />", "AC"),
            array("<roundcube:if condition='1' /><roundcube:if condition='0' />A<roundcube:elseif condition='1' />B<roundcube:else />C<roundcube:endif />D<roundcube:else />E<roundcube:endif />", "BD"),
            array("<roundcube:if condition='0' />A<roundcube:elseif condition='1' /><roundcube:if condition='0' />B<roundcube:else /><roundcube:if condition='1' />C<roundcube:endif />D<roundcube:endif /><roundcube:else />E<roundcube:endif />", "CD")
        );
    }

    /**
     * Test text to html conversion
     *
     * @dataProvider data_conditions
     */
    function test_conditions($input, $output)
    {
        $object = new rcmail_output_html;
        $result = $object->just_parse($input);

        $this->assertEquals($output, $result);
    }
}
