<?php

namespace Roundcube\Tests\Actions\Settings;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_settings_upload
 */
class UploadTest extends ActionTestCase
{
    /**
     * Test file uploads
     */
    public function test_run()
    {
        $action = new \rcmail_action_settings_upload();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'settings', 'upload');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = [
            '_from' => 'add-identity',
            '_unlock' => 'loading123',
            '_uploadid' => 'upload123',
        ];

        // No files uploaded case
        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('upload', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.remove_from_attachment_list("upload123");') !== false);
        $this->assertSame($_GET['_unlock'], $result['unlock']);

        // Upload a file
        $file = $this->fakeUpload();

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('upload', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.add2attachment_list("rcmfile' . $file['id'] . '"') !== false);

        $upload = \rcmail::get_instance()->get_uploaded_file($file['id']);
        $this->assertSame($file['name'], $upload['name']);
        $this->assertSame($file['type'], $upload['mimetype']);
        $this->assertSame($file['size'], $upload['size']);
        $this->assertSame('identity', $upload['group']);

        // Upload error case
        $file = $this->fakeUpload('_file', true, \UPLOAD_ERR_INI_SIZE);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('upload', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("The uploaded file exceeds the maximum size') !== false);
    }
}
