<?php

namespace Roundcube\Tests\Rcmail;

use PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily;
use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_install class
 */
class InstallTest extends ActionTestCase
{
    /**
     * Test getprop() method
     */
    public function test_getprop()
    {
        $install = \rcmail_install::get_instance();

        $this->assertSame('default', $install->getprop('unknown', 'default'));
        $this->assertSame('', $install->getprop('unknown'));
    }

    /**
     * Test create_config() method
     */
    public function test_create_config()
    {
        $install = \rcmail_install::get_instance();

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
    public function test_db_schema_check()
    {
        $rcmail = \rcmail::get_instance();
        $install = \rcmail_install::get_instance();

        $result = $install->db_schema_check($rcmail->get_dbh());

        $this->assertFalse($result);
    }

    /**
     * Test check_mime_detection() method
     */
    public function test_check_mime_detection()
    {
        $rcmail = \rcmail::get_instance();
        $install = \rcmail_install::get_instance();

        $result = $install->check_mime_detection();

        $this->assertSame([], $result);
    }

    /**
     * Test check_mime_extensions() method
     *
     * Windows feature request: https://github.com/php/php-src/issues/12918
     *
     * @requires OSFAMILY Linux
     */
    #[RequiresOperatingSystemFamily('Linux')]
    public function test_check_mime_extensions()
    {
        $rcmail = \rcmail::get_instance();
        $install = \rcmail_install::get_instance();

        $result = $install->check_mime_extensions();

        $this->assertSame([], $result);
    }

    /**
     * Test list_skins() method
     */
    public function test_list_skins()
    {
        $rcmail = \rcmail::get_instance();
        $install = \rcmail_install::get_instance();

        $result = $install->list_skins();

        $this->assertContains('elastic', $result);
    }

    /**
     * Test list_plugins() method
     */
    public function test_list_plugins()
    {
        $rcmail = \rcmail::get_instance();
        $install = \rcmail_install::get_instance();

        $result = $install->list_plugins();

        $acl = [
            'name' => 'acl',
            'desc' => 'IMAP Folders Access Control Lists Management (RFC4314, RFC2086).',
            'enabled' => false,
        ];

        $this->assertSame($acl, $result[0]);
    }

    /**
     * Test merge_config() method
     */
    public function test_merge_config()
    {
        $config = [
            'imap_host' => 'ssl://test:993',
            'smtp_host' => 'ssl://test:465',
        ];

        $install = \rcmail_install::get_instance();
        $install->configured = true;
        $install->config = $config;

        $install->merge_config();

        $this->assertSame($config['imap_host'], $install->config['imap_host']);
        $this->assertSame($config['smtp_host'], $install->config['smtp_host']);

        $this->markTestIncomplete(); // TODO: More tests
    }
}
