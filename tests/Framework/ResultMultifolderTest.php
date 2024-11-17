<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;
use rcube_result_multifolder as rcube_result_multifolder;

/**
 * Test class to test rcube_result_multifolder class
 */
class ResultMultifolderTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_result_multifolder();

        $this->assertInstanceOf(\rcube_result_multifolder::class, $object, 'Class constructor');
    }
}
