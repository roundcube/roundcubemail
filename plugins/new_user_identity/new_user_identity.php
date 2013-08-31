<?php
/**
 * New user identity
 *
 * Populates a new user's default identity from LDAP on their first visit.
 *
 * This plugin requires that a working public_ldap directory be configured.
 *
 * @version @package_version@
 * @author  Kris Steinhoff
 * @license GNU GPLv3+
 *
 * Example configuration:
 *
 *  // The id of the address book to use to automatically set a new
 *  // user's full name in their new identity. (This should be an
 *  // string, which refers to the $config['ldap_public'] array.)
 *  $config['new_user_identity_addressbook'] = 'People';
 *
 *  // When automatically setting a new users's full name in their
 *  // new identity, match the user's login name against this field.
 *  $config['new_user_identity_match'] = 'uid';
 */
class new_user_identity extends rcube_plugin
{
    public $task = 'login';

    private $ldap;

    function init()
    {
        $this->add_hook('user_create', array($this, 'lookup_user_name'));
    }

    /**
     * Look up a username for name and/or email address
     *
     * @param array $args
     *
     * @return array
     */
    function lookup_user_name($args)
    {
        if (!$this->init_ldap($args['host'])) return $args;

        $results = $this->ldap->search('*', $args['user'], true);

        if (count($results->records) != 1) return $args;

        $user_name  = is_array($results->records[0]['name']) ? $results->records[0]['name'][0] : $results->records[0]['name'];
        $user_email = is_array($results->records[0]['email']) ? $results->records[0]['email'][0] : $results->records[0]['email'];

        $args['user_name'] = $user_name;

        if (!$args['user_email'] && strpos($user_email, '@')) {
            $args['user_email'] = rcube_utils::idn_to_ascii($user_email);
        }

        return $args;
    }

    /**
     * Initialize LDAP connection
     *
     * @param string $host
     *
     * @return bool
     */
    private function init_ldap($host)
    {
        if ($this->ldap) return $this->ldap->ready;

        $rcmail = rcmail::get_instance();

        $addressbook = $rcmail->config->get('new_user_identity_addressbook');

        $ldap_config = (array) $rcmail->config->get('ldap_public');

        $match = $rcmail->config->get('new_user_identity_match');

        if (empty($addressbook) || empty($match) || empty($ldap_config[$addressbook])) {
            return false;
        }

        $this->ldap = new new_user_identity_ldap_backend(
            $ldap_config[$addressbook],
            $rcmail->config->get('ldap_debug'),
            $rcmail->config->mail_domain($host),
            $match
        );

        return $this->ldap->ready;
    }
}

class new_user_identity_ldap_backend extends rcube_ldap
{
    /**
     * @see rcube_ldap::__construct
     *
     * @param array $search
     */
    function __construct($p, $debug, $mail_domain, $search)
    {
        parent::__construct($p, $debug, $mail_domain);

        $this->prop['search_fields'] = (array) $search;
    }
}
