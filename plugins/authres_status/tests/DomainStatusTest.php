<?php
namespace AuthresStatusTests;

use stdClass;
use authres_status;
use PHPUnit\Framework\TestCase;

class DomainStatusTest extends TestCase
{
    public function test_pass_from_subdomain()
    {
        $headers = $this->create_header_object('mail.domain.net; dkim=pass (1024-bit key; secure) header.d=email.com header.i=@email.com header.b=XXXXXXXX; dkim-atps=neutral', 'Test <test@subdomain.email.com>');

        $plugin = new authres_status();
        $result = $plugin->get_authentication_status($headers);

        $expected = <<<EOT
<img src="plugins/authres_status/images/status_pass.png" alt="signaturepass" title="Valid signature(s) from the sender's domain. verified by dkim=pass (1024-bit key; secure)" class="authres-status-img" /> 
EOT;

        $this->assertEquals($expected, $result);
    }

    public function test_pass_single_signature()
    {
        $headers = $this->create_header_object('mail.domain.net; dkim=pass (1024-bit key; secure) header.d=email.com header.i=@email.com header.b=XXXXXXXX; dkim-atps=neutral');

        $plugin = new authres_status();
        $result = $plugin->get_authentication_status($headers);

        $expected = <<<EOT
<img src="plugins/authres_status/images/status_pass.png" alt="signaturepass" title="Valid signature(s) from the sender's domain. verified by dkim=pass (1024-bit key; secure)" class="authres-status-img" /> 
EOT;

        $this->assertEquals($expected, $result);
    }

    public function test_pass_multiple_signatures()
    {
        $headers = $this->create_header_object('mail.domain.net; dkim=pass header.i=@smtpcorp.com; dkim=pass header.i=@email.com; dkim=pass header.i=@email.com');

        $plugin = new authres_status();
        $result = $plugin->get_authentication_status($headers);

        $expected = <<<EOT
<img src="plugins/authres_status/images/status_pass.png" alt="signaturepass" title="Valid signature(s) from the sender's domain. verified by dkim=pass; dkim=pass" class="authres-status-img" /> 
EOT;

        $this->assertEquals($expected, $result);
    }

    public function test_third_signature()
    {
        $headers = $this->create_header_object('mail.domain.net; dkim=pass (1024-bit key; unprotected) header.d=mail.3rdparty.com header.i=@mail.3rdparty.com header.b=XXXXXXXX;');

        $plugin = new authres_status();
        $result = $plugin->get_authentication_status($headers);

        $expected = <<<EOT
<img src="plugins/authres_status/images/status_third.png" alt="thirdparty" title="Signed by third party, signature is present but for different domain than from address. verified for mail.3rdparty.com by dkim=pass (1024-bit key; unprotected)" class="authres-status-img" /> 
EOT;

        $this->assertEquals($expected, $result);
    }

    public function test_smtp_auth_signature()
    {
        $headers = $this->create_header_object('auth=pass smtp.auth=sendonly smtp.mailfrom=mail@example.com');

        $plugin = new authres_status();
        $result = $plugin->get_authentication_status($headers);

        $expected = <<<EOT
<img src="plugins/authres_status/images/status_pass.png" alt="signaturepass" title="Valid signature(s) from the sender's domain. verified by " class="authres-status-img" /> 
EOT;

        $this->assertEquals($expected, $result);
    }

    public function test_arc_header()
    {
        $headers = $this->create_header_object('arc=fail (signature failed)');

        $plugin = new authres_status();
        $result = $plugin->get_authentication_status($headers);

        $expected = <<<EOT
<img src="plugins/authres_status/images/status_fail.png" alt="invalidsignature" title="Signature is not valid! verified by arc=fail (signature failed)" class="authres-status-img" /> 
EOT;

        $this->assertEquals($expected, $result);
    }

    protected function create_header_object($authres_header, $from = 'Test <test@email.com>')
    {
        $headers = new stdClass;
        $headers->from = $from;
        $headers->others = array(
            'x-dkim-authentication-results' => false,
            'authentication-results' => $authres_header
        );
        return $headers;
    }
}
