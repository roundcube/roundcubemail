<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube class
 */
class Framework_Rcube extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = rcube::get_instance();

        $this->assertInstanceOf('rcube', $object, 'Class singleton');
    }

    /**
     * rcube::read_localization()
     */
    public function test_read_localization()
    {
        $rcube = rcube::get_instance();
        $result = $rcube->read_localization(INSTALL_PATH . 'plugins/acl/localization', 'pl_PL');

        $this->assertSame('Zapis', $result['aclwrite']);
    }

    /**
     * rcube::list_languages()
     */
    public function test_list_languages()
    {
        $rcube = rcube::get_instance();
        $result = $rcube->list_languages();

        $this->assertSame('English (US)', $result['en_US']);
    }

    /**
     * Test that gen_message_id produces a message-ID that can be
     * used safely in References and In-Reply-To messages.
     */
    function test_gen_message_id()
    {
        $rcube = rcube::get_instance();

        $result = $rcube->gen_message_id('a@example.com');
        $result = preg_replace('/.*@/', '', $result);
        $this->assertSame('example.com>', $result);

        $result = $rcube->gen_message_id('a@dømi.fo');
        $result = preg_replace('/.*@/', '', $result);
        $this->assertSame('xn--dmi-0na.fo>', $result);
   }

    /**
     * rcube::encrypt() and rcube::decrypt()
     */
    public function test_encrypt_and_decrypt()
    {
        $rcube = rcube::get_instance();

        $result = $rcube->decrypt($rcube->encrypt('test'));
        $this->assertSame('test', $result);

        // Test AEAD cipher method
        $defaultCipherMethod = $rcube->config->get('cipher_method');
        $rcube->config->set('cipher_method', 'aes-256-gcm');
        try {
            $result = $rcube->decrypt($rcube->encrypt('test'));
            $this->assertSame('test', $result);
        } finally {
            $rcube->config->set('cipher_method', $defaultCipherMethod);
        }
    }

    /**
     * rcube::exec()
     *
     * @requires function shell_exec
     */
    public function test_exec()
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            $this->assertSame('', rcube::exec('where.exe unknown-command-123 2> nul'));
            $this->assertSame('12', rcube::exec('set /a 10 + {v}', ['v' => '2']));

            return;
        }

        $this->assertSame('', rcube::exec('which unknown-command-123'));
        $this->assertSame("2038\n", rcube::exec('date --date={date} +%Y', ['date' => '@2147483647']));
        // TODO: More cases
    }
}
