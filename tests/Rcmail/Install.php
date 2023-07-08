<?php

/**
 * Test class to test rcmail_install class
 *
 * @package Tests
 */
class Rcmail_RcmailInstall extends ActionTestCase
{
    /**
     * Test getprop() method
     */
    function test_getprop()
    {
        $install = rcmail_install::get_instance();

        $this->assertSame('default', $install->getprop('unknown', 'default'));
        $this->assertSame('', $install->getprop('unknown'));
    }

    /**
     * Test create_config() method
     */
    function test_create_config()
    {
        $install = rcmail_install::get_instance();

        $config = $install->create_config();

        $this->assertSame("<?php\n\n/* Local configuration for Roundcube Webmail */\n\n", $config);

        $install->config = ['test' => 'test'];
        $config = $install->create_config();

        $this->assertStringContainsString("\$config['test'] = 'test';", $config);

        $_POST['_test'] = 'new';
        $config = $install->create_config();

        $this->assertStringContainsString("\$config['test'] = 'test';", $config);

        $_POST['_product_name'] = 'RC';
        $install->config = ['product_name' => 'Roundcube'];
        $config = $install->create_config();

        $this->assertStringContainsString("\$config['product_name'] = 'RC';", $config);
    }

    /**
     * Test db_schema_check() method
     */
    function test_db_schema_check()
    {
        $rcmail  = rcmail::get_instance();
        $install = rcmail_install::get_instance();

        $result = $install->db_schema_check($rcmail->get_dbh());

        $this->assertSame(false, $result);
    }

    /**
     * Test check_mime_detection() method
     */
    function test_check_mime_detection()
    {
        $rcmail  = rcmail::get_instance();
        $install = rcmail_install::get_instance();

        $result = $install->check_mime_detection();

        $this->assertSame([], $result);
    }

    /**
     * Test check_mime_extensions() method
     */
    function test_check_mime_extensions()
    {
        $rcmail  = rcmail::get_instance();
        $install = rcmail_install::get_instance();

        $result = $install->check_mime_extensions();

        $this->assertSame([], $result);
    }

    /**
     * Test list_skins() method
     */
    function test_list_skins()
    {
        $rcmail  = rcmail::get_instance();
        $install = rcmail_install::get_instance();

        $result = $install->list_skins();

        $this->assertContains('elastic', $result);
    }

    /**
     * Test list_plugins() method
     */
    function test_list_plugins()
    {
        $rcmail  = rcmail::get_instance();
        $install = rcmail_install::get_instance();

        $result = $install->list_plugins();

        $acl = [
            'name'    => 'acl',
            'desc'    => 'IMAP Folders Access Control Lists Management (RFC4314, RFC2086).',
            'enabled' => false,
        ];

        $this->assertSame($acl, $result[0]);
    }

    /**
     * Test merge_config() method
     */
    function test_merge_config()
    {
        $config = [
            'imap_host' => 'ssl://test:993',
            'smtp_host' => 'ssl://test:465',
        ];

        $install = rcmail_install::get_instance();
        $install->configured = true;
        $install->config = $config;

        $install->merge_config();

        $this->assertSame($config['imap_host'], $install->config['imap_host']);
        $this->assertSame($config['smtp_host'], $install->config['smtp_host']);

        $this->markTestIncomplete(); // TODO: More tests
    }
}
