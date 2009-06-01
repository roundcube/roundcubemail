<?php
/////////////////////////////////////////////////////////
//	
//	Iloha MIME Library (IML)
//
//	(C)Copyright 2002 Ryo Chijiiwa <Ryo@IlohaMail.org>
//
//	This file is part of IlohaMail. IlohaMail is free software released 
//	under the GPL license.  See enclosed file COPYING for details, or 
//	see http://www.fsf.org/copyleft/gpl.html
//
/////////////////////////////////////////////////////////

/********************************************************

	FILE: include/mime.inc
	PURPOSE:
		Provide functions for handling mime messages.
	USAGE:
		Use iil_C_FetchStructureString to get IMAP structure stirng, then pass that through
		iml_GetRawStructureArray() to get root node to a nested data structure.
		Pass root node to the iml_GetPart*() functions to retreive individual bits of info.

********************************************************/
$MIME_INVALID = -1;
$MIME_TEXT = 0;
$MIME_MULTIPART = 1;
$MIME_MESSAGE = 2;
$MIME_APPLICATION = 3;
$MIME_AUDIO = 4;
$MIME_IMAGE = 5;
$MIME_VIDEO = 6;
$MIME_OTHER = 7;

function iml_ClosingParenPos($str, $start) {
    $level=0;
    $len = strlen($str);
    $in_quote = 0;

    for ($i=$start; $i<$len; $i++) {
    	if ($str[$i] == '"' && $str[$i-1] != "\\")
		$in_quote = ($in_quote + 1) % 2;
    	if (!$in_quote) {
        	if ($str[$i]=="(") $level++;
        	else if (($level > 0) && ($str[$i]==")")) $level--;
        	else if (($level == 0) && ($str[$i]==")")) return $i;
    	}
    }
}

function iml_ParseBSString($str){	
    
    $id = 0;
    $a = array();
    $len = strlen($str);
    $in_quote = 0;

    for ($i=0; $i<$len; $i++) {
        if ($str[$i] == '"') {
	    $in_quote = ($in_quote + 1) % 2;
        } else if (!$in_quote) {
            if ($str[$i] == " ") { //space means new element
                $id++;
                while ($str[$i+1] == " ") $i++;  // skip additional spaces
            } else if ($str[$i]=="(") { //new part
                $i++;
                $endPos = iml_ClosingParenPos($str, $i);
                $partLen = $endPos - $i;
                if ($partLen < 0) break;
                $part = substr($str, $i, $partLen);
                $a[$id] = iml_ParseBSString($part); //send part string
                $i = $endPos;
            } else
		$a[$id].=$str[$i]; //add to current element in array
        } else if ($in_quote) {
            if ($str[$i]=="\\") {
		$i++; //escape backslashes
		if ($str[$i] == '"' || $str[$i] == "\\")
		    $a[$id] .= $str[$i];
            } else
		$a[$id] .= $str[$i]; //add to current element in array
        }
    }
        
    reset($a);
    return $a;
}

function iml_GetRawStructureArray($str){
    $line=substr($str, 1, strlen($str) - 2);
    $line = str_replace(")(", ") (", $line);

	$struct = iml_ParseBSString($line);
	if (!is_array($struct[0]) && (strcasecmp($struct[0], "message")==0)
		&& (strcasecmp($struct[1], "rfc822")==0)) {
		$struct = array($struct);
	}
    return $struct;
}

function iml_GetPartArray($a, $part){
	if (!is_array($a)) return false;
	if (strpos($part, ".") > 0){
		$original_part = $part;
		$pos = strpos($part, ".");
		$rest = substr($original_part, $pos+1);
		$part = substr($original_part, 0, $pos);
		if ((strcasecmp($a[0], "message")==0) && (strcasecmp($a[1], "rfc822")==0)){
			$a = $a[8];
		}
		//echo "m - part: $original_part current: $part rest: $rest array: ".implode(" ", $a)."<br>\n";
		return iml_GetPartArray($a[$part-1], $rest);
	}else if ($part>0){
		if ((strcasecmp($a[0], "message")==0) && (strcasecmp($a[1], "rfc822")==0)){
			$a = $a[8];
		}
		//echo "s - part: $part rest: $rest array: ".implode(" ", $a)."<br>\n";
		if (is_array($a[$part-1])) return $a[$part-1];
		else return $a;
	}else if (($part==0) || (empty($part))){
		return $a;
	}
}

function iml_GetNumParts($a, $part){
	if (is_array($a)){
		$parent=iml_GetPartArray($a, $part);
		
		if ((strcasecmp($parent[0], "message")==0) && (strcasecmp($parent[1], "rfc822")==0)){
			$parent = $parent[8];
		}

		$is_array=true;
		$c=0;
		while (( list ($key, $val) = each ($parent) )&&($is_array)){
			$is_array=is_array($parent[$key]);
			if ($is_array) $c++;
		}
		return $c;
	}
	
	return false;
}

function iml_GetPartTypeString($a, $part){
	$part_a=iml_GetPartArray($a, $part);
	if ($part_a){
		if (is_array($part_a[0])){
			$type_str = "MULTIPART/";
			reset($part_a);
			while(list($n,$element)=each($part_a)){
				if (!is_array($part_a[$n])){
					$type_str.=$part_a[$n];
					break;
				}
			}
			return $type_str;
		}else return $part_a[0]."/".$part_a[1];
	}else return false;
}

