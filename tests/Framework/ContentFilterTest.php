<?php

namespace Roundcube\Mail\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_content_filter class
 */
class ContentFilterTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_content_filter();

        $this->assertInstanceOf('rcube_content_filter', $object, 'Class constructor');
    }
}
