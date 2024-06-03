<?php

namespace Roundcube\Mail\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_imap_search class
 */
class ImapSearchTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_imap_search([], true);

        $this->assertInstanceOf('rcube_imap_search', $object, 'Class constructor');
    }
}
