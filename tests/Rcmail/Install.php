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

        $this->assertSame(['classic', 'elastic', 'larry'], $result);
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
}
