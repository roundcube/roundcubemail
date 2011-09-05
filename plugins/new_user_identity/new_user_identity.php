<?php
/**
 * New user identity
 *
 * Populates a new user's default identity from LDAP on their first visit.
 *
 * This plugin requires that a working public_ldap directory be configured.
 *
 * @version 1.05
 * @author Kris Steinhoff
 *
 * Example configuration:
 *
 *  // The id of the address book to use to automatically set a new
 *  // user's full name in their new identity. (This should be an
 *  // string, which refers to the $rcmail_config['ldap_public'] array.)
 *  $rcmail_config['new_user_identity_addressbook'] = 'People';
 *
 *  // When automatically setting a new users's full name in their
 *  // new identity, match the user's login name against this field.
 *  $rcmail_config['new_user_identity_match'] = 'uid';
 *
 *  // Use this field (from fieldmap configuration) to fill alias col of
 *  // the new user record.
 *  $rcmail_config['new_user_identity_alias'] = 'alias';
 */
class new_user_identity extends rcube_plugin
{
    public $task = 'login';

    private $ldap;

    function init()
    {
        $this->add_hook('user_create', array($this, 'lookup_user_name'));
    }

    function lookup_user_name($args)
    {
        $rcmail = rcmail::get_instance();
        
        if ($this->init_ldap($args['host'])) {
            $results = $this->ldap->search('*', $args['user'], TRUE);
            if (count($results->records) == 1) {
                $args['user_name'] = $results->records[0]['name'];
                if (!$args['user_email'] && strpos($results->records[0]['email'], '@')) {
                    $args['user_email'] = rcube_idn_to_ascii($results->records[0]['email']);
                }
                if (($alias_col = $rcmail->config->get('new_user_identity_alias')) && $results->records[0][$alias_col]) {
                  $args['alias'] = $results->records[0][$alias_col];
                }
            }
        }
        return $args;
    }

    private function init_ldap($host)
    {
        if ($this->ldap)
            return $this->ldap->ready;

        $rcmail = rcmail::get_instance();

        $addressbook = $rcmail->config->get('new_user_identity_addressbook');
        $ldap_config = (array)$rcmail->config->get('ldap_public');
        $match       = $rcmail->config->get('new_user_identity_match');

        if (empty($addressbook) || empty($match) || empty($ldap_config[$addressbook])) {
            return false;
        }

        $this->ldap = new new_user_identity_ldap_backend(
            $ldap_config[$addressbook],
            $rcmail->config->get('ldap_debug'),
            $rcmail->config->mail_domain($host),
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
