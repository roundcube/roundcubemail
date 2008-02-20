<?php
/*
 +-----------------------------------------------------------------------+
 | program/bin/quotaimg.php                                              |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Create a GIF image showing the mailbox quot as bar                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Brett Patterson <brett2@umbc.edu>                             |
 +-----------------------------------------------------------------------+

 $Id: $

*/

$used   = ((isset($_GET['u']) && !empty($_GET['u'])) || $_GET['u']=='0')?(int)$_GET['u']:'??';
$quota  = ((isset($_GET['q']) && !empty($_GET['q'])) || $_GET['q']=='0')?(int)$_GET['q']:'??';
$width  = empty($_GET['w']) ? 100 : (int)$_GET['w'];
$height = empty($_GET['h']) ? 14 : (int)$_GET['h'];

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

	$limit['high'] = 70;
	$limit['mid']  = 45;
	$limit['low']  = 0;

	// Fill Colors
	$color['fill']['high'] = '215, 13, 13';	  // Near quota fill color
	$color['fill']['mid']  = '126, 192, 238'; // Mid-area of quota fill color
	$color['fill']['low']  = '147, 225, 100'; // Far from quota fill color

	// Background colors
	$color['bg']['OL']      = '215, 13, 13';   // Over limit bbackground
	$color['bg']['Unknown'] = '238, 99, 99';   // Unknown background
	$color['bg']['quota']   = '255, 255, 255'; // Normal quota background

	// Misc. Colors
	$color['border'] = '0, 0, 0';
	$color['text']   = '102, 102, 102';


	/************************************
	 *****	DO NOT EDIT BELOW HERE	*****
	 ***********************************/

    // @todo: Set to "??" instead?
	if (ereg("^[^0-9?]*$", $used) || ereg("^[^0-9?]*$", $total)) {
		return false; 
    }

	if (strpos($used, '?') !== false || strpos($total, '?') !== false
        && $used != 0) {
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
		
	list($r, $g, $b) = explode(',', $color['text']);
	$text = imagecolorallocate($im, $r, $g, $b);

	if ($unknown) {
		list($r, $g, $b) = explode(',', $color['bg']['Unknown']);
		$background = imagecolorallocate($im, $r, $g, $b);
		imagefilledrectangle($im, 0, 0, $width, $height, $background);

		$string = 'Unknown';
		$mid    = floor(($width-(strlen($string)*imagefontwidth($font)))/2)+1;
		imagestring($im, $font, $mid, $padding, $string, $text);
	} else if ($used > $total) {
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
			list($r, $g, $b) = explode(',', $color['fill']['high']);
			$fill = imagecolorallocate($im, $r, $g, $b);
		} elseif($quota >= $limit['mid']) {
			list($r, $g, $b) = explode(',', $color['fill']['mid']);
			$fill = imagecolorallocate($im, $r, $g, $b);
		} else {
		    // if($quota >= $limit['low'])
			list($r, $g, $b) = explode(',', $color['fill']['low']);
			$fill = imagecolorallocate($im, $r, $g, $b);
		}

		$quota_width = $quota / 100 * $width;
		imagefilledrectangle($im, $border, 0, $quota, $height-2*$border, $fill);

		$string = $quota . '%';
		$mid    = floor(($width-(strlen($string)*imagefontwidth($font)))/2)+1;
        // Print percent in black
		imagestring($im, $font, $mid, $padding, $string, $text); 
	}

	header('Content-Type: image/gif');
    
    // @todo is harcoding GMT necessary?
	header('Expires: ' . gmdate('D, d M Y H:i:s', mktime()+86400) . ' GMT');
	header('Cache-Control: ');
	header('Pragma: ');
	
	imagegif($im);
	imagedestroy($im);
}

genQuota($used, $quota, $width, $height);
exit;
?>