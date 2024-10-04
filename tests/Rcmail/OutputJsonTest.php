<?php

namespace Roundcube\Tests\Rcmail;

use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_output_json class
 */
class OutputJsonTest extends ActionTestCase
{
    /**
     * Test show_message() method
     */
    public function test_show_message()
    {
        $rcmail = \rcube::get_instance();
        $output = new \rcmail_output_json();

        $reflection = new \ReflectionClass($output);
        $js_calls = $reflection->getProperty('js_calls');
        $js_calls->setAccessible(true);

        $output->show_message('unknown');

        $this->assertSame([['display_message', 'unknown', 'notice', 0]], $js_calls->getValue($output));
    }
}
