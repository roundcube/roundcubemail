<?php

/**
 * DB based User-to-Email and Email-to-User lookup
 *
 * Add it to the plugins list in config/main.inc.php and set
 * SQL query to resolve user names and e-mail addresses from the database
 * %u will be replaced with the current username for login.
 * The query should select the user's e-mail address as first column
 * and optional identity data columns in specified order:
 *    name, organization, reply-to, bcc, signature, html_signature
 *
 * $rcmail_config['virtuser_query'] = '';
 *
 * @version 1.0
 * @author Aleksander Machniak
 */
class virtuser_query extends rcube_plugin
{
    private $query;
    private $app;

    function init()
    {
	    $this->app = rcmail::get_instance();
	    $this->query = $this->app->config->get('virtuser_query');

	    if ($this->query) {
	        $this->add_hook('user2email', array($this, 'user2email'));
//	        $this->add_hook('email2user', array($this, 'email2user'));
	    }
    }

    /**
     * User > Email
     */
    function user2email($p)
    {
	    $dbh = $this->app->get_dbh();

	    $sql_result = $dbh->query(preg_replace('/%u/', $dbh->escapeSimple($p['user']), $this->query));

	    while ($sql_arr = $dbh->fetch_array($sql_result)) {
	        if (strpos($sql_arr[0], '@')) {
		        if ($p['extended'] && count($sql_arr) > 1) {
		            $result[] = array(
			            'email' 	    => $sql_arr[0],
            			'name' 		    => $sql_arr[1],
			            'organization'  => $sql_arr[2],
            			'reply-to' 	    => $sql_arr[3],
			            'bcc' 		    => $sql_arr[4],
        			    'signature' 	=> $sql_arr[5],
		            	'html_signature' => (int)$sql_arr[6],
    		        );
		        }
		        else {
		            $result[] = $sql_arr[0];
		        }

		        if ($p['first'])
		            break;
	        }
	    }
	
	    $p['email'] = $result;

	    return $p;
    }

}
