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
     * Test resize() with Imagick on a JPEG with EXIF orientation 6 (rotated 90 degrees clockwise)
     */
    public function test_resize_imagick_exif_orientation()
    {
        // 64x32 as stored, 32x64 as displayed: left half red, right half blue
        $thumb = $this->imagick_resize('orientation6.jpg', 32, 'jpg');

        $this->assert_image_size($thumb, 16, 32);
        $this->assert_pixel_color($thumb, 2, 2, [1, 0, 0]);
        $this->assert_pixel_color($thumb, 13, 29, [0, 0, 1]);
    }

    /**
     * Test resize() with Imagick on a CMYK JPEG
     */
    public function test_resize_imagick_cmyk()
    {
        // 64x64 white, with a #0080ff square in the middle
        $thumb = $this->imagick_resize('cmyk.jpg', 32, 'jpg');

        $this->assert_image_size($thumb, 32, 32);
        $this->assert_pixel_color($thumb, 1, 1, [1, 1, 1]);
        $this->assert_pixel_color($thumb, 16, 16, [0, 0.5, 1]);
    }

    /**
     * Test resize() with Imagick on a PNG with transparency
     */
    public function test_resize_imagick_transparency()
    {
        // 64x32, left half fully transparent, right half opaque blue
        $thumb = $this->imagick_resize('transparent.png', 32, 'png');

        $this->assert_image_size($thumb, 32, 16);
        $this->assert_pixel_color($thumb, 1, 1, [1, 1, 1]);
        $this->assert_pixel_color($thumb, 30, 14, [0, 0, 1]);
    }

    /**
     * Test resize() with Imagick on a CMYK TIFF with transparency
     */
    public function test_resize_imagick_cmyk_transparency()
    {
        // 64x32 CMYK, left half fully transparent (red underneath), right half opaque blue
        $thumb = $this->imagick_resize('cmyk-transparent.tif', 32, 'jpg');

        $this->assert_image_size($thumb, 32, 16);
        $this->assert_pixel_color($thumb, 1, 1, [1, 1, 1]);
        $this->assert_pixel_color($thumb, 30, 14, [0, 0, 1]);
    }

    /**
     * Test resize() with Imagick on an image smaller than the requested size
     */
    public function test_resize_imagick_smaller_image()
    {
        // 64x32 TIFF, converted to JPEG for the browser, but not enlarged
        $thumb = $this->imagick_resize('cmyk-transparent.tif', 128, 'jpg');

        $this->assert_image_size($thumb, 64, 32);
    }

    /**
     * Test resize() with Imagick on an animated GIF
     */
    public function test_resize_imagick_animated_gif()
    {
        // 64x32, first frame red, second frame blue
        $thumb = $this->imagick_resize('animated.gif', 32, 'gif');

        $this->assert_image_size($thumb, 32, 16);
        $this->assert_pixel_color($thumb, 16, 8, [1, 0, 0]);
    }

    /**
     * Test resize() with Imagick on a HEIF image with an irot (rotation) property
     */
    public function test_resize_imagick_heif_rotation()
    {
        if (!class_exists('Imagick', false)) {
            $this->markTestSkipped('Imagick extension is not available');
        }

        // ImageMagick can list the HEIC format without a working HEVC decoder, so try to read the file
        try {
            (new \Imagick())->readImage(TESTS_DIR . 'src/images/irot.heic');
        } catch (\ImagickException $e) {
            $this->markTestSkipped('Imagick cannot read HEIF images');
        }

        // 128x64 as stored, 64x128 as displayed: left half red, right half blue
        $thumb = $this->imagick_resize('irot.heic', 32, 'jpg');

        $this->assert_image_size($thumb, 16, 32);
        $this->assert_pixel_color($thumb, 2, 2, [1, 0, 0]);
        $this->assert_pixel_color($thumb, 13, 29, [0, 0, 1]);
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
     * Test convert() with Imagick on a CMYK JPEG
     */
    public function test_convert_imagick_cmyk()
    {
        if (!class_exists('Imagick', false)) {
            $this->markTestSkipped('Imagick extension is not available');
        }

        \rcube::get_instance()->config->set('im_convert_path', '');

        $file = \rcube_utils::temp_filename('tests');
        $object = new \rcube_image(TESTS_DIR . 'src/images/cmyk.jpg');

        try {
            $this->assertTrue($object->convert(\rcube_image::TYPE_JPG, $file));

            $image = new \Imagick($file);
        } finally {
            @unlink($file);
        }

        $this->assert_image_size($image, 64, 64);
        $this->assert_pixel_color($image, 1, 1, [1, 1, 1]);
        $this->assert_pixel_color($image, 32, 32, [0, 0.5, 1]);
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

    /**
     * Resize a test image with PHP's Imagick class and return the result
     */
    private function imagick_resize(string $name, int $size, string $type): \Imagick
    {
        if (!class_exists('Imagick', false)) {
            $this->markTestSkipped('Imagick extension is not available');
        }

        \rcube::get_instance()->config->set('im_convert_path', '');

        $file = \rcube_utils::temp_filename('tests');
        $object = new \rcube_image(TESTS_DIR . 'src/images/' . $name);
        $result = $object->resize($size, $file, true);

        try {
            $this->assertSame($type, $result);

            $thumb = new \Imagick($file);
        } finally {
            @unlink($file);
        }

        return $thumb;
    }

    /**
     * Assert the size of an image
     */
    private function assert_image_size(\Imagick $image, int $width, int $height)
    {
        $this->assertSame([$width, $height], [$image->getImageWidth(), $image->getImageHeight()], 'Image size');
    }

    /**
     * Assert the color of an opaque image pixel, with a tolerance for lossy compression
     *
     * @param array $rgb Expected red, green and blue values (0 to 1)
     */
    private function assert_pixel_color(\Imagick $image, int $x, int $y, array $rgb)
    {
        $color = $image->getImagePixelColor($x, $y)->getColor(1);
        $actual = [$color['r'], $color['g'], $color['b'], $color['a']];

        $this->assertEqualsWithDelta(array_merge($rgb, [1]), $actual, 0.15, "Pixel {$x},{$y} (rgba)");
    }
}
