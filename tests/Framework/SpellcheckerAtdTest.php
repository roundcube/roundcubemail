<?php

namespace Roundcube\Mail\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_spellcheck_atd class
 */
class SpellcheckerAtdTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_spellchecker_atd(null, 'en');

        $this->assertInstanceOf('rcube_spellchecker_atd', $object, 'Class constructor');
        $this->assertInstanceOf('rcube_spellchecker_engine', $object, 'Class constructor');
    }
}
