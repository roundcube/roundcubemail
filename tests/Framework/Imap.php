<?php

/**
 * Test class to test rcube_imap class
 *
 * @package Tests
 */
class Framework_Imap extends PHPUnit\Framework\TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_imap;

        $this->assertInstanceOf('rcube_imap', $object, "Class constructor");
    }

    /**
     * Folder sorting
     */
    function test_sort_folder_list()
    {
        $_SESSION['imap_delimiter'] = '.';
        $_SESSION['imap_namespace'] = [
            'personal' => null,
            'other' => [['Other Users.', '.']],
            'shared' => [['Shared.', '.']],
        ];

        foreach (array('drafts', 'sent', 'junk', 'trash') as $mbox) {
            rcube::get_instance()->config->set("$mbox_mbox", ucfirst($mbox));
        }

        $object = new rcube_imap;

        $result = $object->sort_folder_list([]);
        $this->assertSame([], $result);

        $result = $object->sort_folder_list(['B', 'A']);
        $this->assertSame(['A', 'B'], $result);

        $folders = [
            'Trash',
            'Sent',
            'ABC',
            'Drafts',
            'INBOX.Trash',
            'INBOX.Junk',
            'INBOX.Sent',
            'INBOX.Drafts',
            'Shared.Test1',
            'Other Users.Test2',
            'Junk',
            'INBOX',
            'DEF',
        ];

        $expected = [
            'INBOX',
            'INBOX.Drafts',
            'INBOX.Junk',
            'INBOX.Sent',
            'INBOX.Trash',
            'Drafts',
            'Sent',
            'Junk',
            'Trash',
            'ABC',
            'DEF',
            'Other Users.Test2',
            'Shared.Test1',
        ];

        $result = $object->sort_folder_list($folders);

        $this->assertSame($expected, $result);
    }
}
