<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;
use Roundcube\Tests\MessageMock;

use function Roundcube\Tests\getProperty;
use function Roundcube\Tests\setProperty;

/**
 * Test class to test rcube_message class
 */
class MessageTest extends TestCase
{
    /**
     * Test format_part_body() method
     */
    public function test_format_part_body()
    {
        $part = new \rcube_message_part();
        $body = 'test';
        $result = \rcube_message::format_part_body($body, $part);

        $this->assertSame('test', $result);
    }

    /**
     * Test get_part_url() method
     */
    public function test_get_part_url()
    {
        $message = new MessageMock(10, 'Test');
        $message->mime_parts[1] = new \rcube_message_part();

        $url = $message->get_part_url(1, 'test&test=1');
        $this->assertSame('URL&_part=1&_embed=1&_mimeclass=test%26test%3D1', $url);

        $this->assertFalse($message->get_part_url(10));
    }

    /**
     * Test get_part_body() with a byte limit, on a part it would keep in memory whole
     */
    public function test_get_part_body_max_bytes()
    {
        $part = new \rcube_message_part();
        $part->mime_id = '2';
        $part->mimetype = 'message/rfc822';
        $part->size = 400000;

        $storage = new \Roundcube\Tests\StorageMock();
        $storage->registerFunction('set_folder')
            ->registerFunction('get_message_part', "Subject: Test\r\n\r\nBody");

        $message = (new \ReflectionClass(\rcube_message::class))->newInstanceWithoutConstructor();
        $message->uid = 10;
        $message->folder = 'INBOX';
        $message->mime_parts = ['2' => $part];
        \Roundcube\Tests\setProperty($message, 'app', \rcube::get_instance(), \rcube_message::class);
        \Roundcube\Tests\setProperty($message, 'storage', $storage, \rcube_message::class);

        $this->assertSame("Subject: Test\r\n\r\nBody", $message->get_part_body('2', false, 32768));

        // Only the requested bytes are fetched, and the part body is not kept as if it were whole
        $this->assertSame('get_message_part', $storage->methodCalls[1]['name']);
        $this->assertSame(32768, $storage->methodCalls[1]['args'][6]);
        $this->assertNull($part->body);
    }

    /**
     * Test get_part_body() keeping a part of a nested multipart in memory
     */
    public function test_get_part_body_nested_part()
    {
        $part = new \rcube_message_part();
        $part->mime_id = '1.1.2';
        $part->mimetype = 'text/html';
        $part->ctype_primary = 'text';
        $part->size = 100;

        // One fetch only: a second one would be an unhandled storage call
        $storage = new \Roundcube\Tests\StorageMock();
        $storage->registerFunction('set_folder')
            ->registerFunction('get_message_part', '<p>Test</p>');

        $message = (new \ReflectionClass(\rcube_message::class))->newInstanceWithoutConstructor();
        $message->uid = 10;
        $message->folder = 'INBOX';
        $message->mime_parts = ['1.1.2' => $part];
        \Roundcube\Tests\setProperty($message, 'app', \rcube::get_instance(), \rcube_message::class);
        \Roundcube\Tests\setProperty($message, 'storage', $storage, \rcube_message::class);

        $this->assertSame('<p>Test</p>', $message->get_part_body('1.1.2'));
        $this->assertSame('<p>Test</p>', $message->get_part_body('1.1.2'));
        $this->assertCount(2, $storage->methodCalls);
    }

    /**
     * Test tnef_decode() method
     */
    public function test_tnef_decode()
    {
        $message = new MessageMock(123);
        $part = new \rcube_message_part();
        $part->mime_id = '1';

        $message->set_part_body(1, '');
        $result = $message->tnef_decode($part);

        $this->assertSame([], $result);

        $message->set_part_body(1, file_get_contents(TESTS_DIR . 'src/body.tnef'));
        $result = $message->tnef_decode($part);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(\rcube_message_part::class, $result[0]);
        $this->assertSame('winmail.1.html', $result[0]->mime_id);
        $this->assertSame('text/html', $result[0]->mimetype);
        $this->assertSame(5360, $result[0]->size);
        $this->assertStringStartsWith('<!DOCTYPE HTML', $result[0]->body);
        $this->assertSame([], $result[0]->parts);

        $message->set_part_body(1, file_get_contents(TESTS_DIR . 'src/one-file.tnef'));
        $result = $message->tnef_decode($part);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(\rcube_message_part::class, $result[0]);
        $this->assertSame('winmail.1.0', $result[0]->mime_id);
        $this->assertSame('application/octet-stream', $result[0]->mimetype);
        $this->assertSame(244, $result[0]->size);
        $this->assertStringContainsString(' Authors of', $result[0]->body);
        $this->assertSame([], $result[0]->parts);
    }

