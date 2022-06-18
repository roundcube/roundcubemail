<?php

/**
 * Test class to test rcube_db_pgsql class
 *
 * @package Tests
 * @group database
 * @group postgres
 */
class Framework_DBPgsql extends PHPUnit\Framework\TestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_db_pgsql('test');

        $this->assertInstanceOf('rcube_db_pgsql', $object, "Class constructor");
    }

    /**
     * Test script execution and table_prefix replacements when the prefix is a schema prefix
     */
    function test_exec_script_schema_prefix()
    {
        $db = rcube_db::factory('pgsql:test');
        $db->set_option('table_prefix', 'prefix.');

        $script = [
            "CREATE TABLE `xxx` (test int, INDEX xxx (test))",
            "ALTER TABLE `xxx` CHANGE test test int",
            "TRUNCATE xxx",
            "TRUNCATE TABLE xxx",
            "DROP TABLE `vvv`",
            "CREATE TABLE `i` (test int CONSTRAINT `iii`
                FOREIGN KEY (`test`) REFERENCES `xxx`(`test`) ON DELETE CASCADE ON UPDATE CASCADE)",
            "CREATE TABLE `i` (`test` int, INDEX `testidx` (`test`))",
            "CREATE TABLE `i` (`test` int, UNIQUE `testidx` (`test`))",
            "CREATE TABLE `i` (`test` int, UNIQUE INDEX `testidx` (`test`))",
            "INSERT INTO xxx test = 1",
            "SELECT test FROM xxx",
            "CREATE SEQUENCE users_seq INCREMENT BY 1",
            "CREATE TABLE users ( user_id integer DEFAULT nextval('users_seq'::text) PRIMARY KEY )",
            "ALTER SEQUENCE user_ids RENAME TO users_seq",
        ];

        $output = [
            "CREATE TABLE `prefix`.`xxx` (test int, INDEX xxx (test))",
            "ALTER TABLE `prefix`.`xxx` CHANGE test test int",
            "TRUNCATE prefix.xxx",
            "TRUNCATE TABLE prefix.xxx",
            "DROP TABLE `prefix`.`vvv`",
            "CREATE TABLE `prefix`.`i` (test int CONSTRAINT `iii`
                FOREIGN KEY (`test`) REFERENCES `prefix`.`xxx`(`test`) ON DELETE CASCADE ON UPDATE CASCADE)",
            "CREATE TABLE `prefix`.`i` (`test` int, INDEX `testidx` (`test`))",
            "CREATE TABLE `prefix`.`i` (`test` int, UNIQUE `testidx` (`test`))",
            "CREATE TABLE `prefix`.`i` (`test` int, UNIQUE INDEX `testidx` (`test`))",
            "INSERT INTO prefix.xxx test = 1",
            "SELECT test FROM prefix.xxx",
            "CREATE SEQUENCE prefix.users_seq INCREMENT BY 1",
            "CREATE TABLE prefix.users ( user_id integer DEFAULT nextval('prefix.users_seq'::text) PRIMARY KEY )",
            "ALTER SEQUENCE prefix.user_ids RENAME TO prefix.users_seq",
        ];

        $method = new ReflectionMethod('rcube_db_pgsql', 'fix_table_names');
        $method->setAccessible(true);

        foreach ($script as $idx => $query) {
            $res = $method->invoke($db, $query);
            $this->assertSame($output[$idx], $res, "Test case $idx");
        }
    }

    /**
     * Test converting config DSN string into PDO connection string
     */
    function test_dsn_string()
    {
        $db = new rcube_db_pgsql('test');

        $dsn = $db->parse_dsn("pgsql://USERNAME:PASSWORD@HOST:5432/DATABASE");
        $result = invokeMethod($db, 'dsn_string', [$dsn]);
        $this->assertSame("pgsql:host=HOST;port=5432;dbname=DATABASE", $result);

        $dsn = $db->parse_dsn("pgsql:///DATABASE");
        $result = invokeMethod($db, 'dsn_string', [$dsn]);
        $this->assertSame("pgsql:dbname=DATABASE", $result);

        $dsn = $db->parse_dsn("pgsql://user@unix(/var/run/postgresql)/roundcubemail?sslmode=verify-full");
        $result = invokeMethod($db, 'dsn_string', [$dsn]);
        $this->assertSame("pgsql:host=/var/run/postgresql;dbname=roundcubemail;sslmode=verify-full", $result);
    }
}
