<?php

class Password_Plugin extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        include_once __DIR__ . '/../password.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new password($rcube->api);

        $this->assertInstanceOf('password', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }

    function test_driver_cpanel_webmail()
    {
        $driver = 'cpanel_webmail';
        include_once __DIR__ . "/../drivers/$driver.php";
        $driver_class = "rcube_${driver}_password";
        $this->assertTrue(class_exists($driver_class));

        $json_response_fail = '{"data":null,"errors":'
                . '["Execution of Email::passwdpop (api version:3) is not '
                . 'permitted inside of webmail"],"status":0,"metadata":{},'
                . '"messages":null}';
        $result = $driver_class::decode_response($json_response_fail);
        $this->assertTrue(is_array($result));
        $this->assertEquals($result['code'], PASSWORD_ERROR);
        $expected_message = 'Execution of Email::passwdpop (api version:3) is'
                . ' not permitted inside of webmail';
        $this->assertEquals($result['message'], $expected_message);

        $json_response_success = '{"metadata":{},"data":null,"messages":null,'
                . '"errors":null,"status":1}';
        $result = $driver_class::decode_response($json_response_success);
        $this->assertEquals($result, PASSWORD_SUCCESS);
    }
}
