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
     * Test convert_criteria()
     */
    function test_convert_criteria()
    {
        $this->assertSame(
            "FLAGGED SINCE 1-Feb-1994 NOT FROM \"Smith\"",
            rcube_imap::convert_criteria("FLAGGED SINCE 1-Feb-1994 NOT FROM \"Smith\"", RCUBE_CHARSET)
        );

        $this->assertSame(
            "ALL TEXT el",
            rcube_imap::convert_criteria("ALL TEXT {4}\r\nżel", RCUBE_CHARSET)
        );

        $this->assertSame(
            "ALL TEXT {4}\r\nżel",
            rcube_imap::convert_criteria("ALL TEXT {4}\r\nżel", RCUBE_CHARSET, RCUBE_CHARSET)
        );
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

        foreach (['drafts', 'sent', 'junk', 'trash'] as $mbox) {
            rcube::get_instance()->config->set("{$mbox}_mbox", ucfirst($mbox));
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
