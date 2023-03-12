<?php

/**
 * Test class to test rcube_db class
 *
 * @package Tests
 * @group database
 */
class Framework_DB extends PHPUnit\Framework\TestCase
{
    /**
     * Test script execution and table_prefix replacements
     */
    function test_exec_script()
    {
        $db = new rcube_db_test_wrapper('test');
        $db->set_option('table_prefix', 'prefix_');
        $db->set_option('identifier_start', '`');
        $db->set_option('identifier_end', '`');

        $script = implode("\n", [
            "CREATE TABLE `xxx` (test int, INDEX xxx (test));",
            "-- test comment",
            "ALTER TABLE `xxx` CHANGE test test int;",
            "TRUNCATE xxx;",
            "TRUNCATE TABLE xxx;",
            "DROP TABLE `vvv`;",
            "CREATE TABLE `i` (test int CONSTRAINT `iii`
                FOREIGN KEY (`test`) REFERENCES `xxx`(`test`) ON DELETE CASCADE ON UPDATE CASCADE);",
            "CREATE TABLE `i` (`test` int, INDEX `testidx` (`test`))",
            "CREATE TABLE `i` (`test` int, UNIQUE `testidx` (`test`))",
            "CREATE TABLE `i` (`test` int, UNIQUE INDEX `testidx` (`test`))",
            "INSERT INTO xxx test = 1;",
            "SELECT test FROM xxx;",
        ]);

