<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/quotaimg.inc                                       |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Create a GIF image showing the mailbox quot as bar                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Brett Patterson <brett2@umbc.edu>                             |
 +-----------------------------------------------------------------------+

 $Id:  $

*/

$used  = ((isset($_GET['u']) && !empty($_GET['u'])) || $_GET['u']=='0')?(int)$_GET['u']:'??';
$quota = ((isset($_GET['q']) && !empty($_GET['q'])) || $_GET['q']=='0')?(int)$_GET['q']:'??';


function genQuota($used, $total)
{
	/**
	 *	Quota Display
	 *
	 *	Modify the following few elements to change the display of the image.
	 *	Modifiable attributes are:
	 *	bool	border	::	Defines whether you want to show a border around it or not.
	 *	bool	unknown	::	Leave default; Defines whether quota is "unknown"
	 *
	 *	int		height	::	Defines height of the image
	 *	int		width	::	Defines width of the image
	 *	int		font	::	Changes the font size & font used in the GD library.
	 *						Available values are from 1 to 5.
	 *	int		padding	::	Changes the offset (in pixels) from the top of the image to
	 *						where the top of the text will be aligned.  User greater than
	 *						0 to ensure text is off the border.
	 *	array	limit	::	Holds the integer values of in an associative array as to what
	 *						defines the upper and lower levels for quota display.
	 *						High - Quota is nearing capacity.
	 *						Mid  - Quota is around the middle
	 *						Low  - Currently not used.
	 *	array	color	::	An associative array of strings of comma separated values (R,G,B)
	 *						for use in color creation.  Define the RGB values you'd like to 
	 *						use.  A list of colors (and their RGB values) can be found here:
	 *						http://www.december.com/html/spec/colorcodes.html
	 **/

	$unknown = false;
	$border = true;

	$height = 15;
	$width = 102;
	$font = 2;
	$padding = 1;

	$limit['high'] = 70;
	$limit['mid'] = 45;
	$limit['low'] = 0;

	// Fill Colors
	$color['fill']['high'] = '227, 23, 13';	// Near quota fill color
	$color['fill']['mid'] = '126, 192, 238';// Mid-area of quota fill color
	$color['fill']['low'] = '50, 205, 50';	// Far from quota fill color

	// Background colors
	$color['bg']['OL'] = '238, 99, 99';		// Over limit bbackground
	$color['bg']['Unknown'] = '238, 99, 99';// Unknown background
	$color['bg']['quota'] = '255, 255, 255';// Normal quota background

	// Misc. Colors
	$color['border'] = '0, 0, 0';
	$color['text'] = '0, 0, 0';


	/****************************
	 *****	DO NOT EDIT BELOW HERE	*****
	 ****************************/

	if(ereg("^[^0-9?]*$", $used) || ereg("^[^0-9?]*$", $total))
		{ 
		return false; 
		}
	if(strpos($used, '?')!==false || strpos($total, '?')!==false && $used != 0)
		{ 
		$unknown = true; 
		}

	if($unknown)
		{
		$im = imagecreate($width, $height);
		list($r, $g, $b) = explode(',', $color['bg']['Unknown']);
		$background = imagecolorallocate($im, $r, $g, $b);
		list($r, $g, $b) = explode(',', $color['text']);
		$text = imagecolorallocate($im, $r, $g, $b);

		if($border)
			{
			list($r, $g, $b) = explode(',', $color['border']);
			$border = imagecolorallocate($im, $r, $g, $b);
			imageline($im, 0, 0, $width, 0, $border);
			imageline($im, 0, $height-1, 0, 0, $border);
			imageline($im, $width-1, 0, $width-1, $height, $border);
			imageline($im, $width, $height-1, 0, $height-1, $border);
			}

		$string = 'Unknown';

		$mid = floor((100-(strlen($string)*imagefontwidth($font)))/2)+1;

		imagestring($im, $font, $mid, $padding, $string, $text);
		header('Content-type: image/gif');
		imagegif($im);
		imagedestroy($im);
		exit;
		}

	if($used > $total)
		{
		$im = imagecreate($width, $height);
		list($r, $g, $b) = explode(',', $color['bg']['OL']);
		$background = imagecolorallocate($im, $r, $g, $b);
		list($r, $g, $b) = explode(',', $color['text']);
		$text = imagecolorallocate($im, $r, $g, $b);
		list($r, $g, $b) = explode(',', $color['border']);
		$border = imagecolorallocate($im, $r, $g, $b);

		imageline($im, 0, 0, $width, 0, $border);
		imageline($im, 0, $height-1, 0, 0, $border);
		imageline($im, $width-1, 0, $width-1, $height, $border);
		imageline($im, $width, $height-1, 0, $height-1, $border);

		$string = 'Over Limit';

		$mid = floor((100-(strlen($string)*imagefontwidth($font)))/2)+1;

		imagestring($im, $font, $mid, $padding, $string, $text);

		header('Content-type: image/gif');
		imagegif($im);
		exit;
		}

	$quota = ($used==0)?0:(round($used/$total, 2)*100);

	$im = imagecreate($width, $height);
	list($r, $g, $b) = explode(',', $color['bg']['quota']);
	$background = imagecolorallocate($im, $r, $b, $g);
	list($r, $g, $b) = explode(',', $color['border']);
	$border = imagecolorallocate($im, $r, $g, $b);
	list($r, $g, $b) = explode(',', $color['text']);
	$text = imagecolorallocate($im, $r, $g, $b);
	if($quota >= $limit['high'])
		{
		list($r, $g, $b) = explode(',', $color['fill']['high']);
		$fill = imagecolorallocate($im, $r, $g, $b);
		}
	elseif($quota >= $limit['mid'])
		{
		list($r, $g, $b) = explode(',', $color['fill']['mid']);
		$fill = imagecolorallocate($im, $r, $g, $b);
		}
	else // if($quota >= $limit['low'])
		{
		list($r, $g, $b) = explode(',', $color['fill']['low']);
		$fill = imagecolorallocate($im, $r, $g, $b);
		}


	imagefilledrectangle($im, 1, 0, $quota, $height-2, $fill);


	imageline($im, 0, 0, $width-2, 0, $border);
	imageline($im, $width-2, 0, $width-2, $height, $border);
	imageline($im, $width-2, $height-1, 0, $height-1, $border);
	imageline($im, 0, $height, 0, 0, $border);


	$string = $quota.'%';
	$mid = floor((100-(strlen($string)*imagefontwidth($font)))/2)+1;
	imagestring($im, $font, $mid, $padding, $string, $text); // Print percent in black

	header('Content-Type: image/gif');
	imagegif($im);
	imagedestroy($im);
}


genQuota($used, $quota);
exit;
?>