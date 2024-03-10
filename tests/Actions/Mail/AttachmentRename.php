<?php

/**
 * Test class to test rcmail_action_mail_attachment_rename
 */
class Actions_Mail_AttachmentRename extends ActionTestCase
{
    /**
     * Test uploaded attachment rename
     */
    public function test_run()
    {
        $rcmail = rcmail::get_instance();
        $action = new rcmail_action_mail_attachment_rename();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'rename-attachment');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        // First we create the upload record
        $file = $this->fileUpload('100');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION = ['compose_data_100' => ['test' => 'test']];

        // Invoke the rename action
        $_POST = ['_id' => '100', '_file' => 'rcmfile' . $file['id'], '_name' => 'mod.gif'];
        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('rename-attachment', $result['action']);
        self::assertSame('this.rename_attachment_handler("rcmfile' . $file['id'] . '","mod.gif");', trim($result['exec']));

        $upload = $rcmail->get_uploaded_file($file['id']);
        self::assertSame($_POST['_name'], $upload['name']);
        self::assertSame($_POST['_id'], $upload['group']);
    }
}
