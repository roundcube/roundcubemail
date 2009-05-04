<?php
function mod_b64_decode($data){
	return base64_decode(str_replace(",","/",$data));
}

function mod_b64_encode($data){
	return str_replace("/",",",str_replace("=","",base64_encode($data)));
}


function utf8_to_html($str){
	$len = strlen($str);
	$out = "";
	for($i=0;$i<$len;$i+=2){
		$val = ord($str[$i]);
		$next_val = ord($str[$i+1]);
		if ($val<255){
			$out.="&#".($val*256+$next_val).";";
		}else{
			$out.=$str[$i].$str[$i+1];
		}
	}
	return $out;
}

function iil_utf7_decode($str, $raw=false){
	if (strpos($str, '&')===false) return $str;
	
	$len = strlen($str);
	$in_b64 = false;
	$b64_data = "";
	$out = "";
	for ($i=0;$i<$len;$i++){
		$char = $str[$i];
		if ($char=='&') $in_b64 = true;
		else if ($in_b64 && $char=='-'){
			$in_b64 = false;
			if ($b64_data=="") $out.="&";
			else{
				$dec=mod_b64_decode($b64_data);
				$out.=($raw?$dec:utf8_to_html($dec));
				$b64_data = "";
			}
		}else if ($in_b64) $b64_data.=$char;
		else $out.=$char;
	}
	return $out;
}

function iil_utf7_encode($str){
	if (!preg_match('/[\200-\237\241-\377]/', $str))
    		return $str;

	$len = strlen($str);

	for ($i=0;$i<$len;$i++){
		$val = ord($str[$i]);
		if ($val>=224 && $val<=239){
			$unicode = ($val-224) * 4096 + (ord($str[$i+1])-128) * 64 + (ord($str[$i+2])-128);
			$i+=2;
			$utf_code.=chr((int)($unicode/256)).chr($unicode%256);
		}else if ($val>=192 && $val<=223){
			$unicode = ($val-192) * 64 + (ord($str[$i+1])-128);
			$i++;
			$utf_code.=chr((int)($unicode/256)).chr($unicode%256);
		}else{
			if ($utf_code){
				$out.='&'.mod_b64_encode($utf_code).'-';
				$utf_code="";
			}
			if ($str[$i]=="-") $out.="&";
			$out.=$str[$i];
		}
	}
	if ($utf_code)
		$out.='&'.mod_b64_encode($utf_code).'-';
	return $out;
}


?>