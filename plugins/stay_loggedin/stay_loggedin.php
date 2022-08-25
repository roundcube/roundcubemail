<?php
class stay_loggedin extends rcube_plugin {

	private $rc;
	private $days;

	function onload() {
		$this->rc = rcmail::get_instance();
		$this->load_config();
		$this->days = (int)$this->rc->config->get('stay_loggedin_days', 0);
		if ($this->days < 1) $this->days = 0;
		if ($this->days > 365) $this->days = 365;
		if ($this->days) $this->rc->config->set('session_lifetime', $this->days * 24 * 60);
	}

	function init() {
		if (!$this->days) return;
		$this->rc->output->set_env('stay_loggedin_days', $this->days);
		$this->add_hook('template_object_loginform', array($this, 'loginPageTemplate'));
		$this->add_hook('login_after', array($this, 'loginSuccess'));
	}

	function loginPageTemplate($args) {
		$this->add_texts('localization', true);
		$this->include_script('stay_loggedin.js');
		return $args;
	}

	function loginSuccess($args) {
		if (!isset($_POST['_stay_loggedin']) || !$_POST['_stay_loggedin']) return $args;
		$sessCookieName = $this->rc->config->get('session_name');
		$authCookieName = $this->rc->config->get('session_auth_name');
		$sessCookieValue = session_id();
		$authCookieValue = $_COOKIE[$authCookieName];
		$exp = time() + ($this->days * 24 * 60 * 60);
		rcube_utils::setcookie($sessCookieName, $sessCookieValue, $exp);
		rcube_utils::setcookie($authCookieName, $authCookieValue, $exp);
		return $args;
	}

}
