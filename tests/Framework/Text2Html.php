<?php

/**
 * Test class to test rcube_text2html class
 *
 * @package Tests
 */
class Framework_Text2Html extends PHPUnit\Framework\TestCase
{
    /**
     * Data for test_text2html()
     */
    function data_text2html()
    {
        $options = [
            'begin'  => '',
            'end'    => '',
            'break'  => '<br>',
            'links'  => false,
            'flowed' => false,
            'delsp'  => false,
            'wrap'   => false,
            'space'  => '_', // replace UTF-8 non-breaking space for simpler testing
            'nobr_start' => '>',
            'nobr_end'   => '<',
        ];

        $data[] = [" aaaa", ">_aaaa<", $options];
        $data[] = ["aa>aa", ">aa&gt;aa<", $options];
        $data[] = ["aaaa aaaa", ">aaaa_aaaa<", $options];
        $data[] = ["aaaa  aaaa", ">aaaa__aaaa<", $options];
        $data[] = ["aaaa   aaaa", ">aaaa___aaaa<", $options];
        $data[] = ["aaaa\taaaa", ">aaaa____aaaa<", $options];
        $data[] = ["aaaa\naaaa", "aaaa<br>aaaa", $options];
        $data[] = ["aaaa\n aaaa", "aaaa<br>>_aaaa<", $options];
        $data[] = ["aaaa\n  aaaa", "aaaa<br>>__aaaa<", $options];
        $data[] = ["aaaa\n   aaaa", "aaaa<br>>___aaaa<", $options];
        $data[] = ["\n", "<br>", $options];
        $data[] = ["\taaaa", ">____aaaa<", $options];
        $data[] = ["\naaaa", "<br>aaaa", $options];
        $data[] = ["\n aaaa", "<br>>_aaaa<", $options];
        $data[] = ["\n  aaaa", "<br>>__aaaa<", $options];
        $data[] = ["\n   aaaa", "<br>>___aaaa<", $options];
        $data[] = ["aaaa\n\nbbbb", "aaaa<br><br>bbbb", $options];
        $data[] = [">aaaa \n>aaaa", "<blockquote>>aaaa_<<br>aaaa</blockquote>", $options];
        $data[] = [">aaaa\n>aaaa", "<blockquote>aaaa<br>aaaa</blockquote>", $options];
        $data[] = [">aaaa \n>bbbb\ncccc dddd", "<blockquote>>aaaa_<<br>bbbb</blockquote>>cccc_dddd<", $options];
        $data[] = ["aaaa-bbbb/cccc", ">aaaa-bbbb/cccc<", $options];
        $data[] = ["aaaa-bbbb\r\tcccc", ">aaaa-bbbb____cccc<", $options];

        $options['flowed'] = true;

        $data[] = [" aaaa", "aaaa", $options];
        $data[] = ["aaaa aaaa", "aaaa aaaa", $options];
        $data[] = ["aaaa  aaaa", "aaaa _aaaa", $options];
        $data[] = ["aaaa   aaaa", "aaaa _ aaaa", $options];
        $data[] = ["aaaa\taaaa", "aaaa _ _aaaa", $options];
        $data[] = ["aaaa\naaaa", "aaaa<br>aaaa", $options];
        $data[] = ["aaaa\n aaaa", "aaaa<br>aaaa", $options];
        $data[] = ["aaaa\n  aaaa", "aaaa<br>_aaaa", $options];
        $data[] = ["aaaa\n   aaaa", "aaaa<br>_ aaaa", $options];
        $data[] = ["\taaaa", "_ _ aaaa", $options];
        $data[] = ["\naaaa", "<br>aaaa", $options];
        $data[] = ["\n aaaa", "<br>aaaa", $options];
        $data[] = ["\n  aaaa", "<br>_aaaa", $options];
        $data[] = ["\n   aaaa", "<br>_ aaaa", $options];
        $data[] = ["aaaa\n\nbbbb", "aaaa<br><br>bbbb", $options];
        $data[] = [">aaaa \n>aaaa", "<blockquote>aaaa aaaa</blockquote>", $options];
        $data[] = [">aaaa\n>aaaa", "<blockquote>aaaa<br>aaaa</blockquote>", $options];
        $data[] = [">aaaa \n>bbbb\ncccc dddd", "<blockquote>aaaa bbbb</blockquote>cccc dddd", $options];
        $data[] = ["\x02\x03", "\x02\x03", $options];

        $options['flowed'] = true;
        $options['delsp']  = true;

        $data[] = [" aaaa", "aaaa", $options];
        $data[] = ["aaaa aaaa", "aaaa aaaa", $options];
        $data[] = ["aaaa  aaaa", "aaaa _aaaa", $options];
        $data[] = ["aaaa   aaaa", "aaaa _ aaaa", $options];
        $data[] = ["aaaa\taaaa", "aaaa _ _aaaa", $options];
        $data[] = ["aaaa\naaaa", "aaaa<br>aaaa", $options];
        $data[] = ["aaaa\n aaaa", "aaaa<br>aaaa", $options];
        $data[] = ["aaaa\n  aaaa", "aaaa<br>_aaaa", $options];
        $data[] = ["aaaa\n   aaaa", "aaaa<br>_ aaaa", $options];
        $data[] = ["\taaaa", "_ _ aaaa", $options];
        $data[] = ["\naaaa", "<br>aaaa", $options];
        $data[] = ["\n aaaa", "<br>aaaa", $options];
        $data[] = ["\n  aaaa", "<br>_aaaa", $options];
        $data[] = ["\n   aaaa", "<br>_ aaaa", $options];
        $data[] = ["aaaa\n\nbbbb", "aaaa<br><br>bbbb", $options];
        $data[] = [">aaaa \n>aaaa", "<blockquote>aaaaaaaa</blockquote>", $options];
        $data[] = [">aaaa\n>aaaa", "<blockquote>aaaa<br>aaaa</blockquote>", $options];
        $data[] = [">aaaa \n>bbbb\ncccc dddd", "<blockquote>aaaabbbb</blockquote>cccc dddd", $options];

        $options['flowed'] = false;
        $options['delsp']  = false;
        $options['wrap']   = true;

        $data[] = [">>aaaa bbbb\n>>\n>>>\n>cccc\n\ndddd eeee",
            "<blockquote><blockquote>aaaa bbbb<br><br><blockquote><br></blockquote></blockquote>cccc</blockquote><br>dddd eeee", $options];
        $data[] = ["\n>>aaaa\n\ndddd",
            "<br><blockquote><blockquote>aaaa</blockquote></blockquote><br>dddd", $options];
        $data[] = ["aaaa\n>bbbb\n>cccc\n\ndddd\n>>test",
            "aaaa<blockquote>bbbb<br>cccc</blockquote><br>dddd<blockquote><blockquote>test</blockquote></blockquote>", $options];

        return $data;
    }

