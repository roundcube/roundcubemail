<?php

/**
 * Test class to test rcube_addressbook class
 *
 * @package Tests
 */
class Framework_Addressbook extends PHPUnit\Framework\TestCase
{
    /**
     * Test for get_col_values() method
     */
    function test_get_col_values()
    {
        $data = ['email' => 'test@test.com', 'other' => 'test'];
        $result = rcube_addressbook::get_col_values('email', $data, true);

        $this->assertSame(['test@test.com'], $result);

        $data = ['email:home' => 'test@test.com', 'other' => 'test'];
        $result = rcube_addressbook::get_col_values('email', $data, true);

        $this->assertSame(['test@test.com'], $result);

        $data = ['email:home' => 'test@test.com', 'other' => 'test'];
        $result = rcube_addressbook::get_col_values('email', $data, false);

        $this->assertSame(['home' => ['test@test.com']], $result);
    }
}
