<?php

/**
 * Sample plugin that adds a new tab to the settings section
 * to display some information about the current user
 */
 
// error_log(print_r($variable, TRUE)); // I only have this here for development
 
class account_details extends rcube_plugin
{
    public $task    = 'settings';

    function init()
    {
        $this->rc = rcube::get_instance();
        $this->add_texts('localization/', array('account_details'));
        $this->register_action('plugin.account_details', array($this, 'infostep'));
        $this->add_hook('settings_actions', array($this, 'settings_actions'));
		$this->include_stylesheet($this->local_skin_path() .'/account_details.css');
		require($this->home . '/lib/mail_count.php');
		require($this->home . '/lib/Browser.php');
		require($this->home . '/lib/OS.php');
		require($this->home . '/lib/CPU_usage.php');
		require($this->home . '/lib/listplugins.php');
		require($this->home . '/lib/getip.php');
        
    }
    
		private function _load_config()
	{
		
		$fpath_config_dist	= $this->home . '/config.inc.php.dist';
		$fpath_config 		= $this->home . '/config.inc.php';
		
		if (is_file($fpath_config_dist) and is_readable($fpath_config_dist))
			$found_config_dist = true;
		if (is_file($fpath_config) and is_readable($fpath_config))
			$found_config = true;
		
		if ($found_config_dist or $found_config) {
			ob_start();

			if ($found_config_dist) {
				include($fpath_config_dist);
				$account_details_config_dist = $account_details_config;
			}
			if ($found_config) {
				include($fpath_config);
			}
			
			$config_array = array_merge($account_details_config_dist, $account_details_config);
			$this->config = $config_array;
			ob_end_clean();
		} else {
			raise_error(array(
				'code' => 527,
				'type' => 'php',
				'message' => "Failed to load Account Details plugin config"), true, true);
		}
	}
	
    function infostep()
    {
		
        $this->api->output->add_handler('plugin.body', array($this, 'infohtml'));
		$this->api->output->set_pagetitle($this->gettext('account_details'));
		$this->api->output->send('plugin');
		
    }
    
    function settings_actions($args)
    {
        // register as settings action
        $args['actions'][] = array(
            'action'   => 'plugin.account_details',
            'class'    => 'account_details',
            'label'    => 'account_details',
            'title'    => 'account_details_title',
            'domain'   => 'account_details',
        );

        return $args;
    }

