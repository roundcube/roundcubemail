<?php

/**
 * Inject a toggle switch into the login form that makes the session live for a
 * configured number of days (instead of only for the session).
 */

class persisted_login extends rcube_plugin
{
    private $rc;
    private $days;

    public function onload(): void
    {
        $this->rc = rcmail::get_instance();
        $this->load_config();
        $configured_days = $this->rc->config->get('persisted_login_days');
        if (!is_int($configured_days)) {
            $configured_days = 7;
        }
        // Make sure the value is in the range 1..365.
        $this->days = min(max(1, $configured_days), 365);
        $this->rc->config->set('session_lifetime', $this->days * 24 * 60);
    }

    #[\Override]
    public function init(): void
    {
        $this->rc->output->set_env('persisted_login_days', $this->days);
        $this->add_hook('template_object_loginform', [$this, 'login_page_template']);
        $this->add_hook('login_after', [$this, 'login_success']);
    }

    public function login_page_template(array $args): array
    {
        $this->add_texts('localization', true);
        $this->include_script('persisted_login.js');
        return $args;
    }

    public function login_success(array $args): array
    {
        if (empty($_POST['_persisted_login'])) {
            return $args;
        }

        $sessCookieName = $this->rc->config->get('session_name') ?: 'roundcube_sessid';
        $authCookieName = $this->rc->config->get('session_auth_name') ?: 'roundcube_sessauth';
        $sessCookieValue = session_id();
        $authCookieValue = (isset($_COOKIE[$authCookieName])) ? $_COOKIE[$authCookieName] : 'Error: Auth Cookie Missing';
        $exp = time() + ($this->days * 24 * 60 * 60);
        rcube_utils::setcookie($sessCookieName, $sessCookieValue, $exp);
        rcube_utils::setcookie($authCookieName, $authCookieValue, $exp);
        return $args;
    }
}
