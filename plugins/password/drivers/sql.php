<?php

/**
 * SQL Password Driver
 *
 * Driver for passwords stored in SQL database
 *
 * @version 1.1
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

    $sql = str_replace('%u', $db->quote($_SESSION['username'],'text'), $sql);
    $sql = str_replace('%p', $db->quote($passwd,'text'), $sql);
    $sql = str_replace('%o', $db->quote($curpass,'text'), $sql);
    $sql = str_replace('%h', $db->quote($_SESSION['imap_host'],'text'), $sql);

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
