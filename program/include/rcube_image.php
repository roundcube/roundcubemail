<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_image.php                                       |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2012, The Roundcube Dev Team                       |
 | Copyright (C) 2011-2012, Kolab Systems AG                             |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Image resizer                                                       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id$

*/

class rcube_image
{
    private $image_file;

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
     * Resize image to a given size
     *
     * @param int    $size      Max width/height size
     * @param string $filename  Output filename
     *
     * @return Success of convert as true/false
     */
    public function resize($size, $filename = null)
    {
        $result   = false;
        $rcmail   = rcmail::get_instance();
        $convert  = $rcmail->config->get('im_convert_path', false);
        $props    = $this->props();

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
            $p += array('type' => $type, 'types' => "bmp,eps,gif,jp2,jpg,png,svg,tif", 'quality' => 75);
            $p['-opts'] = array('-resize' => $size.'>');

            if (in_array($type, explode(',', $p['types']))) { // Valid type?
                $result = rcmail::exec($convert . ' 2>&1 -flatten -auto-orient -colorspace RGB -quality {quality} {-opts} {in} {type}:{out}', $p) === '';
            }

            if ($result) {
                return true;
            }
        }

        // use GD extension
        $gd_types = array(IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG);
        if ($props['gd_type'] && in_array($props['gd_type'], $gd_types)) {
            if ($props['gd_type'] == IMAGETYPE_JPEG) {
                $image = imagecreatefromjpeg($this->image_file);
            }
            elseif($props['gd_type'] == IMAGETYPE_GIF) {
                $image = imagecreatefromgif($this->image_file);
            }
            elseif($props['gd_type'] == IMAGETYPE_PNG) {
                $image = imagecreatefrompng($this->image_file);
            }

            $scale  = $size / max($props['width'], $props['height']);
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
        $rcmail = rcmail::get_instance();

        if ($cmd = $rcmail->config->get('im_identify_path')) {
            $args = array('in' => $this->image_file, 'format' => "%m %[fx:w] %[fx:h]");
            $id   = rcmail::exec($cmd. ' 2>/dev/null -format {format} {in}', $args);

            if ($id) {
                return explode(' ', strtolower($id));
            }
        }
    }
}
