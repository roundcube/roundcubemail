<?php

/**
 * Copy a new users identity and settings from a nearby Squirrelmail installation
 *
 * Currently only file-based data storage of Squirrelmail is supported.
 *
 * @version 1.1
 * @author Thomas Bruederli
 */
class squirrelmail_usercopy extends rcube_plugin
{
	public $task = 'login|settings';

	private $prefs = null;
	private $abook = array();

	public function init()
	{
		$this->add_hook('create_user', array($this, 'create_user'));
		$this->add_hook('create_identity', array($this, 'create_identity'));
	}

	public function create_user($p)
	{
		// read prefs and add email address
		$this->read_squirrel_prefs($p['user']);
		if ($this->prefs['email_address'])
			$p['user_email'] = $this->prefs['email_address'];

		return $p;
	}

	public function create_identity($p)
	{
		$rcmail = rcmail::get_instance();

		// only execute on login
		if ($rcmail->task == 'login' && $this->prefs) {
			if ($this->prefs['full_name'])
				$p['record']['name'] = $this->prefs['full_name'];
			if ($this->prefs['email_address'])
				$p['record']['email'] = $this->prefs['email_address'];
			if ($this->prefs['signature'])
				$p['record']['signature'] = $this->prefs['signature'];
                        if ($this->prefs['reply-to']) 
                                $p['record']['reply-to'] = $this->prefs['reply-to']; 		

			// copy address book
			$contacts = $rcmail->get_address_book(null, true);
			if ($contacts && count($this->abook)) {
				foreach ($this->abook as $rec)
					$contacts->insert($rec, true);
			}
			
			// mark identity as complete for following hooks
			$p['complete'] = true;
		}

		return $p;
	}

	private function read_squirrel_prefs($uname)
	{
		$this->load_config();
		$rcmail = rcmail::get_instance();

		if ($srcdir = $rcmail->config->get('squirrelmail_data_dir')) {
			$prefsfile = slashify($srcdir) . $uname . '.pref';
			$abookfile = slashify($srcdir) . $uname . '.abook';
			$sigfile = slashify($srcdir) . $uname . '.sig';

			if (is_readable($prefsfile)) {
				$this->prefs = array();
				foreach (file($prefsfile) as $line) {
					list($key, $value) = explode('=', $line);
					$this->prefs[$key] = utf8_encode(rtrim($value));
				}

				// also read signature file if exists
				if (is_readable($sigfile)) {
					$this->prefs['signature'] = utf8_encode(file_get_contents($sigfile));
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
	}

}

?>
