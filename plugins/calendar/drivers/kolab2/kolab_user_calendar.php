<?php

/**
 * Kolab calendar storage class simulating a virtual user calendar
 *
 * @author Thomas Bruederli <bruederli@kolabsys.com>
 *
 * Copyright (C) 2014-2015, Kolab Systems AG <contact@kolabsys.com>
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

class kolab_user_calendar extends kolab_calendar
{
  public $id = 'unknown';
  public $ready = false;
  public $editable = false;
  public $attachments = false;
  public $subscriptions = false;

  protected $userdata = array();
  protected $timeindex = array();


  /**
   * Default constructor
   */
  public function __construct($user_or_folder, $calendar)
  {
    $this->cal = $calendar;

    // full user record is provided
    if (is_array($user_or_folder)) {
      $this->userdata = $user_or_folder;
      $this->storage = new kolab_storage_folder_user($this->userdata['kolabtargetfolder'], '', $this->userdata);
    }
    else {  // get user record from LDAP
      $this->storage = new kolab_storage_folder_user($user_or_folder);
      $this->userdata = $this->storage->ldaprec;
    }

    $this->ready = !empty($this->userdata['kolabtargetfolder']);
    $this->storage->type = 'event';

    if ($this->ready) {
      // ID is derrived from the user's kolabtargetfolder attribute
      $this->id = kolab_storage::folder_id($this->userdata['kolabtargetfolder'], true);
      $this->imap_folder = $this->userdata['kolabtargetfolder'];
      $this->name = $this->storage->get_name();
      $this->parent = '';  // user calendars are top level

      // user-specific alarms settings win
      $prefs = $this->cal->rc->config->get('kolab_calendars', array());
      if (isset($prefs[$this->id]['showalarms']))
        $this->alarms = $prefs[$this->id]['showalarms'];
    }
  }


  /**
   * Getter for a nice and human readable name for this calendar
   *
   * @return string Name of this calendar
   */
  public function get_name()
  {
    return $this->userdata['displayname'] ?: ($this->userdata['name'] ?: $this->userdata['mail']);
  }


  /**
   * Getter for the IMAP folder owner
   *
   * @return string Name of the folder owner
   */
  public function get_owner()
  {
    return $this->userdata['mail'];
  }


  /**
   *
   */
  public function get_title()
  {
    return trim($this->userdata['displayname'] . '; ' . $this->userdata['mail'], '; ');
  }


  /**
   * Getter for the name of the namespace to which the IMAP folder belongs
   *
   * @return string Name of the namespace (personal, other, shared)
   */
  public function get_namespace()
  {
    return 'other user';
  }


  /**
   * Getter for the top-end calendar folder name (not the entire path)
   *
   * @return string Name of this calendar
   */
  public function get_foldername()
  {
    return $this->get_name();
  }

  /**
   * Return color to display this calendar
   */
  public function get_color()
  {
    // calendar color is stored in local user prefs
    $prefs = $this->cal->rc->config->get('kolab_calendars', array());

    if (!empty($prefs[$this->id]) && !empty($prefs[$this->id]['color']))
      return $prefs[$this->id]['color'];

    return 'cc0000';
  }

  /**
   * Compose an URL for CalDAV access to this calendar (if configured)
   */
  public function get_caldav_url()
  {
    return false;
  }

  /**
   * Check subscription status of this folder
   *
   * @return boolean True if subscribed, false if not
   */
  public function is_subscribed()
  {
    return $this->storage->is_subscribed();
  }

  /**
   * Update properties of this calendar folder
   *
   * @see calendar_driver::edit_calendar()
   */
  public function update(&$prop)
  {
    // don't change anything.
    // let kolab_driver save props in local prefs
    return $prop['id'];
  }


  /**
   * Getter for a single event object
   */
  public function get_event($id)
  {
    // TODO: implement this
    return $this->events[$id];
  }

  /**
   * Get attachment body
   * @see calendar_driver::get_attachment_body()
   */
  public function get_attachment_body($id, $event)
  {
    if (!$event['calendar'] && ($ev = $this->get_event($event['id']))) {
      $event['calendar'] = $ev['calendar'];
    }

    if ($event['calendar'] && ($cal = $this->cal->get_calendar($event['calendar']))) {
      return $cal->get_attachment_body($id, $event);
    }

    return false;
  }

  /**
   * @param  integer Event's new start (unix timestamp)
   * @param  integer Event's new end (unix timestamp)
   * @param  string  Search query (optional)
   * @param  boolean Include virtual events (optional)
   * @param  array   Additional parameters to query storage
   * @return array A list of event records
   */
  public function list_events($start, $end, $search = null, $virtual = 1, $query = array())
  {
    // convert to DateTime for comparisons
    try {
      $start_dt = new DateTime('@'.$start);
    }
    catch (Exception $e) {
      $start_dt = new DateTime('@0');
    }
    try {
      $end_dt = new DateTime('@'.$end);
    }
    catch (Exception $e) {
      $end_dt = new DateTime('today +10 years');
    }

    $limit_changed = null;
    if (!empty($query)) {
      foreach ($query as $q) {
        if ($q[0] == 'changed' && $q[1] == '>=') {
          try { $limit_changed = new DateTime('@'.$q[2]); }
          catch (Exception $e) { /* ignore */ }
        }
      }
    }

    // aggregate all calendar folders the user shares (but are not subscribed)
    foreach (kolab_storage::list_user_folders($this->userdata, 'event', false) as $foldername) {
      $cal = new kolab_calendar($foldername, $this->cal);
      foreach ($cal->list_events($start, $end, $search, 1) as $event) {
        $this->events[$event['id']] = $event;
        $this->timeindex[$this->time_key($event)] = $event['id'];
      }
    }

    // get events from the user's free/busy feed (for quickview only)
    $fbview = $this->cal->rc->config->get('calendar_include_freebusy_data', 1);
    if ($fbview && ($fbview == 1 || !empty($_REQUEST['_quickview'])) && empty($search)) {
      $this->fetch_freebusy($limit_changed);
    }

    $events = array();
    foreach ($this->events as $event) {
      // list events in requested time window
      if ($event['start'] <= $end_dt && $event['end'] >= $start_dt &&
           (!$limit_changed || !$event['changed'] || $event['changed'] >= $limit_changed)) {
        $events[] = $event;
      }
    }

    // avoid session race conditions that will loose temporary subscriptions
    $this->cal->rc->session->nowrite = true;

    return $events;
  }

  /**
   *
   * @param  integer Date range start (unix timestamp)
   * @param  integer Date range end (unix timestamp)
   * @return integer Count
   */
  public function count_events($start, $end = null)
  {
    // not implemented
    return 0;
  }

  /**
   * Helper method to fetch free/busy data for the user and turn it into calendar data
   */
  private function fetch_freebusy($limit_changed = null)
  {
    // ask kolab server first
    try {
      $request_config = array(
        'store_body'       => true,
        'follow_redirects' => true,
      );
      $request  = libkolab::http_request(kolab_storage::get_freebusy_url($this->userdata['mail']), 'GET', $request_config);
      $response = $request->send();

      // authentication required
      if ($response->getStatus() == 401) {
        $request->setAuth($this->cal->rc->user->get_username(), $this->cal->rc->decrypt($_SESSION['password']));
        $response = $request->send();
      }

      if ($response->getStatus() == 200)
        $fbdata = $response->getBody();

      unset($request, $response);
    }
    catch (Exception $e) {
      rcube::raise_error(array(
        'code' => 900,
        'type' => 'php',
        'file' => __FILE__,
        'line' => __LINE__,
        'message' => "Error fetching free/busy information: " . $e->getMessage()),
        true, false);

      return false;
    }

    $statusmap = array(
      'FREE' => 'free',
      'BUSY' => 'busy',
      'BUSY-TENTATIVE' => 'tentative',
      'X-OUT-OF-OFFICE' => 'outofoffice',
      'OOF' => 'outofoffice',
    );
    $titlemap = array(
      'FREE' => $this->cal->gettext('availfree'),
      'BUSY' => $this->cal->gettext('availbusy'),
      'BUSY-TENTATIVE' => $this->cal->gettext('availtentative'),
      'X-OUT-OF-OFFICE' => $this->cal->gettext('availoutofoffice'),
    );

    // console('_fetch_freebusy', kolab_storage::get_freebusy_url($this->userdata['mail']), $fbdata);

    // parse free-busy information
    $count = 0;
    if ($fbdata) {
      $ical = $this->cal->get_ical();
      $ical->import($fbdata);
      if ($fb = $ical->freebusy) {
        // consider 'changed >= X' queries
        if ($limit_changed && $fb['created'] && $fb['created'] < $limit_changed) {
          return 0;
        }

        foreach ($fb['periods'] as $tuple) {
          list($from, $to, $type) = $tuple;
          $event = array(
            'id'        => md5($this->id . $from->format('U') . '/' . $to->format('U')),
            'calendar'  => $this->id,
            'changed'   => $fb['created'] ?: new DateTime(),
            'title'     => $this->get_name() . ' ' . ($titlemap[$type] ?: $type),
            'start'     => $from,
            'end'       => $to,
            'free_busy' => $statusmap[$type] ?: 'busy',
            'className' => 'fc-type-freebusy',
            'organizer' => array(
              'email' => $this->userdata['mail'],
              'name'  => $this->userdata['displayname'],
            ),
          );

          // avoid duplicate entries
          $key = $this->time_key($event);
          if (!$this->timeindex[$key]) {
            $this->events[$event['id']] = $event;
            $this->timeindex[$key] = $event['id'];
            $count++;
          }
        }
      }
    }

    return $count;
  }

  /**
   * Helper to build a key for the absolute time slot the given event convers
   */
  private function time_key($event)
  {
    return sprintf('%s/%s', $event['start']->format('U'), is_object($event['end']->format('U')) ?: '0');
  }


  /**
   * Create a new event record
   *
   * @see calendar_driver::new_event()
   * 
   * @return mixed The created record ID on success, False on error
   */
  public function insert_event($event)
  {
    return false;
  }

  /**
   * Update a specific event record
   *
   * @see calendar_driver::new_event()
   * @return boolean True on success, False on error
   */

  public function update_event($event, $exception_id = null)
  {
    return false;
  }

  /**
   * Delete an event record
   *
   * @see calendar_driver::remove_event()
   * @return boolean True on success, False on error
   */
  public function delete_event($event, $force = true)
  {
    return false;
  }

  /**
   * Restore deleted event record
   *
   * @see calendar_driver::undelete_event()
   * @return boolean True on success, False on error
   */
  public function restore_event($event)
  {
    return false;
  }


  /**
   * Convert from Kolab_Format to internal representation
   */
  private function _to_rcube_event($record)
  {
    $record['id'] = $record['uid'];
    $record['calendar'] = $this->id;

    return kolab_driver::to_rcube_event($record);
  }

}
