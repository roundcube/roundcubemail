<?php

/**
 * DB based User-to-Email and Email-to-User lookup
 *
 * Add it to the plugins list in config/main.inc.php and set
 * SQL query to resolve user names and e-mail addresses from the database
 * %u will be replaced with the current username for login.
 * The query should select the user's e-mail address as first column
 * and optional identity name as second column
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
//	    $this->add_hook('email2user', array($this, 'email2user'));
	}
    }

    /**
     * User > Email
     */
    function user2email($p)
    {
	$dbh = $rcmail->get_dbh();

	$sql_result = $dbh->query(preg_replace('/%u/', $dbh->escapeSimple($p['user']), $this->query));

	while ($sql_arr = $dbh->fetch_array($sql_result)) {
	    if (strpos($sql_arr[0], '@')) {
		$result[] = ($p['extended'] && count($sql_arr) > 1) ? $sql_arr : $sql_arr[0];

		if ($p['first'])
		    return $result[0];
	    }
	}

	return $p;
    }

}
