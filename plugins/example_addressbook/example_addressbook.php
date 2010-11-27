<?php

require_once(dirname(__FILE__) . '/example_addressbook_backend.php');

/**
 * Sample plugin to add a new address book
 * with just a static list of contacts
 */
class example_addressbook extends rcube_plugin
{
  private $abook_id = 'static';
  
  public function init()
  {
    $this->add_hook('addressbooks_list', array($this, 'address_sources'));
    $this->add_hook('addressbook_get', array($this, 'get_address_book'));
    
    // use this address book for autocompletion queries
    // (maybe this should be configurable by the user?)
    $config = rcmail::get_instance()->config;
    $sources = (array) $config->get('autocomplete_addressbooks', array('sql'));
    if (!in_array($this->abook_id, $sources)) {
      $sources[] = $this->abook_id;
      $config->set('autocomplete_addressbooks', $sources);
    }
  }
  
  public function address_sources($p)
  {
    $abook = new example_addressbook_backend;
    $p['sources'][$this->abook_id] = array(
      'id' => $this->abook_id,
      'name' => 'Static List',
      'readonly' => $abook->readonly,
      'groups' => $abook->groups,
    );
    return $p;
  }
  
  public function get_address_book($p)
  {
    if ($p['id'] === $this->abook_id) {
      $p['instance'] = new example_addressbook_backend;
    }
    
    return $p;
  }
  
}
