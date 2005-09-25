<?php 
/////////////////////////////////////////////////////////
//	
//	include/smtp.inc
//
//	(C)Copyright 2002 Ryo Chijiiwa <Ryo@IlohaMail.org>
//
//		This file is part of IlohaMail.
//		IlohaMail is free software released under the GPL 
//		license.  See enclosed file COPYING for details,
//		or see http://www.fsf.org/copyleft/gpl.html
//
/////////////////////////////////////////////////////////

/********************************************************

	AUTHOR: Ryo Chijiiwa <ryo@ilohamail.org>
	FILE:  include/smtp.php
	PURPOSE:
		Provide SMTP functionality using pure PHP.
	PRE-CONDITIONS:
		The functions here require a SMTP server configured to allow relaying.
	POST-CONDITIONS:
		The following global variables are returned:
		$smtp_errornum - error number, 0 if successful
		$smtp_error - error message(s)
		$SMTP_TYPE - Optional
	COMMENTS:
		The optional $smtp_message_file can be used for sending extremely large
		messages.  Storing large messages in a variable will consume whatever
		amount of memory required, which may be more than is available if dealing
		with messages with large attachments.  By storing them in a file, it becomes
		possible to read/send bits at a time, drastically reducing memory useage.
		
		This library only provides bare-bones SMTP functionality.  It is up to the
		parent code to form valid RFC822 (MIME) messages.
			
********************************************************/
	
	//set some global constants
	if (strcasecmp($SMTP_TYPE, "courier")==0){
		$SMTP_REPLY_DELIM = "-";
		$SMTP_DATA_REPLY = 250;
	}else{
		$SMTP_REPLY_DELIM = " ";
		$SMTP_DATA_REPLY = 354;
	}

	/* fgets replacement that's multi-line aware */
	function smtp_get_response($fp, $len){
		$end = false;
		do{
			$line = chop(fgets($fp, 5120));
			// echo "SMTP:".$line."<br>\n"; flush();
			if ((strlen($line)==3) || ($line[3]==' ')) $end = true;
		}while(!$end);

		return $line;
	}


	function smtp_check_reply($reply){
		global $smtp_error;
		global $SMTP_REPLY_DELIM;
		
		$a = explode($SMTP_REPLY_DELIM, chop($reply));

		if (count($a) >= 1){

			if ($a[0]==250||$a[0]==354) return true;
			else{
				$smtp_error .= $reply."\n";
			}
		}else{
			$smtp_error .= "Invalid SMTP response line: $reply\n";
		}
		
		return false;
	}
	
	
	function smtp_split($str){
		$result = array();
		$pos = strpos($str, " ");
		if ($pos===false){
			$result[0] = $str;
		}else{
			$result[0] = substr($str, 0, $pos);
			$result[1] = substr($str, $pos+1);
		}
		
		return $result;
	}
	
	
	function smtp_ehlo(&$conn, $host){
		$result = "";
		fputs($conn, "EHLO $host\r\n");
		//echo "Sent: EHLO $host\n"; flush();
		do{
			$line = fgets($conn, 2048);
			//echo "Got: $line"; flush();
			$a = explode(" ", $line);
			if ($a[0]=="250-AUTH") $result .= substr($line, 9);
		}while(!is_numeric($a[0]));
		
		if ($a[0]==250) return $result;
		else return $a[0];
		
	}
	
	
	function smtp_auth_login(&$conn, $user, $pass){
		$auth["username"] = base64_encode($user);
		$auth["password"] = base64_encode($pass);
	
		fputs($conn, "AUTH LOGIN\r\n");
		
		//echo "Sent: AUTH LOGIN\n"; flush();
		
		//get first line
		$line = smtp_get_response($conn, 1024);
		//echo "AUTH_LOGIN << $line"; flush();
		$parts = smtp_split($line);
		//valid reply?
		if (($parts[0]!=334) || (empty($parts[1]))) return false;
		//send data
		$prompt = eregi_replace("[^a-z]", "", strtolower(base64_decode($parts[1])));
		fputs($conn, $auth[$prompt]."\r\n");
		//echo "AUT_LOGIN >> ".$auth[$prompt]."\n"; flush();

		//get second line
		$line = smtp_get_response($conn, 1024);
		//echo "AUTH_LOGIN << $line"; flush();
		$parts = smtp_split($line);
		//valid reply?
		if (($parts[0]!=334) || (empty($parts[1]))) return false;
		$prompt = eregi_replace("[^a-z]", "", strtolower(base64_decode($parts[1])));
		fputs($conn, $auth[$prompt]."\r\n");
		//echo "AUT_LOGIN >> ".$auth[$prompt]."\n"; flush();

		$line = smtp_get_response($conn, 1024);
		//echo "AUTH_LOGIN << $line"; flush();
		$parts = smtp_split($line);
		return ($parts[0]==235);
	}
	
	
	function smtp_connect($host, $port, $user, $pass){
		global $smtp_errornum;
		global $smtp_error;

		//auth user?
		global $SMTP_USER, $SMTP_PASSWORD;
		if ((!empty($SMTP_USER)) && (!empty($SMTP_PASSWORD))){
			$user = $SMTP_USER;
			$pass = $SMTP_PASSWORD;
		}
		
		// echo "User: $user<br>\n";
		
		//figure out auth mode
		global $AUTH_MODE;
		$auth_mode = $AUTH_MODE["smtp"];
		if (empty($auth_mode)) $auth_mode = "none";
		if (empty($user) || empty($pass)) $auth_mode = "none";
		
		// echo "authmode: $auth_mode<br>\n"; flush();
		
		//initialize defaults
		if (empty($host)) $host = "localhost";
		if (empty($port)) $port = 25;
		
		// echo "Connecting to $host:$port<br>\n"; flush();
		
		//connect to SMTP server
		$conn = fsockopen($host, $port);
	
		if (!$conn){
			//echo "fsockopen failed\n";
			$smtp_error = "Couldn't connect to $host:$port<br>\n";
			return false;
		}
		
		//read greeting
		$greeting = smtp_get_response($conn, 1024);
			
		// echo "Connected: $greeting<br>\n"; flush();

		if (($auth_mode=="check") || ($auth_mode=="auth")){
			// echo "Trying EHLO<br>\n"; flush();
			$auth_modes = smtp_ehlo($conn, $_SERVER["SERVER_NAME"]);
			// echo "smtp_ehlo returned: $auth_modes<br>\n"; flush();
			if ($auth_modes===false){
				$smtp_error = "EHLO failed\n";
				$conn = false;
			}else if (stristr($auth_modes, "LOGIN")!==false){
				echo "trying AUTH LOGIN\n"; flush();
				if (!smtp_auth_login($conn, $user, $pass)){
					//echo "CONN: AUTH_LOGIN failed\n"; flush();
					$conn = false;
				}
				//echo "Conn after LOGIN: $conn<br>\n"; flush();
			}
		}else{
			fputs($conn, "HELO ".$_SERVER["SERVER_NAME"]."\r\n");
			$line = smtp_get_response($conn, 1024);
			if (!smtp_check_reply($line)){
				$conn = false;
				$smtp_error .= $line."\n";
			}
			// echo "after HELO: $conn<br>\n"; flush();
		}
	
		return $conn;
	}
	
	
	function smtp_close($conn){
		fclose($conn);
	}


	function smtp_mail($conn, $from, $recipients, $message, $is_file){
		global $smtp_errornum;
		global $smtp_error;
		global $SMTP_DATA_REPLY;
		
		//check recipients and sender addresses
		if ((count($recipients)==0) || (!is_array($recipients))){
			$smtp_errornum = -1;
			$smtp_error .= "Recipients list is empty\n";
			return false;
		}
		if (empty($from)){
			$smtp_errornum = -2;
			$smtp_error .= "From address unspecified\n";
			return false;
		}
		
		if (!$conn){			
			$smtp_errornum = -3;
			$smtp_error .= "Invalid connection\n";
		}

		if (!ereg("^<", $from)) $from = "<".$from;
		if (!ereg(">$", $from)) $from = $from.">";
		
		//send MAIL FROM command
		$command = "MAIL FROM: $from\r\n";
		// echo nl2br(htmlspecialchars($command));
		fputs($conn, $command);

		if (smtp_check_reply(smtp_get_response($conn, 1024))){
			//send RCPT TO commands, count valid recipients
			$num_recipients = 0;	
			while ( list($k, $recipient) = each($recipients) ){
				$command = "RCPT TO: $recipient\r\n";
				fputs($conn, $command);
				$reply = smtp_check_reply(smtp_get_response($conn, 1024));
				if ($reply) $num_recipients++;
				else $smtp_error .= $reply."\n";
			}
			
			//error out if no valid recipiets
			if ($num_recipients == 0){
				$smtp_errornum = -1;
				$smtp_error .= "No valid recipients\n";
				return false;
			}
			
			//send DATA command
			fputs($conn, "DATA\r\n");
			$reply = chop(smtp_get_response($conn, 1024));
			$a = explode(" ", $reply);
			
			//error out if DATA command ill received
			if ($a[0]!=$SMTP_DATA_REPLY){
				$smtp_errornum = -4;
				$smtp_error .= $reply;
				return false;	
			}
			//send data
			if ($is_file){
				//if message file, open file
				$fp = false;
				if (file_exists(realpath($message))) $fp = fopen($message, "rb");
				if (!$fp)
				{ 
					$smtp_errornum = -4;
					$smtp_error .= "Invlid message file\n";
					return false;
				}
				
				//send file
				while(!feof($fp)){
					$buffer = chop(fgets($fp, 4096), "\r\n");
					fputs($conn, $buffer."\r\n");
				}
				fclose($fp);
				fputs($conn, "\r\n.\r\n");
				
				return smtp_check_reply(smtp_get_response($conn, 1024));
			}else{
				//else, send message
				$message = str_replace("\r\n", "\n", $message);
				$message = str_replace("\n", "\r\n", $message);
				$message = str_replace("\r\n.\r\n", "\r\n..\r\n", $message);
				fputs($conn, $message);
				fputs($conn, "\r\n.\r\n");
				
				return smtp_check_reply(smtp_get_response($conn, 1024));
			}
		}
		
		return false;
	}

	
	function smtp_ExplodeQuotedString($delimiter, $string){
		$quotes=explode("\"", $string);
		while ( list($key, $val) = each($quotes))
			if (($key % 2) == 1) 
				$quotes[$key] = str_replace($delimiter, "_!@!_", $quotes[$key]);
		$string=implode("\"", $quotes);
	
		$result=explode($delimiter, $string);
		while ( list($key, $val) = each($result) )
			$result[$key] = str_replace("_!@!_", $delimiter, $result[$key]);
	
		return $result;
	}	
	
	
	function smtp_expand($str){
		$addresses = array();
		$recipients = smtp_ExplodeQuotedString(",", $str);
		reset($recipients);
		while ( list($k, $recipient) = each($recipients) ){
			$a = explode(" ", $recipient);
			while ( list($k2, $word) = each($a) ){
				if ((strpos($word, "@") > 0) && (strpos($word, "\"")===false)){
					if (!ereg("^<", $word)) $word = "<".$word;
					if (!ereg(">$", $word)) $word = $word.">";
					if (in_array($word, $addresses)===false) array_push($addresses, $word);
				}
			}
		}
		return $addresses;
	}
?>