<?php

class Managesieve_Script extends PHPUnit\Framework\TestCase
{
    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../lib/Roundcube/rcube_sieve_script.php';
    }

    /**
     * Sieve script parsing
     *
     * @dataProvider data_parser
     */
    function test_parser($input, $output, $message)
    {
        // get capabilities list from the script
        $caps = [];
        if (preg_match('/require \[([a-z0-9", ]+)\]/', $input, $m)) {
            foreach (explode(',', $m[1]) as $cap) {
                $caps[] = trim($cap, '" ');
            }
        }

        $script = new rcube_sieve_script($input, $caps);
        $result = $script->as_text();

        $this->assertEquals(trim($output), trim($result), $message);
    }

    /**
     * Data provider for test_parser()
     */
    function data_parser()
    {
        $dir_path = realpath(__DIR__ . '/src');
        $dir      = opendir($dir_path);
        $result   = [];

        while ($file = readdir($dir)) {
            if (preg_match('/^[a-z0-9_]+$/', $file)) {
                $input = file_get_contents($dir_path . '/' . $file);

                if (file_exists($dir_path . '/' . $file . '.out')) {
                    $output = file_get_contents($dir_path . '/' . $file . '.out');
                }
                else {
                    $output = $input;
                }

                $result[] = [
                    'input'   => $input,
                    'output'  => $output,
                    'message' => "Error in parsing '$file' file",
                ];
            }
        }

        return $result;
    }

    /**
     * Sieve script parsing
     */
    public function test_parser_bug9562()
    {
        // This is an obviously invalid script
        $input = "vacation :subject \"a\" :from \"b\"\n<a href=\"https://test.org/\">test</a>";
        $script = new \rcube_sieve_script($input);
        $result = $script->as_text();

        // TODO: The output still is BS, but it at least does not cause an infinite loop
        $this->assertSame("require [\"vacation\"];\r\nvacation :subject \"a\" :from \"b\" \"a\";\r\n", $result);
    }

    function data_tokenizer()
    {
        return [
            [1, "text: #test\nThis is test ; message;\nMulti line\n.\n;\n", '"This is test ; message;\nMulti line"'],
            [1, "text: #test\r\nThis is test ; message;\nMulti line\r\n.\r\n;", '"This is test ; message;\nMulti line"'],
            [0, '["test1","test2"]', '[["test1","test2"]]'],
            [1, '["test"]', '["test"]'],
            [1, '"te\\"st"', '"te\\"st"'],
            [0, 'test #comment', '["test"]'],
            [0, "text:\ntest\n.\ntext:\ntest\n.\n", '["test","test"]'],
            [0, "text:\r\ntest\r\n.\r\ntext:\r\ntest\r\n.\r\n", '["test","test"]'],
            [1, '"\\a\\\\\\"a"', '"a\\\\\\"a"'],
        ];
    }

    /**
     * @dataProvider data_tokenizer
     */
    function test_tokenizer($num, $input, $output)
    {
        $res = json_encode(rcube_sieve_script::tokenize($input, $num));

        $this->assertEquals(trim($output), trim($res));
    }

    public function test_escape_string()
    {
        $output = \rcube_sieve_script::escape_string([]);
        $this->assertSame('""', $output);
        $output = \rcube_sieve_script::escape_string(['"']);
        $this->assertSame('"\""', $output);
        $output = \rcube_sieve_script::escape_string('\\a');
        $this->assertSame('"\\\\a"', $output);
        $output = \rcube_sieve_script::escape_string(['"', 'b']);
        $this->assertSame('["\"","b"]', $output);

        // Multiline text
        $input = "line1\r\nline2\n.line3\r\nline4";
        $output = \rcube_sieve_script::escape_string($input);
        $this->assertSame("text:\r\nline1\r\nline2\r\n..line3\r\nline4\r\n.\r\n", $output);
    }
}
