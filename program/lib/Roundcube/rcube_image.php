<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2012, The Roundcube Dev Team                       |
 | Copyright (C) 2011-2012, Kolab Systems AG                             |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Image resizer and converter                                         |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Image resizer and converter
 *
 * @package    Framework
 * @subpackage Utils
 */
class rcube_image
{
    private $image_file;

    const TYPE_GIF = 1;
    const TYPE_JPG = 2;
    const TYPE_PNG = 3;
    const TYPE_TIF = 4;

    public static $extensions = array(
        self::TYPE_GIF => 'gif',
        self::TYPE_JPG => 'jpg',
        self::TYPE_PNG => 'png',
        self::TYPE_TIF => 'tif',
    );


    function __construct($filename)
    {
        $this->image_file = $filename;
    }

    /**
     * Get image properties.
     *
     * @return mixed Hash array with image props like type, width, height
     */
    public function props()
    {
        // use GD extension
        if (function_exists('getimagesize') && ($imsize = @getimagesize($this->image_file))) {
            $width   = $imsize[0];
            $height  = $imsize[1];
            $gd_type = $imsize['2'];
            $type    = image_type_to_extension($imsize['2'], false);
        }

        // use ImageMagick
        if (!$type && ($data = $this->identify())) {
            list($type, $width, $height) = $data;
        }

        if ($type) {
            return array(
                'type'    => $type,
                'gd_type' => $gd_type,
                'width'   => $width,
                'height'  => $height,
            );
        }
    }

    /**
     * Resize image to a given size. Use only to shrink an image.
     * If an image is smaller than specified size it will be not resized.
     *
     * @param int    $size      Max width/height size
     * @param string $filename  Output filename
     * @param boolean $browser_compat  Convert to image type displayable by any browser
     *
     * @return mixed Output type on success, False on failure
     */
    public function resize($size, $filename = null, $browser_compat = false)
    {
        $result  = false;
        $rcube   = rcube::get_instance();
        $convert = $rcube->config->get('im_convert_path', false);
        $props   = $this->props();

        if (!$filename) {
            $filename = $this->image_file;
        }

        // use Imagemagick
        if ($convert) {
            $p['out']  = $filename;
            $p['in']   = $this->image_file;
            $p['size'] = $size.'x'.$size;
            $type      = $props['type'];

            if (!$type && ($data = $this->identify())) {
                $type = $data[0];
            }

            $type = strtr($type, array("jpeg" => "jpg", "tiff" => "tif", "ps" => "eps", "ept" => "eps"));
            $p['intype'] = $type;

            // convert to an image format every browser can display
            if ($browser_compat && !in_array($type, array('jpg','gif','png'))) {
                $type = 'jpg';
            }

            $p += array('type' => $type, 'types' => "bmp,eps,gif,jp2,jpg,png,svg,tif", 'quality' => 75);
            $p['-opts'] = array('-resize' => $p['size'].'>');

            if (in_array($type, explode(',', $p['types']))) { // Valid type?
                $result = rcube::exec($convert . ' 2>&1 -flatten -auto-orient -colorspace RGB -quality {quality} {-opts} {intype}:{in} {type}:{out}', $p);
            }

            if ($result === '') {
                @chmod($filename, 0600);
                return $type;
            }
        }

        // use GD extension
        if ($props['gd_type']) {
            if ($props['gd_type'] == IMAGETYPE_JPEG && function_exists('imagecreatefromjpeg')) {
                $image = imagecreatefromjpeg($this->image_file);
                $type  = 'jpg';
            }
            else if($props['gd_type'] == IMAGETYPE_GIF && function_exists('imagecreatefromgif')) {
                $image = imagecreatefromgif($this->image_file);
                $type  = 'gid';
            }
            else if($props['gd_type'] == IMAGETYPE_PNG && function_exists('imagecreatefrompng')) {
                $image = imagecreatefrompng($this->image_file);
                $type  = 'png';
            }
            else {
                // @TODO: print error to the log?
                return false;
            }

            $scale = $size / max($props['width'], $props['height']);

            // Imagemagick resize is implemented in shrinking mode (see -resize argument above)
            // we do the same here, if an image is smaller than specified size
            // we do nothing but copy original file to destination file
            if ($scale > 1) {
                return $this->image_file == $filename || copy($this->image_file, $filename) ? $type : false;
            }

            $width  = $props['width']  * $scale;
            $height = $props['height'] * $scale;

            $new_image = imagecreatetruecolor($width, $height);

            // Fix transparency of gif/png image
            if ($props['gd_type'] != IMAGETYPE_JPEG) {
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
                imagefilledrectangle($new_image, 0, 0, $width, $height, $transparent);
            }

            imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, $props['width'], $props['height']);
            $image = $new_image;

            if ($props['gd_type'] == IMAGETYPE_JPEG) {
                $result = imagejpeg($image, $filename, 75);
            }
            elseif($props['gd_type'] == IMAGETYPE_GIF) {
                $result = imagegif($image, $filename);
            }
            elseif($props['gd_type'] == IMAGETYPE_PNG) {
                $result = imagepng($image, $filename, 6, PNG_ALL_FILTERS);
            }

            if ($result) {
                @chmod($filename, 0600);
                return $type;
            }
        }

