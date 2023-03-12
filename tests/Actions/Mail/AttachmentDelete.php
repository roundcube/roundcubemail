<?php

/**
 * Test class to test rcmail_action_mail_attachment_delete
 *
 * @package Tests
 */
class Actions_Mail_AttachmentDelete extends ActionTestCase
{
    /**
     * Test uploaded attachment delete
     */
    function test_run()
    {
        $rcmail = rcube::get_instance();
        $action = new rcmail_action_mail_attachment_delete;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'delete-attachment');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // First we create the upload record
        $file = $this->fileUpload('101');

        // Test list_uploaded_files(), just because
        $list = $rcmail->list_uploaded_files('101');

        $this->assertSame([$file], $list);

        // This is needed so upload deletion works
        $rcmail = rcmail::get_instance();
        unset($rcmail->plugins->handlers['attachment_delete']);
        $rcmail->plugins->register_hook('attachment_delete', function($att) {
            $att['status'] = true;
            $att['break'] = true;
            return $att;
        });

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION = ['compose_data_101' => ['test' => 'test']];

        // Invoke the delete action
        $_POST = ['_id' => '101', '_file' => 'rcmfile' . $file['id']];
        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('delete-attachment', $result['action']);
        $this->assertSame('this.remove_from_attachment_list("rcmfile' . $file['id'] . '");', trim($result['exec']));

        $this->assertNull(rcube::get_instance()->get_uploaded_file($file['id']));
    }
}
