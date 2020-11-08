<?php

/**
 * Test class to test rcmail_output_json class
 *
 * @package Tests
 */
class Rcmail_RcmailOutputJson extends ActionTestCase
{
    /**
     * Test show_message() method
     */
    function test_show_message()
    {
        $rcmail = rcube::get_instance();
        $output = new rcmail_output_json();

        $reflection = new ReflectionClass($output);
        $commands   = $reflection->getProperty('commands');
        $commands->setAccessible(true);

        $output->show_message('unknown');

        $this->assertSame([['display_message', 'unknown', 'notice', 0]], $commands->getValue($output));
    }
}
