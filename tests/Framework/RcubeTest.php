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
        $temp_dir = sys_get_temp_dir() . '/rcube-tests-' . uniqid();
        $log_dir = $temp_dir . '/logs';

        mkdir($log_dir . '/alice', 0700, true);
        mkdir($temp_dir . '/elsewhere');

        $default_log_dir = $rcube->config->get('log_dir');
        $user = $rcube->user;

        $rcube->config->set('log_dir', $log_dir);
        $rcube->user = null;

        try {
            $_SESSION['username'] = 'alice';
            $this->assertSame($log_dir . '/alice', invokeMethod($rcube, 'get_user_log_dir'));

            // The user name must not point outside of the log directory
            foreach (['../elsewhere', 'alice/../../elsewhere', '..'] as $username) {
                $_SESSION['username'] = $username;
                $this->assertFalse(invokeMethod($rcube, 'get_user_log_dir'), $username);
            }
        } finally {
            unset($_SESSION['username']);
            $rcube->user = $user;
            $rcube->config->set('log_dir', $default_log_dir);

            rmdir($log_dir . '/alice');
            rmdir($log_dir);
            rmdir($temp_dir . '/elsewhere');
            rmdir($temp_dir);
        }
    }

    /**
     * rcube::gc_temp()
     */
    public function test_gc_temp()
    {
        $rcube = \rcube::get_instance();
        $db = $rcube->get_dbh();
        $temp_dir = sys_get_temp_dir() . '/rcube-tests-' . uniqid();
        $live_file = $temp_dir . '/' . RCUBE_TEMP_FILE_PREFIX . 'live';
        $dead_file = $temp_dir . '/' . RCUBE_TEMP_FILE_PREFIX . 'dead';
        $created = $db->now(-8 * 3600);

        $default_temp_dir = $rcube->config->get('temp_dir');
        $default_temp_dir_ttl = $rcube->config->get('temp_dir_ttl');

        mkdir($temp_dir);
        touch($live_file, time() - 8 * 3600);
        touch($dead_file, time() - 8 * 3600);

        $rcube->config->set('temp_dir', $temp_dir);
        $rcube->config->set('temp_dir_ttl', '6h');

        $db->query('INSERT INTO ' . $db->table_name('session', true)
            . ' (`sess_id`, `vars`, `ip`, `expires_at`)'
            . " VALUES ('live-session', '', '127.0.0.1', " . $db->now(3600) . ')');

        foreach (['live' => $live_file, 'dead' => $dead_file] as $name => $path) {
            $db->query('INSERT INTO ' . $db->table_name('uploads', true)
                . ' (`created`, `session_id`, `upload_id`, `group`, `metadata`)'
                . " VALUES ({$created}, ?, ?, 'compose', ?)",
                "{$name}-session", "{$name}-upload", json_encode(['path' => $path]));
        }

        try {
            $rcube->gc_temp();

            // Uploads of a session that is still alive are kept (#9732)
            $this->assertFileExists($live_file);
            $this->assertFileDoesNotExist($dead_file);

            $sql_result = $db->query('SELECT `upload_id` FROM ' . $db->table_name('uploads', true)
                . " WHERE `upload_id` IN ('live-upload', 'dead-upload')");
            $this->assertSame(['upload_id' => 'live-upload'], $db->fetch_assoc($sql_result));
            $this->assertFalse($db->fetch_assoc($sql_result));
        } finally {
            $rcube->config->set('temp_dir', $default_temp_dir);
            $rcube->config->set('temp_dir_ttl', $default_temp_dir_ttl);

            $db->query('DELETE FROM ' . $db->table_name('uploads', true) . " WHERE `upload_id` = 'live-upload'");
            $db->query('DELETE FROM ' . $db->table_name('session', true) . " WHERE `sess_id` = 'live-session'");

            @unlink($live_file);
            @unlink($dead_file);
            rmdir($temp_dir);
        }
    }
}
