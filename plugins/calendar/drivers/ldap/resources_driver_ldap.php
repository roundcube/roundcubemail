<?php

/**
 * LDAP-based resource directory class using rcube_ldap functionality
 *
 * @author Thomas Bruederli <bruederli@kolabsys.com>
 *
 * Copyright (C) 2014, Kolab Systems AG <contact@kolabsys.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * LDAP-based resource directory implementation
 */
class resources_driver_ldap extends resources_driver
{
    private $rc;
    private $ldap;

    /**
     * Default constructor
     */
    function __construct($cal)
    {
        $this->cal = $cal;
        $this->rc = $cal->rc;
    }

    /**
     * Fetch resource objects to be displayed for booking
     *
     * @param  string  Search query (optional)
     * @return array  List of resource records available for booking
     */
    public function load_resources($query = null, $num = 5000)
    {
      if (!($ldap = $this->connect())) {
        return array();
      }

      // TODO: apply paging
      $ldap->set_pagesize($num);

      if (isset($query)) {
        $results = $ldap->search('*', $query, 0, true, true);
      }
      else {
        $results = $ldap->list_records();
      }

      if ($results instanceof ArrayAccess) {
        foreach ($results as $i => $rec) {
          $results[$i] = $this->decode_resource($rec);
        }
      }

      return $results;
    }

    /**
     * Return properties of a single resource
     *
     * @param string  Unique resource identifier
     * @return array Resource object as hash array
     */
    public function get_resource($dn)
    {
      $rec = null;

      if ($ldap = $this->connect()) {
        $rec = $ldap->get_record(rcube_ldap::dn_encode($dn), true);

        if (!empty($rec)) {
          $rec = $this->decode_resource($rec);
        }
      }

      return $rec;
    }

    /**
     * Return properties of a resource owner
     *
     * @param string  Owner identifier
     * @return array  Resource object as hash array
     */
    public function get_resource_owner($dn)
    {
      $owner = null;

      if ($ldap = $this->connect()) {
        $owner = $ldap->get_record(rcube_ldap::dn_encode($dn), true);
        $owner['ID'] = rcube_ldap::dn_decode($owner['ID']);
        unset($owner['_raw_attrib'], $owner['_type']);
      }

      return $owner;
    }

    /**
     * Extract JSON-serialized attributes
     */
    private function decode_resource($rec)
    {
      $rec['ID'] = rcube_ldap::dn_decode($rec['ID']);

      if (is_array($rec['attributes']) && $rec['attributes'][0]) {
        $attributes = array();

        foreach ($rec['attributes'] as $sattr) {
          $attr = @json_decode($sattr, true);
          $attributes += $attr;
        }

        $rec['attributes'] = $attributes;
      }

      // force $rec['members'] to be an array
      if (!empty($rec['members']) && !is_array($rec['members'])) {
        $rec['members'] = array($rec['members']);
      }

      // remove unused cruft
      unset($rec['_raw_attrib']);

      return $rec;
    }

    private function connect()
    {
      if (!isset($this->ldap)) {
        $this->ldap = new rcube_ldap($this->rc->config->get('calendar_resources_directory'), true);
      }

      return $this->ldap->ready ? $this->ldap : null;
    }

}