    /**
     * Test uu_decode() method
     */
    public function test_uu_decode()
    {
        $message = new MessageMock(123);
        $part = new \rcube_message_part();
        $part->mime_id = '1';

        $message->set_part_body(1, '');
        $result = $message->uu_decode($part);

        $this->assertSame([], $result);

        $content = "begin 644 /dev/stdout\n" . convert_uuencode('test') . 'end';
        $message->set_part_body(1, $content);

        $result = $message->uu_decode($part);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(\rcube_message_part::class, $result[0]);
        $this->assertSame('uu.1.0', $result[0]->mime_id);
        $this->assertSame('text/plain', $result[0]->mimetype);
        $this->assertSame(4, $result[0]->size);
        $this->assertSame('test', $result[0]->body);
        $this->assertSame([], $result[0]->parts);
    }

    /**
     * Test is_referred_attachment() method
     */
    public function test_is_referred_attachment()
    {
        $plain = new \rcube_message_part();
        $plain->mime_id = '1';
        $plain->mimetype = 'text/plain';
        $plain->type = 'content';

        $html = new \rcube_message_part();
        $html->mime_id = '2';
        $html->mimetype = 'text/html';
        $html->type = 'content';
        $html->replaces = ['cid:img1' => 'URL&_part=3&_embed=1&_mimeclass=image', 'cid:img2' => 'URL&_part=4&_embed=1&_mimeclass=image'];

        $img1 = new \rcube_message_part();
        $img1->mime_id = '3';
        $img1->ctype_primary = 'image';
        $img1->content_id = 'img1';

        $img2 = new \rcube_message_part();
        $img2->mime_id = '4';
        $img2->ctype_primary = 'image';
        $img2->content_id = 'img2';

        $file = new \rcube_message_part();
        $file->mime_id = '5';
        $file->mimetype = 'text/html';
        $file->filename = 'page.html';

        $message = new MessageMock(10);
        $message->parts = [$plain, $html];
        $message->mime_parts = ['1' => $plain, '2' => $html, '3' => $img1, '4' => $img2, '5' => $file];
        $message->attachments = [$img1, $img2, $file];
        // A text/plain part shows no image, even if it names one (as Outlook does),
        // and neither does an HTML part that is not displayed (an attached file)
        $message->set_part_body('1', '[cid:img2]');
        $message->set_part_body('2', '<img src="cid:img1">');
        $message->set_part_body('5', '<img src="cid:img2">');
        setProperty($message, 'opt', ['prefer_html' => true, 'get_url' => 'URL'], \rcube_message::class);

        $this->assertTrue($message->is_referred_attachment($img1));
        $this->assertFalse($message->is_referred_attachment($img2));

        // The HTML parts are parsed once per message
        $message->set_part_body('2', '');
        $this->assertTrue($message->is_referred_attachment($img1));

        // An HTML part displayed as plain text shows no image
        $message = new MessageMock(10);
        $message->parts = [$html];
        $message->mime_parts = ['2' => $html, '3' => $img1, '4' => $img2];
        $message->attachments = [$img1, $img2];
        $message->set_part_body('2', '<img src="cid:img1">');
        setProperty($message, 'opt', ['prefer_html' => false, 'get_url' => 'URL'], \rcube_message::class);

        $this->assertFalse($message->is_referred_attachment($img1));
    }

