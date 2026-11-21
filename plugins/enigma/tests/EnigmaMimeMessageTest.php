<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class EnigmaMimeMessageTest extends TestCase
{
    /**
     * Test isMultipart()
     */
    public function test_is_multipart()
    {
        $mime = new \Mail_mime();
        $message1 = new \enigma_mime_message($mime, \enigma_mime_message::PGP_SIGNED);

        $this->assertFalse($message1->isMultipart());

        $mime->setHTMLBody('<html></html>');
        $message = new \enigma_mime_message($mime, \enigma_mime_message::PGP_SIGNED);

        $this->assertTrue($message->isMultipart());

        $message = new \enigma_mime_message($message1, \enigma_mime_message::PGP_SIGNED);

        $this->assertTrue($message->isMultipart());
    }

    /**
     * Test getFromAddress()
     */
    public function test_get_from_address()
    {
        $mime = new \Mail_mime();
        $message = new \enigma_mime_message($mime, \enigma_mime_message::PGP_SIGNED);

        $this->assertNull($message->getFromAddress());

        $mime->setFrom('test@domain.com');
        $message = new \enigma_mime_message($mime, \enigma_mime_message::PGP_SIGNED);

        $this->assertSame('test@domain.com', $message->getFromAddress());
    }

    /**
     * Test getRecipients()
     */
    public function test_get_recipients()
    {
        $mime = new \Mail_mime();
        $mime->setFrom('test1@domain.com');
        $mime->addTo('<test2@domain.com>, undisclosed-recipients:');
        $mime->addCc('<test3@domain.com>');
        $mime->addBcc('test4@domain.com');

        $expected = ['test2@domain.com', 'test3@domain.com', 'test4@domain.com'];

        $message = new \enigma_mime_message($mime, \enigma_mime_message::PGP_SIGNED);

        $this->assertSame($expected, $message->getRecipients());
    }

    /**
     * Test getOrigBody()
     */
    public function test_get_orig_body()
    {
        $mime = new \Mail_mime();
        $mime->setTXTBody('test body');
        $message = new \enigma_mime_message($mime, \enigma_mime_message::PGP_SIGNED);

        $expected = "Content-Transfer-Encoding: quoted-printable\r\n"
            . "Content-Type: text/plain; charset=ISO-8859-1\r\n"
            . "\r\n"
            . "test body\r\n";

        $this->assertSame($expected, $message->getOrigBody());
    }

    /**
     * Test get()
     */
    public function test_get()
    {
        $mime = new \Mail_mime();
        $mime->setTXTBody('test body');

        $message = new \enigma_mime_message($mime, \enigma_mime_message::PGP_SIGNED);

        $expected = "This is an OpenPGP/MIME signed message (RFC 4880 and 3156)\r\n"
            . "\r\n"
            . "--=_%x\r\n"
            . "Content-Transfer-Encoding: quoted-printable\r\n"
            . "Content-Type: text/plain; charset=ISO-8859-1\r\n"
            . "\r\n"
            . "test body\r\n"
            . "\r\n"
            . "--=_%x--\r\n";

        $signed_headers = "MIME-Version: 1.0\r\n"
            . "Content-Type: multipart/signed;\r\n"
            . " protocol=\"application/pgp-signature\";\r\n"
            . " boundary=\"=_%x\"\r\n";

        // Note: The str_replace() below is for phpunit <= 6.5

        $this->assertStringMatchesFormat(
            str_replace("\r\n", "\n", $expected),
            str_replace("\r\n", "\n", $message->get())
        );
        $this->assertStringMatchesFormat(
            str_replace("\r\n", "\n", $signed_headers),
            str_replace("\r\n", "\n", $message->txtHeaders())
        );

        $mime = new \Mail_mime();
        $mime->setTXTBody('test body');
        $message = new \enigma_mime_message($mime, \enigma_mime_message::PGP_SIGNED);
        $message->addPGPSignature('signature', 'algorithm');

        $signed = "This is an OpenPGP/MIME signed message (RFC 4880 and 3156)\r\n"
            . "\r\n"
            . "--=_%x\r\n"
            . "Content-Transfer-Encoding: quoted-printable\r\n"
            . "Content-Type: text/plain; charset=ISO-8859-1\r\n"
            . "\r\n"
            . "test body\r\n"
            . "\r\n"
            . "--=_%x\r\n"
            . "Content-Type: application/pgp-signature;\r\n"
            . " name=signature.asc\r\n"
            . "Content-Disposition: attachment;\r\n"
            . " filename=signature.asc;\r\n"
            . " size=9\r\n"
            . "Content-Description: OpenPGP digital signature\r\n"
            . "\r\n"
            . "signature\r\n"
            . "--=_%x--\r\n";

        $signed_headers = "MIME-Version: 1.0\r\n"
            . "Content-Type: multipart/signed;\r\n"
            . " protocol=\"application/pgp-signature\";\r\n"
            . " boundary=\"=_%x\";\r\n"
            . " micalg=pgp-algorithm\r\n";

        // Note: The str_replace() below is for phpunit <= 6.5

        $this->assertStringMatchesFormat(
            str_replace("\r\n", "\n", $signed),
            str_replace("\r\n", "\n", $message->get())
        );
        $this->assertStringMatchesFormat(
            str_replace("\r\n", "\n", $signed_headers),
            str_replace("\r\n", "\n", $message->txtHeaders())
        );

        $mime = new \Mail_mime();
        $mime->setTXTBody('test body');
        $message = new \enigma_mime_message($mime, \enigma_mime_message::PGP_ENCRYPTED);
        $message->setPGPEncryptedBody('encrypted body');

        $encrypted = "This is an OpenPGP/MIME encrypted message (RFC 4880 and 3156)\r\n"
            . "\r\n"
            . "--=_%x\r\n"
            . "Content-Type: application/pgp-encrypted\r\n"
            . "Content-Description: PGP/MIME version identification\r\n"
            . "\r\n"
            . "Version: 1\r\n"
            . "--=_%x\r\n"
            . "Content-Type: application/octet-stream;\r\n"
            . " name=encrypted.asc\r\n"
            . "Content-Disposition: inline;\r\n"
            . " filename=encrypted.asc;\r\n"
            . " size=14\r\n"
            . "Content-Description: PGP/MIME encrypted message\r\n"
            . "\r\n"
            . "encrypted body\r\n"
            . "--=_%x--\r\n";

        $encrypted_headers = "MIME-Version: 1.0\r\n"
            . "Content-Type: multipart/encrypted;\r\n"
            . " protocol=\"application/pgp-encrypted\";\r\n"
            . " boundary=\"=_%x\"\r\n";

        // Note: The str_replace() below is for phpunit <= 6.5

        $this->assertStringMatchesFormat(
            str_replace("\r\n", "\n", $encrypted),
            str_replace("\r\n", "\n", $message->get())
        );
        $this->assertStringMatchesFormat(
            str_replace("\r\n", "\n", $encrypted_headers),
            str_replace("\r\n", "\n", $message->txtHeaders())
        );
    }

    /**
     * Test PGP armor preservation when LF-delimited PGP blocks exceed 998 bytes (#10413)
     */
    public function test_get_pgp_armor_line_endings()
    {
        $pgp_encrypted = "-----BEGIN PGP MESSAGE-----\n\n"
            . chunk_split(str_repeat('A', 1200), 64, "\n")
            . "=abcd\n-----END PGP MESSAGE-----\n";
        $expected_encrypted = preg_replace('/\r?\n/', "\r\n", $pgp_encrypted);

        $mime = new \Mail_mime();
        $mime->setTXTBody('test body');
        $message = new \enigma_mime_message($mime, \enigma_mime_message::PGP_ENCRYPTED);
        $message->setPGPEncryptedBody($pgp_encrypted);

        $body = $message->get();

        $this->assertStringContainsString("-----BEGIN PGP MESSAGE-----\r\n", $body);
        $this->assertStringContainsString("-----END PGP MESSAGE-----\r\n", $body);
        $this->assertStringContainsString('size=' . strlen($expected_encrypted), $body);
        $this->assertDoesNotMatchRegularExpression('/(?<!\r)\n/', $body);

        $pgp_signature = "-----BEGIN PGP SIGNATURE-----\n\n"
            . chunk_split(str_repeat('B', 1200), 64, "\n")
            . "=efgh\n-----END PGP SIGNATURE-----\n";
        $expected_signature = preg_replace('/\r?\n/', "\r\n", $pgp_signature);

        $mime = new \Mail_mime();
        $mime->setTXTBody('test body');
        $message = new \enigma_mime_message($mime, \enigma_mime_message::PGP_SIGNED);
        $message->addPGPSignature($pgp_signature, 'sha256');

        $body = $message->get();

        $this->assertStringContainsString("-----BEGIN PGP SIGNATURE-----\r\n", $body);
        $this->assertStringContainsString("-----END PGP SIGNATURE-----\r\n", $body);
        $this->assertStringContainsString('size=' . strlen($expected_signature), $body);
        $this->assertDoesNotMatchRegularExpression('/(?<!\r)\n/', $body);
    }
}
