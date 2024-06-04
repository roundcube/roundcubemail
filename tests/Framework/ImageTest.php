<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_image class
 */
class ImageTest extends TestCase
{
    /**
     * Test props() method
     */
    public function test_props()
    {
        $object = new \rcube_image(INSTALL_PATH . 'skins/elastic/thumbnail.png');

        if (!function_exists('getimagesize')) {
            $this->markTestSkipped();
        }

        $props = $object->props();

        $this->assertSame('png', $props['type']);
        $this->assertSame(64, $props['width']);
        $this->assertSame(64, $props['height']);
    }

    /**
     * Test resize() method
     */
    public function test_resize()
    {
        $object = new \rcube_image(INSTALL_PATH . 'skins/elastic/thumbnail.png');

        if (!function_exists('getimagesize')) {
            $this->markTestSkipped();
        }

        $file = \rcube_utils::temp_filename('tests');

        $this->assertSame('png', $object->resize(32, $file));

        $object = new \rcube_image($file);
        $props = $object->props();

        @unlink($file);

        $this->assertSame('png', $props['type']);
        $this->assertSame(32, $props['width']);
        $this->assertSame(32, $props['height']);
    }

    /**
     * Test convert() method
     */
    public function test_convert()
    {
        $object = new \rcube_image(INSTALL_PATH . 'skins/elastic/thumbnail.png');

        if (!function_exists('getimagesize')) {
            $this->markTestSkipped();
        }

        $file = \rcube_utils::temp_filename('tests');

        $this->assertTrue($object->convert(\rcube_image::TYPE_JPG, $file));

        $object = new \rcube_image($file);
        $props = $object->props();

        @unlink($file);

        $this->assertSame('jpeg', $props['type']);
        $this->assertSame(64, $props['width']);
        $this->assertSame(64, $props['height']);
    }

    /**
     * Test is_convertable() method
     */
    public function test_convertable()
    {
        \rcube::get_instance()->config->set('im_convert_path', '');

        $file = \rcube_utils::temp_filename('tests');
        $object = new \rcube_image($file);

        if (class_exists('Imagick', false)) {
            $this->assertTrue($object->is_convertable('image/gif'));
            $this->assertFalse($object->is_convertable('xxx'));
        } elseif (!function_exists('getimagesize')) {
            $this->markTestSkipped();
        }

        if (function_exists('imagecreatefromgif')) {
            $this->assertTrue($object->is_convertable('image/gif'));
        } else {
            $this->assertFalse($object->is_convertable('image/gif'));
        }

        if (function_exists('imagecreatefromjpeg')) {
            $this->assertTrue($object->is_convertable('image/jpg'));
            $this->assertTrue($object->is_convertable('image/jpeg'));
        } else {
            $this->assertFalse($object->is_convertable('image/jpg'));
            $this->assertFalse($object->is_convertable('image/jpeg'));
        }

        if (function_exists('imagecreatefrompng')) {
            $this->assertTrue($object->is_convertable('image/png'));
        } else {
            $this->assertFalse($object->is_convertable('image/png'));
        }

        if (function_exists('imagecreatefromwebp')) {
            $this->assertTrue($object->is_convertable('image/webp'));
        } else {
            $this->assertFalse($object->is_convertable('image/webp'));
        }

        $this->assertFalse($object->is_convertable('xxx'));
    }
}