    /**
     * Test is_referred_attachment() with a Content-ID that begins a longer one,
     * and with an HTML part that names a part of another part's replaces map
     */
    public function test_is_referred_attachment_matching()
    {
        $html1 = new \rcube_message_part();
        $html1->mime_id = '1.1';
        $html1->mimetype = 'text/html';
        $html1->type = 'content';

        $html2 = new \rcube_message_part();
        $html2->mime_id = '2.1';
        $html2->mimetype = 'text/html';
        $html2->type = 'content';

        $images = [];
        foreach (['img1' => '2.2', 'img10' => '2.3', 'img2' => '2.4'] as $cid => $mime_id) {
            $images[$cid] = new \rcube_message_part();
            $images[$cid]->mime_id = $mime_id;
            $images[$cid]->ctype_primary = 'image';
            $images[$cid]->content_id = $cid;
            $html2->replaces["cid:{$cid}"] = "URL&_part={$mime_id}&_embed=1&_mimeclass=image";
        }

        $message = new MessageMock(10);
        $message->parts = [$html1, $html2];
        $message->mime_parts = ['1.1' => $html1, '2.1' => $html2] + array_combine(array_column($images, 'mime_id'), $images);
        $message->attachments = array_values($images);
        // The part without a replaces map shows no image, see print_body()
        $message->set_part_body('1.1', '<img src="cid:img2">');
        $message->set_part_body('2.1', '<img src="cid:img10">');
        setProperty($message, 'opt', ['prefer_html' => true, 'get_url' => 'URL'], \rcube_message::class);

        $this->assertFalse($message->is_referred_attachment($images['img1']));
        $this->assertTrue($message->is_referred_attachment($images['img10']));
        $this->assertFalse($message->is_referred_attachment($images['img2']));
    }

    /**
     * Test is_referred_attachment() with parts sharing the Content-ID of the image
     * an HTML part shows: only the copies of that image are not listed
     */
    public function test_is_referred_attachment_shared_content_id()
    {
        $html = new \rcube_message_part();
        $html->mime_id = '1';
        $html->mimetype = 'text/html';
        $html->type = 'content';
        $html->size = 100;

        $message = (new \ReflectionClass(\rcube_message::class))->newInstanceWithoutConstructor();
        $message->uid = 10;
        $message->folder = 'INBOX';
        $message->parts = [$html];
        $message->mime_parts = ['1' => $html];
        setProperty($message, 'opt', ['prefer_html' => true, 'get_url' => 'URL'], \rcube_message::class);

        // The image shown (2), a copy (3), a different image (4), a part that cannot be read (5),
        // a copy too big for the memory left (6), an image shown that cannot be read (7) and a copy (8)
        $sizes = ['2' => 100, '3' => 100, '4' => 100, '5' => 100, '6' => 256 * 1024 * 1024, '7' => 100, '8' => 100];
        foreach ($sizes as $mime_id => $size) {
            $part = new \rcube_message_part();
            $part->mime_id = (string) $mime_id;
            $part->ctype_primary = 'image';
            $part->content_id = $mime_id < 7 ? 'logo' : 'icon';
            $part->size = $size;
            $message->mime_parts[$mime_id] = $message->attachments[] = $part;
        }

        // The replaces map shows the first part with the Content-ID, not the last one
        $html->replaces = ['cid:logo' => 'URL&_part=2&_embed=1&_mimeclass=image', 'cid:icon' => 'URL&_part=7&_embed=1&_mimeclass=image'];

        // Each part is read once at most: another read would be an unhandled storage call
        $storage = new \Roundcube\Tests\StorageMock();
        foreach (['<img src="cid:logo"><img src="cid:icon">', 'logo', 'logo', 'other', false, false] as $body) {
            $storage->registerFunction('set_folder')->registerFunction('get_message_part', $body);
        }

        setProperty($message, 'app', \rcube::get_instance(), \rcube_message::class);
        setProperty($message, 'storage', $storage, \rcube_message::class);

        $memory_limit = ini_get('memory_limit');
        ini_set('memory_limit', (string) (memory_get_usage(true) + 64 * 1024 * 1024));

        try {
            // The attachment list and the gallery below the body ask for each part
            foreach ([1, 2] as $pass) {
                foreach (['2' => true, '3' => true, '4' => false, '5' => false, '6' => false, '7' => true, '8' => false] as $mime_id => $referred) {
                    $this->assertSame($referred, $message->is_referred_attachment($message->mime_parts[$mime_id]), "Part {$mime_id}");
                }
            }
        } finally {
            ini_set('memory_limit', $memory_limit);
        }

        $reads = [];
        foreach ($storage->methodCalls as $call) {
            if ($call['name'] == 'get_message_part') {
                $reads[] = $call['args'][1];
            }
        }

        $this->assertSame(['1', '2', '3', '4', '5', '7'], $reads);

        // A copy is referred to by the URL of the image shown
        $this->assertSame('URL&_part=2&_embed=1&_mimeclass=image', $message->get_referred_url($message->mime_parts['3']));
        $this->assertNull($message->get_referred_url($message->mime_parts['4']));
    }

