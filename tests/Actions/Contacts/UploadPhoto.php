<?php

/**
 * Test class to test rcmail_action_contacts_upload_photo
 *
 * @package Tests
 */
class Actions_Contacts_Upload_Photo extends ActionTestCase
{
    /**
     * Test photo upload
     */
    function test_run()
    {
        $action = new rcmail_action_contacts_upload_photo;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'upload-photo');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $_SERVER['REQUEST_METHOD'] = 'POST';

        // No files uploaded case
        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('upload-photo', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.photo_upload_end();') !== false);

        // Upload a file
        $content = base64_decode(rcmail_output::BLANK_GIF);
        $file    = $this->createTempFile($content);
        $_SESSION['contacts'] = null;
        $_FILES['_photo']     = [
            'name'     => 'test.gif',
            'type'     => 'image/gif',
            'tmp_name' => $file,
            'error'    => 0,
            'size'     => strlen($content),
        ];

        // Attachments handling plugins use move_uploaded_file() which does not work
        // here. We'll add a fake hook handler for our purposes.
        $rcmail = rcmail::get_instance();
        $rcmail->plugins->register_hook('attachment_upload', function($att) {
            $att['status'] = true;
            $att['id']     = 'fake';
            return $att;
        });

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();
        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('upload-photo', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.replace_contact_photo("fake");') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.photo_upload_end();') !== false);

        $this->assertSame('test.gif', $_SESSION['contacts']['files']['fake']['name']);

        // TODO: Test invalid image format, upload errors handling
    }
}
