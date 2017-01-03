<?php
/**
 * postfixadmin Driver
 *
 * Driver that adds functionality to change the systems user password via
 * postfixadmin XmlRpc
 *
 * For installation instructions please read the README file.
 *
 * @version 1.0
 * @author Dan Marican <dmarican@gmail.com)
 *
 * Setup postfixadmin host into config.inc.php of password plugin as follows:
 * $config['password_postfixadmin_host']='your_postfixadmin_host/postfixadmin/xmlrpc.php';
 *
 * Copyright (C) 2005-2013, The Roundcube Dev Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */


require_once('Zend/XmlRpc/Client.php');
class rcube_postfixadmin_password
{
    public function save($currpass, $newpass)
    {
        $rcmail   = rcmail::get_instance();
        $host     = $rcmail->config->get('password_postfixadmin_host');
        $username = $_SESSION['username'];

	$xmlrpc   = new Zend_XmlRpc_Client($host);
	$http_client = $xmlrpc->getHttpClient();
	$http_client->setCookieJar();

	$login_object = $xmlrpc->getProxy('login');
	$success = $login_object->login($username, $currpass);
	$parola=$xmlrpc->getProxy('user');
	if($parola->changePassword($currpass,$newpass)) {
	return PASSWORD_SUCCESS;
	}
	else {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin postfixadmin: XmlRpc error"
                ), true, false);
        }

        return PASSWORD_ERROR;

    }
}
