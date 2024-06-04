<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

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
