<?php


/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_mime_struct.php                                 |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide functions for handling mime messages structure              |
 |                                                                       |
 |   Based on Iloha MIME Library. See http://ilohamail.org/ for details  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 | Author: Ryo Chijiiwa <Ryo@IlohaMail.org>                              |
 +-----------------------------------------------------------------------+

 $Id$

*/

/**
 * Helper class to process IMAP's BODYSTRUCTURE string
 *
 * @package    Mail
 * @author     Aleksander Machniak <alec@alec.pl>
 */
class rcube_mime_struct
{
    private $structure;


    function __construct($str=null)
    {
        if ($str)
            $this->structure = $this->parseStructure($str);
    }

    /*
     * Parses IMAP's BODYSTRUCTURE string into array
    */
    function parseStructure($str)
    {
        $line = substr($str, 1, strlen($str) - 2);
        $line = str_replace(')(', ') (', $line);

	    $struct = self::parseBSString($line);
    	if (!is_array($struct[0]) && (strcasecmp($struct[0], 'message') == 0)
		    && (strcasecmp($struct[1], 'rfc822') == 0)) {
		    $struct = array($struct);
	    }

        return $struct;
    }

    /*
     * Parses IMAP's BODYSTRUCTURE string into array and loads it into class internal variable
    */
    function loadStructure($str)
    {
        if (empty($str))
            return true;

        $this->structure = $this->parseStructure($str);
        return (!empty($this->structure));
    }

    function getPartType($part)
    {
	    $part_a = $this->getPartArray($this->structure, $part);
	    if (!empty($part_a)) {
		    if (is_array($part_a[0]))
                return 'multipart';
		    else if ($part_a[0])
                return $part_a[0];
	    }
        
        return 'other';
    }

    function getPartEncoding($part)
    {
	    $part_a = $this->getPartArray($this->structure, $part);
	    if ($part_a) {
		    if (!is_array($part_a[0]))
                return $part_a[5];
	    }
        
        return '';
    }

    function getPartCharset($part)
    {
	    $part_a = $this->getPartArray($this->structure, $part);
	    if ($part_a) {
		    if (is_array($part_a[0]))
                return '';
		    else {
			    if (is_array($part_a[2])) {
				    $name = '';
				    while (list($key, $val) = each($part_a[2]))
                        if (strcasecmp($val, 'charset') == 0)
                            return $part_a[2][$key+1];
			    }
		    }
	    }
        
        return '';
    }

    function getPartArray($a, $part)
    {
	    if (!is_array($a)) {
            return false;
        }
	    if (strpos($part, '.') > 0) {
		    $original_part = $part;
		    $pos = strpos($part, '.');
		    $rest = substr($original_part, $pos+1);
		    $part = substr($original_part, 0, $pos);
		    if ((strcasecmp($a[0], 'message') == 0) && (strcasecmp($a[1], 'rfc822') == 0)) {
			    $a = $a[8];
		    }
		    return self::getPartArray($a[$part-1], $rest);
	    }
        else if ($part>0) {
		    if (!is_array($a[0]) && (strcasecmp($a[0], 'message') == 0)
                && (strcasecmp($a[1], 'rfc822') == 0)) {
			    $a = $a[8];
		    }
		    if (is_array($a[$part-1]))
                return $a[$part-1];
		    else
                return $a;
	    }
        else if (($part==0) || (empty($part))) {
		    return $a;
	    }
    }

    private function closingParenPos($str, $start)
    {
        $level = 0;
        $len = strlen($str);
        $in_quote = 0;

        for ($i=$start; $i<$len; $i++) {
    	    if ($str[$i] == '"' && $str[$i-1] != "\\") {
		        $in_quote = ($in_quote + 1) % 2;
    	    }
            if (!$in_quote) {
        	    if ($str[$i] == '(')
                    $level++;
        	    else if (($level > 0) && ($str[$i] == ')'))
                    $level--;
        	    else if (($level == 0) && ($str[$i] == ')'))
                    return $i;
    	    }
        }
    }

    /*
     * Parses IMAP's BODYSTRUCTURE string into array
    */
    private function parseBSString($str)
    {	
        $id = 0;
        $a = array();
        $len = strlen($str);
        $in_quote = 0;

        for ($i=0; $i<$len; $i++) {
            if ($str[$i] == '"') {
	            $in_quote = ($in_quote + 1) % 2;
            } else if (!$in_quote) {
                // space means new element
                if ($str[$i] == ' ') {
                    $id++;
                    // skip additional spaces
                    while ($str[$i+1] == ' ')
                        $i++;
                // new part
                } else if ($str[$i] == '(') {
                    $i++;
                    $endPos = self::closingParenPos($str, $i);
                    $partLen = $endPos - $i;
                    if ($partLen < 0)
                        break;
                    $part = substr($str, $i, $partLen);
                    $a[$id] = self::parseBSString($part); // send part string
                    $i = $endPos;
                } else
		            $a[$id] .= $str[$i]; //add to current element in array
            } else if ($in_quote) {
                if ($str[$i] == "\\") {
		            $i++; // escape backslashes
		            if ($str[$i] == '"' || $str[$i] == "\\")
		                $a[$id] .= $str[$i];
                }
                else
		            $a[$id] .= $str[$i]; //add to current element in array
            }
        }
        
        reset($a);
        return $a;
    }


}
