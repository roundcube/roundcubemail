<?php

/**
 * Test class to test rcmail_oauth class
 *
 * @package Tests
 */
class Rcmail_RcmailOauth extends ActionTestCase
{
    /**
     * Test jwt_decode() method
     */
    function test_jwt_decode()
    {
        $jwt = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.EkN-DOsnsuRjRO6BxXemmJDm3HbxrbRzXglbN2S4sOkopdU4IsDxTI8jO19W_A4K8ZPJijNLis4EZsHeY559a4DFOd50_OqgHGuERTqYZyuhtF39yxJPAjUESwxk2J5k_4zM3O-vtd1Ghyo4IbqKKSy6J9mTniYJPenn5-HIirE';

        $oauth = rcmail_oauth::get_instance();

        // We can't use expectException until we drop support for phpunit 4.8 (i.e. PHP 5.4)
        // $this->expectException(RuntimeException::class);

        try {
            $oauth->jwt_decode($jwt);
        }
        catch (RuntimeException $e) {
        }

        $this->assertTrue(isset($e));
    }
}
