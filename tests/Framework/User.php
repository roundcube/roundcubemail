<?php

/**
 * Test class to test rcube_user class
 *
 * @package Tests
 */
class Framework_User extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $user = new rcube_user;

        $this->assertInstanceOf('rcube_user', $user, "Class constructor");
    }
}