    /**
     * Test is_referred_attachment() with an HTML part too big to display
     */
    public function test_is_referred_attachment_part_too_big()
    {
        $html = new \rcube_message_part();
        $html->mime_id = '1';
        $html->mimetype = 'text/html';
        $html->type = 'content';
        $html->size = 64 * 1024 * 1024;
        $html->replaces = ['cid:img1' => 'URL&_part=2&_embed=1&_mimeclass=image'];

        $img = new \rcube_message_part();
        $img->mime_id = '2';
        $img->ctype_primary = 'image';
        $img->content_id = 'img1';

        $message = (new \ReflectionClass(\rcube_message::class))->newInstanceWithoutConstructor();
        $message->uid = 10;
        $message->folder = 'INBOX';
        $message->parts = [$html];
        $message->mime_parts = ['1' => $html, '2' => $img];
        $message->attachments = [$img];
        setProperty($message, 'opt', ['prefer_html' => true, 'get_url' => 'URL'], \rcube_message::class);

        // The HTML part is not read: a read would be an unhandled storage call
        setProperty($message, 'app', \rcube::get_instance(), \rcube_message::class);
        setProperty($message, 'storage', new \Roundcube\Tests\StorageMock(), \rcube_message::class);

        $memory_limit = ini_get('memory_limit');
        ini_set('memory_limit', (string) (memory_get_usage(true) + 64 * 1024 * 1024));

        try {
            // The body shows a notice instead of the part, and the image is listed
            $this->assertTrue($message->is_part_too_big($html));
            $this->assertFalse($message->is_referred_attachment($img));
        } finally {
            ini_set('memory_limit', $memory_limit);
        }
    }

    /**
     * Test get_part_body() keeping an HTML part of 1 MB or more for its display
     */
    public function test_get_part_body_reuse()
    {
        $html = new \rcube_message_part();
        $html->mime_id = '1';
        $html->mimetype = 'text/html';
        $html->ctype_secondary = 'html';
        $html->type = 'content';
        $html->size = \rcube_message::BODY_MAX_SIZE;
        $html->replaces = ['cid:img1' => 'URL&_part=2&_embed=1&_mimeclass=image'];

        $img = new \rcube_message_part();
        $img->mime_id = '2';
        $img->ctype_primary = 'image';
        $img->content_id = 'img1';

        $big = new \rcube_message_part();
        $big->mime_id = '3';
        $big->mimetype = 'text/html';
        $big->ctype_secondary = 'html';
        $big->size = 16 * 1024 * 1024;

        $message = (new \ReflectionClass(\rcube_message::class))->newInstanceWithoutConstructor();
        $message->uid = 10;
        $message->folder = 'INBOX';
        $message->parts = [$html];
        $message->mime_parts = ['1' => $html, '2' => $img, '3' => $big];
        $message->attachments = [$img];
        setProperty($message, 'opt', ['prefer_html' => true, 'get_url' => 'URL'], \rcube_message::class);

        // Every fetch: another one would be an unhandled storage call
        $storage = new \Roundcube\Tests\StorageMock();
        foreach (['<img src="cid:img1">', '<p>Big</p>', '<p>Big</p>'] as $body) {
            $storage->registerFunction('set_folder')->registerFunction('get_message_part', $body);
        }

        setProperty($message, 'app', \rcube::get_instance(), \rcube_message::class);
        setProperty($message, 'storage', $storage, \rcube_message::class);

        $memory_limit = ini_get('memory_limit');
        ini_set('memory_limit', (string) (memory_get_usage(true) + 64 * 1024 * 1024));

        try {
            // The HTML part is fetched once, to find the images it shows, and displayed
            $this->assertTrue($message->is_referred_attachment($img));
            $this->assertSame('<img src="cid:img1">', $message->get_part_body('1', true));
            $this->assertCount(2, $storage->methodCalls);

            // A part whose display does not fit in memory with its body kept is fetched again
            setProperty($message, 'keep_for_display', '3', \rcube_message::class);
            $this->assertSame('<p>Big</p>', $message->get_part_body('3'));
            $this->assertNull($big->body);
            $this->assertSame('<p>Big</p>', $message->get_part_body('3', true));
            $this->assertCount(6, $storage->methodCalls);
        } finally {
            ini_set('memory_limit', $memory_limit);
        }
    }

