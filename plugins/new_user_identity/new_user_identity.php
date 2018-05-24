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
    private $debug = true;
    function init()
    {
        $this->rc = rcmail::get_instance();

        $this->add_hook('user_create', array($this, 'lookup_user_name'));
        $this->add_hook('login_after', array($this, 'login_after'));
    }

    function lookup_user_name($args)
    {
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

        //HW-HACK: list_identities anstatt list_emails()
        $identities = $this->rc->user->list_identities();
        //$identities = $this->rc->user->list_emails();
        $ldap_entry = $this->lookup_user_name(array(
                'user' => $this->rc->user->data['username'],
                'host' => $this->rc->user->data['mail_host'],
        ));
        
        foreach ((array) $ldap_entry['email_list'] as $email) {
            //HW-HACK: (array)
            foreach ((array) $identities as $identity) {
            //foreach ($identities as $identity) {
                if ($identity['email'] == $email) {
                    // HW-HACK: Aenderung des Namens wenn im LDAP geaendert
                    if($identity['name'] != $ldap_entry['user_name']) {
                        $this->log_msg('Name has changed. Change it to: ' . $ldap_entry['user_name']);
                        $identity['name'] = $ldap_entry['user_name'];
                        $this->rc->user->update_identity($identity['identity_id'],$identity);
                    }
                    // HW-HACK: END
                    continue 2;
                }
                // HW-HACK: Setze die Standard-Identität auf die LDAP-Mailadresse.
                // Das geht an dieser Stelle weil vorher aus der Schleife
                // gesprungen wird, falls sich die Mail-Adresse nicht
                // geändert hat.
                // Es wird die alte Email-Adresse behalten in einer Nicht-Standard-
                // Identität.
                if (isset($identity['email']) && $identity['standard'] == 1) {
                    $this->log_msg('Mail-Address in LDAP has changed. Edit the standard identitiy and jump away...');
                    $this->rc->user->update_identity($identity['identity_id'],array('standard' => 0));

                    $plugin = $this->rc->plugins->exec_hook('identity_create', array(
                         'record' => array(
                             'user_id'  => $this->rc->user->ID,
                             'standard' => 1,
                             'email'    => $email,
                             'name'     => $ldap_entry['user_name']
                         ),
                     ));
         
                     if (!$plugin['abort'] && $plugin['record']['email']) {
                         $this->rc->user->insert_identity($plugin['record']);
                     }
                     continue 2;
                }
               // HW-HACK: END
            }

            $plugin = $this->rc->plugins->exec_hook('identity_create', array(
               // 'login'  => true,
                'record' => array(
                    'user_id'  => $this->rc->user->ID,
                    'standard' => 1,
                    'email'    => $email,
                    'name'     => $ldap_entry['user_name']
                ),
            ));

            if (!$plugin['abort'] && $plugin['record']['email']) {
                $this->rc->user->insert_identity($plugin['record']);
            }
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
        //HW-HACK: Dont use a LDAP addressbook
        //$ldap_config = (array)$this->rc->config->get('ldap_public');
        $ldap_config = (array)$this->rc->config->get('ldap_private');
        //HW-HACK: END
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
    //HW-HACK: Debug added
    private function log_msg($str) {
        if ($this->debug) {
            rcube::write_log('ldap', "new_user_identity_ldap: $str");
        }
    }
    //HW-HACK: END

}

class new_user_identity_ldap_backend extends rcube_ldap
{
    function __construct($p, $debug, $mail_domain, $search)
    {
        parent::__construct($p, $debug, $mail_domain);
        $this->prop['search_fields'] = (array)$search;
    }
}
