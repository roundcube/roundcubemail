<?php

/**
 * Test class to test rcube_result_multifolder class
 *
 * @package Tests
 */
class Framework_ResultMultifolder extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_result_multifolder;

        $this->assertInstanceOf('rcube_result_multifolder', $object, "Class constructor");
    }
}
