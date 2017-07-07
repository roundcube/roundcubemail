<?php

/**
 * Test class to test rcube_db class
 *
 * @package Tests
 * @group database
 */
class Framework_DB extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor test
     */
    function test_class()
    {
        $object = new rcube_db('test');

        $this->assertInstanceOf('rcube_db', $object, "Class constructor");
    }

    /**
     * Test script execution and table_prefix replacements
     */
    function test_exec_script()
    {
        $db = new rcube_db_test_wrapper('test');
        $db->set_option('table_prefix', 'prefix_');
        $db->set_option('identifier_start', '`');
        $db->set_option('identifier_end', '`');

        $script = implode("\n", array(
            "CREATE TABLE `xxx` (test int, INDEX xxx (test));",
            "-- test comment",
            "ALTER TABLE `xxx` CHANGE test test int;",
            "TRUNCATE xxx;",
            "DROP TABLE `vvv`;",
            "CREATE TABLE `i` (test int CONSTRAINT `iii`
                FOREIGN KEY (`test`) REFERENCES `xxx`(`test`) ON DELETE CASCADE ON UPDATE CASCADE);",
            "INSERT INTO xxx test = 1;",
            "SELECT test FROM xxx;",
        ));
        $output = implode("\n", array(
            "CREATE TABLE `prefix_xxx` (test int, INDEX prefix_xxx (test))",
            "ALTER TABLE `prefix_xxx` CHANGE test test int",
            "TRUNCATE prefix_xxx",
            "DROP TABLE `prefix_vvv`",
            "CREATE TABLE `prefix_i` (test int CONSTRAINT `prefix_iii`
                FOREIGN KEY (`test`) REFERENCES `prefix_xxx`(`test`) ON DELETE CASCADE ON UPDATE CASCADE)",
            "INSERT INTO prefix_xxx test = 1",
            "SELECT test FROM prefix_xxx",
        ));

        $result = $db->exec_script($script);
        $out    = array();

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

        $expected = implode("\n", array(
            "SELECT 'test`test'",
            "SELECT 'test?test'",
            "SELECT 'test``test'",
            "SELECT 'test??test'",
            "SELECT `test` WHERE 'test`test'",
            "SELECT `test` WHERE 'test?test'",
            "SELECT `test` WHERE `test` = '`te``st`'",
            "SELECT `test` WHERE `test` = '?test?'",
            "SELECT `test` WHERE `test` = '????'",
        ));

       $this->assertSame($expected, implode("\n", $db->queries), "Query parsing [1]");

        $db->set_option('identifier_start', '"');
        $db->set_option('identifier_end', '"');
        $db->queries = array();

        $db->query("SELECT ?", "test`test");
        $db->query("SELECT ?", "test?test");
        $db->query("SELECT ?", "test``test");
        $db->query("SELECT ?", "test??test");
        $db->query("SELECT `test` WHERE 'test``test'");
        $db->query("SELECT `test` WHERE 'test??test'");
        $db->query("SELECT `test` WHERE `test` = ?", "`te``st`");
        $db->query("SELECT `test` WHERE `test` = ?", "?test?");
        $db->query("SELECT `test` WHERE `test` = ?", "????");

        $expected = implode("\n", array(
            "SELECT 'test`test'",
            "SELECT 'test?test'",
            "SELECT 'test``test'",
            "SELECT 'test??test'",
            "SELECT \"test\" WHERE 'test`test'",
            "SELECT \"test\" WHERE 'test?test'",
            "SELECT \"test\" WHERE \"test\" = '`te``st`'",
            "SELECT \"test\" WHERE \"test\" = '?test?'",
            "SELECT \"test\" WHERE \"test\" = '????'",
        ));

       $this->assertSame($expected, implode("\n", $db->queries), "Query parsing [2]");
    }

    function test_parse_dsn()
    {
        $dsn = "mysql://USERNAME:PASSWORD@HOST:3306/DATABASE";

        $result = rcube_db::parse_dsn($dsn);

        $this->assertSame('mysql', $result['phptype'], "DSN parser: phptype");
        $this->assertSame('USERNAME', $result['username'], "DSN parser: username");
        $this->assertSame('PASSWORD', $result['password'], "DSN parser: password");
        $this->assertSame('3306', $result['port'], "DSN parser: port");
        $this->assertSame('HOST', $result['hostspec'], "DSN parser: hostspec");
        $this->assertSame('DATABASE', $result['database'], "DSN parser: database");
    }
}

/**
 * rcube_db wrapper to test some protected methods
 */
class rcube_db_test_wrapper extends rcube_db
{
    public $queries = array();

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
