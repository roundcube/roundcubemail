<?php

/**
 * SQL Password Driver
 *
 * Driver for passwords stored in SQL database
 *
 * @version 2.0
 * @author Aleksander 'A.L.E.C' Machniak <alec@alec.pl>
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

class rcube_isp_config_password
{
    private function _dbInit() {
     
     	$db = 'db';
        $db_admin = 'user'; 
        $db_password = 'pass'; 
        $host = 'localhost';
                  
        $pSQL = mysql_connect($host, $db_admin, $db_password)
        or die(mysql_error());
		
        mysql_select_db("$db", $pSQL)
        or die(mysql_error());
		
		return $pSQL;
     
    }
    
    function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();

        if (!($sql = $rcmail->config->get('password_query'))) {
            $sql = 'UPDATE mail_user SET password=%c WHERE email=%u LIMIT 1';
        }

        if ($dsn = $rcmail->config->get('password_db_dsn')) {
            $db = rcube_db::factory($dsn, '', false);
            $db->set_debug((bool)$rcmail->config->get('sql_debug'));
        }
        else {
            $db = $rcmail->get_dbh();
        }

        if ($db->is_error()) {
            return PASSWORD_ERROR;
        }
        
        // crypted password (deprecated, use %P)
        if (strpos($sql, '%c') !== false) {
         
            $password = password::hash_password($passwd, 'crypt', false);

            if ($password === false) {
             
                return PASSWORD_CRYPT_ERROR;
                
            }

            $sql = str_replace('%c',  $db->quote($password), $sql);
            $sql = str_replace('%u',  $db->quote($_SESSION['username']), $sql);
            
        }
        $db_isp = $this->_dbinit();
        
        $result = mysql_query($sql);
        
        if ($result) {
         
            return PASSWORD_SUCCESS;
            
        } else {
         
            return PASSWORD_ERROR;
        }
    }
}
