<?php
/////////////////////////////
//	utf8.inc
//	(C)2002 Ryo Chijiiwa <Ryo@IlohaMail.org>
//		
//		Description:
//				UTF-8 handling functions
//
//	This file is part of IlohaMail. IlohaMail is free software released 
//	under the GPL license.  See enclosed file COPYING for details, or 
//	see http://www.fsf.org/copyleft/gpl.html
////////////////////////////

/**
* takes a string of utf-8 encoded characters and converts it to a string of unicode entities
* each unicode entitiy has the form &#nnnnn; n={0..9} and can be displayed by utf-8 supporting
* browsers
* @param $source string encoded using utf-8 [STRING]
* @return string of unicode entities [STRING]
* @access public
*/
/**
* Author: ronen at greyzone dot com
* Taken from php.net comment:
*	http://www.php.net/manual/en/function.utf8-decode.php
**/
function utf8ToUnicodeEntities ($source) {
	// array used to figure what number to decrement from character order value
	// according to number of characters used to map unicode to ascii by utf-8
	$decrement[4] = 240;
	$decrement[3] = 224;
	$decrement[2] = 192;
	$decrement[1] = 0;

	// the number of bits to shift each charNum by
	$shift[1][0] = 0;
	$shift[2][0] = 6;
	$shift[2][1] = 0;
	$shift[3][0] = 12;
	$shift[3][1] = 6;
	$shift[3][2] = 0;
	$shift[4][0] = 18;
	$shift[4][1] = 12;
	$shift[4][2] = 6;
	$shift[4][3] = 0;

	$pos = 0;
	$len = strlen ($source);
	$encodedString = '';
	while ($pos < $len) {
		$asciiPos = ord (substr ($source, $pos, 1));
		if (($asciiPos >= 240) && ($asciiPos <= 255)) {
			// 4 chars representing one unicode character
			$thisLetter = substr ($source, $pos, 4);
			$pos += 4;
		}
		else if (($asciiPos >= 224) && ($asciiPos <= 239)) {
			// 3 chars representing one unicode character
			$thisLetter = substr ($source, $pos, 3);
			$pos += 3;
		}
		else if (($asciiPos >= 192) && ($asciiPos <= 223)) {
			// 2 chars representing one unicode character
			$thisLetter = substr ($source, $pos, 2);
			$pos += 2;
		}
		else {
			// 1 char (lower ascii)
			$thisLetter = substr ($source, $pos, 1);
			$pos += 1;
		}

		// process the string representing the letter to a unicode entity
		$thisLen = strlen ($thisLetter);
		$thisPos = 0;
		$decimalCode = 0;
		while ($thisPos < $thisLen) {
			$thisCharOrd = ord (substr ($thisLetter, $thisPos, 1));
			if ($thisPos == 0) {
				$charNum = intval ($thisCharOrd - $decrement[$thisLen]);
				$decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
			}
			else {
				$charNum = intval ($thisCharOrd - 128);
				$decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
			}

			$thisPos++;
		}

		if ($thisLen == 1)
			$encodedLetter = "&#". str_pad($decimalCode, 3, "0", STR_PAD_LEFT) . ';';
		else
			$encodedLetter = "&#". str_pad($decimalCode, 5, "0", STR_PAD_LEFT) . ';';

		$encodedString .= $encodedLetter;
	}

	return $encodedString;
}

?>