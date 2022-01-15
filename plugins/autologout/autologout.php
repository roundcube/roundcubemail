<?php

/**
 * Plugin to auto log out users with a POST request sent from an external site.
 *
 * @license GNU GPLv3+
 * @author  Cover Tower LLC
 *
 * First enable this plugin by setting $config['plugins'] = array(..., 'autologout')
 * in the Roundcube configuration file (config.inc.php). To use it, embed
 * a form like the following in a web page:
 *
 * <form id="rcLogoutForm" method="POST" action="https://mail.example.com/">
 * <input type="hidden" name="_action" value="logout" />
 * <input type="hidden" name="_task" value="logout" />
 * <input type="hidden" name="_autologout" value="1" />
 * <input id="loSubmitButton" type="submit" value="Logout" />
 * </form>
 *
 * This plugin won't work if the POST request is made using CURL or other
 * methods. It will only work if the POST request is made by submitting a
 * form similar to the one from above. The form can be hidden and it can
 * be sent automatically using JavaScript or JQuery (for example by using:
 * $("#loSubmitButton").click();)
 */

class autologout extends rcube_plugin
{
    public $task = 'logout';

    /**
     * Plugin initialization
     */
    public function init()
    {
        $this->add_hook('startup', [$this, 'startup']);
    }

    /**
     * Request handler
     */
    public function startup($args)
    {
        $rcmail = rcmail::get_instance();

        // Change task and action to logout
        if (!empty($_SESSION['user_id']) && !empty($_POST['_autologout']) && $this->known_client()) {
            $rcmail->logout_actions();
            $rcmail->kill_session();
        }

        return $args;
    }

    /**
     * Checks if the request came from an allowed client IP
     */
    private function known_client()
    {
        /*
         * If you want to restrict the use of this plugin to specific
         * remote clients, you can verify the remote client's IP like this:
         *
         * return in_array(rcube_utils::remote_addr(), ['123.123.123.123', '124.124.124.124']);
         */

        return true;
    }
}
