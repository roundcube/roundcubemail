<?php

/**
 * Test class to test rcube_result_multifolder class
 */
class Framework_ResultMultifolder extends PHPUnit\Framework\TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_result_multifolder();

        $this->assertInstanceOf('rcube_result_multifolder', $object, 'Class constructor');
    }
}
