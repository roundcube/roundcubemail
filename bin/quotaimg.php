<?php
/*
 +-----------------------------------------------------------------------+
 | bin/quotaimg.php                                                      |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Create a GIF image showing the mailbox quot as bar                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Brett Patterson <brett2@umbc.edu>                             |
 +-----------------------------------------------------------------------+

 $Id$

*/

define('INSTALL_PATH', realpath(dirname(__FILE__).'/..') . '/');
require INSTALL_PATH . 'program/include/iniset.php';

$RCMAIL = rcmail::get_instance();

$used   = isset($_GET['u']) ? intval($_GET['u']) : '??';
$quota  = isset($_GET['q']) ? intval($_GET['q']) : '??';
$width  = empty($_GET['w']) ? 100 : min(300, intval($_GET['w']));
$height = empty($_GET['h']) ? 14  : min(50,  intval($_GET['h']));

/**
 * Quota display
 * 
 *	Modify the following few elements to change the display of the image.
 *	Modifiable attributes are:
 *	bool	border	::	Defines whether you want to show a border around it?
 *	bool	unknown	::	Leave default; Defines whether quota is "unknown"
 *
 *	int		height	::	Defines height of the image
 *	int		width	::	Defines width of the image
 *	int		font	::	Changes the font size & font used in the GD library.
 *						Available values are from 1 to 5.
 *	int		padding	::	Changes the offset (in pixels) from the top of the image
 *                      to where the top of the text will be aligned. User
 *                      greater than 0 to ensure text is off the border.
 *	array	limit	::	Holds the integer values of in an associative array as
 *                      to what defines the upper and lower levels for quota
 *                      display.
 *						High - Quota is nearing capacity.
 *						Mid  - Quota is around the middle
 *						Low  - Currently not used.
 *	array	color	::	An associative array of strings of comma separated
 *                      values (R,G,B) for use in color creation.  Define the
 *                      RGB values you'd like to use. A list of colors (and
 *                      their RGB values) can be found here:
 *						http://www.december.com/html/spec/colorcodes.html
 * 
 * @return void
 * 
 * @param mixed $used   The amount used, or ?? if unknown.
 * @param mixed $total  The total available, or ?? if unknown.
 * @param int   $width  Width of the image.
 * @param int   $height Height of the image.
 * 
 * @see rcube_imap::get_quota()
 * @see iil_C_GetQuota()
 * 
 * @todo Make colors a config option.
 */
function genQuota($used, $total, $width, $height)
{
	$unknown = false;
	$border  = 0;

	$font    = 2;
	$padding = 0;

	$limit['high'] = 80;
	$limit['mid']  = 55;
	$limit['low']  = 0;

	// Fill Colors
	$color['fill']['high'] = '243, 49, 49';	  // Near quota fill color
	$color['fill']['mid']  = '245, 173, 60'; // Mid-area of quota fill color
	$color['fill']['low']  = '145, 225, 100'; // Far from quota fill color

	// Background colors
	$color['bg']['OL']      = '215, 13, 13';   // Over limit bbackground
	$color['bg']['Unknown'] = '238, 99, 99';   // Unknown background
	$color['bg']['quota']   = '255, 255, 255'; // Normal quota background

	// Misc. Colors
	$color['border'] = '0, 0, 0';
	$color['text']['high'] = '255, 255, 255';  // white text for red background
	$color['text']['mid'] = '102, 102, 102';
	$color['text']['low'] = '102, 102, 102';
	$color['text']['normal'] = '102, 102, 102';


	/************************************
	 *****	DO NOT EDIT BELOW HERE	*****
	 ***********************************/

	// @todo: Set to "??" instead?
	if (preg_match('/^[^0-9?]*$/', $used) || preg_match('/^[^0-9?]*$/', $total)) {
		return false; 
	}

	if (strpos($used, '?') !== false || strpos($total, '?') !== false && $used != 0) {
		$unknown = true; 
	}

	$im = imagecreate($width, $height);

	if ($border) {
		list($r, $g, $b) = explode(',', $color['border']);
        
		$borderc = imagecolorallocate($im, $r, $g, $b);
        
		imageline($im, 0, 0, $width, 0, $borderc);
		imageline($im, 0, $height-$border, 0, 0, $borderc);
		imageline($im, $width-1, 0, $width-$border, $height, $borderc);
		imageline($im, $width, $height-$border, 0, $height-$border, $borderc);
	}
		
	if ($unknown) {
		list($r, $g, $b) = explode(',', $color['text']['normal']);
		$text = imagecolorallocate($im, $r, $g, $b);
		list($r, $g, $b) = explode(',', $color['bg']['Unknown']);
		$background = imagecolorallocate($im, $r, $g, $b);

		imagefilledrectangle($im, 0, 0, $width, $height, $background);

		$string = 'Unknown';
		$mid    = floor(($width-(strlen($string)*imagefontwidth($font)))/2)+1;
		imagestring($im, $font, $mid, $padding, $string, $text);
	} else if ($used > $total) {
		list($r, $g, $b) = explode(',', $color['text']['normal']);
		$text = imagecolorallocate($im, $r, $g, $b);
		list($r, $g, $b) = explode(',', $color['bg']['OL']);
		$background = imagecolorallocate($im, $r, $g, $b);
        
		imagefilledrectangle($im, 0, 0, $width, $height, $background);

		$string = 'Over Limit';
		$mid    = floor(($width-(strlen($string)*imagefontwidth($font)))/2)+1;
		imagestring($im, $font, $mid, $padding, $string, $text);
	} else {
		list($r, $g, $b) = explode(',', $color['bg']['quota']);
		$background = imagecolorallocate($im, $r, $b, $g);
        
		imagefilledrectangle($im, 0, 0, $width, $height, $background);
		
		$quota = ($used==0)?0:(round($used/$total, 2)*100);

		if ($quota >= $limit['high']) {
			list($r, $g, $b) = explode(',', $color['text']['high']);
			$text = imagecolorallocate($im, $r, $g, $b);
			list($r, $g, $b) = explode(',', $color['fill']['high']);
			$fill = imagecolorallocate($im, $r, $g, $b);
		} elseif($quota >= $limit['mid']) {
			list($r, $g, $b) = explode(',', $color['text']['mid']);
			$text = imagecolorallocate($im, $r, $g, $b);
			list($r, $g, $b) = explode(',', $color['fill']['mid']);
			$fill = imagecolorallocate($im, $r, $g, $b);
		} else {
			// if($quota >= $limit['low'])
			list($r, $g, $b) = explode(',', $color['text']['low']);
			$text = imagecolorallocate($im, $r, $g, $b);
			list($r, $g, $b) = explode(',', $color['fill']['low']);
			$fill = imagecolorallocate($im, $r, $g, $b);
		}

		$quota_width = $quota / 100 * $width;
		if ($quota_width)
			imagefilledrectangle($im, $border, 0, $quota_width, $height-2*$border, $fill);

		$string = $quota . '%';
		$mid    = floor(($width-(strlen($string)*imagefontwidth($font)))/2)+1;
		// Print percent in black
		imagestring($im, $font, $mid, $padding, $string, $text); 
	}

	header('Content-Type: image/gif');

	// cache for 1 hour
	$maxage = 3600;
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$maxage). ' GMT');
	header('Cache-Control: max-age=' . $maxage);
	
	imagegif($im);
	imagedestroy($im);
}

if (!empty($RCMAIL->user->ID) && $width > 1 && $height > 1) {
	genQuota($used, $quota, $width, $height);
}
else {
	header("HTTP/1.0 403 Forbidden");
	echo "Requires a valid user session and positive values";
}

exit;
?>