    /**
     * Test text to html conversion
     *
     * @dataProvider data_text2html
     */
    function test_text2html($input, $output, $options)
    {
        $t2h = new rcube_text2html($input, false, $options);

        $html = $t2h->get_html();

        $this->assertEquals($output, $html);
    }

    /**
     * Test XSS issue
     */
    function test_text2html_xss()
    {
        $input = "\n[<script>evil</script>]:##str_replacement_0##\n";
        $t2h = new rcube_text2html($input);

        $html = $t2h->get_html();

        $expected = "<div class=\"pre\"><br>\n"
            . "[&lt;script&gt;evil&lt;/script&gt;]:##str_replacement_0##<br>\n"
            . "</div>";

        $this->assertEquals($expected, $html);
    }

    /**
     * Test XSS issue
     */
    function test_text2html_xss2()
    {
        $input = "\n[<script>evil</script>] https://google.com\n";
        $t2h = new rcube_text2html($input);

        $html = $t2h->get_html();

        $expected = "<div class=\"pre\"><br>\n[&lt;script&gt;evil&lt;/script&gt;] "
            . "<a rel=\"noreferrer\" target=\"_blank\" href=\"https://google.com\">https://google.com</a><br>\n"
            . "</div>";

        $this->assertEquals($expected, $html);
    }

    /**
     * Test bug #8021
     */
    function test_text2html_8021()
    {
        $input = "Test1 [1]\n\n[1] http://d1.tld\n\nyou wrote:\n> Test2 [1]\n>\n> [1] http://d2.tld";
        $expected = '<div class="pre">Test1 [<a href="http://d1.tld">1</a>]'
            . "<br>\n<br>\n"
            . '[1] <a href="http://d1.tld">http://d1.tld</a>'
            . "<br>\n<br>\n"
            . 'you wrote:<blockquote>Test2 [<a href="http://d2.tld">1</a>]'
            . "<br>\n<br>\n"
            . '[1] <a href="http://d2.tld">http://d2.tld</a></blockquote></div>';

        $t2h = new rcube_text2html($input);
        $html = $t2h->get_html();
        $html = preg_replace('/ (rel|target)="(noreferrer|_blank)"/', '', $html);

        $this->assertEquals($expected, $html);
    }

    /**
     * Test patches/diffs handling
     */
    function test_text2html_patches_handling()
    {
        $input = "Start\n"
            . "diff --git a/test.txt b/test.txt\n"
            . "index 7642f44b9..6ce0170aa 100644\n"
            . "--- a/test.txt\n"
            . "+++ b/test.txt\n"
            . "@@ -1982,7 +1982,7 @@ class test\n"
            . " test1\n"
            . " test2\n"
            . " test3\n"
            . "-test4\n"
            . "+test5\n"
            . " \n"
            . "End";

        $expected = "<div class=\"pre\">Start<br>\n"
            . "diff --git a/test.txt b/test.txt<br>\n"
            . "index 7642f44b9..6ce0170aa 100644<br>\n"
            . "<span style=\"white-space:nowrap\">---_a/test.txt</span><br>\n"
            . "<span style=\"white-space:nowrap\">+++_b/test.txt</span><br>\n"
            . "<span style=\"white-space:nowrap\">@@_-1982,7_+1982,7_@@_class_test</span><br>\n"
            . "<span style=\"white-space:nowrap\">_test1</span><br>\n"
            . "<span style=\"white-space:nowrap\">_test2</span><br>\n"
            . "<span style=\"white-space:nowrap\">_test3</span><br>\n"
            . "<span style=\"white-space:nowrap\">-test4</span><br>\n"
            . "<span style=\"white-space:nowrap\">+test5</span><br>\n"
            . "<span style=\"white-space:nowrap\">_</span><br>\n"
            . "End</div>";

        $t2h = new rcube_text2html($input, false, ['space' => '_']);
        $html = $t2h->get_html();

        $this->assertEquals($expected, $html);
    }
}