        // @TODO: print error to the log?
        return false;
    }

    /**
     * Convert image to a given type
     *
     * @param int    $type      Destination file type (see class constants)
     * @param string $filename  Output filename (if empty, original file will be used
     *                          and filename extension will be modified)
     *
     * @return bool True on success, False on failure
     */
    public function convert($type, $filename = null)
    {
        $rcube   = rcube::get_instance();
        $convert = $rcube->config->get('im_convert_path', false);

        if (!$filename) {
            $filename = $this->image_file;

            // modify extension
            if ($extension = self::$extensions[$type]) {
                $filename = preg_replace('/\.[^.]+$/', '', $filename) . '.' . $extension;
            }
        }

        // use ImageMagick
        if ($convert) {
            $p['in']   = $this->image_file;
            $p['out']  = $filename;
            $p['type'] = self::$extensions[$type];

            $result = rcube::exec($convert . ' 2>&1 -colorspace RGB -quality 75 {in} {type}:{out}', $p);

            if ($result === '') {
                @chmod($filename, 0600);
                return true;
            }
        }

        // use GD extension (TIFF isn't supported)
        $props = $this->props();

        if ($props['gd_type']) {
            if ($props['gd_type'] == IMAGETYPE_JPEG && function_exists('imagecreatefromjpeg')) {
                $image = imagecreatefromjpeg($this->image_file);
            }
            else if ($props['gd_type'] == IMAGETYPE_GIF && function_exists('imagecreatefromgif')) {
                $image = imagecreatefromgif($this->image_file);
            }
            else if ($props['gd_type'] == IMAGETYPE_PNG && function_exists('imagecreatefrompng')) {
                $image = imagecreatefrompng($this->image_file);
            }
            else {
                // @TODO: print error to the log?
                return false;
            }

            if ($type == self::TYPE_JPG) {
                $result = imagejpeg($image, $filename, 75);
            }
            else if ($type == self::TYPE_GIF) {
                $result = imagegif($image, $filename);
            }
            else if ($type == self::TYPE_PNG) {
                $result = imagepng($image, $filename, 6, PNG_ALL_FILTERS);
            }

            if ($result) {
                @chmod($filename, 0600);
                return true;
            }
        }

        // @TODO: print error to the log?
        return false;
    }

    /**
     * Identify command handler.
     */
    private function identify()
    {
        $rcube = rcube::get_instance();

        if ($cmd = $rcube->config->get('im_identify_path')) {
            $args = array('in' => $this->image_file, 'format' => "%m %[fx:w] %[fx:h]");
            $id   = rcube::exec($cmd. ' 2>/dev/null -format {format} {in}', $args);

            if ($id) {
                return explode(' ', strtolower($id));
            }
        }
    }

}
