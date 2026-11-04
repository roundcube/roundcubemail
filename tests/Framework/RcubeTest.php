<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\RequiresFunction;
use PHPUnit\Framework\TestCase;

use function Roundcube\Tests\invokeMethod;

/**
 * Test class to test rcube class
 */
class RcubeTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = \rcube::get_instance();

        $this->assertInstanceOf(\rcube::class, $object, 'Class singleton');
    }

    /**
     * rcube::read_localization()
     */
    public function test_read_localization()
    {
        $rcube = \rcube::get_instance();
        $result = $rcube->read_localization(INSTALL_PATH . 'plugins/acl/localization', 'pl_PL');

        $this->assertSame('Zapis', $result['aclwrite']);
    }

    /**
     * rcube::list_languages()
     */
    public function test_list_languages()
    {
        $rcube = \rcube::get_instance();
        $result = $rcube->list_languages();

        $this->assertSame('English (US)', $result['en_US']);
    }

    /**
     * rcube::encrypt() and rcube::decrypt()
     */
    public function test_encrypt_and_decrypt()
    {
        $rcube = \rcube::get_instance();

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
    #[RequiresFunction('shell_exec')]
    public function test_exec()
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            $this->assertSame('', \rcube::exec('where.exe unknown-command-123 2> nul'));
            $this->assertSame('12', \rcube::exec('set /a 10 + {v}', ['v' => '2']));

            return;
        }

        $this->assertSame('', \rcube::exec('which unknown-command-123 2> /dev/null'));
        $this->assertSame("2038\n", \rcube::exec('date --date={date} +%Y', ['date' => '@2147483647']));
        // TODO: More cases
    }

    /**
     * rcube::get_user_log_dir()
     */
    public function test_get_user_log_dir()
    {
        $rcube = \rcube::get_instance();
        $log_dir = sys_get_temp_dir() . '/rcube-log-test-' . uniqid();
        $default_log_dir = $rcube->config->get('log_dir');
        $session_user = $_SESSION['username'] ?? null;

        mkdir($log_dir . '/logs/user@example.org', 0777, true);
        mkdir($log_dir . '/elsewhere', 0777, true);
        $rcube->config->set('log_dir', $log_dir . '/logs');

        try {
            $_SESSION['username'] = 'user@example.org';
            $this->assertSame($log_dir . '/logs/user@example.org', invokeMethod($rcube, 'get_user_log_dir'));

            foreach (['', '.', '..', '../elsewhere', '..\\elsewhere', "user\0x", 'missing'] as $name) {
                $_SESSION['username'] = $name;
                $this->assertFalse(invokeMethod($rcube, 'get_user_log_dir'), "Username: {$name}");
            }
        } finally {
            $rcube->config->set('log_dir', $default_log_dir);
            $_SESSION['username'] = $session_user;
            rmdir($log_dir . '/logs/user@example.org');
            rmdir($log_dir . '/logs');
            rmdir($log_dir . '/elsewhere');
            rmdir($log_dir);
        }
    }
}
