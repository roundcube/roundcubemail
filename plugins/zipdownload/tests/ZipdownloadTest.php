<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class ZipdownloadTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \zipdownload($rcube->plugins);

        $this->assertInstanceOf('zipdownload', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * Every line starting with "From " must be quoted in the Mbox output,
     * regardless of how the message body is split into stream chunks.
     *
     * Regression test for the case where the body is written in large chunks
     * (rcube_imap_generic::handlePartBody() writes up to 1 MB at a time), not
     * line by line, so interior "From " lines were left unescaped.
     */
    public function test_mbox_from_escaping()
    {
        // Loading the plugin also defines the mbox stream filter class
        $this->assertTrue(class_exists('zipdownload'));
        $this->assertTrue(class_exists('zipdownload_mbox_filter'));

        stream_filter_register('zipdownload_mbox_test', 'zipdownload_mbox_filter');

        $body = "Received: from mx\r\n"
            . "\r\n"
            . "Hello,\r\n"
            . "From all of us, hi\r\n"       // must be quoted
            . "Fromage is not a delimiter\r\n" // must NOT be quoted
            . ">From already quoted\r\n"       // must get one more quote (mboxrd)
            . "Bye\r\n";

        $expected = "Received: from mx\r\n"
            . "\r\n"
            . "Hello,\r\n"
            . ">From all of us, hi\r\n"
            . "Fromage is not a delimiter\r\n"
            . ">>From already quoted\r\n"
            . "Bye\r\n";

        // Test a single write and a byte-by-byte write (which splits lines,
        // and even the word "From ", across filter buckets).
        $chunkings = [
            [strlen($body)],
            array_fill(0, strlen($body), 1),
        ];

        foreach ($chunkings as $chunks) {
            $tmp = tempnam(sys_get_temp_dir(), 'mboxtest');
            $fp = fopen($tmp, 'w');
            $filter = stream_filter_append($fp, 'zipdownload_mbox_test', \STREAM_FILTER_WRITE);

            $offset = 0;
            foreach ($chunks as $len) {
                fwrite($fp, substr($body, $offset, $len));
                $offset += $len;
            }

            stream_filter_remove($filter);
            fclose($fp);

            $result = file_get_contents($tmp);
            unlink($tmp);

            $this->assertSame($expected, $result);
        }
    }
}