    function infohtml()
    {	
		$this->_load_config();
		$user = $this->rc->user;
		
			// Set commalist variables from config and language file
		if ($this->config['pn_newline']) {
			$pn_newline = true;
			$pn_parentheses = false;
		} else {
			$pn_newline = false;
			$pn_parentheses = true;
		}
				
		// Grabs Browser Info
	$browser = new Browser();
	
		// Grabs Screen Resolution Info	
	$width = " <script>document.write(screen.width); </script>";
	$height = " <script>document.write(screen.height); </script>";
	
		// Server Uptime Info
	$uptime = shell_exec("cut -d. -f1 /proc/uptime");
	$days = floor($uptime/60/60/24);
	$hours = $uptime/60/60%24;
	$mins = $uptime/60%60;
	$secs = $uptime%60;
	
	$domainpart = $temp[1] ? $temp[1] : 'default';
	$url_box_length = $this->config['urlboxlength'];
	
	$table = new html_table(array('cols' => 2, 'cellpadding' => 0, 'cellspacing' => 0, 'class' => 'account_details'));
    $table = new html_table(array('class' => 'account_details', 'cols' => 2, 'cellpadding' => 0, 'cellspacing' => 0));
	
    $table->add('title', html::tag('h4', null, '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('userdet') . ':')));
    $table->add('', '');
	
	$identity = $user->get_identity();
	if ($this->config['enable_userid']) {
	$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('userid') . ':'));
    $table->add('value', rcube_utils::rep_specialchars_output($user->ID));
	}
	
    $table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('defaultidentity') . ':'));
    $table->add('value', rcube_utils::rep_specialchars_output($identity['name'] . ' '));
	
    $table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('email') . ':'));
    $table->add('value', rcube_utils::rep_specialchars_output($identity['email']));
	
    $date_format = $this->rc->config->get('date_format', 'm/d/Y') . ' ' . $this->rc->config->get('time_format', 'H:i');
    if(date('Y', strtotime($user->data['created'])) > 1970){
      $created = new DateTime($user->data['created']);
	  if ($this->config['display_create']) {
      $table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('created') . ':'));
      $table->add('', rcube_utils::rep_specialchars_output(date_format($created, $date_format)));
		}
	}
	$lastlogin = new DateTime($user->data['last_login']);
	if ($this->config['display_lastlogin']) {
    $table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('last') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('login') . ':')));
    $table->add('', rcube_utils::rep_specialchars_output(date_format($lastlogin, $date_format)));
	}
 				
			$this->rc->storage_connect(true);
			$imap = $this->rc->imap;
		
			$quota = $imap->get_quota();
			
		if (!empty($this->config['enable_quota'])) {			
		if ('quota') {
				$quotatotal = $this->rc->show_bytes($quota['total'] * 1024);
				$quotaused = $this->rc->show_bytes($quota['used'] * 1024) . ' (' . $quota['percent'] . '%)';

		if ($quota && ($quota['total']==0 && $this->rc->config->get('quota_zero_as_unlimited'))) {
				$quotatotal = 'unlimited';
			}
				
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('storagequota') . ':'));
			$table->add('value', $quotatotal);
				
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('usedstorage') . ':'));
			$table->add('value', $quotaused);
				}
			}
			
		if (!empty($this->config['enable_ip'])) {
		if (!empty($this->config['useipinfo'])) {
			$locationdetails = json_decode(file_get_contents("http://ipinfo.io/"));
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('ipaddress') . ':'));
			$table->add('value', $locationdetails->ip . '&nbsp;-&nbsp;' . $locationdetails->city . ',&nbsp;' . $locationdetails->region . '&nbsp; (' . $locationdetails->country . ')');
			} else {			
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('ipaddress') . ':'));
			$table->add('value', get_client_ip_server());
				}
			}
			
		if (!empty($this->config['enable_support'])) {				
			$support_url = $this->rc->config->get('support_url');
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('support') . ':'));
			$table->add('value', html::tag('a', array('href' => $support_url, 'title' => $this->gettext('supporturl'), 'target' => '_blank'), $support_url));
			}
		
		if (!empty($this->config['enable_osystem'])) {
			$table->add('title', html::tag('h4', null, '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('usystem') . ':')));
			$table->add('', '');
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('os') . ':'));
			$table->add('value', os_info($uagent));
			
		if (!empty($this->config['enable_resolution'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('resolution') . ':'));
			$table->add('value', $width . ' x' . $height);			
			}
			
		if (!empty($this->config['enable_browser'])) {
			$table->add('top', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('webbrowser') . ': <br/>' . '&nbsp;&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('version') . ': <br/>' . '&nbsp;&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' .  rcube_utils::rep_specialchars_output($this->gettext('browser-user-agent') . ': <br/>' . ''))));
			$table->add('value', $browser);	
				}
			}
			
			
		if (!empty($this->config['enable_mailbox'])) {
			$table->add('title', html::tag('h4', null, '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('mailboxdetails') . ':')));
			$table->add('', '');
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('inbox') . ':'));
			$table->add('value', $imap->count('INBOX', 'UNSEEN') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('unread') . '&nbsp;-&nbsp;' . $imap->count('INBOX', 'ALL') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('total') . '&nbsp;-&nbsp;' . round($imap->folder_size('INBOX')/ 1024 / 1024,2) . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('MB')))));
			
		if (!empty($this->config['enable_drafts'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('drafts') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('folder') . ':')));
			$table->add('value', $imap->count('INBOX.Drafts', 'UNSEEN') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('unread') . '&nbsp;-&nbsp;' . $imap->count('INBOX.Drafts', 'ALL') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('total') . '&nbsp;-&nbsp;' . round($imap->folder_size('INBOX.Drafts')/ 1024 / 1024,2) . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('MB')))));
			}
		if (!empty($this->config['enable_sent'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('sent') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('folder') . ':')));
			$table->add('value', $imap->count('INBOX.Sent', 'UNSEEN') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('unread') . '&nbsp;-&nbsp;' . $imap->count('INBOX.Sent', 'ALL') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('total') . '&nbsp;-&nbsp;' . round($imap->folder_size('INBOX.Sent')/ 1024 / 1024,2) . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('MB')))));
			}
		if (!empty($this->config['enable_trash'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('trash') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('folder') . ':')));
			$table->add('value', $imap->count('INBOX.Trash', 'UNSEEN') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('unread') . '&nbsp;-&nbsp;' . $imap->count('INBOX.Trash', 'ALL') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('total') . '&nbsp;-&nbsp;' . round($imap->folder_size('INBOX.Trash')/ 1024 / 1024,2) . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('MB')))));
			}
		if (!empty($this->config['enable_junk'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('junk') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('folder') . ':')));
			$table->add('value', $imap->count('INBOX.Junk', 'UNSEEN') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('unread') . '&nbsp;-&nbsp;' . $imap->count('INBOX.Junk', 'ALL') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('total') . '&nbsp;-&nbsp;' . round($imap->folder_size('INBOX.Junk')/ 1024 / 1024,2) . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('MB')))));
			}
		if (!empty($this->config['enable_archive'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('archive') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('folder') . ':')));
			$table->add('value', $imap->count('INBOX.Archive', 'UNSEEN') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('unread') . '&nbsp;-&nbsp;' . $imap->count('INBOX.Archive', 'ALL') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('total') . '&nbsp;-&nbsp;' . round($imap->folder_size('INBOX.Archive')/ 1024 / 1024,2) . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('MB')))));
				}
			}
			
		if (!empty($this->config['enable_server_os'])) {
		if (!empty($this->config['location'])) {
			$table->add('title', html::tag('h4', null, '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('server') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('details')  . ':'))));
			$table->add('', '');
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('server') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('location')  . ':')));
			$table->add('value', $this->config['location']);
			}
			
		if (!empty($this->config['enable_server_os_name'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('server') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('os')  . ':')));
			$table->add('value', php_uname ("s"));
			}
			
		if (!empty($this->config['enable_server_os_rel'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('server') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('os')  . ':')));
			$table->add('value', php_uname ("s") . " - " . php_uname ("r"));
			}
			
		if (!empty($this->config['enable_server_os_ver'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('server') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('os')  . ':')));
			$table->add('value', php_uname ("s") . " - " . php_uname ("r") . " - " . php_uname ("v"));
			}
			
		if (!empty($this->config['enable_server_memory'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('server') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('memory')  . ':')));
			$table->add('value', $this->rc->show_bytes(round(memory_get_usage(),2)) . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('used')));
			}
			
		if (!empty($this->config['enable_server_cpu'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('server') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('cpu')  . ':')));
			$table->add('value', get_server_cpu_usage() . ' % Used');
			}
			
		if (!empty($this->config['enable_server_uptime'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('server') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('uptime') . ':')));
			$table->add('value', rcube_utils::rep_specialchars_output($days) . '&nbsp;' .  $this->gettext('days') . '&nbsp;' .  rcube_utils::rep_specialchars_output($hours) . '&nbsp;' .  $this->gettext('hours') . '&nbsp;' .  rcube_utils::rep_specialchars_output($mins) . '&nbsp;' .  $this->gettext('minutes') . '&nbsp;' .  rcube_utils::rep_specialchars_output($secs) . '&nbsp;' .  $this->gettext('seconds'));
			}
		
		if ($this->config['display_php_version']) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' .  rcube_utils::rep_specialchars_output($this->gettext('php_version') . ':'));
			$table->add('value', PHP_VERSION);
			}
		
		if ($this->config['display_http_server']) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('htmlserver') . ':'));
			$table->add('value', $_SERVER['SERVER_SOFTWARE']);
			}
		
		if (!empty($this->config['display_admin_email'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('server') . '&nbsp;' .  'Admin ' . rcube_utils::rep_specialchars_output($this->gettext('email') . ':')));
			$table->add('value', html::tag('a', array('href' => 'mailto:' . $_SERVER['SERVER_ADMIN'], 'title' => $this->gettext('contactadmin')), $_SERVER['SERVER_ADMIN']));
			}
			
		// Add custom fields
		$this->_custom_fields('customfields_server');
		
		// Port numbers - initial checking and generating of detailed information
		
		if ($this->config['spa_support_smtp'] and $this->config['spa_support_imap'] and $this->config['spa_support_pop']) {
		// SPA supported on all three protocols. We set this variable and will use it later to print the info on a row.
			$spa_all = true;
		} else {
		// SPA not supported on all three protocols. Instead of printing on a row we append to each supportet protocol
			if ($this->config['spa_support_smtp'])
				$smtp_notes_array_regularonly[] = $this->gettext('spaauthsupported');
			if ($this->config['spa_support_imap'])
				$imap_notes_regular = ' (' . $this->gettext('spaauthsupported') . ')';
			if ($this->config['spa_support_pop'])
				$pop_notes_regular = ' (' . $this->gettext('spaauthsupported') . ')';
		} 
		

		if (!empty($this->config['smtp_auth_required_always'])) {
			$smtp_notes_array_all[] = $this->gettext('authrequired');
		} else {
		// SMTP auth is not always enabled, we have to print something based on
		// the next config settings
		
			// Set the correct "SMTP after *" based on conf combination
			if ($this->config['smtp_after_pop'] and !$this->config['smtp_after_imap'])
				$smtp_after_text = $this->gettext('smtpafterpop');
			elseif (!$this->config['smtp_after_pop'] and $this->config['smtp_after_imap'])
				$smtp_after_text = $this->gettext('smtpafterimap');
			elseif ($this->config['smtp_after_pop'] and $this->config['smtp_after_imap'])
				$smtp_after_text = $this->gettext('smtpafterpopimap');
		
			if ($this->config['smtp_auth_required_else']) {
			// If SMTP auth is required unless something
				if ($this->config['smtp_relay_local'] and !$this->config['smtp_after_pop'] and !$this->config['smtp_after_imap']) {
					$smtp_notes_array_all[] = $this->gettext('authrequired_local');
				} else if ($this->config['smtp_relay_local'] and ($this->config['smtp_after_pop'] || !$this->config['smtp_after_imap'])) {
					$smtp_notes_array_all[] = str_replace("%s", $smtp_after_text, $this->gettext('authrequired_local_smtpafter'));
				} else if (!$this->config['smtp_relay_local'] and ($this->config['smtp_after_pop'] || !$this->config['smtp_after_imap'])) {
					$smtp_notes_array_all[] = str_replace("%s", $smtp_after_text, $this->gettext('authrequired_smtpafter'));
				}
			} else {
			// If SMTP auth is not required, but some other infp may be given
				if ($this->config['smtp_relay_local'])
					$smtp_notes_array_all[] = $this->gettext('openrelaylocal');
				if ($smtp_after_text)
					$smtp_notes_array_all[] = $smtp_after_text;
			}			
		}
		
		// We summarize the correct arrays
		$smtp_notes_array_regular = array_merge((array)$smtp_notes_array_all, (array)$smtp_notes_array_regularonly);
		$smtp_notes_array_encrypted = array_merge((array)$smtp_notes_array_all, (array)$smtp_notes_array_encryptedonly);
		
		// If we have some info in the SMTP information arrays, make them ready for printing
		if (!empty($smtp_notes_array_regular))
			$smtp_notes_regular = ucfirst($this->_separated_list($smtp_notes_array_regular, $and = false, $sentences = true, $commalist_ucfirst, $pn_parentheses, $pn_newline));
		if (!empty($smtp_notes_array_regular))
			$smtp_notes_encrypted = ucfirst($this->_separated_list($smtp_notes_array_encrypted, $and = false, $sentences = true, $commalist_ucfirst, $pn_parentheses, $pn_newline));
			
		
		// Port numbers - regular
		
		if (!empty($this->config['port_smtp'] or !empty($this->config['port_imap']) or !empty($this->config['port_pop']) or count($this->config['customfields_regularports']) > 0)) {
		
			$table->add('title', html::tag('h4', null, '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('portnumbers') . ' - ' . $this->gettext('portnumbersregular'))));
			$table->add('', '');
			
			if ($spa_all) {	
			// SPA supported for all three protocols. We print it on a row.
				$table->add(array('colspan' => 2, 'class' => 'categorynote'), ucfirst($this->gettext('spaauthsupported')));
				$table->add_row();
			}
			
			if (!empty($this->config['port_smtp'])) {
				$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('smtp') . ':'));
				$table->add('value', $this->_host_replace($this->config['hostname_smtp']) . ':' . $this->_separated_list($this->config['port_smtp'], $and = true) . $smtp_notes_regular);
			}
		
			if (!empty($this->config['port_imap'])) {
				$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('imap') . ':'));
				$table->add('value', $this->_host_replace($this->config['hostname_imap']) . ':' . $this->_separated_list($this->config['port_imap'], $and = true) . $imap_notes_regular);
			}
		
			if (!empty($this->config['port_pop'])) {
				$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('pop') . ':'));
				$table->add('value', $this->_host_replace($this->config['hostname_pop']) . ':' . $this->_separated_list($this->config['port_pop'], $and = true) . $pop_notes_regular);
			}
		
			// Add custom fields
			$this->_custom_fields('customfields_regularports');
		
		}
		
		// Port numbers - encrypted
		
		if (!empty($this->config['port_smtp-ssl'] or !empty($this->config['port_imap-ssl']) or !empty($this->config['port_pop-ssl']) or count($this->config['customfields_encryptedports']) > 0)) {

			$portnumbers_regular_header =  html::tag('h4', null, '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('portnumbers') . ' - ' . $this->gettext('portnumbersencrypted')));
			if ($this->config['recommendssl'])
				$portnumbers_regular_header .= ' ' . html::tag('div', array('style' => 'color:red;'), '&nbsp;' .  $this->gettext('recommended'));

			$table->add(array('colspan' => 2, 'class' => 'header'), $portnumbers_regular_header);
			$table->add_row();
			
			if (!empty($this->config['port_smtp-ssl'])) {
				$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('smtp-ssl') . ':'));
				$table->add('value', $this->_host_replace($this->config['hostname_smtp']) . ':' . $this->_separated_list($this->config['port_smtp-ssl'], $and = true) . $smtp_notes_encrypted);
			}
		
			if (!empty($this->config['port_imap-ssl'])) {
				$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('imap-ssl') . ':'));
				$table->add('value', $this->_host_replace($this->config['hostname_imap']) . ':' . $this->_separated_list($this->config['port_imap-ssl'], $and = true) . $imap_notes_encrypted);
			}
		
			if (!empty($this->config['port_pop-ssl'])) {
				$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('pop-ssl') . ':'));
				$table->add('value', $this->_host_replace($this->config['hostname_pop']) . ':' . $this->_separated_list($this->config['port_pop-ssl'], $and = true) . $pop_notes_encrypted);
			}

			// Add custom fields
			$this->_custom_fields('customfields_encryptedports');
			
		}
	}

		if ($this->config['display_rc']) {
		$table->add('title', html::tag('h4', null, '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('rcdetails') . ':')));
		$table->add('', '');
		
		$rc_url = $this->gettext('version');
		if ($this->config['display_rc_version']) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('currver') . ':'));
			$table->add('value', rcube_utils::rep_specialchars_output($this->gettext('roundcube') . '<span style="font-weight:bold">&nbsp;v' . RCMAIL_VERSION . '</span>'));
		}
		
		$rc_latest = $this->_print_file_contents($this->config['rc_latest']);
		if ($this->config['display_rc_release']) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('latestversion') . ':'));
			$table->add('value', rcube_utils::rep_specialchars_output($this->gettext('roundcube') . '<span style="font-weight:bold">&nbsp;v' . $rc_latest . '</span>&nbsp;' . $this->gettext('is_available'). html::tag('a', array('href' => 'https://roundcube.net/download', 'title' => $this->gettext('downloadupdate'), 'target' => '_blank'), $this->gettext('download')) . '!'));
		}
		
		$webmail_url = $this->_host_replace($this->config['webmail_url']);
		if (!empty($this->config['hostname'])) {
			$table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('web_url') . ':'));
			$table->add('value', html::tag('a', array('href' => $webmail_url, 'title' => $this->gettext('web_url_alt'), 'target' => '_top'), $webmail_url));
		}
		
		if ($this->config['rc_pluginlist']) {
			$table->add('top', '&nbsp;&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('installedplugins') . ':'));
			$table->add('value', rcmail_ad_plugin_list($attrib));
		}
	}
		
	if (!empty($this->config['enable_dav_urls'])) {
    $cals = array();
    $user = $username;
    if(class_exists('calendar')){
      $query = 'SELECT user_id, caldav_user, caldav_pass, caldav_url, name from ' . $this->rc->db->table_name('caldav_calendars') . ' WHERE user_id=?';
      $sql_result = $this->rc->db->query($query, $this->rc->user->ID);
      while ($sql_result && ($sql_arr = $this->rc->db->fetch_assoc($sql_result))) {
        $cals[$sql_arr['name']] = $sql_arr;
      }
    }
    if(count($cals) > 0){
      $i ++;
      $table->add('title', html::tag('h4', null, '&nbsp;' . $this->gettext('calendars') . ':&nbsp;&sup' . $i . ';'));
      $table->add('', '');
      ksort($cals);
      $repl = $this->rc->config->get('caldav_url_replace', false);
      foreach($cals as $key => $cal){
        $temp = explode('?', $cal['caldav_url'], 2);
        $url = slashify($temp[0]) . ($temp[1] ? ('?' . $temp[1]) : '');
         if(is_array($repl)){
          foreach($repl as $key => $val){
            $url = str_replace($key, $val, $url);
          }
        }
        $table->add('title','&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . $key);
        $table->add('', html::tag('input', array('id' => $url, 'class' => 'account_details', 'value' => $url, 'onclick' => 'this.setSelectionRange(0, this.value.length)', 'name' => $key,  'type' => 'text', 'size' => $url_box_length)));
      }
      if($clients == '' && $this->rc->config->get('account_details_show_tutorial_links', true)){
        $clients = ('');
      }
      if($i > 0 && $this->rc->config->get('account_details_show_tutorial_links', true)){
        $clients .= html::tag('hr') . '&nbsp;&sup' . $i . ';&nbsp;' . sprintf($this->gettext('clients'), $this->gettext('caldav')) . ':' . html::tag('br') . '&nbsp;&nbsp;- ' . html::tag('a', array('href' => 'https://www.mozilla.org/en-US/thunderbird/all.html', 'target' => '_blank'), $this->gettext('thunderbird'));
        $clients .= ' + ' . html::tag('a', array('href' => 'https://addons.mozilla.org/en-US/thunderbird/addon/lightning/', 'target' => '_blank'), $this->gettext('lightning'));
        $url = $this->rc->config->get('caldav_thunderbird','../tutorials/thunderbird-caldav');
        $clients .= html::tag('a', array('href' => $url, 'target' =>'_blank'), html::tag('div', array('style' => 'display:inline;float:right;'), $this->gettext('thunderbird') . '&nbsp;' . $this->gettext('tutorial')));
        $url = $this->rc->config->get('caldav_android_app','https://play.google.com/store/apps/details?id=org.dmfs.caldav.lib&hl=en');
        $clients .= html::tag('br') . '&nbsp;&nbsp;- ' . html::tag('a', array('href' => 'http://www.android.com/', 'target' => '_blank'), $this->gettext('android')) . ' + ' . html::tag('a', array('href' => $url, 'target' => '_blank'), $this->gettext('caldavsync'));
        $url = $this->rc->config->get('caldav_android','../tutorials/android-caldav');
        $clients .= html::tag('a', array('href' => $url, 'target' =>'_blank'), html::tag('div', array('style' => 'display:inline;float:right;'), $this->gettext('android') . '&nbsp;' . $this->gettext('tutorial')));
        $url = $this->rc->config->get('caldav_iphone','../tutorials/iphone-caldav');
        $clients .= html::tag('br') . '&nbsp;&nbsp;- ' . html::tag('a', array('href' => 'http://www.apple.com/iphone/', 'target' => '_blank'), $this->gettext('iphone')) . html::tag('a', array('href' => $url, 'target' => '_blank'), html::tag('div', array('style' => 'display:inline;float:right;'), $this->gettext('iphone') . '&nbsp;' . $this->gettext('tutorial'))) . html::tag('br') . html::tag('br');
      }
    }
    $addressbooks = array();
    if(class_exists('carddav')){
      $query = 'SELECT user_id, username, password, url, name from ' . $this->rc->db->table_name('carddav_addressbooks') . ' WHERE user_id=?';
      $sql_result = $this->rc->db->query($query, $this->rc->user->ID);
      while ($sql_result && ($sql_arr = $this->rc->db->fetch_assoc($sql_result))) {
        $addressbooks[$sql_arr['name']] = $sql_arr;
      }
    }
    if(count($addressbooks) > 0){
      $i ++;
      $table->add('title', html::tag('h4', null, '&nbsp;' . $this->gettext('addressbook') . ':&nbsp;&sup' . $i . ';'));
      $table->add('', '');
      ksort($addressbooks);
      $repl = $this->rc->config->get('carddav_url_replace', false);
      foreach($addressbooks as $key => $addressbook){
        $temp = explode('?', $addressbook['url'], 2);
        $url = slashify($temp[0]) . ($temp[1] ? ('?' . $temp[1]) : '');
         if(is_array($repl)){
          foreach($repl as $key1 => $val){
            $url = str_replace($key1, $val, $url);
          }
        }
        $table->add('title', '&nbsp;' .  $this->config['bulletstyle'] . '&nbsp;' . $key);
        $table->add('', html::tag('input', array('id' => $url, 'class' => 'account_details', 'value' => $url, 'onclick' => 'this.setSelectionRange(0, this.value.length)', 'name' => $key,  'type' => 'text', 'size' => $url_box_length)));
      }
      if($clients == '' && $this->rc->config->get('account_details_show_tutorial_links', true)){
        $clients = html::tag('hr');
      }
      if($i > 0 && $this->rc->config->get('account_details_show_tutorial_links', true)){
        $clients .= '&nbsp;&sup' . $i . ';&nbsp;' . sprintf($this->gettext('clients'), $this->gettext('carddav')) . ':' . html::tag('br') . '&nbsp;&nbsp;- ' . html::tag('a', array('href' => 'http://www.mozilla.org/en-US/thunderbird/all.html', 'target' => '_blank'), $this->gettext('thunderbird'));
        $url = $this->rc->config->get('carddav_thunderbird','../tutorials/thunderbird-carddav');
        $clients .= ' + ' . html::tag('a', array('href' => 'https://sogo.nu/download.html#/frontends', 'target' => '_blank'), $this->gettext('sogoconn')) . html::tag('a', array('href' => $url, 'target' =>'_blank'), html::tag('div', array('style' => 'display:inline;float:right;'), 'Thunderbird ' . $this->gettext('tutorial')));
        $url = $this->rc->config->get('carddav_android_app','https://play.google.com/store/apps/details?id=org.dmfs.carddav.sync&hl=en');
        $clients .= html::tag('br') . '&nbsp;&nbsp;- ' . html::tag('a', array('href' => 'http://www.android.com/', 'target' => '_blank'), $this->gettext('android')) . ' + ' . html::tag('a', array('href' => $url, 'target' => '_blank'), $this->gettext('carddavsync'));
        $url = $this->rc->config->get('carddav_android_app_editor','https://play.google.com/store/apps/details?id=org.dmfs.android.contacts&hl=en');
        if($url){
          $clients .=  ' + ' . html::tag('a', array('href' => $url, 'target' => '_blank'), $this->gettext('contacteditor'));
        }
			$url = $this->rc->config->get('carddav_android','../tutorials/android-carddav');
			$clients .= html::tag('a', array('href' => $url, 'target' =>'_blank'), html::tag('div', array('style' => 'display:inline;float:right;'), $this->gettext('android') . '&nbsp;' . $this->gettext('tutorial')));
			$url = $this->rc->config->get('carddav_iphone','../tutorials/iphone-carddav');
			$clients .= html::tag('br') . '&nbsp;&nbsp;- ' . html::tag('a', array('href' => 'http://www.apple.com/iphone/', 'target' => '_blank'), $this->gettext('iphone')) . html::tag('a', array('href' => $url, 	'target' => '_blank'), html::tag('div', array('style' => 'display:inline;float:right;'), $this->gettext('iphone') . '&nbsp;' . $this->gettext('tutorial'))) . html::tag('br') . html::tag('br');
			}
		}
	}
	
	$out = html::div(array('class' => 'main'), html::div(array('class' => 'boxtitle'), $this->gettext('account_details') . ' for ' . $identity['name'])) . html::div(array('class' => 'scroller'), $table->show() . $clients);
	
			if ($this->config['enable_custombox']) {
			
			$out .= html::div(array('class' => 'settingsbox settingsbox-account_details-custom'), html::div(array('class' => 'boxtitle'), $this->config['custombox_header']) . html::div(array('class' => 'boxcontent'), $this->_print_file_contents($this->config['custombox_file'])));
		} 
	
    return $out;
  }

	private function _custom_fields($arrayname)
	{
	// Add custom fields from a defines array name
		
	global $table;
	
	if (is_array($this->config[$arrayname]) > 0) {
			
			foreach ($this->config[$arrayname] as $key => $arrayvalue) {
				
				$coltype = $arrayvalue['type'];
				$coltext = $arrayvalue['text'];
				
			    if ($coltype == 'header' or $coltype == 'wholeline') {
					$table->add(array('colspan' => 2, 'class' => $coltype), $coltext);
					$table->add_row();
				} elseif ($coltype == 'title' or $coltype == 'value') {
					$table->add($coltype, $coltext);
				}
				
				$coltype = '';
				$coltext = '';
				
			}
		}
		
		return false;
	}
	
	
	private function _separated_list($array, $and = false, $sentences = false, $ucfirst = false, $parentheses = false, $newline = false)
	{
	// Return array as a separated list
		$str = '';
		$size = count($array);
		$i = 0;
		if ($sentences)
			$separator = ". ";
		else
			$separator = ", ";
		
		if ($parentheses and $newline)
			$str .= '<span class="fieldnote-parentheses fieldnote-newline">(';
		elseif ($parentheses)
			$str .= '<span class="fieldnote-parentheses"> (';
		elseif ($newline)
			$str .= '<span class="fieldnote-newline">';
		
		
		foreach ($array as $item) {
			if ($i == 0 and $ucfirst)
				$item = ucfirst($item);
			$str .= $item;
			$i++;
			if ($i < $size-1)
				$str .= $separator;
			elseif ($i == $size-1) {
				// final separator, and or comma?
				if ($and)
					$str .= ' ' . $this->gettext('and') . ' ';
				else 
					$str .= $separator;
				
			}
			if ($i == $size and $sentences and count($array) > 1)
				$str .= '.';
		}
		
		if ($parentheses and $newline)
			$str .= ')</span>';
		elseif ($parentheses)
			$str .= ')</span>';
		elseif ($newline)
			$str .= '</span>';
		
		return $str;
	}
	
	private function _print_file_contents($filename)
	{
	// Return contents of a file
	
	$filename = $filename;
	
		if (is_file($filename) and is_readable($filename)) {
			$handle = fopen($filename, "r");
			$contents = fread($handle, filesize($filename));
			fclose($handle);
			return $contents;
			/*include*/
		} else {
			return 'Could not output file ' . $filename;
		}
	}
	
	private function _host_replace($host) {
	// Does some replacements in a host string

		$user = $this->rc->user;

		$host = str_replace('%h', $user->data['mail_host'], $host);
		$host = str_replace('%s', $_SERVER['SERVER_NAME'], $host);

		if(empty($_SERVER['HTTPS']))
			$protocol = 'http';
		else 
			$protocol = 'https';	
		$host = str_replace('%p', $protocol, $host);
		
		$stripped_h_array = explode('.', $user->data['mail_host']);
		array_shift($stripped_h_array);
		$stripped_s_array = explode('.', $_SERVER['SERVER_NAME']);
		array_shift($stripped_s_array);
		$stripped_h = implode('.', $stripped_h_array);
		$stripped_s = implode('.', $stripped_s_array);
		$host = str_replace('%H', $stripped_h, $host);
		$host = str_replace('%S', $stripped_s, $host);
		
		return $host;
		
	}
	
	
}

?>
