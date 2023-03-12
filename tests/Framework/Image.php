<?php

/**
 * Test class to test rcube_image class
 *
 * @package Tests
 */
class Framework_Image extends PHPUnit\Framework\TestCase
{
    /**
     * Test props() method
     */
    function test_props()
    {
        $object = new rcube_image(INSTALL_PATH . 'skins/elastic/thumbnail.png');

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
    function test_resize()
    {
        $object = new rcube_image(INSTALL_PATH . 'skins/elastic/thumbnail.png');

        if (!function_exists('getimagesize')) {
            $this->markTestSkipped();
        }

        $file = rcube_utils::temp_filename('tests');

        $this->assertSame('png', $object->resize(32, $file));

        $object = new rcube_image($file);
        $props  = $object->props();

        @unlink($file);

        $this->assertSame('png', $props['type']);
        $this->assertSame(32, $props['width']);
        $this->assertSame(32, $props['height']);
    }

    /**
     * Test convert() method
     */
    function test_convert()
    {
        $object = new rcube_image(INSTALL_PATH . 'skins/elastic/thumbnail.png');

        if (!function_exists('getimagesize')) {
            $this->markTestSkipped();
        }

        $file = rcube_utils::temp_filename('tests');

        $this->assertTrue($object->convert(rcube_image::TYPE_JPG, $file));

        $object = new rcube_image($file);
        $props  = $object->props();

        @unlink($file);

        $this->assertSame('jpeg', $props['type']);
        $this->assertSame(64, $props['width']);
        $this->assertSame(64, $props['height']);
    }
}
