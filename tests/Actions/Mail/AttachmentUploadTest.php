<?php

namespace Roundcube\Tests\Actions\Mail;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_mail_attachment_upload
 */
class AttachmentUploadTest extends ActionTestCase
{
    /**
     * Test file upload
     */
    public function test_run()
    {
        $action = new \rcmail_action_mail_attachment_upload();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'mail', 'upload');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = [
            '_id' => '123',
            '_uploadid' => 'upload123',
        ];

        $_SESSION = ['compose_data_123' => ['test' => 'test']];

        // No files uploaded case
        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('upload', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.remove_from_attachment_list("upload123");') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.auto_save_start(false);') !== false);

        // Upload a file
        $_SESSION = ['compose_data_123' => ['test' => 'test']];

        $file = $this->fakeUpload('_attachments');

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('upload', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.add2attachment_list("rcmfile' . $file['id'] . '"') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.auto_save_start(false);') !== false);

        $upload = \rcmail::get_instance()->get_uploaded_file($file['id']);
        $this->assertSame($file['name'], $upload['name']);
        $this->assertSame($file['type'], $upload['mimetype']);
        $this->assertSame($file['size'], $upload['size']);
        $this->assertSame($_GET['_id'], $upload['group']);

        // Upload error case
        $_SESSION = ['compose_data_123' => ['test' => 'test']];
        $file = $this->fakeUpload('_attachments', true, \UPLOAD_ERR_INI_SIZE);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('upload', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("The uploaded file exceeds the maximum size') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.auto_save_start(false);') !== false);

        // TODO: Test max_message_size handling
    }

    /**
     * Test file upload via URI
     */
    public function test_uri()
    {
        $this->markTestIncomplete();
    }
}
