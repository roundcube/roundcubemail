<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ManagesieveScriptTest extends TestCase
{
    /**
     * Sieve script parsing
     *
     * @dataProvider provide_parser_cases
     */
    #[DataProvider('provide_parser_cases')]
    public function test_parser($input, $output, $message)
    {
        // get capabilities list from the script
        $caps = [];
        if (preg_match('/require \[([a-z0-9", ]+)\]/', $input, $m)) {
            foreach (explode(',', $m[1]) as $cap) {
                $caps[] = trim($cap, '" ');
            }
        }

        $script = new \rcube_sieve_script($input, $caps);
        $result = $script->as_text();

        $this->assertSame(trim($output), trim($result), $message);
    }

    /**
     * Data provider for test_parser()
     */
    public static function provide_parser_cases(): iterable
    {
        $dir_path = realpath(__DIR__ . '/src');
        $dir = opendir($dir_path);
        $result = [];

        while ($file = readdir($dir)) {
            if (preg_match('/^[a-z0-9_]+$/', $file)) {
                $input = file_get_contents($dir_path . '/' . $file);

                if (file_exists($dir_path . '/' . $file . '.out')) {
                    $output = file_get_contents($dir_path . '/' . $file . '.out');
                } else {
                    $output = $input;
                }

                $result[] = [
                    'input' => $input,
                    'output' => $output,
                    'message' => "Error in parsing '{$file}' file",
                ];
            }
        }

        return $result;
    }

    public static function provide_tokenizer_cases(): iterable
    {
        return [
            [1, "text: #test\nThis is test ; message;\nMulti line\n.\n;\n", '"This is test ; message;\nMulti line"'],
            [1, "text: #test\r\nThis is test ; message;\nMulti line\r\n.\r\n;", '"This is test ; message;\nMulti line"'],
            [0, '["test1","test2"]', '[["test1","test2"]]'],
            [1, '["test"]', '["test"]'],
            [1, '"te\"st"', '"te\"st"'],
            [0, 'test #comment', '["test"]'],
            [0, "text:\ntest\n.\ntext:\ntest\n.\n", '["test","test"]'],
            [0, "text:\r\ntest\r\n.\r\ntext:\r\ntest\r\n.\r\n", '["test","test"]'],
            [1, '"\a\\\\\"a"', '"a\\\\\"a"'],
        ];
    }

    /**
     * @dataProvider provide_tokenizer_cases
     */
    #[DataProvider('provide_tokenizer_cases')]
    public function test_tokenizer($num, $input, $output)
    {
        $res = json_encode(\rcube_sieve_script::tokenize($input, $num));

        $this->assertSame(trim($output), trim($res));
    }
}
