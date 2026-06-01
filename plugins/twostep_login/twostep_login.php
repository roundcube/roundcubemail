<?php

/**
 * Two-step login
 *
 * Turns the Roundcube login form into a two-step flow: the username
 * (<input id="rcmloginuser">) is asked for first; only after the user
 * confirms it does the password field (<input id="rcmloginpwd">) get
 * revealed and the authentication form completed.
 *
 * This is a pure client-side (progressive enhancement) plugin: it adds a
 * small script + stylesheet to the login page and does not change the
 * server-side authentication flow in any way. With JavaScript disabled the
 * regular one-step login form is shown unchanged.
 *
 * @license GNU GPLv3+
 * @author Claude
 */
class twostep_login extends rcube_plugin
{
    /** @var rcmail */
    private $rc;

    /**
     * Plugin initialization.
     */
    #[\Override]
    public function init()
    {
        $this->rc = rcmail::get_instance();

        // Assets are injected from the 'render_page' hook rather than
        // 'template_object_loginform'. In modern skins (e.g. Elastic) the
        // latter does not reliably get the plugin's JS/CSS onto the login
        // page, so the client-side script never ran and the form was left
        // untouched. 'render_page' fires for the fully assembled 'login'
        // template on every skin, and it runs *before* write() emits the
        // page <head> and footer scripts, so includes added here are output.
        $this->add_hook('render_page', [$this, 'render_page']);

        // Pre-auth endpoint used to refresh the CSRF token (see startup()).
        // Unauthenticated requests never reach action_handler(), so this must
        // be served from the 'startup' hook.
        $this->add_hook('startup', [$this, 'startup']);
    }

    /**
     * Pre-dispatch hook: serve the token-refresh endpoint.
     *
     * When a user leaves the login page open long enough for the PHP session
     * to expire, the CSRF token rendered into the form goes stale and the
     * login is rejected as an invalid request — regardless of the credentials
     * entered. The two-step flow lets us fix this transparently: when the user
     * advances past the username step, the client calls this endpoint to
     * (re-)establish the temporary login session and obtain a fresh token, so
     * a valid token is in place by the time the password is submitted.
     *
     * @param array $args Hook arguments ('task', 'action')
     *
     * @return array
     */
    public function startup($args)
    {
        if (($args['action'] ?? '') === 'plugin.twostep_token') {
            // Mark this as the temporary login session (as login_form() does)
            // and hand back the matching request token.
            $_SESSION['temp'] = true;
            $token = $this->rc->get_request_token();
            $this->rc->session->write_close();

            if (!headers_sent()) {
                header('Content-Type: application/json; charset=UTF-8');
                header('X-Content-Type-Options: nosniff');
            }

            echo json_encode(['token' => $token]);
            exit;
        }

        return $args;
    }

    /**
     * Hook handler: runs once the page template has been parsed.
     *
     * The actual two-step transformation happens client-side (the password
     * step is shown/hidden in response to user interaction), so the server
     * side only needs to make sure the behaviour script, its styles and the
     * localized labels reach the login page.
     *
     * @param array $args Hook arguments ('template', 'content', 'write')
     *
     * @return array Hook arguments, unmodified
     */
    public function render_page($args)
    {
        // Only act on the login page.
        if (($args['template'] ?? '') !== 'login') {
            return $args;
        }

        // Localized labels, also exported to the JS client.
        $this->add_texts('localization/', ['next', 'changeuser']);

        // Load behaviour + styles. This hook runs before write() assembles
        // the page, so these includes are emitted into the output.
        $this->include_stylesheet('twostep_login.css');
        $this->include_script('twostep_login.js');

        // URL of the token-refresh endpoint (see startup()).
        $this->rc->output->set_env('twostep_login', [
            'token_url' => $this->rc->url(['_task' => 'login', '_action' => 'plugin.twostep_token']),
        ]);

        // Leave the rendered page content untouched; the form is transformed
        // in the browser by twostep_login.js.
        return $args;
    }
}
