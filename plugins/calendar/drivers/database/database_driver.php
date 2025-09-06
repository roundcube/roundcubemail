<?php

/**
 * Database driver for the Calendar plugin
 *
 * @author Lazlo Westerhof <hello@lazlo.me>
 * @author Thomas Bruederli <bruederli@kolabsys.com>
 *
 * Copyright (C) 2010, Lazlo Westerhof <hello@lazlo.me>
 * Copyright (C) 2012-2015, Kolab Systems AG <contact@kolabsys.com>
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


class database_driver extends calendar_driver
{
  const DB_DATE_FORMAT = 'Y-m-d H:i:s';

  public static $scheduling_properties = array('start', 'end', 'allday', 'recurrence', 'location', 'cancelled');

  // features this backend supports
  public $alarms = true;
  public $attendees = true;
  public $freebusy = false;
  public $attachments = true;
  public $alarm_types = array('DISPLAY');

  private $rc;
  private $cal;
  private $cache = array();
  private $calendars = array();
  private $calendar_ids = '';
  private $free_busy_map = array('free' => 0, 'busy' => 1, 'out-of-office' => 2, 'outofoffice' => 2, 'tentative' => 3);
  private $sensitivity_map = array('public' => 0, 'private' => 1, 'confidential' => 2);
  private $server_timezone;
  
  private $db_events = 'events';
  private $db_calendars = 'calendars';
  private $db_attachments = 'attachments';


  /**
   * Default constructor
   */
  public function __construct($cal)
  {
    $this->cal = $cal;
    $this->rc = $cal->rc;
    $this->server_timezone = new DateTimeZone(date_default_timezone_get());
    
    // read database config
    $db = $this->rc->get_dbh();
    $this->db_events = $this->rc->config->get('db_table_events', $db->table_name($this->db_events));
    $this->db_calendars = $this->rc->config->get('db_table_calendars', $db->table_name($this->db_calendars));
    $this->db_attachments = $this->rc->config->get('db_table_attachments', $db->table_name($this->db_attachments));
    
    $this->_read_calendars();
  }

  /**
   * Read available calendars for the current user and store them internally
   */
  private function _read_calendars()
  {
    $hidden = array_filter(explode(',', $this->rc->config->get('hidden_calendars', '')));
    
    if (!empty($this->rc->user->ID)) {
      $calendar_ids = array();
      $result = $this->rc->db->query(
        "SELECT *, calendar_id AS id FROM " . $this->db_calendars . "
         WHERE user_id=?
         ORDER BY name",
         $this->rc->user->ID
      );
      while ($result && ($arr = $this->rc->db->fetch_assoc($result))) {
        $arr['showalarms'] = intval($arr['showalarms']);
        $arr['active']     = !in_array($arr['id'], $hidden);
        $arr['name']       = html::quote($arr['name']);
        $arr['listname']   = html::quote($arr['name']);
        $arr['rights']     = 'lrswikxteav';
        $arr['editable']  = true;
        $this->calendars[$arr['calendar_id']] = $arr;
        $calendar_ids[] = $this->rc->db->quote($arr['calendar_id']);
      }
      $this->calendar_ids = join(',', $calendar_ids);
    }
  }

  /**
   * Get a list of available calendars from this source
   *
   * @param integer Bitmask defining filter criterias
   *
   * @return array List of calendars
   */
  public function list_calendars($filter = 0)
  {
    // attempt to create a default calendar for this user
    if (empty($this->calendars)) {
      if ($this->create_calendar(array('name' => 'Default', 'color' => 'cc0000', 'showalarms' => true)))
        $this->_read_calendars();
    }

    $calendars = $this->calendars;

    // filter active calendars
    if ($filter & self::FILTER_ACTIVE) {
      foreach ($calendars as $idx => $cal) {
        if (!$cal['active']) {
          unset($calendars[$idx]);
        }
      }
    }

    // 'personal' is unsupported in this driver

    // append the virtual birthdays calendar
    if ($this->rc->config->get('calendar_contact_birthdays', false)) {
      $prefs = $this->rc->config->get('birthday_calendar', array('color' => '87CEFA'));
      $hidden = array_filter(explode(',', $this->rc->config->get('hidden_calendars', '')));

      $id = self::BIRTHDAY_CALENDAR_ID;
      if (!$active || !in_array($id, $hidden)) {
        $calendars[$id] = array(
          'id'         => $id,
          'name'       => $this->cal->gettext('birthdays'),
          'listname'   => $this->cal->gettext('birthdays'),
          'color'      => $prefs['color'],
          'showalarms' => (bool)$this->rc->config->get('calendar_birthdays_alarm_type'),
          'active'     => !in_array($id, $hidden),
          'group'      => 'x-birthdays',
          'editable'  => false,
          'default'    => false,
          'children'   => false,
        );
      }
    }

    return $calendars;
  }

  /**
   * Create a new calendar assigned to the current user
   *
   * @param array Hash array with calendar properties
   *    name: Calendar name
   *   color: The color of the calendar
   * @return mixed ID of the calendar on success, False on error
   */
  public function create_calendar($prop)
  {
    $result = $this->rc->db->query(
      "INSERT INTO " . $this->db_calendars . "
       (user_id, name, color, showalarms)
       VALUES (?, ?, ?, ?)",
       $this->rc->user->ID,
       $prop['name'],
       $prop['color'],
       $prop['showalarms']?1:0
    );
    
    if ($result)
      return $this->rc->db->insert_id($this->db_calendars);
    
    return false;
  }

  /**
   * Update properties of an existing calendar
   *
   * @see calendar_driver::edit_calendar()
   */
  public function edit_calendar($prop)
  {
    // birthday calendar properties are saved in user prefs
    if ($prop['id'] == self::BIRTHDAY_CALENDAR_ID) {
      $prefs['birthday_calendar'] = $this->rc->config->get('birthday_calendar', array('color' => '87CEFA'));
      if (isset($prop['color']))
        $prefs['birthday_calendar']['color'] = $prop['color'];
      if (isset($prop['showalarms']))
        $prefs['calendar_birthdays_alarm_type'] = $prop['showalarms'] ? $this->alarm_types[0] : '';
      $this->rc->user->save_prefs($prefs);
      return true;
    }

    $query = $this->rc->db->query(
      "UPDATE " . $this->db_calendars . "
       SET   name=?, color=?, showalarms=?
       WHERE calendar_id=?
       AND   user_id=?",
      $prop['name'],
      $prop['color'],
      $prop['showalarms']?1:0,
      $prop['id'],
      $this->rc->user->ID
    );
    
    return $this->rc->db->affected_rows($query);
  }

  /**
   * Set active/subscribed state of a calendar
   * Save a list of hidden calendars in user prefs
   *
   * @see calendar_driver::subscribe_calendar()
   */
  public function subscribe_calendar($prop)
  {
    $hidden = array_flip(explode(',', $this->rc->config->get('hidden_calendars', '')));
    
    if ($prop['active'])
      unset($hidden[$prop['id']]);
    else
      $hidden[$prop['id']] = 1;
    
    return $this->rc->user->save_prefs(array('hidden_calendars' => join(',', array_keys($hidden))));
  }

  /**
   * Delete the given calendar with all its contents
   *
   * @see calendar_driver::delete_calendar()
   */
  public function delete_calendar($prop)
  {
    if (!$this->calendars[$prop['id']])
      return false;

    // events and attachments will be deleted by foreign key cascade

    $query = $this->rc->db->query(
      "DELETE FROM " . $this->db_calendars . "
       WHERE calendar_id=?",
       $prop['id']
    );

    return $this->rc->db->affected_rows($query);
  }

  /**
   * Search for shared or otherwise not listed calendars the user has access
   *
   * @param string Search string
   * @param string Section/source to search
   * @return array List of calendars
   */
  public function search_calendars($query, $source)
  {
    // not implemented
    return array();
  }

  /**
   * Add a single event to the database
   *
   * @param array Hash array with event properties
   * @see calendar_driver::new_event()
   */
  public function new_event($event)
  {
    if (!$this->validate($event))
      return false;

    if (!empty($this->calendars)) {
      if ($event['calendar'] && !$this->calendars[$event['calendar']])
        return false;
      if (!$event['calendar'])
        $event['calendar'] = reset(array_keys($this->calendars));

      if ($event_id = $this->_insert_event($event)) {
        $this->_update_recurring($event);
      }

      return $event_id;
    }
    
    return false;
  }

  /**
   *
   */
  private function _insert_event(&$event)
  {
    $event = $this->_save_preprocess($event);

    $this->rc->db->query(sprintf(
      "INSERT INTO " . $this->db_events . "
       (calendar_id, created, changed, uid, recurrence_id, instance, isexception, %s, %s, all_day, recurrence,
          title, description, location, categories, url, free_busy, priority, sensitivity, status, attendees, alarms, notifyat)
       VALUES (?, %s, %s, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        $this->rc->db->quote_identifier('start'),
        $this->rc->db->quote_identifier('end'),
        $this->rc->db->now(),
        $this->rc->db->now()
      ),
      $event['calendar'],
      strval($event['uid']),
      intval($event['recurrence_id']),
      strval($event['_instance']),
      intval($event['isexception']),
      $event['start']->format(self::DB_DATE_FORMAT),
      $event['end']->format(self::DB_DATE_FORMAT),
      intval($event['all_day']),
      $event['_recurrence'],
      strval($event['title']),
      strval($event['description']),
      strval($event['location']),
      join(',', (array)$event['categories']),
      strval($event['url']),
      intval($event['free_busy']),
      intval($event['priority']),
      intval($event['sensitivity']),
      strval($event['status']),
      $event['attendees'],
      $event['alarms'],
      $event['notifyat']
    );

    $event_id = $this->rc->db->insert_id($this->db_events);

    if ($event_id) {
      $event['id'] = $event_id;

      // add attachments
      if (!empty($event['attachments'])) {
        foreach ($event['attachments'] as $attachment) {
          $this->add_attachment($attachment, $event_id);
          unset($attachment);
        }
      }

      return $event_id;
    }

    return false;
  }

  /**
   * Update an event entry with the given data
   *
   * @param array Hash array with event properties
   * @see calendar_driver::edit_event()
   */
  public function edit_event($event)
  {
    if (!empty($this->calendars)) {
      $update_master = false;
      $update_recurring = true;
      $old = $this->get_event($event);
      $ret = true;

      // check if update affects scheduling and update attendee status accordingly
      $reschedule = $this->_check_scheduling($event, $old, true);

      // increment sequence number
      if (empty($event['sequence']) && $reschedule)
        $event['sequence'] = max($event['sequence'], $old['sequence']) + 1;
      
      // modify a recurring event, check submitted savemode to do the right things
      if ($old['recurrence'] || $old['recurrence_id']) {
        $master = $old['recurrence_id'] ? $this->get_event(array('id' => $old['recurrence_id'])) : $old;
        
        // keep saved exceptions (not submitted by the client)
        if ($old['recurrence']['EXDATE'])
          $event['recurrence']['EXDATE'] = $old['recurrence']['EXDATE'];
        
        switch ($event['_savemode']) {
          case 'new':
            $event['uid'] = $this->cal->generate_uid();
            return $this->new_event($event);
          
          case 'current':
            // save as exception
            $event['isexception'] = 1;
            $update_recurring = false;

            // set exception to first instance (= master)
            if ($event['id'] == $master['id']) {
              $event += $old;
              $event['recurrence_id'] = $master['id'];
              $event['_instance'] = libcalendaring::recurrence_instance_identifier($old);
              $event['isexception'] = 1;
              $event_id = $this->_insert_event($event);
              return $event_id;
            }
            break;
          
          case 'future':
            if ($master['id'] != $event['id']) {
              // set until-date on master event, then save this instance as new recurring event
              $master['recurrence']['UNTIL'] = clone $event['start'];
              $master['recurrence']['UNTIL']->sub(new DateInterval('P1D'));
              unset($master['recurrence']['COUNT']);
              $update_master = true;
              
              // if recurrence COUNT, update value to the correct number of future occurences
              if ($event['recurrence']['COUNT']) {
                $fromdate = clone $event['start'];
                $fromdate->setTimezone($this->server_timezone);
                $sqlresult = $this->rc->db->query(sprintf(
                  "SELECT event_id FROM " . $this->db_events . "
                   WHERE calendar_id IN (%s)
                   AND %s >= ?
                   AND recurrence_id=?",
                  $this->calendar_ids,
                  $this->rc->db->quote_identifier('start')
                  ),
                  $fromdate->format(self::DB_DATE_FORMAT),
                  $master['id']);
                if ($count = $this->rc->db->num_rows($sqlresult))
                  $event['recurrence']['COUNT'] = $count;
              }
            
              $update_recurring = true;
              $event['recurrence_id'] = 0;
              $event['isexception'] = 0;
              $event['_instance'] = '';
              break;
            }
            // else: 'future' == 'all' if modifying the master event
          
          default:  // 'all' is default
            $event['id'] = $master['id'];
            $event['recurrence_id'] = 0;
            
            // use start date from master but try to be smart on time or duration changes
            $old_start_date = $old['start']->format('Y-m-d');
            $old_start_time = $old['allday'] ? '' : $old['start']->format('H:i');
            $old_duration = $old['end']->format('U') - $old['start']->format('U');
            
            $new_start_date = $event['start']->format('Y-m-d');
            $new_start_time = $event['allday'] ? '' : $event['start']->format('H:i');
            $new_duration = $event['end']->format('U') - $event['start']->format('U');
            
            $diff = $old_start_date != $new_start_date || $old_start_time != $new_start_time || $old_duration != $new_duration;
            $date_shift = $old['start']->diff($event['start']);
            
            // shifted or resized
            if ($diff && ($old_start_date == $new_start_date || $old_duration == $new_duration)) {
              $event['start'] = $master['start']->add($old['start']->diff($event['start']));
              $event['end'] = clone $event['start'];
              $event['end']->add(new DateInterval('PT'.$new_duration.'S'));
            }
            // dates did not change, use the ones from master
            else if ($new_start_date . $new_start_time == $old_start_date . $old_start_time) {
              $event['start'] = $master['start'];
              $event['end'] = $master['end'];
            }
            
            // adjust recurrence-id when start changed and therefore the entire recurrence chain changes
            if (is_array($event['recurrence']) && ($old_start_date != $new_start_date || $old_start_time != $new_start_time)
                && ($exceptions = $this->_load_exceptions($old))) {
              $recurrence_id_format = libcalendaring::recurrence_id_format($event);
              foreach ($exceptions as $exception) {
                $recurrence_id = rcube_utils::anytodatetime($exception['_instance'], $old['start']->getTimezone());
                if (is_a($recurrence_id, 'DateTime')) {
                  $recurrence_id->add($date_shift);
                  $exception['_instance'] = $recurrence_id->format($recurrence_id_format);
                  $this->_update_event($exception, false);
                }
              }
            }
            
            $ret = $event['id'];  // return master ID
            break;
        }
      }
      
      $success = $this->_update_event($event, $update_recurring);
      
      if ($success && $update_master)
        $this->_update_event($master, true);
      
      return $success ? $ret : false;
    }
    
    return false;
  }

  /**
   * Extended event editing with possible changes to the argument
   *
   * @param array  Hash array with event properties
   * @param string New participant status
   * @param array  List of hash arrays with updated attendees
   * @return boolean True on success, False on error
   */
  public function edit_rsvp(&$event, $status, $attendees)
  {
    $update_event = $event;

    // apply changes to master (and all exceptions)
    if ($event['_savemode'] == 'all' && $event['recurrence_id']) {
      $update_event = $this->get_event(array('id' => $event['recurrence_id']));
      $update_event['_savemode'] = $event['_savemode'];
      calendar::merge_attendee_data($update_event, $attendees);
    }

    if ($ret = $this->update_attendees($update_event, $attendees)) {
      // replace $event with effectively updated event (for iTip reply)
      if ($ret !== true && $ret != $update_event['id'] && ($new_event = $this->get_event(array('id' => $ret)))) {
        $event = $new_event;
      }
      else {
        $event = $update_event;
      }
    }

    return $ret;
  }

  /**
   * Update the participant status for the given attendees
   *
   * @see calendar_driver::update_attendees()
   */
  public function update_attendees(&$event, $attendees)
  {
    $success = $this->edit_event($event, true);

    // apply attendee updates to recurrence exceptions too
    if ($success && $event['_savemode'] == 'all' && !empty($event['recurrence']) && empty($event['recurrence_id']) && ($exceptions = $this->_load_exceptions($event))) {
      foreach ($exceptions as $exception) {
        calendar::merge_attendee_data($exception, $attendees);
        $this->_update_event($exception, false);
      }
    }

    return $success;
  }

  /**
   * Determine whether the current change affects scheduling and reset attendee status accordingly
   */
  private function _check_scheduling(&$event, $old, $update = true)
  {
    // skip this check when importing iCal/iTip events
    if (isset($event['sequence']) || !empty($event['_method'])) {
      return false;
    }

    $reschedule = false;

    // iterate through the list of properties considered 'significant' for scheduling
    foreach (self::$scheduling_properties as $prop) {
        $a = $old[$prop];
        $b = $event[$prop];
        if ($event['allday'] && ($prop == 'start' || $prop == 'end') && $a instanceof DateTime && $b instanceof DateTime) {
            $a = $a->format('Y-m-d');
            $b = $b->format('Y-m-d');
        }
        if ($prop == 'recurrence' && is_array($a) && is_array($b)) {
            unset($a['EXCEPTIONS'], $b['EXCEPTIONS']);
            $a = array_filter($a);
            $b = array_filter($b);

            // advanced rrule comparison: no rescheduling if series was shortened
            if ($a['COUNT'] && $b['COUNT'] && $b['COUNT'] < $a['COUNT']) {
              unset($a['COUNT'], $b['COUNT']);
            }
            else if ($a['UNTIL'] && $b['UNTIL'] && $b['UNTIL'] < $a['UNTIL']) {
              unset($a['UNTIL'], $b['UNTIL']);
            }
        }
        if ($a != $b) {
            $reschedule = true;
            break;
        }
    }

    // reset all attendee status to needs-action (#4360)
    if ($update && $reschedule && is_array($event['attendees'])) {
      $is_organizer = false;
      $emails = $this->cal->get_user_emails();
      $attendees = $event['attendees'];
      foreach ($attendees as $i => $attendee) {
        if ($attendee['role'] == 'ORGANIZER' && $attendee['email'] && in_array(strtolower($attendee['email']), $emails)) {
          $is_organizer = true;
        }
        else if ($attendee['role'] != 'ORGANIZER' && $attendee['role'] != 'NON-PARTICIPANT' && $attendee['status'] != 'DELEGATED') {
          $attendees[$i]['status'] = 'NEEDS-ACTION';
          $attendees[$i]['rsvp'] = true;
        }
      }

      // update attendees only if I'm the organizer
      if ($is_organizer || ($event['organizer'] && in_array(strtolower($event['organizer']['email']), $emails))) {
        $event['attendees'] = $attendees;
      }
    }

    return $reschedule;
  }

  /**
   * Convert save data to be used in SQL statements
   */
  private function _save_preprocess($event)
  {
    // shift dates to server's timezone (except for all-day events)
    if (!$event['allday']) {
      $event['start'] = clone $event['start'];
      $event['start']->setTimezone($this->server_timezone);
      $event['end'] = clone $event['end'];
      $event['end']->setTimezone($this->server_timezone);
    }
    
    // compose vcalendar-style recurrencue rule from structured data
    $rrule = $event['recurrence'] ? libcalendaring::to_rrule($event['recurrence']) : '';
    $event['_recurrence'] = rtrim($rrule, ';');
    $event['free_busy'] = intval($this->free_busy_map[strtolower($event['free_busy'])]);
    $event['sensitivity'] = intval($this->sensitivity_map[strtolower($event['sensitivity'])]);

    if ($event['free_busy'] == 'tentative') {
        $event['status'] = 'TENTATIVE';
    }

    if (isset($event['allday'])) {
      $event['all_day'] = $event['allday'] ? 1 : 0;
    }

    // compute absolute time to notify the user
    $event['notifyat'] = $this->_get_notification($event);

    if (is_array($event['valarms'])) {
        $event['alarms'] = $this->serialize_alarms($event['valarms']);
    }

    // process event attendees
    if (!empty($event['attendees']))
      $event['attendees'] = json_encode((array)$event['attendees']);
    else
      $event['attendees'] = '';

    return $event;
  }
  
  /**
   * Compute absolute time to notify the user
   */
  private function _get_notification($event)
  {
    if ($event['valarms'] && $event['start'] > new DateTime()) {
      $alarm = libcalendaring::get_next_alarm($event);

      if ($alarm['time'] && in_array($alarm['action'], $this->alarm_types))
        return date('Y-m-d H:i:s', $alarm['time']);
    }

    return null;
  }

  /**
   * Save the given event record to database
   *
   * @param array Event data
   * @param boolean True if recurring events instances should be updated, too
   */
  private function _update_event($event, $update_recurring = true)
  {
    $event = $this->_save_preprocess($event);
    $sql_set = array();
    $set_cols = array('start', 'end', 'all_day', 'recurrence_id', 'isexception', 'sequence', 'title', 'description', 'location', 'categories', 'url', 'free_busy', 'priority', 'sensitivity', 'status', 'attendees', 'alarms', 'notifyat');
    foreach ($set_cols as $col) {
      if (is_object($event[$col]) && is_a($event[$col], 'DateTime'))
        $sql_set[] = $this->rc->db->quote_identifier($col) . '=' . $this->rc->db->quote($event[$col]->format(self::DB_DATE_FORMAT));
      else if (is_array($event[$col]))
        $sql_set[] = $this->rc->db->quote_identifier($col) . '=' . $this->rc->db->quote(join(',', $event[$col]));
      else if (array_key_exists($col, $event))
        $sql_set[] = $this->rc->db->quote_identifier($col) . '=' . $this->rc->db->quote($event[$col]);
    }
    
    if ($event['_recurrence'])
      $sql_set[] = $this->rc->db->quote_identifier('recurrence') . '=' . $this->rc->db->quote($event['_recurrence']);
    
    if ($event['_instance'])
      $sql_set[] = $this->rc->db->quote_identifier('instance') . '=' . $this->rc->db->quote($event['_instance']);
    
    if ($event['_fromcalendar'] && $event['_fromcalendar'] != $event['calendar'])
        $sql_set[] = 'calendar_id=' . $this->rc->db->quote($event['calendar']);
    
    $query = $this->rc->db->query(sprintf(
      "UPDATE " . $this->db_events . "
       SET   changed=%s %s
       WHERE event_id=?
       AND   calendar_id IN (" . $this->calendar_ids . ")",
        $this->rc->db->now(),
        ($sql_set ? ', ' . join(', ', $sql_set) : '')
      ),
      $event['id']
    );

    $success = $this->rc->db->affected_rows($query);

    // add attachments
    if ($success && !empty($event['attachments'])) {
      foreach ($event['attachments'] as $attachment) {
        $this->add_attachment($attachment, $event['id']);
        unset($attachment);
      }
    }

    // remove attachments
    if ($success && !empty($event['deleted_attachments'])) {
      foreach ($event['deleted_attachments'] as $attachment) {
        $this->remove_attachment($attachment, $event['id']);
      }
    }

    if ($success) {
      unset($this->cache[$event['id']]);
      if ($update_recurring)
        $this->_update_recurring($event);
    }

    return $success;
  }

  /**
   * Insert "fake" entries for recurring occurences of this event
   */
  private function _update_recurring($event)
  {
    if (empty($this->calendars))
      return;

    if (!empty($event['recurrence'])) {
      $exdata = array();
      $exceptions = $this->_load_exceptions($event);

      foreach ($exceptions as $exception) {
        $exdate = substr($exception['_instance'], 0, 8);
        $exdata[$exdate] = $exception;
      }
    }

    // clear existing recurrence copies
    $this->rc->db->query(
      "DELETE FROM " . $this->db_events . "
       WHERE recurrence_id=?
       AND isexception=0
       AND calendar_id IN (" . $this->calendar_ids . ")",
       $event['id']
    );

    // create new fake entries
    if (!empty($event['recurrence'])) {
      // include library class
      require_once($this->cal->home . '/lib/calendar_recurrence.php');
      
      $recurrence = new calendar_recurrence($this->cal, $event);

      $count = 0;
      $event['allday'] = $event['all_day'];
      $duration = $event['start']->diff($event['end']);
      $recurrence_id_format = libcalendaring::recurrence_id_format($event);
      while ($next_start = $recurrence->next_start()) {
        $instance = $next_start->format($recurrence_id_format);
        $datestr = substr($instance, 0, 8);

        // skip exceptions
        // TODO: merge updated data from master event
        if ($exdata[$datestr]) {
          continue;
        }

        $next_start->setTimezone($this->server_timezone);
        $next_end = clone $next_start;
        $next_end->add($duration);

        $notify_at = $this->_get_notification(array('alarms' => $event['alarms'], 'start' => $next_start, 'end' => $next_end, 'status' => $event['status']));
        $query = $this->rc->db->query(sprintf(
          "INSERT INTO " . $this->db_events . "
           (calendar_id, recurrence_id, created, changed, uid, instance, %s, %s, all_day, sequence, recurrence, title, description, location, categories, url, free_busy, priority, sensitivity, status, alarms, attendees, notifyat)
            SELECT calendar_id, ?, %s, %s, uid, ?, ?, ?, all_day, sequence, recurrence, title, description, location, categories, url, free_busy, priority, sensitivity, status, alarms, attendees, ?
            FROM  " . $this->db_events . " WHERE event_id=? AND calendar_id IN (" . $this->calendar_ids . ")",
            $this->rc->db->quote_identifier('start'),
            $this->rc->db->quote_identifier('end'),
            $this->rc->db->now(),
            $this->rc->db->now()
          ),
          $event['id'],
          $instance,
          $next_start->format(self::DB_DATE_FORMAT),
          $next_end->format(self::DB_DATE_FORMAT),
          $notify_at,
          $event['id']
        );
        
        if (!$this->rc->db->affected_rows($query))
          break;
        
        // stop adding events for inifinite recurrence after 20 years
        if (++$count > 999 || (!$recurrence->recurEnd && !$recurrence->recurCount && $next_start->format('Y') > date('Y') + 20))
          break;
      }
      
      // remove all exceptions after recurrence end
      if ($next_end && !empty($exceptions)) {
          $this->rc->db->query(
          "DELETE FROM " . $this->db_events . "
           WHERE `recurrence_id`=?
           AND `isexception`=1
           AND `start` > ?
           AND `calendar_id` IN (" . $this->calendar_ids . ")",
           $event['id'],
           $next_end->format(self::DB_DATE_FORMAT)
        );
      }
    }
  }
  
  /**
   *
   */
  private function _load_exceptions($event, $instance_id = null)
  {
    $sql_add_where = '';
    if (!empty($instance_id)) {
      $sql_add_where = 'AND `instance`=?';
    }

    $result = $this->rc->db->query(
      "SELECT * FROM " . $this->db_events . "
       WHERE `recurrence_id`=?
       AND `isexception`=1
       AND `calendar_id` IN (" . $this->calendar_ids . ")
       $sql_add_where
       ORDER BY `instance`, `start`",
       $event['id'],
       $instance_id
    );

    $exceptions = array();
    while ($result && ($sql_arr = $this->rc->db->fetch_assoc($result)) && $sql_arr['event_id']) {
      $exception = $this->_read_postprocess($sql_arr);
      $instance = $exception['_instance'] ?: $exception['start']->format($exception['allday'] ? 'Ymd' : 'Ymd\THis');
      $exceptions[$instance] = $exception;
    }

    return $exceptions;
  }

  /**
   * Move a single event
   *
   * @param array Hash array with event properties
   * @see calendar_driver::move_event()
   */
  public function move_event($event)
  {
    // let edit_event() do all the magic
    return $this->edit_event($event + (array)$this->get_event($event));
  }

  /**
   * Resize a single event
   *
   * @param array Hash array with event properties
   * @see calendar_driver::resize_event()
   */
  public function resize_event($event)
  {
    // let edit_event() do all the magic
    return $this->edit_event($event + (array)$this->get_event($event));
  }

  /**
   * Remove a single event from the database
   *
   * @param array   Hash array with event properties
   * @param boolean Remove record irreversible (@TODO)
   *
   * @see calendar_driver::remove_event()
   */
  public function remove_event($event, $force = true)
  {
    if (!empty($this->calendars)) {
      $event += (array)$this->get_event($event);
      $master = $event;
      $update_master = false;
      $savemode = 'all';
      $ret = true;

      // read master if deleting a recurring event
      if ($event['recurrence'] || $event['recurrence_id']) {
        $master = $event['recurrence_id'] ? $this->get_event(array('id' => $event['recurrence_id'])) : $event;
        $savemode = $event['_savemode'];
      }

      switch ($savemode) {
        case 'current':
          // add exception to master event
          $master['recurrence']['EXDATE'][] = $event['start'];
          $update_master = true;
          
          // just delete this single occurence
          $query = $this->rc->db->query(
            "DELETE FROM " . $this->db_events . "
             WHERE calendar_id IN (" . $this->calendar_ids . ")
             AND event_id=?",
            $event['id']
          );
          break;

        case 'future':
          if ($master['id'] != $event['id']) {
            // set until-date on master event
            $master['recurrence']['UNTIL'] = clone $event['start'];
            $master['recurrence']['UNTIL']->sub(new DateInterval('P1D'));
            unset($master['recurrence']['COUNT']);
            $update_master = true;
            
            // delete this and all future instances
            $fromdate = clone $event['start'];
            $fromdate->setTimezone($this->server_timezone);
            $query = $this->rc->db->query(
              "DELETE FROM " . $this->db_events . "
               WHERE calendar_id IN (" . $this->calendar_ids . ")
               AND " . $this->rc->db->quote_identifier('start') . " >= ?
               AND recurrence_id=?",
              $fromdate->format(self::DB_DATE_FORMAT),
              $master['id']
            );
            $ret = $master['id'];
            break;
          }
          // else: future == all if modifying the master event

        default:  // 'all' is default
          $query = $this->rc->db->query(
            "DELETE FROM " . $this->db_events . "
             WHERE (event_id=? OR recurrence_id=?)
             AND calendar_id IN (" . $this->calendar_ids . ")",
             $master['id'],
             $master['id']
          );
          break;
      }

      $success = $this->rc->db->affected_rows($query);
      if ($success && $update_master)
        $this->_update_event($master, true);

      return $success ? $ret : false;
    }
    
    return false;
  }

  /**
   * Return data of a specific event
   * @param mixed  Hash array with event properties or event UID
   * @param integer Bitmask defining the scope to search events in
   * @param boolean If true, recurrence exceptions shall be added
   * @return array Hash array with event properties
   */
  public function get_event($event, $scope = 0, $full = false)
  {
    $id  = is_array($event) ? ($event['id'] ?: $event['uid']) : $event;
    $cal = is_array($event) ? $event['calendar'] : null;
    $col = is_array($event) && is_numeric($id) ? 'event_id' : 'uid';

    $where_add = '';
    if (is_array($event) && !$event['id'] && !empty($event['_instance'])) {
      $where_add = 'AND instance=' . $this->rc->db->quote($event['_instance']);
    }

    if ($this->cache[$id])
      return $this->cache[$id];

    // get event from the address books birthday calendar
    if ($cal == self::BIRTHDAY_CALENDAR_ID) {
      return $this->get_birthday_event($id);
    }

    if ($scope & self::FILTER_ACTIVE) {
      $calendars = $this->calendars;
      foreach ($calendars as $idx => $cal) {
        if (!$cal['active']) {
          unset($calendars[$idx]);
        }
      }
      $cals = join(',', $calendars);
    }
    else {
      $cals = $this->calendar_ids;
    }

    $result = $this->rc->db->query(sprintf(
      "SELECT e.*, (SELECT COUNT(attachment_id) FROM " . $this->db_attachments . " 
         WHERE event_id = e.event_id OR event_id = e.recurrence_id) AS _attachments
       FROM " . $this->db_events . " AS e
       WHERE e.calendar_id IN (%s)
       AND e.$col=?
       %s",
       $cals,
       $where_add
      ),
      $id);

    if ($result && ($sql_arr = $this->rc->db->fetch_assoc($result)) && $sql_arr['event_id']) {
      $event = $this->_read_postprocess($sql_arr);

      // also load recurrence exceptions
      if (!empty($event['recurrence']) && $full) {
        $event['recurrence']['EXCEPTIONS'] = array_values($this->_load_exceptions($event));
      }

      $this->cache[$id] = $event;
      return $this->cache[$id];
    }

    return false;
  }

  /**
   * Get event data
   *
   * @see calendar_driver::load_events()
   */
  public function load_events($start, $end, $query = null, $calendars = null, $virtual = 1, $modifiedsince = null)
  {
    if (empty($calendars))
      $calendars = array_keys($this->calendars);
    else if (!is_array($calendars))
      $calendars = explode(',', strval($calendars));
      
    // only allow to select from calendars of this use
    $calendar_ids = array_map(array($this->rc->db, 'quote'), array_intersect($calendars, array_keys($this->calendars)));

    // compose (slow) SQL query for searching
    // FIXME: improve searching using a dedicated col and normalized values
    if ($query) {
      foreach (array('title','location','description','categories','attendees') as $col)
        $sql_query[] = $this->rc->db->ilike($col, '%'.$query.'%');
      $sql_add = 'AND (' . join(' OR ', $sql_query) . ')';
    }
    
    if (!$virtual)
      $sql_add .= ' AND e.recurrence_id = 0';
    
    if ($modifiedsince)
      $sql_add .= ' AND e.changed >= ' . $this->rc->db->quote(date('Y-m-d H:i:s', $modifiedsince));
    
    $events = array();
    if (!empty($calendar_ids)) {
      $result = $this->rc->db->query(sprintf(
        "SELECT e.*, (SELECT COUNT(attachment_id) FROM " . $this->db_attachments . "
            WHERE event_id = e.event_id OR event_id = e.recurrence_id) AS _attachments
         FROM " . $this->db_events . " e
         WHERE e.calendar_id IN (%s)
            AND e.start <= %s AND e.end >= %s
            %s",
         join(',', $calendar_ids),
         $this->rc->db->fromunixtime($end),
         $this->rc->db->fromunixtime($start),
         $sql_add
       ));

      while ($result && ($sql_arr = $this->rc->db->fetch_assoc($result))) {
        $event = $this->_read_postprocess($sql_arr);
        $add = true;

        if (!empty($event['recurrence']) && !$event['recurrence_id']) {
          // load recurrence exceptions (i.e. for export)
          if (!$virtual) {
            $event['recurrence']['EXCEPTIONS'] = $this->_load_exceptions($event);
          }
          // check for exception on first instance
          else {
            $instance = libcalendaring::recurrence_instance_identifier($event);
            $exceptions = $this->_load_exceptions($event, $instance);
            if ($exceptions && is_array($exceptions[$instance])) {
              $event = $exceptions[$instance];
              $add = false;
            }
          }
        }

        if ($add)
          $events[] = $event;
      }
    }

    // add events from the address books birthday calendar
    if (in_array(self::BIRTHDAY_CALENDAR_ID, $calendars) && empty($query)) {
      $events = array_merge($events, $this->load_birthday_events($start, $end, $search, $modifiedsince));
    }

    return $events;
  }

  /**
   * Get number of events in the given calendar
   *
   * @param  mixed   List of calendar IDs to count events (either as array or comma-separated string)
   * @param  integer Date range start (unix timestamp)
   * @param  integer Date range end (unix timestamp)
   * @return array   Hash array with counts grouped by calendar ID
   */
  public function count_events($calendars, $start, $end = null)
  {
      // not implemented
      return array();
  }

  /**
   * Convert sql record into a rcube style event object
   */
  private function _read_postprocess($event)
  {
    $free_busy_map = array_flip($this->free_busy_map);
    $sensitivity_map = array_flip($this->sensitivity_map);
    
    $event['id'] = $event['event_id'];
    $event['start'] = new DateTime($event['start']);
    $event['end'] = new DateTime($event['end']);
    $event['allday'] = intval($event['all_day']);
    $event['created'] = new DateTime($event['created']);
    $event['changed'] = new DateTime($event['changed']);
    $event['free_busy'] = $free_busy_map[$event['free_busy']];
    $event['sensitivity'] = $sensitivity_map[$event['sensitivity']];
    $event['calendar'] = $event['calendar_id'];
    $event['recurrence_id'] = intval($event['recurrence_id']);
    $event['isexception'] = intval($event['isexception']);
    
    // parse recurrence rule
    if ($event['recurrence'] && preg_match_all('/([A-Z]+)=([^;]+);?/', $event['recurrence'], $m, PREG_SET_ORDER)) {
      $event['recurrence'] = array();
      foreach ($m as $rr) {
        if (is_numeric($rr[2]))
          $rr[2] = intval($rr[2]);
        else if ($rr[1] == 'UNTIL')
          $rr[2] = date_create($rr[2]);
        else if ($rr[1] == 'RDATE')
          $rr[2] = array_map('date_create', explode(',', $rr[2]));
        else if ($rr[1] == 'EXDATE')
          $rr[2] = array_map('date_create', explode(',', $rr[2]));
        $event['recurrence'][$rr[1]] = $rr[2];
      }
    }
    
    if ($event['recurrence_id']) {
      libcalendaring::identify_recurrence_instance($event);
    }
    
    if (strlen($event['instance'])) {
      $event['_instance'] = $event['instance'];

      if (empty($event['recurrence_id'])) {
        $event['recurrence_date'] = rcube_utils::anytodatetime($event['_instance'], $event['start']->getTimezone());
      }
    }
    
    if ($event['_attachments'] > 0) {
      $event['attachments'] = (array)$this->list_attachments($event);
    }
    
    // decode serialized event attendees
    if (strlen($event['attendees'])) {
      $event['attendees'] = $this->unserialize_attendees($event['attendees']);
    }
    else {
      $event['attendees'] = array();
    }
    
    // decode serialized alarms
    if ($event['alarms']) {
      $event['valarms'] = $this->unserialize_alarms($event['alarms']);
    }
    
    unset($event['event_id'], $event['calendar_id'], $event['notifyat'], $event['all_day'], $event['instance'], $event['_attachments']);
    return $event;
  }

  /**
   * Get a list of pending alarms to be displayed to the user
   *
   * @see calendar_driver::pending_alarms()
   */
  public function pending_alarms($time, $calendars = null)
  {
    if (empty($calendars))
      $calendars = array_keys($this->calendars);
    else if (is_string($calendars))
      $calendars = explode(',', $calendars);
    
    // only allow to select from calendars with activated alarms
    $calendar_ids = array();
    foreach ($calendars as $cid) {
      if ($this->calendars[$cid] && $this->calendars[$cid]['showalarms'])
        $calendar_ids[] = $cid;
    }
    $calendar_ids = array_map(array($this->rc->db, 'quote'), $calendar_ids);
    
    $alarms = array();
    if (!empty($calendar_ids)) {
      $result = $this->rc->db->query(sprintf(
        "SELECT * FROM " . $this->db_events . "
         WHERE calendar_id IN (%s)
         AND notifyat <= %s AND %s > %s",
         join(',', $calendar_ids),
         $this->rc->db->fromunixtime($time),
         $this->rc->db->quote_identifier('end'),
         $this->rc->db->fromunixtime($time)
       ));

      while ($result && ($event = $this->rc->db->fetch_assoc($result)))
        $alarms[] = $this->_read_postprocess($event);
    }

    return $alarms;
  }

  /**
   * Feedback after showing/sending an alarm notification
   *
   * @see calendar_driver::dismiss_alarm()
   */
  public function dismiss_alarm($event_id, $snooze = 0)
  {
    // set new notifyat time or unset if not snoozed
    $notify_at = $snooze > 0 ? date(self::DB_DATE_FORMAT, time() + $snooze) : null;
    
    $query = $this->rc->db->query(sprintf(
      "UPDATE " . $this->db_events . "
       SET   changed=%s, notifyat=?
       WHERE event_id=?
       AND calendar_id IN (" . $this->calendar_ids . ")",
        $this->rc->db->now()),
      $notify_at,
      $event_id
    );
    
    return $this->rc->db->affected_rows($query);
  }

  /**
   * Save an attachment related to the given event
   */
  private function add_attachment($attachment, $event_id)
  {
    $data = $attachment['data'] ? $attachment['data'] : file_get_contents($attachment['path']);
    
    $query = $this->rc->db->query(
      "INSERT INTO " . $this->db_attachments .
      " (event_id, filename, mimetype, size, data)" .
      " VALUES (?, ?, ?, ?, ?)",
      $event_id,
      $attachment['name'],
      $attachment['mimetype'],
      strlen($data),
      base64_encode($data)
    );

    return $this->rc->db->affected_rows($query);
  }

  /**
   * Remove a specific attachment from the given event
   */
  private function remove_attachment($attachment_id, $event_id)
  {
    $query = $this->rc->db->query(
      "DELETE FROM " . $this->db_attachments .
      " WHERE attachment_id = ?" .
        " AND event_id IN (SELECT event_id FROM " . $this->db_events .
          " WHERE event_id = ?"  .
            " AND calendar_id IN (" . $this->calendar_ids . "))",
      $attachment_id,
      $event_id
    );

    return $this->rc->db->affected_rows($query);
  }

  /**
   * List attachments of specified event
   */
  public function list_attachments($event)
  {
    $attachments = array();

    if (!empty($this->calendar_ids)) {
      $result = $this->rc->db->query(
        "SELECT attachment_id AS id, filename AS name, mimetype, size " .
        " FROM " . $this->db_attachments .
        " WHERE event_id IN (SELECT event_id FROM " . $this->db_events .
          " WHERE event_id=?"  .
            " AND calendar_id IN (" . $this->calendar_ids . "))".
        " ORDER BY filename",
        $event['recurrence_id'] ? $event['recurrence_id'] : $event['event_id']
      );

      while ($result && ($arr = $this->rc->db->fetch_assoc($result))) {
        $attachments[] = $arr;
      }
    }

    return $attachments;
  }

  /**
   * Get attachment properties
   */
  public function get_attachment($id, $event)
  {
    if (!empty($this->calendar_ids)) {
      $result = $this->rc->db->query(
        "SELECT attachment_id AS id, filename AS name, mimetype, size " .
        " FROM " . $this->db_attachments .
        " WHERE attachment_id=?".
          " AND event_id=?",
        $id,
        $event['recurrence_id'] ? $event['recurrence_id'] : $event['id']
      );

      if ($result && ($arr = $this->rc->db->fetch_assoc($result))) {
        return $arr;
      }
    }

    return null;
  }

  /**
   * Get attachment body
   */
  public function get_attachment_body($id, $event)
  {
    if (!empty($this->calendar_ids)) {
      $result = $this->rc->db->query(
        "SELECT data " .
        " FROM " . $this->db_attachments .
        " WHERE attachment_id=?".
          " AND event_id=?",
        $id,
        $event['id']
      );

      if ($result && ($arr = $this->rc->db->fetch_assoc($result))) {
        return base64_decode($arr['data']);
      }
    }

    return null;
  }

  /**
   * Remove the given category
   */
  public function remove_category($name)
  {
    $query = $this->rc->db->query(
      "UPDATE " . $this->db_events . "
       SET   categories=''
       WHERE categories=?
       AND   calendar_id IN (" . $this->calendar_ids . ")",
      $name
    );
    
    return $this->rc->db->affected_rows($query);
  }

  /**
   * Update/replace a category
   */
  public function replace_category($oldname, $name, $color)
  {
    $query = $this->rc->db->query(
      "UPDATE " . $this->db_events . "
       SET   categories=?
       WHERE categories=?
       AND   calendar_id IN (" . $this->calendar_ids . ")",
      $name,
      $oldname
    );
    
    return $this->rc->db->affected_rows($query);
  }

  /**
   * Helper method to serialize the list of alarms into a string
   */
  private function serialize_alarms($valarms)
  {
      foreach ((array)$valarms as $i => $alarm) {
          if ($alarm['trigger'] instanceof DateTime) {
              $valarms[$i]['trigger'] = '@' . $alarm['trigger']->format('c');
          }
      }

      return $valarms ? json_encode($valarms) : null;
  }

  /**
   * Helper method to decode a serialized list of alarms
   */
  private function unserialize_alarms($alarms)
  {
      // decode json serialized alarms
      if ($alarms && $alarms[0] == '[') {
          $valarms = json_decode($alarms, true);
          foreach ($valarms as $i => $alarm) {
              if ($alarm['trigger'][0] == '@') {
                  try {
                      $valarms[$i]['trigger'] = new DateTime(substr($alarm['trigger'], 1));
                  }
                  catch (Exception $e) {
                      unset($valarms[$i]);
                  }
              }
          }
      }
      // convert legacy alarms data
      else if (strlen($alarms)) {
          list($trigger, $action) = explode(':', $alarms, 2);
          if ($trigger = libcalendaring::parse_alarm_value($trigger)) {
              $valarms = array(array('action' => $action, 'trigger' => $trigger[3] ?: $trigger[0]));
          }
      }

      return $valarms;
  }

  /**
   * Helper method to decode the attendees list from string
   */
  private function unserialize_attendees($s_attendees)
  {
    $attendees = array();

    // decode json serialized string
    if ($s_attendees[0] == '[') {
      $attendees = json_decode($s_attendees, true);
    }
    // decode the old serialization format
    else {
      foreach (explode("\n", $s_attendees) as $line) {
        $att = array();
        foreach (rcube_utils::explode_quoted_string(';', $line) as $prop) {
          list($key, $value) = explode("=", $prop);
          $att[strtolower($key)] = stripslashes(trim($value, '""'));
        }
        $attendees[] = $att;
      }
    }

    return $attendees;
  }

  /**
   * Handler for user_delete plugin hook
   */
  public function user_delete($args)
  {
      $db = $this->rc->db;
      $user = $args['user'];
      $event_ids = array();

      $events = $db->query(
          "SELECT event_id FROM " . $this->db_events . " AS ev" .
          " LEFT JOIN " . $this->db_calendars . " cal ON (ev.calendar_id = cal.calendar_id)".
          " WHERE user_id=?",
          $user->ID);

      while ($row = $db->fetch_assoc($events)) {
          $event_ids[] = $row['event_id'];
      }

      if (!empty($event_ids)) {
          foreach (array($this->db_attachments, $this->db_events) as $table) {
              $db->query(sprintf("DELETE FROM $table WHERE event_id IN (%s)", join(',', $event_ids)));
          }
      }

      foreach (array($this->db_calendars, 'itipinvitations') as $table) {
          $db->query("DELETE FROM $table WHERE user_id=?", $user->ID);
      }
  }

}
