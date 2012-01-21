<?php

/**
 * Render SVG gradients for IE 9
 *
 * Copyright (c) 2012, The Roundcube Dev Team
 *
 * The contents are subject to the Creative Commons Attribution-ShareAlike
 * License. It is allowed to copy, distribute, transmit and to adapt the work
 * by keeping credits to the original autors in the README file.
 * See http://creativecommons.org/licenses/by-sa/3.0/ for details.
 *
 * $Id$
 */

header('Content-Type: image/svg+xml');
header("Expires: ".gmdate("D, d M Y H:i:s", time()+864000)." GMT");
header("Cache-Control: max-age=864000");
header("Pragma: ");

$svg_stops = '';
$color_stops = explode(';', preg_replace('/[^a-f0-9,;%]/i', '', $_GET['c']));
$last = count($color_stops) - 1;
foreach ($color_stops as $i => $stop) {
	list($color, $offset) = explode(',', $stop);
	if ($offset)
		$offset = intval($offset);
	else
		$offset = $i == $last ? 100 : 0;

	$svg_stops .= '<stop offset="' . $offset . '%" stop-color="#' . $color . '" stop-opacity="1"/>';
}

?>
<svg xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" version="1.0" width="100%" height="100%">
<defs>
  <linearGradient id="LG1" x1="0%" y1="0%" x2="0%" y2="100%" spreadMethod="pad">
    <?php echo $svg_stops; ?>
  </linearGradient>
</defs>
<rect width="100%" height="100%" style="fill:url(#LG1);"/>
</svg>
