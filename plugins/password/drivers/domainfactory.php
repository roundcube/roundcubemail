<?php

/**
 * domainFACTORY Password Driver
 *
 * Driver to change passwords with the hosting provider domainFACTORY.
 * http://www.df.eu/
 *
 * @version 2.1
 * @author Till Krüss <me@tillkruess.com>
 * @link http://tillkruess.com/projects/roundcube/
 *
 */

class rcube_domainfactory_password
{
	function save($curpass, $passwd)
	{
		$rcmail = rcmail::get_instance();

		if (is_null($curpass)) {
			$curpass = $rcmail->decrypt($_SESSION['password']);
		}

		if ($ch = curl_init()) {

			// initial login
			curl_setopt_array($ch, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL => 'https://ssl.df.eu/chmail.php',
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query(array(
					'login' => $rcmail->user->get_username(),
					'pwd' => $curpass,
					'action' => 'change'
				))
			));

			if ($result = curl_exec($ch)) {
				// login successful, get token!
				$postfields = array(
					'pwd1' => $passwd,
					'pwd2' => $passwd,
					'action[update]' => 'Speichern'
				);

				preg_match_all('~<input name="(.+?)" type="hidden" value="(.+?)">~i', $result, $fields);
				foreach ($fields[1] as $field_key => $field_name) {
					$postfields[$field_name] = $fields[2][$field_key];
				}

				// change password
				$ch = curl_copy_handle($ch);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
				if ($result = curl_exec($ch)) {

					// has the password been changed?
					if (strpos($result, 'Einstellungen erfolgreich') !== false) {
						return PASSWORD_SUCCESS;
					}

					// show error message(s) if possible
					if (strpos($result, '<div class="d-msg-text">') !== false) {
						preg_match_all('#<div class="d-msg-text">(.*?)</div>#s', $result, $errors);
						if (isset($errors[1])) {
							$error_message = '';
							foreach ( $errors[1] as $error ) {
								$error_message .= trim(mb_convert_encoding( $error, 'UTF-8', 'ISO-8859-15' )).' ';
							}
							return array('code' => PASSWORD_ERROR, 'message' => $error_message);
						}
					}


				} else {
					return PASSWORD_CONNECT_ERROR;
				}

			} else {
				return PASSWORD_CONNECT_ERROR;
			}

		} else {
			return PASSWORD_CONNECT_ERROR;
		}

		return PASSWORD_ERROR;
	}
}
