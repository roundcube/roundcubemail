<?php

/**
 * Test class to test rcube_utils class
 *
 * @package Tests
 */
class Utils extends PHPUnit_Framework_TestCase
{

    /**
     * Valid email addresses for test_valid_email()
     */
    function data_valid_email()
    {
        return array(
            array('email@domain.com', 'Valid email'),
            array('firstname.lastname@domain.com', 'Email contains dot in the address field'),
            array('email@subdomain.domain.com', 'Email contains dot with subdomain'),
            array('firstname+lastname@domain.com', 'Plus sign is considered valid character'),
            array('email@[123.123.123.123]', 'Square bracket around IP address'),
            array('email@[IPv6:::1]', 'Square bracket around IPv6 address (1)'),
            array('email@[IPv6:::1.2.3.4]', 'Square bracket around IPv6 address (2)'),
            array('email@[IPv6:2001:2d12:c4fe:5afe::1]', 'Square bracket around IPv6 address (3)'),
            array('"email"@domain.com', 'Quotes around email is considered valid'),
            array('1234567890@domain.com', 'Digits in address are valid'),
            array('email@domain-one.com', 'Dash in domain name is valid'),
            array('_______@domain.com', 'Underscore in the address field is valid'),
            array('email@domain.name', '.name is valid Top Level Domain name'),
            array('email@domain.co.jp', 'Dot in Top Level Domain name also considered valid (use co.jp as example here)'),
            array('firstname-lastname@domain.com', 'Dash in address field is valid'),
        );
    }

    /**
     * Invalid email addresses for test_invalid_email()
     */
    function data_invalid_email()
    {
        return array(
            array('plainaddress', 'Missing @ sign and domain'),
            array('#@%^%#$@#$@#.com', 'Garbage'),
            array('@domain.com', 'Missing username'),
            array('Joe Smith <email@domain.com>', 'Encoded html within email is invalid'),
            array('email.domain.com', 'Missing @'),
            array('email@domain@domain.com', 'Two @ sign'),
            array('.email@domain.com', 'Leading dot in address is not allowed'),
            array('email.@domain.com', 'Trailing dot in address is not allowed'),
            array('email..email@domain.com', 'Multiple dots'),
            array('あいうえお@domain.com', 'Unicode char as address'),
            array('email@domain.com (Joe Smith)', 'Text followed email is not allowed'),
            array('email@domain', 'Missing top level domain (.com/.net/.org/etc)'),
            array('email@-domain.com', 'Leading dash in front of domain is invalid'),
//            array('email@domain.web', '.web is not a valid top level domain'),
            array('email@123.123.123.123', 'IP address without brackets'),
            array('email@2001:2d12:c4fe:5afe::1', 'IPv6 address without brackets'),
            array('email@IPv6:2001:2d12:c4fe:5afe::1', 'IPv6 address without brackets (2)'),
            array('email@[111.222.333.44444]', 'Invalid IP format'),
            array('email@[111.222.255.257]', 'Invalid IP format (2)'),
            array('email@[.222.255.257]', 'Invalid IP format (3)'),
            array('email@[::1]', 'Invalid IPv6 format (1)'),
            array('email@[IPv6:2001:23x2:1]', 'Invalid IPv6 format (2)'),
            array('email@[IPv6:1111:2222:33333::4444:5555]', 'Invalid IPv6 format (3)'),
            array('email@[IPv6:1111::3333::4444:5555]', 'Invalid IPv6 format (4)'),
            array('email@domain..com', 'Multiple dot in the domain portion is invalid'),
        );
    }

    /**
     * @dataProvider data_valid_email
     */
    function test_valid_email($email, $title)
    {
        $this->assertTrue(rcube_utils::check_email($email, false), $title);
    }

    /**
     * @dataProvider data_invalid_email
     */
    function test_invalid_email($email, $title)
    {
        $this->assertFalse(rcube_utils::check_email($email, false), $title);
    }

}