function iml_GetFirstTextPart($structure,$part){
    if ($part==0) $part="";
    $typeCode = -1;
    while ($typeCode!=0){
        $typeCode = iml_GetPartTypeCode($structure, $part);
        if ($typeCode == 1){
            $part .= (empty($part)?"":".")."1";
        }else if ($typeCode > 0){
            $parts_a = explode(".", $part);
            $lastPart = count($parts_a) - 1;
            $parts_a[$lastPart] = (int)$parts_a[$lastPart] + 1;
            $part = implode(".", $parts_a);
        }else if ($typeCode == -1){
            return "";
        }
    }
    
    return $part;
}

function iml_GetPartTypeCode($a, $part){
	$types=array(0=>"text",1=>"multipart",2=>"message",3=>"application",4=>"audio",5=>"image",6=>"video",7=>"other");

	$part_a=iml_GetPartArray($a, $part);
	if ($part_a){
		if (is_array($part_a[0])) $str="multipart";
		else $str=$part_a[0];

		$code=7;
		while ( list($key, $val) = each($types)) if (strcasecmp($val, $str)==0) $code=$key;
		return $code;
	}else return -1;
}

function iml_GetPartEncodingCode($a, $part){
	$encodings=array("7BIT", "8BIT", "BINARY", "BASE64", "QUOTED-PRINTABLE", "OTHER");

	$part_a=iml_GetPartArray($a, $part);
	if ($part_a){
		if (is_array($part_a[0])) return -1;
		else $str=$part_a[5];

		$code=5;
		while ( list($key, $val) = each($encodings)) if (strcasecmp($val, $str)==0) $code=$key;

		return $code;

	}else return -1;
}

function iml_GetPartEncodingString($a, $part){
	$part_a=iml_GetPartArray($a, $part);
	if ($part_a){
		if (is_array($part_a[0])) return -1;
		else return $part_a[5];
	}else return -1;
}

function iml_GetPartSize($a, $part){
	$part_a=iml_GetPartArray($a, $part);
	if ($part_a){
		if (is_array($part_a[0])) return -1;
		else return $part_a[6];
	}else return -1;
}

function iml_GetPartID($a, $part){
	$part_a=iml_GetPartArray($a, $part);
	if ($part_a){
		if (is_array($part_a[0])) return -1;
		else return $part_a[3];
	}else return -1;
}

function iml_GetPartDisposition($a, $part){
	$part_a=iml_GetPartArray($a, $part);
	if ($part_a){
		if (is_array($part_a[0])) return -1;
		else{
            $id = count($part_a) - 2;
			if (is_array($part_a[$id])) return $part_a[$id][0];
			else return "";
		}
	}else return "";
}

function iml_GetPartName($a, $part){
	$part_a=iml_GetPartArray($a, $part);
	if ($part_a){
		if (is_array($part_a[0])) return -1;
		else{
            $name = "";
			if (is_array($part_a[2])){
                //first look in content type
				$name="";
				while ( list($key, $val) = each ($part_a[2])){
                    if ((strcasecmp($val, "NAME")==0)||(strcasecmp($val, "FILENAME")==0)) 
                        $name=$part_a[2][$key+1];
                }
			}
            if (empty($name)){
                //check in content disposition
                $id = count($part_a) - 2;
                if ((is_array($part_a[$id])) && (is_array($part_a[$id][1]))){
                    $array = $part_a[$id][1];
                    while ( list($key, $val) = each($array)){
                        if ((strcasecmp($val, "NAME")==0)||(strcasecmp($val, "FILENAME")==0)) 
                            $name=$array[$key+1];
                    }
                }
            }
			return $name;
		}
	}else return "";
}


function iml_GetPartCharset($a, $part){
	$part_a=iml_GetPartArray($a, $part);
	if ($part_a){
		if (is_array($part_a[0])) return -1;
		else{
			if (is_array($part_a[2])){
				$name="";
				while ( list($key, $val) = each ($part_a[2])) if (strcasecmp($val, "charset")==0) $name=$part_a[2][$key+1];
				return $name;
			}
			else return "";
		}
	}else return "";
}

function iml_GetPartList($a, $part){
	//echo "MOO?"; flush();
	$data = array();
	$num_parts = iml_GetNumParts($a, $part);
	//echo "($num_parts)"; flush();
	if ($num_parts !== false){
		//echo "<!-- ($num_parts parts)//-->\n";
		for ($i = 0; $i<$num_parts; $i++){
			$part_code = $part.(empty($part)?"":".").($i+1);
			$part_type = iml_GetPartTypeCode($a, $part_code);
			$part_disposition = iml_GetPartDisposition($a, $part_code);
			//echo "<!-- part: $part_code type: $part_type //-->\n";
			if (strcasecmp($part_disposition, "attachment")!=0 && 
				(($part_type == 1) || ($part_type==2))){
				$data = array_merge($data, iml_GetPartList($a, $part_code));
			}else{
				$data[$part_code]["typestring"] = iml_GetPartTypeString($a, $part_code);
				$data[$part_code]["disposition"] = $part_disposition;
				$data[$part_code]["size"] = iml_GetPartSize($a, $part_code);
				$data[$part_code]["name"] = iml_GetPartName($a, $part_code);
				$data[$part_code]["id"] = iml_GetPartID($a, $part_code);
			}
		}
	}
	return $data;
}

function iml_GetNextPart($part){
	if (strpos($part, ".")===false) return $part++;
	else{
		$parts_a = explode(".", $part);
		$num_levels = count($parts_a);
		$parts_a[$num_levels-1]++;
		return implode(".", $parts_a);
	}
}
?>