    /**
     * Test is_part_too_big() releasing the bodies kept since the references were read,
     * one of 1 MB or more kept for its display and one kept by the cache of get_part_body()
     */
    public function test_is_part_too_big_kept_body()
    {
        $message = $this->message_with_html_parts(['1' => [2 * 1024 * 1024, 8 * 1024 * 1024], '2' => [900 * 1024, 8 * 1024 * 1024]]);
        $storage = getProperty($message, 'storage', \rcube_message::class);
        $storage->registerFunction('set_folder')->registerFunction('get_message_part', '<p>Test</p>');

        $memory_limit = ini_get('memory_limit');
        ini_set('memory_limit', (string) (memory_get_usage(true) + 64 * 1024 * 1024));

        try {
            $this->assertTrue($message->is_referred_attachment($message->mime_parts['3']));
            $this->assertTrue($message->is_referred_attachment($message->mime_parts['4']));
            $this->assertIsString($message->mime_parts['1']->body);
            $this->assertIsString($message->mime_parts['2']->body);

            // The first part fits in memory only without both kept bodies, which the display fetches again
            ini_set('memory_limit', (string) (memory_get_usage() + 8 * 1024 * 1024));
            $this->assertFalse($message->is_part_too_big($message->mime_parts['1']));
            $this->assertNull($message->mime_parts['1']->body);
            $this->assertNull($message->mime_parts['2']->body);
            $this->assertSame('<p>Test</p>', $message->get_part_body('1', true));
        } finally {
            ini_set('memory_limit', $memory_limit);
        }
    }

    /**
     * Test is_referred_attachment() on an HTML part that fits in memory only
     * without the body of the part read before it
     */
    public function test_is_referred_attachment_releases_read_body()
    {
        $message = $this->message_with_html_parts(['1' => [100, 16 * 1024 * 1024], '2' => [2 * 1024 * 1024, 0]]);

        $memory_limit = ini_get('memory_limit');
        ini_set('memory_limit', (string) (memory_get_usage() + 12 * 1024 * 1024));

        try {
            $this->assertTrue($message->is_referred_attachment($message->mime_parts['4']));
            $this->assertNull($message->mime_parts['1']->body);
        } finally {
            ini_set('memory_limit', $memory_limit);
        }
    }

    /**
     * Creates a message of HTML parts, each showing an image, from their size and
     * the length of the body the storage returns once for each after its <img>
     */
    private function message_with_html_parts(array $parts): \rcube_message
    {
        $message = (new \ReflectionClass(\rcube_message::class))->newInstanceWithoutConstructor();
        $message->uid = 10;
        $message->folder = 'INBOX';
        setProperty($message, 'opt', ['prefer_html' => true, 'get_url' => 'URL'], \rcube_message::class);

        $storage = new \Roundcube\Tests\StorageMock();

        foreach ($parts as $mime_id => [$size, $length]) {
            $img = new \rcube_message_part();
            $img->mime_id = (string) ($mime_id + 2);
            $img->ctype_primary = 'image';
            $img->content_id = "img{$mime_id}";

            $html = new \rcube_message_part();
            $html->mime_id = (string) $mime_id;
            $html->mimetype = 'text/html';
            $html->ctype_secondary = 'html';
            $html->size = $size;
            $html->replaces = ["cid:img{$mime_id}" => "URL&_part={$img->mime_id}&_embed=1&_mimeclass=image"];

            $message->parts[] = $html;
            $message->mime_parts[$html->mime_id] = $html;
            $message->mime_parts[$img->mime_id] = $message->attachments[] = $img;

            // Only the storage holds the body until it is read
            $storage->registerFunction('set_folder')
                ->registerFunction('get_message_part', "<img src=\"cid:img{$mime_id}\">" . str_repeat('x', $length));
        }

        setProperty($message, 'app', \rcube::get_instance(), \rcube_message::class);
        setProperty($message, 'storage', $storage, \rcube_message::class);

        return $message;
    }
}
