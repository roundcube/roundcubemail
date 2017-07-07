<?php

/**
 * Test class to test rcube_csv2vcard class
 *
 * @package Tests
 */
class Framework_Csv2vcard extends PHPUnit_Framework_TestCase
{

    function test_import_generic()
    {
        $csv = new rcube_csv2vcard;

        // empty input
        $csv->import('');
        $this->assertSame(array(), $csv->export());
    }

    function test_import_tb_plain()
    {
        $csv_text = file_get_contents(TESTS_DIR . '/src/Csv2vcard/tb_plain.csv');
        $vcf_text = file_get_contents(TESTS_DIR . '/src/Csv2vcard/tb_plain.vcf');

        $csv = new rcube_csv2vcard;
        $csv->import($csv_text);
        $result = $csv->export();
        $vcard = $result[0]->export(false);

        $this->assertCount(1, $result);

        $vcf_text = trim(str_replace("\r\n", "\n", $vcf_text));
        $vcard    = trim(str_replace("\r\n", "\n", $vcard));

        $this->assertEquals($vcf_text, $vcard);
    }

    function test_import_email()
    {
        $csv_text = file_get_contents(TESTS_DIR . '/src/Csv2vcard/email.csv');
        $vcf_text = file_get_contents(TESTS_DIR . '/src/Csv2vcard/email.vcf');

        $csv = new rcube_csv2vcard;
        $csv->import($csv_text);
        $result = $csv->export();

        $this->assertCount(4, $result);

        $vcard = '';
        foreach ($result as $vcf) {
            $vcard .= $vcf->export(false) . "\n";
        }

        $vcf_text = trim(str_replace("\r\n", "\n", $vcf_text));
        $vcard    = trim(str_replace("\r\n", "\n", $vcard));
        $this->assertEquals($vcf_text, $vcard);
    }

    function test_import_gmail()
    {
        $csv_text = file_get_contents(TESTS_DIR . '/src/Csv2vcard/gmail.csv');
        $vcf_text = file_get_contents(TESTS_DIR . '/src/Csv2vcard/gmail.vcf');

        $csv = new rcube_csv2vcard;
        $csv->import($csv_text);
        $result = $csv->export();
        $vcard  = $result[0]->export(false);

        $this->assertCount(1, $result);

        $vcf_text = trim(str_replace("\r\n", "\n", $vcf_text));
        $vcard    = trim(str_replace("\r\n", "\n", $vcard));

        $this->assertEquals($vcf_text, $vcard);
    }

    function test_import_outlook()
    {
        $csv_text = file_get_contents(TESTS_DIR . '/src/Csv2vcard/outlook.csv');
        $vcf_text = file_get_contents(TESTS_DIR . '/src/Csv2vcard/outlook.vcf');

        $csv = new rcube_csv2vcard;
        $csv->import($csv_text);
        $result = $csv->export();
        $vcard  = $result[0]->export(false);

        $this->assertCount(1, $result);

        $vcf_text = trim(str_replace("\r\n", "\n", $vcf_text));
        $vcard    = trim(str_replace("\r\n", "\n", $vcard));

        $this->assertEquals($vcf_text, $vcard);
    }
}
