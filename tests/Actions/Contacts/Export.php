<?php

/**
 * Test class to test rcmail_action_contacts_export
 *
 * @package Tests
 */
class Actions_Contacts_Export extends ActionTestCase
{
    /**
     * Test exporting all contacts
     */
    function test_export_all()
    {
        $action = new rcmail_action_contacts_export;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'export');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $_GET = ['_source' => '0'];
        $_POST = [];

        // Here we expect request security check error
        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $this->assertSame('ERROR: Request security check failed', trim(StderrMock::$output));

        // Now we'll try with the proper token
        $_SESSION['request_token']           = 'secure';
        $_SERVER['HTTP_X_ROUNDCUBE_REQUEST'] = 'secure';

        ob_start();
        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);
        $vcf = ob_get_contents();
        ob_end_clean();

        $this->assertSame([
                'Content-Type: text/vcard; charset=UTF-8',
                'Content-Disposition: attachment; filename="contacts.vcf"'
            ], $output->headers
        );
        $this->assertSame(6, substr_count($vcf, 'BEGIN:VCARD'));
        $this->assertSame(6, substr_count($vcf, 'END:VCARD'));
        $this->assertSame(1, substr_count($vcf, 'FN:Jane Stalone'));
    }

    /**
     * Test exporting selected contacts
     *
     * @depends test_export_all
     */
    function test_export_selected()
    {
        $action = new rcmail_action_contacts_export;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'export');

        $this->assertTrue($action->checks());

        $cids   = [];
        $db     = rcmail::get_instance()->get_dbh();
        $query  = $db->query("SELECT `contact_id` FROM `contacts` WHERE `email` IN ('j.rian@gmail.com', 'g.bush@gov.com')");
        while ($result = $db->fetch_assoc($query)) {
            $cids[] = $result['contact_id'];
        }

        $_GET = ['_source' => '0', '_cid' => implode(',', $cids)];
        // TODO: This really shouldn't be needed
        $_REQUEST = ['_cid' => implode(',', $cids)];

        $_SESSION['request_token']           = 'secure';
        $_SERVER['HTTP_X_ROUNDCUBE_REQUEST'] = 'secure';

        ob_start();
        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);
        $vcf = ob_get_contents();
        ob_end_clean();

        $this->assertSame([
                'Content-Type: text/vcard; charset=UTF-8',
                'Content-Disposition: attachment; filename="contacts.vcf"'
            ], $output->headers
        );
        $this->assertSame(2, substr_count($vcf, 'BEGIN:VCARD'));
        $this->assertSame(2, substr_count($vcf, 'END:VCARD'));
        $this->assertSame(0, substr_count($vcf, 'FN:Jane Stalone'));
        $this->assertSame(1, substr_count($vcf, 'FN:Jack Rian'));
        $this->assertSame(1, substr_count($vcf, 'FN:George Bush'));
    }

    /**
     * Test exporting search result
     *
     * @depends test_export_all
     */
    function test_export_search()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test prepare_for_export() method
     */
    function test_prepare_for_export()
    {
        $this->markTestIncomplete();
    }
}
