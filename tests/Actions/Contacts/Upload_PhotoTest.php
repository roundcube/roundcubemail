<?php

namespace Roundcube\Tests\Actions\Contacts;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_contacts_upload_photo
 */
class Upload_PhotoTest extends ActionTestCase
{
    /**
     * Test photo upload
     */
    public function test_run()
    {
        $action = new \rcmail_action_contacts_upload_photo();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'contacts', 'upload-photo');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        $_SERVER['REQUEST_METHOD'] = 'POST';

        // No files uploaded case
        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('upload-photo', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.photo_upload_end();') !== false);

        // Upload a file
        $file = $this->fakeUpload('_photo', false);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('upload-photo', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.replace_contact_photo("' . $file['id'] . '");') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.photo_upload_end();') !== false);

        $upload = \rcmail::get_instance()->get_uploaded_file($file['id']);
        $this->assertSame($file['name'], $upload['name']);
        $this->assertSame($file['type'], $upload['mimetype']);
        $this->assertSame($file['size'], $upload['size']);
        $this->assertSame('contact', $upload['group']);

        // TODO: Test invalid image format, upload errors handling
    }
}
