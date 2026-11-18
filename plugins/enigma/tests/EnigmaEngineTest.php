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
     * Test that LF-delimited PGP blocks > 998 bytes are not corrupted when building outgoing mail (#10413)
     */
    public function test_outgoing_pgp_armor_preserved()
    {
        $rcube = \rcube::get_instance();
        $orig_passwordless = $rcube->config->get('enigma_passwordless');
        $rcube->config->set('enigma_passwordless', true);
        unset($_SESSION['enigma_pass']);

        $expected_encrypted = "-----BEGIN PGP MESSAGE-----\n\n"
            . chunk_split(str_repeat('E', 1400), 64, "\n")
            . "=1234\n-----END PGP MESSAGE-----\n";
        $expected_clearsigned = "-----BEGIN PGP SIGNED MESSAGE-----\nHash: SHA512\n\n"
            . "Test\n-----BEGIN PGP SIGNATURE-----\n\n"
            . chunk_split(str_repeat('S', 1200), 64, "\n")
            . "=5678\n-----END PGP SIGNATURE-----\n";
        $expected_signature = "-----BEGIN PGP SIGNATURE-----\n\n"
            . chunk_split(str_repeat('S', 1200), 64, "\n")
            . "=5678\n-----END PGP SIGNATURE-----\n";
        $expected_pubkey = "-----BEGIN PGP PUBLIC KEY BLOCK-----\n\n"
            . chunk_split(str_repeat('K', 1400), 64, "\n")
            . "=9abc\n-----END PGP PUBLIC KEY BLOCK-----\n";
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

        try {
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
            $this->assertStringContainsString($expected_pubkey, $mail->getMessage());

            // 2. Encrypted mail with attached public key (PGP/MIME)
            $mail = $create_mail();
            $this->assertTrue($engine->attach_public_key($mail));
            $this->assertNull($engine->encrypt_message($mail));
            $this->assertStringContainsString($expected_encrypted, $mail->getMessage());

            // 3. Inline encrypted mail without attachments (ENCRYPT_MODE_BODY)
            $mail = $create_mail();
            $this->assertNull($engine->encrypt_message($mail, \enigma_engine::ENCRYPT_MODE_BODY));
            $this->assertStringContainsString($expected_encrypted, $mail->getMessage());

            // 4. Inline clearsigned mail (SIGN_MODE_BODY)
            $mail = $create_mail();
            $this->assertNull($engine->sign_message($mail, \enigma_engine::SIGN_MODE_BODY));
            $this->assertStringContainsString($expected_clearsigned, $mail->getMessage());

            // 5. PGP/MIME signed mail with attached public key (SIGN_MODE_MIME)
            $mail = $create_mail();
            $this->assertTrue($engine->attach_public_key($mail));
            $this->assertNull($engine->sign_message($mail, \enigma_engine::SIGN_MODE_MIME));
            $raw = $mail->getMessage();
            $this->assertStringContainsString($expected_pubkey, $raw);
            $this->assertStringContainsString($expected_signature, $raw);
        } finally {
            $rcube->config->set('enigma_passwordless', $orig_passwordless);
        }
    }

    /**
     * End-to-end GnuPG encryption + attached public key round-trip (#10413)
     */
    public function test_gnupg_encrypt_roundtrip()
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

            // 1. PGP/MIME encrypted message with attached public key
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
            $this->assertMatchesRegularExpression('/-----BEGIN PGP MESSAGE-----\r?\n/', $raw);
            $this->assertMatchesRegularExpression('/-----END PGP MESSAGE-----\r?\n/', $raw);

            $parsed = \rcube_mime::parse_message(preg_replace('/\r?\n/', "\r\n", $raw));
            $this->assertSame('multipart/encrypted', $parsed->mimetype);
            $this->assertCount(2, $parsed->parts);

            $decrypted = $parsed->parts[1]->body;
            $res = \Roundcube\Tests\invokeMethod($engine, 'pgp_decrypt', [&$decrypted]);
            $this->assertTrue($res);

            $inner = \Roundcube\Tests\invokeMethod($engine, 'parse_body', [&$decrypted]);
            $this->assertSame('multipart/mixed', $inner->mimetype);
            $this->assertSame("Test\r\n", $inner->parts[0]->body);
            $this->assertSame('application/pgp-keys', $inner->parts[1]->mimetype);
            $this->assertMatchesRegularExpression('/^-----BEGIN PGP PUBLIC KEY BLOCK-----\r?\n/', $inner->parts[1]->body);
            $this->assertMatchesRegularExpression('/-----END PGP PUBLIC KEY BLOCK-----\r?\n$/', $inner->parts[1]->body);

            $key_reimport = $engine->import_key($inner->parts[1]->body);
            $this->assertIsArray($key_reimport);
            $this->assertSame(1, $key_reimport['unchanged']);

            // 2. Inline PGP encrypted message > 998 bytes without attachments
            $lines = [];
            for ($i = 0; $i < 25; $i++) {
                $lines[] = hash('sha256', 'line-' . $i);
            }
            $long_text = implode("\r\n", $lines) . "\r\n";
            $mail = new \Mail_mime("\r\n");
            $mail->setParam('text_encoding', '7bit');
            $mail->setParam('text_charset', 'US-ASCII');
            $mail->setTXTBody($long_text);
            $mail->headers([
                'From' => 'alice@example.com',
                'To' => 'alice@example.com',
                'Subject' => 'Inline Test',
            ]);

            $this->assertNull($engine->encrypt_message($mail));

            $raw_inline = $mail->getMessage();
            $this->assertGreaterThan(998, strlen($raw_inline));
            $this->assertMatchesRegularExpression('/-----BEGIN PGP MESSAGE-----\r?\n/', $raw_inline);
            $this->assertMatchesRegularExpression('/-----END PGP MESSAGE-----\r?\n/', $raw_inline);

            $parsed_inline = \rcube_mime::parse_message(preg_replace('/\r?\n/', "\r\n", $raw_inline));
            $decrypted_inline = $parsed_inline->body;
            $res_inline = \Roundcube\Tests\invokeMethod($engine, 'pgp_decrypt', [&$decrypted_inline]);
            $this->assertTrue($res_inline);
            $this->assertSame(
                preg_replace('/\r?\n/', "\r\n", $long_text),
                preg_replace('/\r?\n/', "\r\n", $decrypted_inline)
            );
        } finally {
            $rcube->config->set('enigma_pgp_homedir', $orig_homedir);
            $rcube->config->set('enigma_passwordless', $orig_passwordless);
            if ($orig_user) {
                $rcube->set_user($orig_user);
            }
            \enigma_engine::delete_dir($homedir);
        }
    }

    /**
     * End-to-end GnuPG signing + attached public key round-trip (#10413)
     */
    public function test_gnupg_sign_roundtrip()
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

            // PGP/MIME signed message with attached public key
            $mail = new \Mail_mime("\r\n");
            $mail->setParam('text_encoding', '7bit');
            $mail->setParam('text_charset', 'US-ASCII');
            $mail->setTXTBody("Test\r\n");
            $mail->headers([
                'From' => 'alice@example.com',
                'To' => 'alice@example.com',
                'Subject' => 'Signed Test',
            ]);

            $this->assertTrue($engine->attach_public_key($mail));
            $this->assertNull($engine->sign_message($mail, \enigma_engine::SIGN_MODE_MIME));

            $body = $mail->get();
            $headers = $mail->headers();
            $this->assertSame(1, preg_match('/boundary="([^"]+)"/', $headers['Content-Type'], $m));

            [$signed_body, $sig_body] = \Roundcube\Tests\invokeMethod($engine, 'explode_signed_body', [$body, $m[1]]);
            $this->assertMatchesRegularExpression('/^-----BEGIN PGP PUBLIC KEY BLOCK-----\r?\n/m', $signed_body);
            $this->assertMatchesRegularExpression('/^-----END PGP PUBLIC KEY BLOCK-----\r?\n/m', $signed_body);
            $this->assertMatchesRegularExpression('/^-----BEGIN PGP SIGNATURE-----\r?\n/', $sig_body);

            $sig = \Roundcube\Tests\invokeMethod($engine, 'pgp_verify', [&$signed_body, $sig_body]);
            $this->assertInstanceOf(\enigma_signature::class, $sig);
            $this->assertTrue($sig->valid);

            // Inline clearsigned message (SIGN_MODE_BODY) > 998 bytes
            $clear_mail = new \Mail_mime("\r\n");
            $clear_mail->setParam('text_encoding', '7bit');
            $clear_mail->setParam('text_charset', 'US-ASCII');
            $clear_mail->setTXTBody(str_repeat("Clearsigned test line.\r\n", 30));
            $clear_mail->headers([
                'From' => 'alice@example.com',
                'To' => 'alice@example.com',
                'Subject' => 'Clearsigned Test',
            ]);

            $this->assertNull($engine->sign_message($clear_mail, \enigma_engine::SIGN_MODE_BODY));

            $clear_body = $clear_mail->get();
            $this->assertGreaterThan(998, strlen($clear_body));
            $this->assertMatchesRegularExpression('/^-----BEGIN PGP SIGNED MESSAGE-----\r?\n/', $clear_body);
            $this->assertMatchesRegularExpression('/-----BEGIN PGP SIGNATURE-----\r?\n/', $clear_body);
            $this->assertMatchesRegularExpression('/-----END PGP SIGNATURE-----\r?\n$/', $clear_body);

            $clear_sig = \Roundcube\Tests\invokeMethod($engine, 'pgp_verify', [&$clear_body]);
            $this->assertInstanceOf(\enigma_signature::class, $clear_sig);
            $this->assertTrue($clear_sig->valid);
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
