<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

use function Roundcube\Tests\invokeMethod;

/**
 * Test class to test rcube_imap class
 */
class ImapTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_imap();

        $this->assertInstanceOf(\rcube_imap::class, $object, 'Class constructor');
    }

    /**
     * Test convert_criteria()
     */
    public function test_convert_criteria()
    {
        $this->assertSame(
            'FLAGGED SINCE 1-Feb-1994 NOT FROM "Smith"',
            \rcube_imap::convert_criteria('FLAGGED SINCE 1-Feb-1994 NOT FROM "Smith"', RCUBE_CHARSET)
        );

        $this->assertSame(
            'ALL TEXT el',
            \rcube_imap::convert_criteria("ALL TEXT {4}\r\nżel", RCUBE_CHARSET)
        );

        $this->assertSame(
            "ALL TEXT {4}\r\nżel",
            \rcube_imap::convert_criteria("ALL TEXT {4}\r\nżel", RCUBE_CHARSET, RCUBE_CHARSET)
        );
    }

    /**
     * Folder sorting
     */
    public function test_sort_folder_list()
    {
        // The sorting requires this locale.
        if (setlocale(\LC_ALL, 'en_US.UTF-8', 'en_US.utf8', 'en_US', 'en-US') === false) {
            throw new \Error('This test requires `en_US` to be settable as locale, but those appear to not be present in your environment!');
        }

        $_SESSION['imap_delimiter'] = '.';
        $_SESSION['imap_namespace'] = [
            'personal' => null,
            'other' => [['Other Users.', '.']],
            'shared' => [['Shared.', '.']],
        ];

        foreach (['drafts', 'sent', 'junk', 'trash'] as $mbox) {
            \rcube::get_instance()->config->set("{$mbox}_mbox", ucfirst($mbox));
        }

        $object = new \rcube_imap();

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

        // More tricky scenario where a special folder is a subfolder of INBOX
        \rcube::get_instance()->config->set('junk_mbox', 'INBOX.Junk');

        $object = new \rcube_imap();

        $folders = [
            'Trash',
            'Sent',
            'ABC',
            'Drafts',
            'INBOX',
            'INBOX.Trash',
            'INBOX.Junk',
            'INBOX.Sent',
            'INBOX.Drafts',
            'INBOX.Junk.Sub',
            'INBOX.sub',
            'Shared.Test1',
            'Other Users.Test2',
            'Junk',
            'DEF',
        ];

        $expected = [
            'INBOX',
            'INBOX.Drafts',
            'INBOX.Sent',
            'INBOX.sub',
            'INBOX.Trash',
            'Drafts',
            'Sent',
            'INBOX.Junk',
            'INBOX.Junk.Sub',
            'Trash',
            'ABC',
            'DEF',
            'Junk',
            'Other Users.Test2',
            'Shared.Test1',
        ];

        $result = $object->sort_folder_list($folders);

        $this->assertSame($expected, $result);
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

        $structure = \rcube_imap_generic::tokenizeResponse($str, 1);

        $imap = new \rcube_imap();

        $result = invokeMethod($imap, 'structure_part', [$structure]);

        $this->assertSame('0', $result->mime_id);
        $this->assertSame('multipart', $result->ctype_primary);
        $this->assertSame('report', $result->ctype_secondary);
        $this->assertSame('multipart/report', $result->mimetype);
        $this->assertSame(['boundary' => '=_RrjQxjLYBqTMnoYWobuYlwN'], $result->ctype_parameters);
        $this->assertSame([], $result->d_parameters);
        $this->assertSame('8bit', $result->encoding);
        $this->assertCount(3, $result->parts);

        $part = $result->parts[2];
        $this->assertSame('3', $part->mime_id);
        $this->assertSame('message/rfc822', $part->mimetype);
        $this->assertSame('multipart/mixed', $part->real_mimetype);
        $this->assertSame(3953, $part->size);
        $this->assertCount(1, $part->parts);
    }
}
