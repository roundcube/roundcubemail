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

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('upload', $result['action']);
        $this->assertContains(['remove_from_attachment_list', 'upload123'], $result['js_calls']);
        $this->assertContains(['auto_save_start', false], $result['js_calls']);

        // Upload a file
        $_SESSION = ['compose_data_123' => ['test' => 'test']];

        $file = $this->fakeUpload('_attachments');

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('upload', $result['action']);

        $attachment = $result['js_calls'][0];
        unset($attachment[2]['html']);

        $this->assertSame(['add2attachment_list', 'rcmfile' . $file['id'], [
            'name' => 'test.gif',
            'mimetype' => 'image/gif',
            'classname' => 'image gif',
            'complete' => true,
        ], 'upload123'], $attachment);
        $this->assertContains(['auto_save_start', false], $result['js_calls']);

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

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('upload', $result['action']);
        $this->assertSame('display_message', $result['js_calls'][0][0]);
        $this->assertStringMatchesFormat('The uploaded file exceeds the maximum size of %s.', $result['js_calls'][0][1]);
        $this->assertSame('error', $result['js_calls'][0][2]);
        $this->assertContains(['auto_save_start', false], $result['js_calls']);

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
