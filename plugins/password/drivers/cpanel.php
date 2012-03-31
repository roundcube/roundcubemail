<?php

/**
 * cPanel Password Driver
 *
 * Driver that adds functionality to change the users cPanel password.
 * The cPanel PHP API code has been taken from: http://www.phpclasses.org/browse/package/3534.html
 *
 * This driver has been tested with Hostmonster hosting and seems to work fine.
 *
 * @version 2.0
 * @author Fulvio Venturelli <fulvio@venturelli.org>
 */

class rcube_cpanel_password
{
    public function save($curpas, $newpass)
    {
        $rcmail = rcmail::get_instance();

        // Create a cPanel email object
        $cPanel = new emailAccount($rcmail->config->get('password_cpanel_host'),
    	$rcmail->config->get('password_cpanel_username'),
	    $rcmail->config->get('password_cpanel_password'),
    	$rcmail->config->get('password_cpanel_port'),
	    $rcmail->config->get('password_cpanel_ssl'),
    	$rcmail->config->get('password_cpanel_theme'),
	    $_SESSION['username'] );

        if ($cPanel->setPassword($newpass)){
            return PASSWORD_SUCCESS;
        }
        else {
            return PASSWORD_ERROR;
        }
    }
}


class HTTP
{
	function HTTP($host, $username, $password, $port, $ssl, $theme)
	{
		$this->ssl = $ssl ? 'ssl://' : '';
		$this->username = $username;
		$this->password = $password;
		$this->theme = $theme;
		$this->auth = base64_encode($username . ':' . $password);
		$this->port = $port;
		$this->host = $host;
		$this->path = '/frontend/' . $theme . '/';
	}

	function getData($url, $data = '')
	{
		$url = $this->path . $url;
		if(is_array($data))
		{
			$url = $url . '?';
			foreach($data as $key=>$value)
			{
				$url .= urlencode($key) . '=' . urlencode($value) . '&';
			}
			$url = substr($url, 0, -1);
		}
		$response = '';
		$fp = fsockopen($this->ssl . $this->host, $this->port);
		if(!$fp)
		{
			return false;
		}
		$out = 'GET ' . $url . ' HTTP/1.0' . "\r\n";
		$out .= 'Authorization: Basic ' . $this->auth . "\r\n";
		$out .= 'Connection: Close' . "\r\n\r\n";
		fwrite($fp, $out);
		while (!feof($fp))
		{
			$response .= @fgets($fp);
		}
		fclose($fp);
		return $response;
	}
}


class emailAccount
{
	function emailAccount($host, $username, $password, $port, $ssl, $theme, $address)
	{
		$this->HTTP = new HTTP($host, $username, $password, $port, $ssl, $theme);
		if(strpos($address, '@'))
		{
			list($this->email, $this->domain) = explode('@', $address);
		}
		else
		{
			list($this->email, $this->domain) = array($address, '');
		}
	}

    /**
     * Change email account password
     *
     * Returns true on success or false on failure.
     * @param string $password email account password
     * @return bool
     */
	function setPassword($password)
	{
		$data['email'] = $this->email;
		$data['domain'] = $this->domain;
		$data['password'] = $password;
		$response = $this->HTTP->getData('mail/dopasswdpop.html', $data);
		if(strpos($response, 'success') && !strpos($response, 'failure'))
		{
			return true;
		}
		return false;
	}
}
