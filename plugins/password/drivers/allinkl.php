<?php

/**
 * allinkl KAS Driver
 *
 * Driver that adds functionality to change the user password via KAS API.
 *
 * For installation instructions please read the README file.
 *
 * @version 1.0
 * @author Jan N.
 */
class rcube_allinkl_password
{
    public function save($currpass, $newpass)
    {
  	$rcmail = rcmail::get_instance();

		$WSDL_AUTH = 'https://kasserver.com/schnittstelle/soap/wsdl/KasAuth.wsdl';
		$WSDL_API = 'https://kasserver.com/schnittstelle/soap/wsdl/KasApi.wsdl';


		//Create SOAP-Session to KAS-Server
		try
		{
			$SoapLogon = new SoapClient($WSDL_AUTH);
			$CredentialToken = $SoapLogon->KasAuth
			(
				array
				(
					'KasUser' => $rcmail->config->get('password_allinkl_user'),
					'KasAuthType' => 'sha1',
					'KasPassword' => sha1($rcmail->config->get('password_allinkl_passwd')),
					'SessionLifeTime' => 30,
					'SessionUpdateLifeTime' => 'Y'
				)
			);
		}
		catch(SoapFault $fault)
		{
			raise_error(array(
				'code' => 600,
				'type' => 'php',
				'file' => __FILE__, 'line' => __LINE__,
				'message' => 'Password plugin: '.$fault->faultstring
				), true, false);

			return PASSWORD_CONNECT_ERROR;
		}



		//load list of mail account
		try
		{
			$Params = array();

			$SoapRequest = new SoapClient($WSDL_API);
			$req = $SoapRequest->KasApi
			(
				array
				(
					'KasUser' => $rcmail->config->get('password_allinkl_user'),
					'CredentialToken' => $CredentialToken,
					'KasRequestType' => 'get_mailaccounts',
					'KasRequestParams' => $Params
				)
			);
		}
		catch(SoapFault $fault)
		{
			raise_error(array(
				'code' => 600,
				'type' => 'php',
				'file' => __FILE__, 'line' => __LINE__,
				'message' => 'Password plugin: '.$fault->faultstring
				), true, false);

			return PASSWORD_ERROR;
		}


		//Search internal username of mail account
		foreach($req['Response']['ReturnInfo'] as $key => $value)
		{
			foreach($req['Response']['ReturnInfo'][$key] as $key2 => $value2)
			{
				if($value2 == $_SESSION['username'])
				{
					$mail_login = $req['Response']['ReturnInfo'][$key]['mail_login'];
				}
			}
		}


		//If no user found
		if(empty($mail_login))
		{
			return PASSWORD_ERROR;
		}
		//If mail account exists => change password
		else
		{
			try
			{
				$Params = array
				(
					'mail_login' => $mail_login,
					'mail_new_password' => $newpass
				);

				$SoapRequest = new SoapClient($WSDL_API);
				$req = $SoapRequest->KasApi
				(
					array
					(
						'KasUser' => $rcmail->config->get('password_allinkl_user'),
						'CredentialToken' => $CredentialToken,
						'KasRequestType' => 'update_mailaccount',
						'KasRequestParams' => $Params
					)
				);
			}
			catch(SoapFault $fault)
			{
				raise_error(array(
					'code' => 600,
					'type' => 'php',
					'file' => __FILE__, 'line' => __LINE__,
					'message' => 'Password plugin: '.$fault->faultstring
					), true, false);

				return PASSWORD_ERROR;
			}

			return PASSWORD_SUCCESS;
		}
    }
}
?>
