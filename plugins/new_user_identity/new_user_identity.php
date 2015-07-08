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
<<<<<<< HEAD
 *
=======
>>>>>>> upstream/master
 */
class new_user_identity extends rcube_plugin
{
    public $task = 'login';

    private $rc;
    private $ldap;
    private $rc;

    function init()
    {

        $this->rc = rcmail::get_instance();

        $this->add_hook('user_create', array($this, 'create_user'));
        $this->add_hook('login_after', array($this, 'login_after'));
    }

    public function create_user($args)
    {
        $this->load_config('config.inc.php');

        $mode = (string) $this->rc->config->get('new_user_identity_driver', 'ldap');

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

                    $args['user_name']  = $user_name;
                    $args['email_list'] = array();

                    if (!$args['user_email'] && strpos($user_email, '@')) {
                        $args['user_email'] = rcube_utils::idn_to_ascii($user_email);
                    }
                }

                foreach (array_keys($results[0]) as $key) {
                    if (!preg_match('/^email($|:)/', $key)) {
                        continue;
                    }

                    foreach ((array) $results->records[0][$key] as $alias) {
                        if (strpos($alias, '@')) {
                            $args['email_list'][] = rcube_utils::idn_to_ascii($alias);
                        }
                    }
                }

            }
        }

        return $args;
    }

    function login_after($args)
    {
        $this->load_config();

        if ($this->ldap || !$this->rc->config->get('new_user_identity_onlogin')) {
            return $args;
        }

        $identities = $this->rc->user->list_emails();
        $ldap_entry = $this->lookup_user_name(array(
                'user' => $this->rc->user->data['username'],
                'host' => $this->rc->user->data['mail_host'],
        ));

        foreach ((array) $ldap_entry['email_list'] as $email) {
            foreach ($identities as $identity) {
                if ($identity['email'] == $email) {
                    continue 2;
                }
            }

            $plugin = $this->rc->plugins->exec_hook('identity_create', array(
                'login'  => true,
                'record' => array(
                    'user_id'  => $this->rc->user->ID,
                    'standard' => 0,
                    'email'    => $email,
                    'name'     => $ldap_entry['user_name']
                ),
            ));

            if (!$plugin['abort'] && $plugin['record']['email']) {
                $this->rc->user->insert_identity($plugin['record']);
            }
            break;
        }
        return $args;
    }

    private function init_ldap($host)
    {
        if ($this->ldap) {
            return $this->ldap->ready;
        }

        $this->load_config();

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
