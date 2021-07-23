<?php
/**
 * New user identity
 *
 * Populates a new user's default identity from LDAP on their first visit.
 *
 * This plugin requires that a working public_ldap directory be configured.
 *
 * @author Kris Steinhoff
 * @license GNU GPLv3+
 */
class new_user_identity extends rcube_plugin
{
    public $task = 'login';

    private $rc;
    private $ldap;

    /**
     * Plugin initialization. API hooks binding.
     */
    function init()
    {
        $this->rc = rcmail::get_instance();

        $this->add_hook('user_create', [$this, 'lookup_user_name']);
        $this->add_hook('login_after', [$this, 'login_after']);
    }

    /**
     * 'user_create' hook handler.
     */
    function lookup_user_name($args)
    {
        if ($this->init_ldap($args['host'], $args['user'])) {
            $results = $this->ldap->search('*', $args['user'], true);

            if (count($results->records) == 1) {
                $user       = $results->records[0];
                $user_name  = is_array($user['name']) ? $user['name'][0] : $user['name'];
                $user_email = is_array($user['email']) ? $user['email'][0] : $user['email'];

                $args['user_name']  = $user_name;
                $args['email_list'] = [];

                if (empty($args['user_email']) && strpos($user_email, '@')) {
                    $args['user_email'] = rcube_utils::idn_to_ascii($user_email);
                }

                if (!empty($args['user_email'])) {
                    $args['email_list'][] = $args['user_email'];
                }

                foreach (array_keys($user) as $key) {
                    if (!preg_match('/^email($|:)/', $key)) {
                        continue;
                    }

                    foreach ((array) $user[$key] as $alias) {
                        if (strpos($alias, '@')) {
                            $args['email_list'][] = rcube_utils::idn_to_ascii($alias);
                        }
                    }
                }

                $args['email_list'] = array_unique($args['email_list']);
            }
        }

        return $args;
    }

    /**
     * 'login_after' hook handler. This is where we create identities for
     * all user email addresses.
     */
    function login_after($args)
    {
        $this->load_config();

        if ($this->ldap || !$this->rc->config->get('new_user_identity_onlogin')) {
            return $args;
        }

        $identities = $this->rc->user->list_emails();
        $ldap_entry = $this->lookup_user_name([
                'user' => $this->rc->user->data['username'],
                'host' => $this->rc->user->data['mail_host'],
        ]);

        if (empty($ldap_entry['email_list'])) {
            return $args;
        }

        foreach ((array) $ldap_entry['email_list'] as $email) {
            foreach ($identities as $identity) {
                if ($identity['email'] == $email) {
                    continue 2;
                }
            }

            $plugin = $this->rc->plugins->exec_hook('identity_create', [
                'login'  => true,
                'record' => [
                    'user_id'  => $this->rc->user->ID,
                    'standard' => 0,
                    'email'    => $email,
                    'name'     => $ldap_entry['user_name']
                ],
            ]);

            if (!$plugin['abort'] && !empty($plugin['record']['email'])) {
                $this->rc->user->insert_identity($plugin['record']);
            }
        }

        return $args;
    }

    /**
     * Initialize LDAP backend connection
     */
    private function init_ldap($host, $user)
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

        $debug  = $this->rc->config->get('ldap_debug');
        $domain = $this->rc->config->mail_domain($host);
        $props  = $ldap_config[$addressbook];

        $this->ldap = new new_user_identity_ldap_backend($props, $debug, $domain, $match);

        return $this->ldap->ready;
    }
}

class new_user_identity_ldap_backend extends rcube_ldap
{
    function __construct($p, $debug, $mail_domain, $search)
    {
        parent::__construct($p, $debug, $mail_domain);
        $this->prop['search_fields'] = (array) $search;
    }
}