        $output = implode("\n", [
            "CREATE TABLE `prefix_xxx` (test int, INDEX prefix_xxx (test))",
            "ALTER TABLE `prefix_xxx` CHANGE test test int",
            "TRUNCATE prefix_xxx",
            "TRUNCATE TABLE prefix_xxx",
            "DROP TABLE `prefix_vvv`",
            "CREATE TABLE `prefix_i` (test int CONSTRAINT `prefix_iii`
                FOREIGN KEY (`test`) REFERENCES `prefix_xxx`(`test`) ON DELETE CASCADE ON UPDATE CASCADE)",
            "CREATE TABLE `prefix_i` (`test` int, INDEX `prefix_testidx` (`test`))",
            "CREATE TABLE `prefix_i` (`test` int, UNIQUE `prefix_testidx` (`test`))",
            "CREATE TABLE `prefix_i` (`test` int, UNIQUE INDEX `prefix_testidx` (`test`))",
            "INSERT INTO prefix_xxx test = 1",
            "SELECT test FROM prefix_xxx",
        ]);

        $result = $db->exec_script($script);
        $out    = [];

        foreach ($db->queries as $q) {
            $out[] = $q;
        }

        $this->assertTrue($result, "Execute SQL script (result)");
        $this->assertSame(implode("\n", $out), $output, "Execute SQL script (content)");
    }

    /**
     * Test script execution and table_prefix replacements when the prefix is a schema prefix
     */
    function test_exec_script_schema_prefix()
    {
        $db = new rcube_db_test_wrapper('test');
        $db->set_option('table_prefix', 'prefix.');
        $db->set_option('identifier_start', '`');
        $db->set_option('identifier_end', '`');

        $script = implode("\n", [
            "CREATE TABLE `xxx` (test int, INDEX xxx (test));",
            "-- test comment",
            "ALTER TABLE `xxx` CHANGE test test int;",
            "TRUNCATE xxx;",
            "TRUNCATE TABLE xxx;",
            "DROP TABLE `vvv`;",
            "CREATE TABLE `i` (test int CONSTRAINT `iii`
                FOREIGN KEY (`test`) REFERENCES `xxx`(`test`) ON DELETE CASCADE ON UPDATE CASCADE);",
            "CREATE TABLE `i` (`test` int, INDEX `testidx` (`test`))",
            "CREATE TABLE `i` (`test` int, UNIQUE `testidx` (`test`))",
            "CREATE TABLE `i` (`test` int, UNIQUE INDEX `testidx` (`test`))",
            "INSERT INTO xxx test = 1;",
            "SELECT test FROM xxx;",
        ]);

        $output = implode("\n", [
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
        ]);

        $result = $db->exec_script($script);
        $out    = [];

        foreach ($db->queries as $q) {
            $out[] = $q;
        }

        $this->assertTrue($result, "Execute SQL script (result)");
        $this->assertSame(implode("\n", $out), $output, "Execute SQL script (content)");
    }

    /**
     * Test query parsing and arguments quoting
     */
    function test_query_parsing()
    {
        $db = new rcube_db_test_wrapper('test');
        $db->set_option('identifier_start', '`');
        $db->set_option('identifier_end', '`');

        $db->query("SELECT ?", "test`test");
        $db->query("SELECT ?", "test?test");
        $db->query("SELECT ?", "test``test");
        $db->query("SELECT ?", "test??test");
        $db->query("SELECT `test` WHERE 'test``test'");
        $db->query("SELECT `test` WHERE 'test??test'");
        $db->query("SELECT `test` WHERE `test` = ?", "`te``st`");
        $db->query("SELECT `test` WHERE `test` = ?", "?test?");
        $db->query("SELECT `test` WHERE `test` = ?", "????");

        $expected = implode("\n", [
            "SELECT 'test`test'",
            "SELECT 'test?test'",
            "SELECT 'test``test'",
            "SELECT 'test??test'",
            "SELECT `test` WHERE 'test`test'",
            "SELECT `test` WHERE 'test?test'",
            "SELECT `test` WHERE `test` = '`te``st`'",
            "SELECT `test` WHERE `test` = '?test?'",
            "SELECT `test` WHERE `test` = '????'",
        ]);

        $this->assertSame($expected, implode("\n", $db->queries), "Query parsing [1]");

        $db->set_option('identifier_start', '"');
        $db->set_option('identifier_end', '"');
        $db->queries = [];

        $db->query("SELECT ?", "test`test");
        $db->query("SELECT ?", "test?test");
        $db->query("SELECT ?", "test``test");
        $db->query("SELECT ?", "test??test");
        $db->query("SELECT `test` WHERE 'test``test'");
        $db->query("SELECT `test` WHERE 'test??test'");
        $db->query("SELECT `test` WHERE `test` = ?", "`te``st`");
        $db->query("SELECT `test` WHERE `test` = ?", "?test?");
        $db->query("SELECT `test` WHERE `test` = ?", "????");

        $expected = implode("\n", [
            "SELECT 'test`test'",
            "SELECT 'test?test'",
            "SELECT 'test``test'",
            "SELECT 'test??test'",
            "SELECT \"test\" WHERE 'test`test'",
            "SELECT \"test\" WHERE 'test?test'",
            "SELECT \"test\" WHERE \"test\" = '`te``st`'",
            "SELECT \"test\" WHERE \"test\" = '?test?'",
            "SELECT \"test\" WHERE \"test\" = '????'",
        ]);

        $this->assertSame($expected, implode("\n", $db->queries), "Query parsing [2]");
    }

    function test_parse_dsn()
    {
        $dsn = "mysql://USERNAME:PASSWORD@HOST:3306/DATABASE";

        $result = rcube_db::parse_dsn($dsn);

        $this->assertSame('mysql', $result['phptype']);
        $this->assertSame('USERNAME', $result['username']);
        $this->assertSame('PASSWORD', $result['password']);
        $this->assertSame('3306', $result['port']);
        $this->assertSame('HOST', $result['hostspec']);
        $this->assertSame('DATABASE', $result['database']);

        $dsn = "pgsql:///DATABASE";

        $result = rcube_db::parse_dsn($dsn);

        $this->assertSame('pgsql', $result['phptype']);
        $this->assertTrue(!array_key_exists('username', $result));
        $this->assertTrue(!array_key_exists('password', $result));
        $this->assertTrue(!array_key_exists('port', $result));
        $this->assertTrue(!array_key_exists('hostspec', $result));
        $this->assertSame('DATABASE', $result['database']);
    }

    /**
     * Test list_tables() method
     */
    function test_list_tables()
    {
        $db = rcube::get_instance()->get_dbh();

        $tables = $db->list_tables();

        $this->assertContains('users', $tables);
    }

    /**
     * Test list_columns() method
     */
    function test_list_cols()
    {
        $db = rcube::get_instance()->get_dbh();

        $columns = $db->list_cols('cache');

        $this->assertSame(['user_id', 'cache_key', 'expires', 'data'], $columns);
    }

    /**
     * Test array2list() method
     */
    function test_array2list()
    {
        $db = rcube::get_instance()->get_dbh();

        $this->assertSame('', $db->array2list([]));
        $this->assertSame('\'test\'', $db->array2list(['test']));
        $this->assertSame('\'test\'\'test\'', $db->array2list(['test\'test']));
        $this->assertSame('\'test\'', $db->array2list('test'));
    }

    /**
     * Test concat() method
     */
    function test_concat()
    {
        $db = rcube::get_instance()->get_dbh();

        $this->assertSame('(test)', $db->concat('test'));
        $this->assertSame('(test1 || test2)', $db->concat('test1', 'test2'));
        $this->assertSame('(test)', $db->concat(['test']));
        $this->assertSame('(test1 || test2)', $db->concat(['test1', 'test2']));
    }

    /**
     * Test encode() and decode() methods
     */
    function test_encode_decode()
    {
        $str = '';
        for ($x=0; $x<256; $x++) {
            $str .= chr($x);
        }

        $this->assertSame($str, rcube_db::decode(rcube_db::encode($str)));
        $this->assertSame($str, rcube_db::decode(rcube_db::encode($str, true), true));

        $str = "グーグル谷歌中信фδοκιμήóźdźрöß😁😃";

        $this->assertSame($str, rcube_db::decode(rcube_db::encode($str)));
        $this->assertSame($str, rcube_db::decode(rcube_db::encode($str, true), true));
    }
}

/**
 * rcube_db wrapper to test some protected methods
 */
class rcube_db_test_wrapper extends rcube_db
{
    public $queries = [];

    protected function query_execute($query)
    {
        $this->queries[] = $query;
    }

    public function db_connect($mode, $force = false)
    {
        $this->dbh = new rcube_db_test_dbh();
    }

    public function is_connected()
    {
        return true;
    }

    protected function debug($data)
    {
    }
}

class rcube_db_test_dbh
{
    public function quote($data, $type)
    {
        return "'$data'";
    }
}
