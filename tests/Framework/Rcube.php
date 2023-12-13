<?php

/**
 * Test class to test rcube class
 *
 * @package Tests
 */
class Framework_Rcube extends PHPUnit\Framework\TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = rcube::get_instance();

        $this->assertInstanceOf('rcube', $object, "Class singleton");
    }

    /**
     * rcube::read_localization()
     */
    function test_read_localization()
    {
        $rcube = rcube::get_instance();
        $result = $rcube->read_localization(INSTALL_PATH . 'plugins/acl/localization', 'pl_PL');

        $this->assertSame('Zapis', $result['aclwrite']);
    }

    /**
     * rcube::list_languages()
     */
    function test_list_languages()
    {
        $rcube = rcube::get_instance();
        $result = $rcube->list_languages();

        $this->assertSame('English (US)', $result['en_US']);
    }

    /**
     * rcube::encrypt() and rcube::decrypt()
     */
    function test_encrypt_and_decrypt()
    {
        $rcube = rcube::get_instance();

        $result = $rcube->decrypt($rcube->encrypt('test'));
        $this->assertSame('test', $result);

        // Test AEAD cipher method
        $defaultCipherMethod = $rcube->config->get('cipher_method');
        $rcube->config->set('cipher_method', 'aes-256-gcm');
        try {
            $result = $rcube->decrypt($rcube->encrypt('test'));
            $this->assertSame('test', $result);
        } finally {
            $rcube->config->set('cipher_method', $defaultCipherMethod);
        }
    }
}
