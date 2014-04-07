<?php
/**
 * New user identity
 *
 * Populates a new user's default identity from LDAP on their first visit.
 *
 * If using the ldap driver, this plugin requires that a working public_ldap
 * directory be configured.
 *
 * @version @package_version@
 * @author Kris Steinhoff
 * @license GNU GPLv3+
 *
 */
class new_user_identity extends rcube_plugin
{
    public $task = 'login';

    private $ldap;
    private $rc;

    function init()
    {
        $this->load_config('config.inc.php.dist');
        $this->load_config('config.inc.php');

        $this->rc = rcmail::get_instance();

        $this->add_hook('user_create', array($this, 'create_user'));
    }

    public function create_user($args)
    {
        $mode = (string) $this->rc->config->get('new_user_identity_driver');

        switch ($mode) {
        case 'token':
            // ToDo: Add more useful tokens here
            $search = array('%u', '%h');
            $replace = array($args['user'], $args['host']);
            $user_name  = str_replace($search, $replace, $this->rc->config->get('new_user_identity_name_pattern'));
            $user_email = str_replace($search, $replace, $this->rc->config->get('new_user_identity_email_pattern'));
            $args['user_name'] = $user_name;

//            if (!$args['user_email'] && strpos($user_email, '@')) {
                $args['user_email'] = rcube_utils::idn_to_ascii($user_email);
//            }

            break;
        case 'ldap':
            if ($this->init_ldap($args['host'])) {
                $results = $this->ldap->search('*', $args['user'], true);
                if (count($results->records) == 1) {
                    $user_name  = is_array($results->records[0]['name']) ? $results->records[0]['name'][0] : $results->records[0]['name'];
                    $user_email = is_array($results->records[0]['email']) ? $results->records[0]['email'][0] : $results->records[0]['email'];

                    $args['user_name'] = $user_name;
                    if (!$args['user_email'] && strpos($user_email, '@')) {
                        $args['user_email'] = rcube_utils::idn_to_ascii($user_email);
                    }
                }
            }
            break;
        }

        write_log('new_user_identity', 'created user "' . $args['user_name'] . ' <' . $args['user_email'] . '>"');

        return $args;
    }

    private function init_ldap($host)
    {
        if ($this->ldap) {
            return $this->ldap->ready;
        }

        $this->rc = rcmail::get_instance();

        $addressbook = $this->rc->config->get('new_user_identity_addressbook');
        $ldap_config = (array)$this->rc->config->get('ldap_public');
        $match       = $this->rc->config->get('new_user_identity_match');

        if (empty($addressbook) || empty($match) || empty($ldap_config[$addressbook])) {
            return false;
        }

        $this->ldap = new new_user_identity_ldap_backend(
            $ldap_config[$addressbook],
            $this->rc->config->get('ldap_debug'),
            $this->rc->config->mail_domain($host),
            $match);

        return $this->ldap->ready;
    }
}

class new_user_identity_ldap_backend extends rcube_ldap
{
    function __construct($p, $debug, $mail_domain, $search)
    {
        parent::__construct($p, $debug, $mail_domain);
        $this->prop['search_fields'] = (array)$search;
    }
}
