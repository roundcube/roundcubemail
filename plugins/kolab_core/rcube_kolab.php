<?php

require_once 'Horde/Kolab/Storage/List.php';
require_once 'Horde/Kolab/Format.php';
require_once 'Horde/Auth.php';
require_once 'Horde/Auth/kolab.php';
require_once 'Horde/Perms.php';

/**
 * Glue class to handle access to the Kolab data using the Kolab_* classes
 * from the Horde project.
 *
 * @author Thomas Bruederli
 */
class rcube_kolab
{
    private static $horde_auth;
    
    
    /**
     * Setup the environment needed by the Kolab_* classes to access Kolab data
     */
    public static function setup()
    {
        global $conf;
        
        // setup already done
        if (self::$horde_auth)
            return;
        
        $rcmail = rcmail::get_instance();
        
        // load ldap credentials from local config
        $conf['kolab'] = $rcmail->config->get('kolab');
        
        $conf['kolab']['ldap']['server'] = 'ldap://' . $_SESSION['imap_host'] . ':389';
        $conf['kolab']['imap']['server'] = $_SESSION['imap_host'];
        $conf['kolab']['imap']['port'] = $_SESSION['imap_port'];
        
        // pass the current IMAP authentication credentials to the Horde auth system
        self::$horde_auth = Auth::singleton('kolab');
        if (self::$horde_auth->authenticate($_SESSION['username'], array('password' => ($pwd = $rcmail->decrypt($_SESSION['password']))), false)) {
            $_SESSION['__auth'] = array(
                'authenticated' => true,
                'userId' => $_SESSION['username'],
                'timestamp' => time(),
                'remote_addr' => $_SERVER['REMOTE_ADDR'],
            );
            Auth::setCredential('password', $pwd);
        }
    }
    
    
    /**
     * Get instance of a Kolab (XML) format object
     *
     * @param string Data type (contact,event,task,note)
     * @return object Horde_Kolab_Format_XML The format object
     */
    public static function get_format($type)
    {
      self::setup();
      return Horde_Kolab_Format::factory('XML', $type);
    }

    /**
     * Get a list of storage folders for the given data type
     *
     * @param string Data type to list folders for (contact,event,task,note)
     * @return array List of Kolab_Folder objects
     */
    public static function get_folders($type)
    {
        self::setup();
        $kolab = Kolab_List::singleton();
        return $kolab->getByType($type);
    }

    /**
     * Get storage object for read/write access to the Kolab backend
     *
     * @param string IMAP folder to access
     * @param string Object type to deal with (leave empty for auto-detection using annotations)
     * @return object Kolab_Data The data storage object
     */
    public static function get_storage($folder, $data_type = null)
    {
        self::setup();
        $kolab = Kolab_List::singleton();
        return $kolab->getFolder($folder)->getData($data_type);
    }

    /**
     * Cleanup session data when done
     */
    public static function shutdown()
    {
        if (isset($_SESSION['__auth'])) {
            // unset auth data from session. no need to store it persistantly
            unset($_SESSION['__auth']);
            
            // FIXME: remove strange numeric entries
            foreach ($_SESSION as $key => $val) {
                if (!$val && is_numeric($key))
                    unset($_SESSION[$key]);
            }
        }
    }
}
