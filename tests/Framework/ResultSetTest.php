<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_result_set class
 */
class ResultSetTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_result_set();

        $this->assertInstanceOf(\rcube_result_set::class, $object, 'Class constructor');
    }
}
