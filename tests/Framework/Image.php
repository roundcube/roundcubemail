<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_image class
 */
class Framework_Image extends TestCase
{
    /**
     * Test props() method
     */
    public function test_props()
    {
        $object = new rcube_image(INSTALL_PATH . 'skins/elastic/thumbnail.png');

        if (!function_exists('getimagesize')) {
            self::markTestSkipped();
        }

        $props = $object->props();

        self::assertSame('png', $props['type']);
        self::assertSame(64, $props['width']);
        self::assertSame(64, $props['height']);
    }

    /**
     * Test resize() method
     */
    public function test_resize()
    {
        $object = new rcube_image(INSTALL_PATH . 'skins/elastic/thumbnail.png');

        if (!function_exists('getimagesize')) {
            self::markTestSkipped();
        }

        $file = rcube_utils::temp_filename('tests');

        self::assertSame('png', $object->resize(32, $file));

        $object = new rcube_image($file);
        $props = $object->props();

        @unlink($file);

        self::assertSame('png', $props['type']);
        self::assertSame(32, $props['width']);
        self::assertSame(32, $props['height']);
    }

    /**
     * Test convert() method
     */
    public function test_convert()
    {
        $object = new rcube_image(INSTALL_PATH . 'skins/elastic/thumbnail.png');

        if (!function_exists('getimagesize')) {
            self::markTestSkipped();
        }

        $file = rcube_utils::temp_filename('tests');

        self::assertTrue($object->convert(rcube_image::TYPE_JPG, $file));

        $object = new rcube_image($file);
        $props = $object->props();

        @unlink($file);

        self::assertSame('jpeg', $props['type']);
        self::assertSame(64, $props['width']);
        self::assertSame(64, $props['height']);
    }

    /**
     * Test is_convertable() method
     */
    public function test_convertable()
    {
        rcube::get_instance()->config->set('im_convert_path', '');

        $file = rcube_utils::temp_filename('tests');
        $object = new rcube_image($file);

        if (class_exists('Imagick', false)) {
            self::assertTrue($object->is_convertable('image/gif'));
            self::assertFalse($object->is_convertable('xxx'));
        } elseif (!function_exists('getimagesize')) {
            self::markTestSkipped();
        }

        if (function_exists('imagecreatefromgif')) {
            self::assertTrue($object->is_convertable('image/gif'));
        } else {
            self::assertFalse($object->is_convertable('image/gif'));
        }

        if (function_exists('imagecreatefromjpeg')) {
            self::assertTrue($object->is_convertable('image/jpg'));
            self::assertTrue($object->is_convertable('image/jpeg'));
        } else {
            self::assertFalse($object->is_convertable('image/jpg'));
            self::assertFalse($object->is_convertable('image/jpeg'));
        }

        if (function_exists('imagecreatefrompng')) {
            self::assertTrue($object->is_convertable('image/png'));
        } else {
            self::assertFalse($object->is_convertable('image/png'));
        }

        if (function_exists('imagecreatefromwebp')) {
            self::assertTrue($object->is_convertable('image/webp'));
        } else {
            self::assertFalse($object->is_convertable('image/webp'));
        }

        self::assertFalse($object->is_convertable('xxx'));
    }
}
