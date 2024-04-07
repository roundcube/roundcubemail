<?php

/**
 * Test class to test rcmail_action class
 */
class Rcmail_RcmailAction extends ActionTestCase
{
    /**
     * Test rcmail_action::set_env_config()
     */
    public function test_set_env_config()
    {
        $rcmail = rcmail::get_instance();

        self::assertFalse($rcmail->config->get('ip_check'));
        rcmail_action::set_env_config(['ip_check']);
        self::assertNull($rcmail->output->get_env('ip_check'));

        $rcmail->config->set('ip_check', true);
        rcmail_action::set_env_config(['ip_check']);
        self::assertTrue($rcmail->output->get_env('ip_check'));
    }

    /**
     * Test rcmail_action::table_output()
     */
    public function test_table_output()
    {
        $attrib = [];
        $table_data = [];

        $result = rcmail_action::table_output($attrib, $table_data, ['id'], 'id');
        $expected = '<table border="0"><thead><tr><th class="id">[id]</th></tr></thead><tbody></tbody></table>';
        self::assertSame($expected, $result);

        // TODO: More cases
    }

    /**
     * Test rcmail_action::quota_content()
     */
    public function test_quota_content()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail_action::display_server_error()
     */
    public function test_display_server_error()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail_action::html_editor()
     */
    public function test_html_editor()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail_action::upload_init()
     */
    public function test_upload_init()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail_action::upload_form()
     */
    public function test_upload_form()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail_action::upload_error()
     */
    public function test_upload_error()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail_action::upload_failure()
     */
    public function test_upload_failure()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail_action::display_uploaded_file()
     */
    public function test_display_uploaded_file()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail_action::autocomplete_init()
     */
    public function test_autocomplete_init()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail_action::font_defs()
     */
    public function test_font_defs()
    {
        $result = rcmail_action::font_defs();
        self::assertCount(13, $result);
    }

    /**
     * Test rcmail_action::fontsize_defs()
     */
    public function test_fontsize_defs()
    {
        $result = rcmail_action::fontsize_defs();
        self::assertCount(9, $result);
    }

    /**
     * Test rcmail_action::show_bytes)
     */
    public function test_show_bytes()
    {
        $result = rcmail_action::show_bytes(0);
        self::assertSame('0 B', $result);

        $result = rcmail_action::show_bytes(2000, $unit);
        self::assertSame('2 KB', $result);

        $result = rcmail_action::show_bytes(2000000, $unit);
        self::assertSame('1.9 MB', $result);
        self::assertSame('MB', $unit);
    }

    /**
     * Test rcmail_action::message_part_size()
     */
    public function test_message_part_size()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail_action::get_uids()
     */
    public function test_get_uids()
    {
        $result = rcmail_action::get_uids();
        self::assertSame([], $result);

        $_GET = [
            '_mbox' => 'Test<a>',
            '_uid' => '1',
        ];
        $result = rcmail_action::get_uids(null, null, $is_multifolder);
        self::assertSame(['Test<a>' => ['1']], $result);
        self::assertFalse($is_multifolder);

        $_GET = [
            '_uid' => '1-Test<a>',
        ];
        $result = rcmail_action::get_uids(null, null, $is_multifolder);
        self::assertSame(['Test<a>' => ['1']], $result);
        self::assertTrue($is_multifolder);

        $_GET = [
            '_uid' => '1-Test<a>,2-INBOX',
        ];
        $result = rcmail_action::get_uids(null, null, $is_multifolder);
        self::assertSame(['Test<a>' => ['1'], 'INBOX' => ['2']], $result);
        self::assertTrue($is_multifolder);

        $_GET = [
            '_mbox' => 'INBOX',
            '_uid' => '*',
        ];
        $result = rcmail_action::get_uids(null, null, $is_multifolder);
        self::assertSame(['INBOX' => '*'], $result);
        self::assertFalse($is_multifolder);

        $_GET = [
            '_mbox' => 'INBOX',
            '_uid' => '1.1',
        ];
        $result = rcmail_action::get_uids(null, null, $is_multifolder);
        self::assertSame(['INBOX' => ['1.1']], $result);
        self::assertFalse($is_multifolder);

        $_GET = [
            '_mbox' => 'INBOX',
            '_uid' => '1:2,56',
        ];
        $result = rcmail_action::get_uids(null, null, $is_multifolder);
        self::assertSame(['INBOX' => ['1:2', '56']], $result);
        self::assertFalse($is_multifolder);
    }

    /**
     * Test rcmail_action::get_resource_content()
     */
    public function test_get_resource_content()
    {
        $result = rcmail_action::get_resource_content('blocked.gif');
        self::assertTrue(strpos($result, 'GIF89') === 0);
    }

    /**
     * Test rcmail_action::get_form_tags()
     */
    public function test_get_form_tags()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail_action::folder_list()
     */
    public function test_folder_list()
    {
        self::markTestIncomplete();
    }

    /**
     * Test rcmail_action::folder_selector()
     */
    public function test_folder_selector()
    {
        self::markTestIncomplete();
    }
}
