<?php

/**
 * Test class to test rcube_db class
 *
 * @package Tests
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
            "CREATE TABLE `prefix_xxx` (test int, INDEX prefix_xxx (test));",
            "ALTER TABLE `prefix_xxx` CHANGE test test int;",
            "TRUNCATE prefix_xxx;",
            "DROP TABLE `prefix_vvv`;",
            "CREATE TABLE `prefix_i` (test int CONSTRAINT `prefix_iii`
                FOREIGN KEY (`test`) REFERENCES `prefix_xxx`(`test`) ON DELETE CASCADE ON UPDATE CASCADE);",
            "INSERT INTO prefix_xxx test = 1;",
            "SELECT test FROM prefix_xxx;",
        ));

        $result = $db->exec_script($script);
        $out    = '';

        foreach ($db->queries as $q) {
            $out[] = $q[0];
        }

        $this->assertTrue($result, "Execute SQL script (result)");
        $this->assertSame(implode("\n", $out), $output, "Execute SQL script (content)");
    }
}

/**
 * rcube_db wrapper to test some protected methods
 */
class rcube_db_test_wrapper extends rcube_db
{
    public $queries = array();

    protected function _query($query, $offset, $numrows, $params)
    {
        $this->queries[] = array(trim($query), $offset, $numrows, $params);
    }
}
