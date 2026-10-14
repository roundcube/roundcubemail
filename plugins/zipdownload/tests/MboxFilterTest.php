<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class MboxFilterTest extends TestCase
{
    private $fp;
    private $filter;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        stream_filter_register('test_mbox_filter', '\Roundcube\Plugins\Tests\test_mbox_filter');
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->fp = fopen('php://memory', 'w+');
        $this->filter = stream_filter_append($this->fp, 'test_mbox_filter', \STREAM_FILTER_WRITE);
    }

    /**
     * Basic test with no special case
     */
    public function test_escape()
    {
        $this->assertIsResource($this->filter);
        $this->assertSame(15, fwrite($this->fp, "test\nFrom \ntest"));
        $this->assertTrue(stream_filter_remove($this->filter));
        $this->assertTrue(rewind($this->fp));
        $this->assertSame("test\n>From \ntest", fread($this->fp, 100));
    }

    /**
     * The very first line may be escaped
     */
    public function test_escape_first_line()
    {
        fwrite($this->fp, '>From test');
        $this->assertFalse(test_mbox_filter::was_maybe_split());
        stream_filter_remove($this->filter);
        rewind($this->fp);
        $this->assertSame('>>From test', fread($this->fp, 100));
    }

    /**
     * The beginning of a bucket which isn't the beginning of a new line
     * must not be escaped
     */
    public function test_noescape_bucket_beginning()
    {
        fwrite($this->fp, 'test');
        fwrite($this->fp, 'From ');
        $this->assertFalse(test_mbox_filter::was_maybe_split());
        stream_filter_remove($this->filter);
        rewind($this->fp);
        $this->assertSame('testFrom ', fread($this->fp, 100));
    }

    /**
     * A split From line with a minimal portion in the first bucket
     */
    public function test_escape_split_min()
    {
        fwrite($this->fp, "test\n");
        $this->assertTrue(test_mbox_filter::was_maybe_split());
        fwrite($this->fp, 'From ');
        $this->assertFalse(test_mbox_filter::was_maybe_split());
        stream_filter_remove($this->filter);
        rewind($this->fp);
        $this->assertSame("test\n>From ", fread($this->fp, 100));
    }

    /**
     * A split From line with a maximal portion in the first bucket
     */
    public function test_escape_split_max()
    {
        fwrite($this->fp, "test\n>From");
        $this->assertTrue(test_mbox_filter::was_maybe_split());
        fwrite($this->fp, ' ');
        stream_filter_remove($this->filter);
        rewind($this->fp);
        $this->assertSame("test\n>>From ", fread($this->fp, 100));
    }

    /**
     * A From line that is maybe-split in the first bucket, but not
     * actually a split From line after seeing the second bucket
     */
    public function test_noescape_split()
    {
        fwrite($this->fp, "test\n>From");
        $this->assertTrue(test_mbox_filter::was_maybe_split());
        fwrite($this->fp, "\ntest");
        stream_filter_remove($this->filter);
        rewind($this->fp);
        $this->assertSame("test\n>From\ntest", fread($this->fp, 100));
    }

    /**
     * A From line that is maybe-split with no second bucket
     */
    public function test_noescape_split_last()
    {
        fwrite($this->fp, "test\n>From");
        $this->assertTrue(test_mbox_filter::was_maybe_split());
        stream_filter_remove($this->filter);
        rewind($this->fp);
        $this->assertSame("test\n>From", fread($this->fp, 100));
    }
}

/**
 * The assumption is that separate writes result in separate calls to
 * zipdownload_mbox_filter::filter() which tries to detect a From line
 * that might be split between calls. This checks the internal state of
 * the most recently created (by PHP) zipdownload_mbox_filter to ensure
 * that assumption is correct, otherwise the tests above may be invalid.
 */
class test_mbox_filter extends \zipdownload_mbox_filter
{
    private static $most_recently_created;

    #[\Override]
    public function onCreate(): bool
    {
        self::$most_recently_created = $this;
        return parent::onCreate();
    }

    /**
     * If prev_bucket is a bucket (a stdClass or StreamBucket object, depending
     * on PHP version), zipdownload_mbox_filter's most recently seen bucket has
     * what looks like a split From line at its end.
     */
    public static function was_maybe_split()
    {
        return is_object(self::$most_recently_created->prev_bucket);
    }
}
