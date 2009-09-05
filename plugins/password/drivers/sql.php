<?php

/**
 * SQL Password Driver
 *
 * Driver for passwords stored in SQL database
 *
 * @version 1.3
 * @author Aleksander 'A.L.E.C' Machniak <alec@alec.pl>
 *
 */

function password_save($curpass, $passwd)
{
    $rcmail = rcmail::get_instance();

    if (!($sql = $rcmail->config->get('password_query')))
        $sql = 'SELECT update_passwd(%c, %u)';

    if ($dsn = $rcmail->config->get('password_db_dsn')) {
	// #1486067: enable new_link option
	if (is_array($dsn) && empty($dsn['new_link']))
	    $dsn['new_link'] = true;
	else if (!is_array($dsn) && !preg_match('/\?new_link=true/', $dsn))
	  $dsn .= '?new_link=true';

        $db = new rcube_mdb2($dsn, '', FALSE);
        $db->set_debug((bool)$rcmail->config->get('sql_debug'));
        $db->db_connect('w');
    } else {
        $db = $rcmail->get_dbh();
    }

    if ($err = $db->is_error())
        return PASSWORD_ERROR;
    
    // crypted password
    if (strpos($sql, '%c') !== FALSE) {
        $salt = '';
        if (CRYPT_MD5) { 
    	    $len = rand(3, CRYPT_SALT_LENGTH);
        } else if (CRYPT_STD_DES) {
    	    $len = 2;
        } else {
    	    return PASSWORD_CRYPT_ERROR;
        }
        for ($i = 0; $i < $len ; $i++) {
    	    $salt .= chr(rand(ord('.'), ord('z')));
        }
        $sql = str_replace('%c',  $db->quote(crypt($passwd, CRYPT_MD5 ? '$1$'.$salt.'$' : $salt)), $sql);
    }
    
    // hashed passwords
    if (preg_match('/%[n|q]/', $sql)) {

	if (!extension_loaded('hash')) {
	    raise_error(array(
	        'code' => 600,
		'type' => 'php',
		'file' => __FILE__,
		'message' => "Password plugin: 'hash' extension not loaded!"
		), true, false);
	    return PASSWORD_ERROR;			    
	}

	if (!($hash_algo = strtolower($rcmail->config->get('password_hash_algorithm'))))
            $hash_algo = 'sha1';
        
	$hash_passwd = hash($hash_algo, $passwd);
        $hash_curpass = hash($hash_algo, $curpass);
        
	if ($rcmail->config->get('password_hash_base64')) {
            $hash_passwd = base64_encode(pack('H*', $hash_passwd));
            $hash_curpass = base64_encode(pack('H*', $hash_curpass));
        }
	
	$sql = str_replace('%n', $db->quote($hash_passwd, 'text'), $sql);
	$sql = str_replace('%q', $db->quote($hash_curpass, 'text'), $sql);
    }

    $user_info = explode('@', $_SESSION['username']);
    if (count($user_info) >= 2) {
	$sql = str_replace('%l', $db->quote($user_info[0], 'text'), $sql);
	$sql = str_replace('%d', $db->quote($user_info[1], 'text'), $sql);
    }
    
    $sql = str_replace('%u', $db->quote($_SESSION['username'],'text'), $sql);
    $sql = str_replace('%h', $db->quote($_SESSION['imap_host'],'text'), $sql);
    $sql = str_replace('%p', $db->quote($passwd,'text'), $sql);
    $sql = str_replace('%o', $db->quote($curpass,'text'), $sql);

    $res = $db->query($sql);

    if (!$db->is_error()) {
	if (strtolower(substr(trim($query),0,6))=='select') {
    	    if ($result = $db->fetch_array($res))
		return PASSWORD_SUCCESS;
	} else { 
    	    if ($db->affected_rows($res) == 1)
		return PASSWORD_SUCCESS; // This is the good case: 1 row updated
	}
    }

    return PASSWORD_ERROR;
}

?>
