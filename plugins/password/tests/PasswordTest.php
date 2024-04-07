<?php

use PHPUnit\Framework\TestCase;

class Password_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new password($rcube->plugins);

        self::assertInstanceOf('password', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * A dummy test testing PHP syntax on password drivers
     */
    public function test_all_drivers()
    {
        if ($files = glob(__DIR__ . '/../drivers/*.php')) {
            foreach ($files as $file) {
                if (preg_match('|/([a-z_]+)\.php$|', $file, $matches)) {
                    $this->load_driver($matches[1]);
                }
            }
        }
    }

    /**
     * cpanel driver test
     */
    public function test_driver_cpanel()
    {
        $driver_class = $this->load_driver('cpanel');

        $error_result = $driver_class::decode_response(false);
        self::assertSame($error_result, PASSWORD_CONNECT_ERROR);

        $bad_result = $driver_class::decode_response(null);
        self::assertSame($bad_result, PASSWORD_CONNECT_ERROR);

        $null_result = $driver_class::decode_response('null');
        self::assertSame($null_result, PASSWORD_ERROR);

        $malformed_result = $driver_class::decode_response('random {string]!');
        self::assertSame($malformed_result, PASSWORD_ERROR);

        $other_result = $driver_class::decode_response('{"a":"b"}');
        self::assertSame($other_result, PASSWORD_ERROR);

        $fail_response = '{"data":null,"errors":["Execution of Email::passwdp'
                . 'op (api version:3) is not permitted inside of webmail"],"sta'
                . 'tus":0,"metadata":{},"messages":null}';
        $error_message = 'Execution of Email::passwdpop (api version:3) is no'
                . 't permitted inside of webmail';
        $expected_result = [
            'code' => PASSWORD_ERROR,
            'message' => $error_message,
        ];
        $fail_result = $driver_class::decode_response($fail_response);
        self::assertSame($expected_result, $fail_result);

        $success_response = '{"metadata":{},"data":null,"messages":null,"errors'
                . '":null,"status":1}';
        $good_result = $driver_class::decode_response($success_response);
        self::assertSame($good_result, PASSWORD_SUCCESS);
    }

    /**
     * Loads a driver's source file, checks that its class exist and returns the
     * driver's class name.
     *
     * @param string $driver driver name, example: "chpasswd"
     *
     * @return string driver's class name, example: "rcube_chpasswd_password"
     */
    protected function load_driver($driver)
    {
        $driver_class = "rcube_{$driver}_password";
        self::assertTrue(class_exists($driver_class));
        return $driver_class;
    }

    /**
     * Test hash_password()
     */
    public function test_hash_password()
    {
        $pass = password::hash_password('test', 'clear');
        self::assertSame('test', $pass);

        $pass = password::hash_password('test', 'ad');
        self::assertSame("\"\0t\0e\0s\0t\0\"\0", $pass);

        $pass = password::hash_password('test', 'ssha');
        self::assertMatchesRegularExpression('/^\{SSHA\}[a-zA-Z0-9+\/]{32}$/', $pass);

        $pass = password::hash_password('test', 'ssha256');
        self::assertMatchesRegularExpression('/^\{SSHA256\}[a-zA-Z0-9+\/=]{48}$/', $pass);

        $pass = password::hash_password('test', 'sha256-crypt');
        self::assertMatchesRegularExpression('/^\{SHA256-CRYPT\}\$5\$[a-zA-Z0-9]{16}\$[a-zA-Z0-9.\/]{43}$/', $pass);

        $pass = password::hash_password('test', 'hash-bcrypt');
        self::assertMatchesRegularExpression('/^\{BLF-CRYPT\}\$2y\$10\$[a-zA-Z0-9.\/]{53}$/', $pass);

        // TODO: Test all algos
    }
}
