<?php

/**
 * Test class to test rcmail_action class
 *
 * @package Tests
 */
class Rcmail_RcmailAction extends ActionTestCase
{

    /**
     * Test rcmail_action::set_env_config()
     */
    function test_set_env_config()
    {
        $rcmail = rcmail::get_instance();

        $this->assertFalse($rcmail->config->get('ip_check'));
        rcmail_action::set_env_config(['ip_check']);
        $this->assertNull($rcmail->output->get_env('ip_check'));

        $rcmail->config->set('ip_check', true);
        rcmail_action::set_env_config(['ip_check']);
        $this->assertTrue($rcmail->output->get_env('ip_check'));
    }

    /**
     * Test rcmail_action::table_output()
     */
    function test_table_output()
    {
        $attrib = [];
        $table_data = [];

        $result = rcmail_action::table_output($attrib, $table_data, ['id'], 'id');
        $expected = '<table border="0"><thead><tr><th class="id">[id]</th></tr></thead><tbody></tbody></table>';
        $this->assertSame($expected, $result);

        // TODO: More cases
    }

    /**
     * Test rcmail_action::quota_content()
     */
    function test_quota_content()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_action::display_server_error()
     */
    function test_display_server_error()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_action::html_editor()
     */
    function test_html_editor()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_action::upload_init()
     */
    function test_upload_init()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_action::upload_form()
     */
    function test_upload_form()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_action::upload_error()
     */
    function test_upload_error()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_action::upload_failure()
     */
    function test_upload_failure()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_action::display_uploaded_file()
     */
    function test_display_uploaded_file()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_action::autocomplete_init()
     */
    function test_autocomplete_init()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_action::font_defs()
     */
    function test_font_defs()
    {
        $result = rcmail_action::font_defs();
        $this->assertCount(13, $result);
    }

    /**
     * Test rcmail_action::show_bytes)
     */
    function test_show_bytes()
    {
        $result = rcmail_action::show_bytes(0);
        $this->assertSame('0 B', $result);

        $result = rcmail_action::show_bytes(2000, $unit);
        $this->assertSame('2 KB', $result);

        $result = rcmail_action::show_bytes(2000000, $unit);
        $this->assertSame('1.9 MB', $result);
        $this->assertSame('MB', $unit);
    }

    /**
     * Test rcmail_action::message_part_size()
     */
    function test_message_part_size()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_action::get_uids()
     */
    function test_get_uids()
    {
        $result = rcmail_action::get_uids();
        $this->assertSame([], $result);

        $_GET = [
            '_mbox' => 'Test<a>',
            '_uid' => '1',
        ];
        $result = rcmail_action::get_uids(null, null, $is_multifolder);
        $this->assertSame(['Test<a>' => ['1']], $result);
        $this->assertFalse($is_multifolder);

        $_GET = [
            '_uid' => '1-Test<a>',
        ];
        $result = rcmail_action::get_uids(null, null, $is_multifolder);
        $this->assertSame(['Test<a>' => ['1']], $result);
        $this->assertTrue($is_multifolder);

        $_GET = [
            '_uid' => '1-Test<a>,2-INBOX',
        ];
        $result = rcmail_action::get_uids(null, null, $is_multifolder);
        $this->assertSame(['Test<a>' => ['1'], 'INBOX' => ['2']], $result);
        $this->assertTrue($is_multifolder);

        $_GET = [
            '_mbox' => 'INBOX',
            '_uid' => '*',
        ];
        $result = rcmail_action::get_uids(null, null, $is_multifolder);
        $this->assertSame(['INBOX' => '*'], $result);
        $this->assertFalse($is_multifolder);

        $_GET = [
            '_mbox' => 'INBOX',
            '_uid' => '1.1',
        ];
        $result = rcmail_action::get_uids(null, null, $is_multifolder);
        $this->assertSame(['INBOX' => ['1.1']], $result);
        $this->assertFalse($is_multifolder);

        $_GET = [
            '_mbox' => 'INBOX',
            '_uid' => '1:2,56',
        ];
        $result = rcmail_action::get_uids(null, null, $is_multifolder);
        $this->assertSame(['INBOX' => ['1:2','56']], $result);
        $this->assertFalse($is_multifolder);
    }

    /**
     * Test rcmail_action::get_resource_content()
     */
    function test_get_resource_content()
    {
        $result = rcmail_action::get_resource_content('blocked.gif');
        $this->assertTrue(strpos($result, 'GIF89') === 0);
    }

    /**
     * Test rcmail_action::get_form_tags()
     */
    function test_get_form_tags()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_action::folder_list()
     */
    function test_folder_list()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_action::folder_selector()
     */
    function test_folder_selector()
    {
        $this->markTestIncomplete();
    }
}
