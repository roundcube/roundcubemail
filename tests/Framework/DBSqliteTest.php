<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_db_sqlite class
 *
 * @group database
 * @group sqlite
 */
#[Group('database')]
#[Group('sqlite')]
class DBSqliteTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_db_sqlite('test');

        $this->assertInstanceOf(\rcube_db_sqlite::class, $object, 'Class constructor');
    }
}
