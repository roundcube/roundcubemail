<?php

/**
 * Domain-Offensive Webspace Password Driver
 *
 * Driver to change passwords of Mail accounts at Domain-Offensive (do.de).
 * http://www.do.de/
 *
 * @version 1.0
 * @author Tobias Mädel <t.maedel@alfeld.de>
 * @link http://tbspace.de
 *
 */

class rcube_dode_password
{
	function save($curpass, $passwd)
	{
		$rcmail = rcmail::get_instance();

		if (is_null($curpass)) 
		{
			$curpass = $rcmail->decrypt($_SESSION['password']);
		}

		if ($cURL = curl_init()) 
		{
			// We need the confixx interface, not the users webspace.
			// Its reachable via the Default-VHost
			$interfaceScheme = "http://";
			$interfaceURL = $_SERVER['SERVER_ADDR'];
			$apiUsername = $rcmail->user->get_username();
			curl_setopt( $cURL, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8" );
			curl_setopt( $cURL, CURLOPT_URL, $interfaceScheme . $interfaceURL . "/login.php" );
			curl_setopt( $cURL, CURLOPT_POST, 1 );
			curl_setopt( $cURL, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt( $cURL, CURLOPT_SSL_VERIFYHOST, 2 );
			curl_setopt( $cURL, CURLOPT_HEADER, 0 );
			curl_setopt( $cURL, CURLOPT_POSTFIELDS, http_build_query( array( "_cat" => "pop3", "username" => $apiUsername, "password" => $curpass ) ) );
			curl_setopt( $cURL, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt( $cURL, CURLOPT_RETURNTRANSFER, 1 );

			// Dirty hack here to store cookies.
			$tmpmeta = stream_get_meta_data(tmpfile());
			$tempfile = realpath($tmpmeta["uri"]);

			curl_setopt( $cURL, CURLOPT_COOKIEJAR, $tempfile );
			curl_setopt( $cURL, CURLOPT_COOKIEFILE, $tempfile );

			$pageContent = curl_exec( $cURL );

			curl_setopt( $cURL, CURLOPT_URL, $interfaceScheme . $interfaceURL . "/poplogin/" . $apiUsername . "/allgemein_pwaendern2.php" );
			curl_setopt( $cURL, CURLOPT_POST, 1 );
			curl_setopt( $cURL, CURLOPT_POSTFIELDS, http_build_query( array( "altpw" => $curpass, "neupw1" => $passwd, "neupw2" => $passwd ) ) );

			$pageContent = curl_exec( $cURL );
			
			if( preg_match( "/Ihr Passwort wurde/", $pageContent, $matches ) )
			{
				$_SESSION['password'] = $rcmail->encrypt($passwd);
				return PASSWORD_SUCCESS;
			}
			
			return PASSWORD_ERROR;
		}
		else 
		{
			return PASSWORD_CONNECT_ERROR;
		}

		return PASSWORD_ERROR;
	}
}
