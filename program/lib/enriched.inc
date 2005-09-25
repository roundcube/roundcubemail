<?php
/*
	File:		read_enriched.inc
	Author: 	Ryo Chijiiwa
	License:	GPL (part of IlohaMail)
	Purpose: 	functions for handling text/enriched messages
	Reference: 	RFC 1523, 1896
*/


function enriched_convert_newlines($str){
	//remove single newlines, convert N newlines to N-1
	
	$str = str_replace("\r\n","\n",$str);
	$len = strlen($str);
	
	$nl = 0;
	$out = '';
	for($i=0;$i<$len;$i++){
		$c = $str[$i];
		if (ord($c)==10) $nl++;
		if ($nl && ord($c)!=10) $nl = 0;
		if ($nl!=1) $out.=$c;
		else $out.=' ';		
	}
	return $out;
}

function enriched_convert_formatting($body){
	$a=array('<bold>'=>'<b>','</bold>'=>'</b>','<italic>'=>'<i>',
			'</italic>'=>'</i>','<fixed>'=>'<tt>','</fixed>'=>'</tt>',
			'<smaller>'=>'<font size=-1>','</smaller>'=>'</font>',
			'<bigger>'=>'<font size=+1>','</bigger>'=>'</font>',
			'<underline>'=>'<span style="text-decoration: underline">',
			'</underline>'=>'</span>',
			'<flushleft>'=>'<span style="text-align:left">',
			'</flushleft>'=>'</span>',
			'<flushright>'=>'<span style="text-align:right">',
			'</flushright>'=>'</span>',
			'<flushboth>'=>'<span style="text-align:justified">',
			'</flushboth>'=>'</span>',
			'<indent>'=>'<span style="padding-left: 20px">',
			'</indent>'=>'</span>',
			'<indentright>'=>'<span style="padding-right: 20px">',
			'</indentright>'=>'</span>');
	
	while(list($find,$replace)=each($a)){
		$body = eregi_replace($find,$replace,$body);
	}
	return $body;
}

function enriched_font($body){
	$pattern = '/(.*)\<fontfamily\>\<param\>(.*)\<\/param\>(.*)\<\/fontfamily\>(.*)/ims';
	while(preg_match($pattern,$body,$a)){
		//print_r($a);
		if (count($a)!=5) continue;
		$body=$a[1].'<span style="font-family: '.$a[2].'">'.$a[3].'</span>'.$a[4];
	}

	return $body;
}


function enriched_color($body){
	$pattern = '/(.*)\<color\>\<param\>(.*)\<\/param\>(.*)\<\/color\>(.*)/ims';
	while(preg_match($pattern,$body,$a)){
		//print_r($a);
		if (count($a)!=5) continue;

		//extract color (either by name, or ####,####,####)
		if (strpos($a[2],',')){
			$rgb = explode(',',$a[2]);
			$color ='#';
			for($i=0;$i<3;$i++) $color.=substr($rgb[$i],0,2); //just take first 2 bytes
		}else{
			$color = $a[2];
		}
		
		//put it all together
		$body = $a[1].'<span style="color: '.$color.'">'.$a[3].'</span>'.$a[4];
	}

	return $body;
}

function enriched_excerpt($body){

	$pattern = '/(.*)\<excerpt\>(.*)\<\/excerpt\>(.*)/i';
	while(preg_match($pattern,$body,$a)){
		//print_r($a);
		if (count($a)!=4) continue;
		$quoted = '';
		$lines = explode('<br>',$a[2]);
		foreach($lines as $n=>$line) $quoted.='&gt;'.$line.'<br>';
		$body=$a[1].'<span class="quotes">'.$quoted.'</span>'.$a[3];
	}

	return $body;
}

function enriched_to_html($body){
	$body = str_replace('<<','&lt;',$body);
	$body = enriched_convert_newlines($body);
	$body = str_replace("\n", '<br>', $body);
	$body = enriched_convert_formatting($body);
	$body = enriched_color($body);
	$body = enriched_font($body);
	$body = enriched_excerpt($body);
	//$body = nl2br($body);
	return $body;
}

?>