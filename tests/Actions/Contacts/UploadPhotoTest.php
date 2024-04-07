<?php

/**
 * Test class to test rcmail_action_contacts_upload_photo
 */
class Actions_Contacts_Upload_Photo extends ActionTestCase
{
    /**
     * Test photo upload
     */
    public function test_run()
    {
        $action = new rcmail_action_contacts_upload_photo();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'upload-photo');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $_SERVER['REQUEST_METHOD'] = 'POST';

        // No files uploaded case
        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('upload-photo', $result['action']);
        self::assertTrue(strpos($result['exec'], 'this.photo_upload_end();') !== false);

        // Upload a file
        $file = $this->fakeUpload('_photo', false);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('upload-photo', $result['action']);
        self::assertTrue(strpos($result['exec'], 'this.replace_contact_photo("' . $file['id'] . '");') !== false);
        self::assertTrue(strpos($result['exec'], 'this.photo_upload_end();') !== false);

        $upload = rcmail::get_instance()->get_uploaded_file($file['id']);
        self::assertSame($file['name'], $upload['name']);
        self::assertSame($file['type'], $upload['mimetype']);
        self::assertSame($file['size'], $upload['size']);
        self::assertSame('contact', $upload['group']);

        // TODO: Test invalid image format, upload errors handling
    }
}
