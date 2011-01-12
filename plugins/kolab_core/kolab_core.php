<?php

/**
 * Kolab core library
 * 
 * Plugin to setup a basic environment for interaction with a Kolab server.
 * Other Kolab-related plugins will depend on it and can use the static API rcube_core
 *
 * This is work-in-progress for the Roundcube+Kolab integration.
 *
 * @author Thomas Bruederli <roundcube@gmail.com>
 * 
 */
class kolab_core extends rcube_plugin
{
    /**
     * Required startup method of a Roundcube plugin
     */
    public function init()
    {
        // load local config
        $this->load_config();
        
        // extend include path to load bundled Horde classes
        $include_path = $this->home . PATH_SEPARATOR . ini_get('include_path');
        set_include_path($include_path);
    }
    
}

