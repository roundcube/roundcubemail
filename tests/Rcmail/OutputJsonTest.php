<?php

namespace Roundcube\Tests\Rcmail;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\ExitException;

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
        $commands = $reflection->getProperty('commands');

        $output->show_message('unknown');

        $this->assertSame([['display_message', 'unknown', 'notice', 0]], $commands->getValue($output));
    }

    /**
     * Test js_calls in the response
     */
    public function test_send_js_calls()
    {
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'mail', 'test');

        $output->command('parent.set_quota', ['a' => '</b>']);
        $output->command('list_contacts');

        try {
            $output->send();
        } catch (ExitException $e) {
        }

        $result = $output->getOutput();

        $this->assertSame([['set_quota', ['a' => '</b>']], ['list_contacts']], $result['js_calls']);
        $this->assertArrayNotHasKey('exec', $result);
    }
}
