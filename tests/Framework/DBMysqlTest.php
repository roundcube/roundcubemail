<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_db_mysql class
 *
 * @group database
 * @group mysql
 */
#[Group('database')]
#[Group('mysql')]
class DBMysqlTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_db_mysql('test');

        $this->assertInstanceOf(\rcube_db_mysql::class, $object, 'Class constructor');
    }
}
