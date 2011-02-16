<?php

/**
 * Copy a new users identity and settings from a nearby Squirrelmail installation
 *
 * @version 1.4
 * @author Thomas Bruederli, Johannes Hessellund, pommi, Thomas Lueder
 */
class squirrelmail_usercopy extends rcube_plugin
{
	public $task = 'login';

	private $prefs = null;
	private $identities_level = 0;
	private $abook = array();

	public function init()
	{
		$this->add_hook('user_create', array($this, 'create_user'));
		$this->add_hook('identity_create', array($this, 'create_identity'));
	}

	public function create_user($p)
	{
		$rcmail = rcmail::get_instance();

		// Read plugin's config
		$this->initialize();

		// read prefs and add email address
		$this->read_squirrel_prefs($p['user']);
		if (($this->identities_level == 0 || $this->identities_level == 2) && $rcmail->config->get('squirrelmail_set_alias') && $this->prefs['email_address'])
			$p['user_email'] = $this->prefs['email_address'];
		return $p;
	}

	public function create_identity($p)
	{
		$rcmail = rcmail::get_instance();

		// prefs are set in create_user()
		if ($this->prefs) {
			if ($this->prefs['full_name'])
				$p['record']['name'] = $this->prefs['full_name'];
			if (($this->identities_level == 0 || $this->identities_level == 2) && $this->prefs['email_address'])
				$p['record']['email'] = $this->prefs['email_address'];
			if ($this->prefs['___signature___'])
				$p['record']['signature'] = $this->prefs['___signature___'];
			if ($this->prefs['reply_to']) 
				$p['record']['reply-to'] = $this->prefs['reply_to']; 
			if (($this->identities_level == 0 || $this->identities_level == 1) && isset($this->prefs['identities']) && $this->prefs['identities'] > 1) {
				for ($i=1; $i < $this->prefs['identities']; $i++) {
					unset($ident_data);
					$ident_data = array('name' => '', 'email' => ''); // required data
					if ($this->prefs['full_name'.$i])
						$ident_data['name'] = $this->prefs['full_name'.$i];
					if ($this->identities_level == 0 && $this->prefs['email_address'.$i])
						$ident_data['email'] = $this->prefs['email_address'.$i];
					else
						$ident_data['email'] = $p['record']['email'];
					if ($this->prefs['reply_to'.$i])
						$ident_data['reply-to'] = $this->prefs['reply_to'.$i];
					if ($this->prefs['___sig'.$i.'___'])
						$ident_data['signature'] = $this->prefs['___sig'.$i.'___'];
					// insert identity
					$identid = $rcmail->user->insert_identity($ident_data);
				}
			}

			// copy address book
			$contacts = $rcmail->get_address_book(null, true);
			if ($contacts && count($this->abook)) {
				foreach ($this->abook as $rec) {
				    // #1487096 handle multi-address and/or too long items
				    $rec['email'] = array_shift(explode(';', $rec['email']));
                    if (check_email(rcube_idn_to_ascii($rec['email']))) {
                        $rec['email'] = rcube_idn_to_utf8($rec['email']);
    					$contacts->insert($rec, true);
			        }
			    }
			}

			// mark identity as complete for following hooks
			$p['complete'] = true;
		}

		return $p;
	}

	private function initialize()
	{
		$rcmail = rcmail::get_instance();

		// Load plugin's config file
		$this->load_config();

		// Set identities_level for operations of this plugin
		$ilevel = $rcmail->config->get('squirrelmail_identities_level');
		if ($ilevel === null)
			$ilevel = $rcmail->config->get('identities_level', 0);

		$this->identities_level = intval($ilevel);
	}

	private function read_squirrel_prefs($uname)
	{
		$rcmail = rcmail::get_instance();

		/**** File based backend ****/
		if ($rcmail->config->get('squirrelmail_driver') == 'file' && ($srcdir = $rcmail->config->get('squirrelmail_data_dir'))) {
			if (($hash_level = $rcmail->config->get('squirrelmail_data_dir_hash_level')) > 0) 
				$srcdir = slashify($srcdir).chunk_split(substr(base_convert(crc32($uname), 10, 16), 0, $hash_level), 1, '/');
			$prefsfile = slashify($srcdir) . $uname . '.pref';
			$abookfile = slashify($srcdir) . $uname . '.abook';
			$sigfile = slashify($srcdir) . $uname . '.sig';
			$sigbase = slashify($srcdir) . $uname . '.si';

			if (is_readable($prefsfile)) {
				$this->prefs = array();
				foreach (file($prefsfile) as $line) {
					list($key, $value) = explode('=', $line);
					$this->prefs[$key] = utf8_encode(rtrim($value));
				}

				// also read signature file if exists
				if (is_readable($sigfile)) {
					$this->prefs['___signature___'] = utf8_encode(file_get_contents($sigfile));
				}

				if (isset($this->prefs['identities']) && $this->prefs['identities'] > 1) {
					for ($i=1; $i < $this->prefs['identities']; $i++) {
						// read signature file if exists
						if (is_readable($sigbase.$i)) {
							$this->prefs['___sig'.$i.'___'] = utf8_encode(file_get_contents($sigbase.$i));
						}
					}
				}

				// parse addres book file
				if (filesize($abookfile)) {
					foreach(file($abookfile) as $line) {
						list($rec['name'], $rec['firstname'], $rec['surname'], $rec['email']) = explode('|', utf8_encode(rtrim($line)));
						if ($rec['name'] && $rec['email'])
							$this->abook[] = $rec;
					}
				}
			}
		} 
		/**** Database backend ****/
		else if ($rcmail->config->get('squirrelmail_driver') == 'sql') { 
			$this->prefs = array();

			/* connect to squirrelmail database */
			$db = new rcube_mdb2($rcmail->config->get('squirrelmail_dsn'));
			$db->db_connect('r'); // connect in read mode

			// $db->set_debug(true);

			/* retrieve prefs */
			$userprefs_table = $rcmail->config->get('squirrelmail_userprefs_table');
			$address_table = $rcmail->config->get('squirrelmail_address_table');
			$db_charset = $rcmail->config->get('squirrelmail_db_charset');

			if ($db_charset)
				$db->query('SET NAMES '.$db_charset);

			$sql_result = $db->query('SELECT * FROM '.$userprefs_table.' WHERE user=?', $uname); // ? is replaced with emailaddress

			while ($sql_array = $db->fetch_assoc($sql_result) ) { // fetch one row from result
				$this->prefs[$sql_array['prefkey']] = rcube_charset_convert(rtrim($sql_array['prefval']), $db_charset);
			}

			/* retrieve address table data */
			$sql_result = $db->query('SELECT * FROM '.$address_table.' WHERE owner=?', $uname); // ? is replaced with emailaddress

			// parse addres book
			while ($sql_array = $db->fetch_assoc($sql_result) ) { // fetch one row from result
				$rec['name']      = rcube_charset_convert(rtrim($sql_array['nickname']), $db_charset);
				$rec['firstname'] = rcube_charset_convert(rtrim($sql_array['firstname']), $db_charset);
				$rec['surname']   = rcube_charset_convert(rtrim($sql_array['lastname']), $db_charset);
				$rec['email']     = rcube_charset_convert(rtrim($sql_array['email']), $db_charset);
				$rec['note']      = rcube_charset_convert(rtrim($sql_array['label']), $db_charset);

				if ($rec['name'] && $rec['email'])
					$this->abook[] = $rec;
			}
		} // end if 'sql'-driver
	}

}
