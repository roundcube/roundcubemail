<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_imap class
 */
class Framework_Imap extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_imap();

        self::assertInstanceOf('rcube_imap', $object, 'Class constructor');
    }

    /**
     * Test convert_criteria()
     */
    public function test_convert_criteria()
    {
        self::assertSame(
            'FLAGGED SINCE 1-Feb-1994 NOT FROM "Smith"',
            rcube_imap::convert_criteria('FLAGGED SINCE 1-Feb-1994 NOT FROM "Smith"', RCUBE_CHARSET)
        );

        self::assertSame(
            'ALL TEXT el',
            rcube_imap::convert_criteria("ALL TEXT {4}\r\nżel", RCUBE_CHARSET)
        );

        self::assertSame(
            "ALL TEXT {4}\r\nżel",
            rcube_imap::convert_criteria("ALL TEXT {4}\r\nżel", RCUBE_CHARSET, RCUBE_CHARSET)
        );
    }

    /**
     * Folder sorting
     */
    public function test_sort_folder_list()
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

        $object = new rcube_imap();

        $result = $object->sort_folder_list([]);
        self::assertSame([], $result);

        $result = $object->sort_folder_list(['B', 'A']);
        self::assertSame(['A', 'B'], $result);

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

        self::assertSame($expected, $result);
    }

    /**
     * BODYSTRUCTURE parsing
     */
    public function test_bodystructure()
    {
        // A sample from #8803
        $str = '(("TEXT" "PLAIN" ("CHARSET" "utf-8") NIL NIL "8bit" 232 7)'
            . '("MESSAGE" "DISPOSITION-NOTIFICATION" ("NAME" "ATT-3.dat") NIL NIL "7bit" 269 NIL ("ATTACHMENT" ("FILENAME" "ATT-3.dat")))'
            . '("MESSAGE" "RFC822" ("NAME" "Test mail.eml") NIL NIL "7bit" 3953 ("Fri, 25 Nov 2022 18:08:05 +0000" "Test mail"'
                . ' (("Sender" NIL "sender" "hostname.tld"))'
                . ' (("Sender" NIL "sender" "hostname.tld"))'
                . ' (("Sender" NIL "sender" "hostname.tld"))'
                . ' (("Recipient A" NIL "extmail" "exthost.tld"))'
                . ' (("Recipient B" NIL "otherusr" "hostname.tld"))'
                . ' NIL NIL "<960564af959918c2a7b2e59bde1ebb79@hostname.tld>")'
                . ' ( "MIXED" ("BOUNDARY" "=_0cc01990d46dea96cd7d692970fcbf82") NIL NIL) 1 NIL ("ATTACHMENT" ("FILENAME" "Test mail.eml")))'
            . ' "REPORT" ("BOUNDARY" "=_RrjQxjLYBqTMnoYWobuYlwN") NIL NIL)';

        $structure = rcube_imap_generic::tokenizeResponse($str, 1);

        $imap = new rcube_imap();

        $result = invokeMethod($imap, 'structure_part', [$structure]);

        self::assertSame('0', $result->mime_id);
        self::assertSame('multipart', $result->ctype_primary);
        self::assertSame('report', $result->ctype_secondary);
        self::assertSame('multipart/report', $result->mimetype);
        self::assertSame(['boundary' => '=_RrjQxjLYBqTMnoYWobuYlwN'], $result->ctype_parameters);
        self::assertSame([], $result->d_parameters);
        self::assertSame('8bit', $result->encoding);
        self::assertCount(3, $result->parts);

        $part = $result->parts[2];
        self::assertSame('3', $part->mime_id);
        self::assertSame('message/rfc822', $part->mimetype);
        self::assertSame('multipart/mixed', $part->real_mimetype);
        self::assertSame(3953, $part->size);
        self::assertCount(1, $part->parts);
    }
}
