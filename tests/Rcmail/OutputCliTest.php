<?php

namespace Roundcube\Tests\Rcmail;

use rcmail_output_cli as rcmail_output_cli;
use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_output_cli class
 */
class OutputCliTest extends ActionTestCase
{
    /**
     * Test show_message() method
     */
    public function test_show_message()
    {
        $rcmail = \rcube::get_instance();
        $output = new \rcmail_output_cli();

        ob_start();
        $output->show_message('unknown');
        $out = ob_get_contents();
        ob_end_clean();

        $this->assertSame('[NOTICE] unknown', trim($out));

        ob_start();
        $output->show_message('errortitle', 'error');
        $out = ob_get_contents();
        ob_end_clean();

        $this->assertSame('[ERROR] An error occurred!', trim($out));
    }
}
