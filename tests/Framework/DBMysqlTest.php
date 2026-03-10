<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function Roundcube\Tests\invokeMethod;

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
    public function test_dsn_string()
    {
        $db = new \rcube_db_mysql('test');

        $result = $db->parse_dsn('mysql://user:pass@[fd00:3::11]:3306/test');
        $dsn = invokeMethod($db, 'dsn_string', [$result]);
        $this->assertSame('mysql:dbname=test;host=[fd00:3::11];port=3306;charset=utf8mb4', $dsn);

        $result = $db->parse_dsn('mysql://user:pass@[::1]/test');
        $dsn = invokeMethod($db, 'dsn_string', [$result]);
        $this->assertSame('mysql:dbname=test;host=[::1];charset=utf8mb4', $dsn);
    }
}
