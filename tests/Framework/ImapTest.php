<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\DataProvider;
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
            throw new \Error('This test requires `en_US` to be settable as locale.');
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

        // Test sorting when using INBOX/ as a personal namespace prefix (#9452)
        $_SESSION['imap_delimiter'] = '/';
        $_SESSION['imap_namespace'] = [
            'personal' => [['INBOX/', '/']],
            'other' => [['Other Users/', '/']],
            'shared' => [['Shared/', '/']],
        ];

        foreach (['drafts', 'sent', 'junk', 'trash'] as $mbox) {
            \rcube::get_instance()->config->set("{$mbox}_mbox", 'INBOX/' . ucfirst($mbox));
        }

        $object = new \rcube_imap();

        $folders = [
            'INBOX',
            'INBOX/Joker',
            'INBOX/Junk',
            'INBOX/Trash',
            'INBOX/Contacts',
            'INBOX/Sent',
            'INBOX/Sent/RIPE',
            'INBOX/Calendar',
            'INBOX/AJunk',
            'INBOX/Drafts',
        ];

        $expected = [
            'INBOX',
            'INBOX/Drafts',
            'INBOX/Sent',
            'INBOX/Sent/RIPE',
            'INBOX/Junk',
            'INBOX/Trash',
            'INBOX/AJunk',
            'INBOX/Calendar',
            'INBOX/Contacts',
            'INBOX/Joker',
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

    /**
     * Fetching MIME headers of message parts in structure_part()
     *
     * @dataProvider provide_structure_part_mime_headers_cases
     */
    #[DataProvider('provide_structure_part_mime_headers_cases')]
    public function test_structure_part_mime_headers($str, $grouped, $single)
    {
        $structure = \rcube_imap_generic::tokenizeResponse($str, 1);
        $fetched = ['grouped' => [], 'single' => []];

        $conn = $this->createStub(\rcube_imap_generic::class);
        $conn->method('fetchMIMEHeaders')->willReturnCallback(
            static function ($mailbox, $uid, $parts) use (&$fetched) {
                $fetched['grouped'][] = $parts;
                return array_combine($parts, array_map(static fn ($id) => "Content-Location: part-{$id}", $parts));
            }
        );
        $conn->method('fetchPartHeader')->willReturnCallback(
            static function ($mailbox, $id, $is_uid, $part) use (&$fetched) {
                $fetched['single'][] = $part;
                return "Content-Location: part-{$part}";
            }
        );

        $imap = new \rcube_imap();
        $imap->conn = $conn;

        $result = invokeMethod($imap, 'structure_part', [$structure]);

        $this->assertSame(['grouped' => $grouped, 'single' => $single], $fetched);

        // every part got its own headers, and only these parts got headers
        $locations = [];
        $collect = static function ($part) use (&$collect, &$locations) {
            if (isset($part->headers['content-location'])) {
                $locations[$part->mime_id] = $part->headers['content-location'];
            }
            foreach ($part->parts as $subpart) {
                $collect($subpart);
            }
        };
        $collect($result);

        $ids = array_merge($single, ...$grouped);
        $expected = array_combine($ids, array_map(static fn ($id) => "part-{$id}", $ids));
        ksort($expected, \SORT_STRING);
        ksort($locations, \SORT_STRING);

        $this->assertSame($expected, $locations);
    }

    /**
     * Data for test_structure_part_mime_headers(): BODYSTRUCTURE, the part lists
     * of fetchMIMEHeaders() calls, the parts of fetchPartHeader() calls
     */
    public static function provide_structure_part_mime_headers_cases(): iterable
    {
        $alternative = '(("text" "plain" ("charset" "utf-8") NIL NIL "7bit" 10 1 NIL NIL NIL NIL)'
            . '("text" "html" ("charset" "utf-8") NIL NIL "quoted-printable" 50 2 NIL NIL NIL NIL)'
            . ' "alternative" ("boundary" "alt") NIL NIL NIL)';
        $envelope = '("Tue, 29 Sep 2026 10:00:00 +0000" "Forwarded" (("Sender" NIL "sender" "example.org"))'
            . ' (("Sender" NIL "sender" "example.org")) (("Sender" NIL "sender" "example.org"))'
            . ' (("Recipient" NIL "rcpt" "example.net")) NIL NIL NIL "<fwd@example.org>")';

        return [
            // Yahoo Mail: images with a Content-ID, the file name only in Content-Disposition
            'inline images' => [
                '(' . $alternative
                    . '("image" "jpeg" NIL "<i1@example.org>" NIL "base64" 100 NIL ("inline" ("filename" "IMG_0001.jpg")) NIL NIL)'
                    . '("image" "jpeg" NIL "<i2@example.org>" NIL "base64" 100 NIL ("inline" ("filename" "IMG_0002.jpg")) NIL NIL)'
                    . ' "mixed" ("boundary" "mix") NIL NIL NIL)',
                [['2', '3']],
                [],
            ],
            // Gmail: a named image and a named attachment with a Content-ID, a named attachment without
            'named parts' => [
                '((' . $alternative
                    . '("image" "png" ("name" "image.png") "<ii_1>" NIL "base64" 100 NIL ("inline" ("filename" "image.png")) NIL NIL)'
                    . ' "related" ("boundary" "rel") NIL NIL NIL)'
                    . '("application" "pdf" ("name" "a.pdf") "<f_1>" NIL "base64" 100 NIL ("attachment" ("filename" "a.pdf")) NIL NIL)'
                    . '("image" "jpeg" ("name" "b.jpg") NIL NIL "base64" 100 NIL ("attachment" ("filename" "b.jpg")) NIL NIL)'
                    . ' "mixed" ("boundary" "mix") NIL NIL NIL)',
                [['2', '3'], ['1.2']],
                [],
            ],
            // Parts of a message/rfc822 part, on each level
            'forwarded message' => [
                '(("text" "plain" ("charset" "utf-8") NIL NIL "7bit" 10 1 NIL NIL NIL NIL)'
                    . '("message" "rfc822" NIL NIL NIL "7bit" 1000 ' . $envelope . ' ('
                        . '(("text" "html" ("charset" "utf-8") NIL NIL "7bit" 50 2 NIL NIL NIL NIL)'
                        . '("image" "png" NIL "<i1@example.org>" NIL "base64" 100 NIL NIL NIL NIL)'
                        . ' "related" ("boundary" "rel") NIL NIL NIL)'
                        . '("image" "jpeg" NIL "<i2@example.org>" NIL "base64" 100 NIL ("inline" NIL) NIL NIL)'
                        . '("application" "pdf" ("name" "a.pdf") NIL NIL "base64" 100 NIL ("attachment" ("filename" "a.pdf")) NIL NIL)'
                        . ' "mixed" ("boundary" "fwd") NIL NIL NIL) 30 NIL ("attachment" ("filename" "fwd.eml")) NIL NIL)'
                    . ' "mixed" ("boundary" "mix") NIL NIL NIL)',
                [['2'], ['2.2', '2.3'], ['2.1.2']],
                [],
            ],
            // The single part of a message/rfc822 part: only an image, not a named attachment
            'forwarded single parts' => [
                '(("text" "plain" ("charset" "utf-8") NIL NIL "7bit" 10 1 NIL NIL NIL NIL)'
                    . '("message" "rfc822" NIL NIL NIL "7bit" 1000 ' . $envelope
                        . ' ("image" "png" NIL "<i1@example.org>" NIL "base64" 100 NIL NIL NIL NIL) 30 NIL NIL NIL NIL)'
                    . '("message" "rfc822" NIL NIL NIL "7bit" 1000 ' . $envelope
                        . ' ("application" "pdf" ("name" "a.pdf") NIL NIL "base64" 100 NIL ("attachment" ("filename" "a.pdf")) NIL NIL) 30 NIL NIL NIL NIL)'
                    . ' "mixed" ("boundary" "mix") NIL NIL NIL)',
                [['2', '3']],
                ['2.1'],
            ],
            // Text parts, a part with a Content-ID but no parameters, and malformed parts (#9689, #9896)
            'no headers' => [
                '(' . $alternative
                    . '("application" "octet-stream" NIL "<x1@example.org>" NIL "base64" 100 NIL NIL NIL NIL)'
                    . '("application" "octet-stream" (("name") "x.bin") "<x2@example.org>" NIL "base64" 100 NIL NIL NIL NIL)'
                    . '("image" ("name" "x.png") NIL NIL "base64" 100 NIL NIL NIL NIL)'
                    . '(NIL NIL NIL NIL NIL "7bit" 100 NIL NIL NIL NIL)'
                    . ' "mixed" ("boundary" "mix") NIL NIL NIL)',
                [],
                [],
            ],
        ];
    }

    /**
     * Parts without MIME headers (in a multipart/digest), whose empty headers the grouped fetch returns
     *
     * @dataProvider provide_structure_part_empty_mime_headers_cases
     */
    #[DataProvider('provide_structure_part_empty_mime_headers_cases')]
    public function test_structure_part_empty_mime_headers($str, $grouped)
    {
        $structure = \rcube_imap_generic::tokenizeResponse($str, 1);
        $fetched = ['grouped' => [], 'single' => []];

        $conn = $this->createStub(\rcube_imap_generic::class);
        $conn->method('fetchMIMEHeaders')->willReturnCallback(
            static function ($mailbox, $uid, $parts) use (&$fetched) {
                $fetched['grouped'][] = $parts;
                return array_fill_keys($parts, '');
            }
        );
        $conn->method('fetchPartHeader')->willReturnCallback(
            static function ($mailbox, $id, $is_uid, $part) use (&$fetched) {
                $fetched['single'][] = $part;
                return '';
            }
        );

        $imap = new \rcube_imap();
        $imap->conn = $conn;

        invokeMethod($imap, 'structure_part', [$structure]);

        $this->assertSame(['grouped' => $grouped, 'single' => []], $fetched);
    }

    /**
     * Data for test_structure_part_empty_mime_headers(): BODYSTRUCTURE, the part lists of fetchMIMEHeaders() calls
     */
    public static function provide_structure_part_empty_mime_headers_cases(): iterable
    {
        $envelope = '(NIL "one" ((NIL NIL "a" "example.org")) ((NIL NIL "a" "example.org"))'
            . ' ((NIL NIL "a" "example.org")) NIL NIL NIL NIL NIL)';
        $message = '("message" "rfc822" NIL NIL NIL "7bit" 47 ' . $envelope
            . ' ("text" "plain" ("charset" "us-ascii") NIL NIL "7bit" 8 0 NIL NIL NIL NIL) 3 NIL NIL NIL NIL)';
        $digest = '(' . $message . $message . ' "digest" ("boundary" "d") NIL NIL NIL)';

        return [
            'digest' => [$digest, [['1', '2']]],
            'forwarded digest' => [
                '(("text" "plain" ("charset" "us-ascii") NIL NIL "7bit" 8 0 NIL NIL NIL NIL)'
                    . '("message" "rfc822" NIL NIL NIL "7bit" 200 ' . $envelope . ' ' . $digest . ' 10 NIL NIL NIL NIL)'
                    . ' "mixed" ("boundary" "m") NIL NIL NIL)',
                [['2'], ['2.1', '2.2']],
            ],
        ];
    }
}
