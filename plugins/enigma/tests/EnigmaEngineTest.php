<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class EnigmaEngineTest extends TestCase
{
    /**
     * Test password_handler()
     */
    public function test_password_handler()
    {
        $rcube = \rcube::get_instance();
        $engine = new \enigma_engine();

        unset($_SESSION['enigma_pass']);

        $engine->password_handler();

        $this->assertFalse(array_key_exists('enigma_pass', $_SESSION)); // @phpstan-ignore-line
        $this->assertSame([], $engine->get_passwords());

        $_POST = ['_keyid' => 'abc', '_passwd' => '123<a>456'];

        $time = time();
        $engine->password_handler();

        $store = unserialize($rcube->decrypt($_SESSION['enigma_pass']));

        $this->assertCount(2, $store['ABC']);
        $this->assertSame('123<a>456', $store['ABC'][0]);
        $this->assertEqualsWithDelta($time, $store['ABC'][1], 1);
        $this->assertSame(['ABC' => '123<a>456'], $engine->get_passwords());
    }

    /**
     * Test outgoing PGP armor formatting with LF-delimited driver output > 998 bytes (#10413)
     */
    public function test_outgoing_pgp_armor_line_endings()
    {
        $rcube = \rcube::get_instance();
        $orig_passwordless = $rcube->config->get('enigma_passwordless');
        $rcube->config->set('enigma_passwordless', true);
        unset($_SESSION['enigma_pass']);
        $driver = new class('alice@example.com') extends \enigma_driver {
            protected string $user;

            #[\Override]
            public function __construct($user)
            {
                $this->user = $user;
            }

            #[\Override]
            public function init()
            {
                return null;
            }

            #[\Override]
            public function encrypt($text, $keys, $sign_key = null)
            {
                return "-----BEGIN PGP MESSAGE-----\n\n"
                    . chunk_split(str_repeat('E', 1400), 64, "\n")
                    . "=1234\n-----END PGP MESSAGE-----\n";
            }

            #[\Override]
            public function decrypt($text, $keys = [], &$signature = null)
            {
                return '';
            }

            #[\Override]
            public function sign($text, $key, $mode = null)
            {
                if ($mode == \Crypt_GPG::SIGN_MODE_CLEAR) {
                    return "-----BEGIN PGP SIGNED MESSAGE-----\nHash: SHA512\n\n"
                        . "Test\n-----BEGIN PGP SIGNATURE-----\n\n"
                        . chunk_split(str_repeat('S', 1200), 64, "\n")
                        . "=5678\n-----END PGP SIGNATURE-----\n";
                }

                return "-----BEGIN PGP SIGNATURE-----\n\n"
                    . chunk_split(str_repeat('S', 1200), 64, "\n")
                    . "=5678\n-----END PGP SIGNATURE-----\n";
            }

            #[\Override]
            public function verify($text, $signature)
            {
                return null;
            }

            #[\Override]
            public function import($content, $isfile = false, $passwords = [])
            {
                return [];
            }

            #[\Override]
            public function export($key, $with_private = false, $passwords = [])
            {
                return "-----BEGIN PGP PUBLIC KEY BLOCK-----\n\n"
                    . chunk_split(str_repeat('K', 1400), 64, "\n")
                    . "=9abc\n-----END PGP PUBLIC KEY BLOCK-----\n";
            }

            #[\Override]
            public function list_keys($pattern = '')
            {
                $uid = new \enigma_userid();
                $uid->email = $this->user;
                $uid->valid = true;
                $uid->revoked = false;

                $sub = new \enigma_subkey();
                $sub->id = 'DD9D87B5A67EC7D0';
                $sub->has_private = true;
                $sub->revoked = false;
                $sub->usage = \enigma_key::CAN_ENCRYPT | \enigma_key::CAN_SIGN;

                $key = new \enigma_key();
                $key->id = 'DD9D87B5A67EC7D0';
                $key->name = 'Alice Test <' . $this->user . '>';
                $key->users = [$uid];
                $key->subkeys = [$sub];

                return [$key];
            }

            #[\Override]
            public function get_key($keyid)
            {
                return $this->list_keys()[0];
            }

            #[\Override]
            public function gen_key($data)
            {
                return null;
            }

            #[\Override]
            public function delete_key($keyid)
            {
                return true;
            }

            #[\Override]
            public function signature_algorithm()
            {
                return 'sha512';
            }
        };

        $engine = new \enigma_engine();
        \Roundcube\Tests\setProperty($engine, 'pgp_driver', $driver);

        $create_mail = static function () {
            $mail = new \Mail_mime("\r\n");
            $mail->setParam('text_encoding', '7bit');
            $mail->setParam('text_charset', 'US-ASCII');
            $mail->setTXTBody("Test\r\n");
            $mail->headers([
                'From' => 'alice@example.com',
                'To' => 'alice@example.com',
                'Subject' => 'Test',
            ]);

            return $mail;
        };

        // 1. Unencrypted mail with attached public key
        $mail = $create_mail();
        $this->assertTrue($engine->attach_public_key($mail));
        $raw = $mail->getMessage();
        $this->assertStringContainsString("-----BEGIN PGP PUBLIC KEY BLOCK-----\r\n", $raw);
        $this->assertStringContainsString("-----END PGP PUBLIC KEY BLOCK-----\r\n", $raw);
        $this->assertDoesNotMatchRegularExpression('/(?<!\r)\n/', $raw);

        // 2. Encrypted mail with attached public key (PGP/MIME)
        $mail = $create_mail();
        $this->assertTrue($engine->attach_public_key($mail));
        $this->assertNull($engine->encrypt_message($mail));
        $raw = $mail->getMessage();
        $this->assertStringContainsString("-----BEGIN PGP MESSAGE-----\r\n", $raw);
        $this->assertStringContainsString("-----END PGP MESSAGE-----\r\n", $raw);
        $this->assertDoesNotMatchRegularExpression('/(?<!\r)\n/', $raw);

        // 3. Inline encrypted mail without attachments (ENCRYPT_MODE_BODY)
        $mail = $create_mail();
        $this->assertNull($engine->encrypt_message($mail, \enigma_engine::ENCRYPT_MODE_BODY));
        $raw = $mail->getMessage();
        $this->assertStringContainsString("-----BEGIN PGP MESSAGE-----\r\n", $raw);
        $this->assertStringContainsString("-----END PGP MESSAGE-----\r\n", $raw);
        $this->assertDoesNotMatchRegularExpression('/(?<!\r)\n/', $raw);

        // 4. Inline clearsigned mail (SIGN_MODE_BODY)
        $mail = $create_mail();
        $this->assertNull($engine->sign_message($mail, \enigma_engine::SIGN_MODE_BODY));
        $raw = $mail->getMessage();
        $this->assertStringContainsString("-----BEGIN PGP SIGNED MESSAGE-----\r\n", $raw);
        $this->assertStringContainsString("-----BEGIN PGP SIGNATURE-----\r\n", $raw);
        $this->assertStringContainsString("-----END PGP SIGNATURE-----\r\n", $raw);
        $this->assertDoesNotMatchRegularExpression('/(?<!\r)\n/', $raw);

        // 5. PGP/MIME signed mail with attached public key (SIGN_MODE_MIME)
        $mail = $create_mail();
        $this->assertTrue($engine->attach_public_key($mail));
        $this->assertNull($engine->sign_message($mail, \enigma_engine::SIGN_MODE_MIME));
        $raw = $mail->getMessage();
        $this->assertStringContainsString("-----BEGIN PGP PUBLIC KEY BLOCK-----\r\n", $raw);
        $this->assertStringContainsString("-----END PGP PUBLIC KEY BLOCK-----\r\n", $raw);
        $this->assertStringContainsString("-----BEGIN PGP SIGNATURE-----\r\n", $raw);
        $this->assertStringContainsString("-----END PGP SIGNATURE-----\r\n", $raw);
        $this->assertDoesNotMatchRegularExpression('/(?<!\r)\n/', $raw);

        $rcube->config->set('enigma_passwordless', $orig_passwordless);
    }

    /**
     * End-to-end GnuPG round-trip test for encrypted mail with attached public key (#10413)
     */
    public function test_gnupg_encrypt_with_attached_pubkey_roundtrip()
    {
        exec('gpg --version 2>&1', $out, $retval);
        if ($retval !== 0) {
            $this->markTestSkipped('gpg binary not available');
        }

        $rcube = \rcmail::get_instance();
        $homedir = sys_get_temp_dir() . '/rcube-enigma-test-' . uniqid();
        mkdir($homedir, 0700, true);

        $orig_homedir = $rcube->config->get('enigma_pgp_homedir');
        $orig_passwordless = $rcube->config->get('enigma_passwordless');
        $orig_user = $rcube->user;

        try {
            $rcube->config->set('enigma_pgp_homedir', $homedir);
            $rcube->config->set('enigma_passwordless', true);
            unset($_SESSION['enigma_pass']);
            $user = new \rcube_user();
            $user->data = ['username' => 'alice@example.com'];
            $rcube->set_user($user);

            $engine = new \enigma_engine();
            $import = $engine->import_key(__DIR__ . '/src/keys.asc', true);
            $this->assertIsArray($import);
            $this->assertGreaterThan(0, $import['imported']);

            $mail = new \Mail_mime("\r\n");
            $mail->setParam('text_encoding', '7bit');
            $mail->setParam('text_charset', 'US-ASCII');
            $mail->setTXTBody("Test\r\n");
            $mail->headers([
                'From' => 'alice@example.com',
                'To' => 'alice@example.com',
                'Subject' => 'Test',
            ]);

            $this->assertTrue($engine->attach_public_key($mail));
            $this->assertNull($engine->encrypt_message($mail));

            $raw = $mail->getMessage();

            $this->assertStringContainsString("-----BEGIN PGP MESSAGE-----\r\n", $raw);
            $this->assertStringContainsString("-----END PGP MESSAGE-----\r\n", $raw);
            $this->assertStringNotContainsString("-----BEGIN PGP\r\nMESSAGE-----", $raw);
            $this->assertStringNotContainsString("-----END\r\nPGP MESSAGE-----", $raw);
            $this->assertDoesNotMatchRegularExpression('/(?<!\r)\n/', $raw);

            $parsed = \rcube_mime::parse_message($raw);
            $this->assertSame('multipart/encrypted', $parsed->mimetype);
            $this->assertCount(2, $parsed->parts);

            $decrypted = $parsed->parts[1]->body;
            $res = \Roundcube\Tests\invokeMethod($engine, 'pgp_decrypt', [&$decrypted]);
            $this->assertTrue($res);

            $inner = \Roundcube\Tests\invokeMethod($engine, 'parse_body', [&$decrypted]);
            $this->assertSame('multipart/mixed', $inner->mimetype);
            $this->assertSame("Test\r\n", $inner->parts[0]->body);
            $this->assertSame('application/pgp-keys', $inner->parts[1]->mimetype);
            $this->assertStringStartsWith("-----BEGIN PGP PUBLIC KEY BLOCK-----\r\n", $inner->parts[1]->body);
            $this->assertStringEndsWith("-----END PGP PUBLIC KEY BLOCK-----\r\n", $inner->parts[1]->body);
        } finally {
            $rcube->config->set('enigma_pgp_homedir', $orig_homedir);
            $rcube->config->set('enigma_passwordless', $orig_passwordless);
            if ($orig_user) {
                $rcube->set_user($orig_user);
            }
            \enigma_engine::delete_dir($homedir);
        }
    }
}
