<?php

/**
 * Driver interface for the Calendar plugin
 *
 * @version @package_version@
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


/**
 * Struct of an internal event object how it is passed from/to the driver classes:
 *
 *  $event = array(
 *            'id' => 'Event ID used for editing',
 *           'uid' => 'Unique identifier of this event',
 *      'calendar' => 'Calendar identifier to add event to or where the event is stored',
 *         'start' => DateTime,  // Event start date/time as DateTime object
 *           'end' => DateTime,  // Event end date/time as DateTime object
 *        'allday' => true|false,  // Boolean flag if this is an all-day event
 *       'changed' => DateTime,    // Last modification date of event
 *         'title' => 'Event title/summary',
 *      'location' => 'Location string',
 *   'description' => 'Event description',
 *           'url' => 'URL to more information',
 *    'recurrence' => array(   // Recurrence definition according to iCalendar (RFC 2445) specification as list of key-value pairs
 *            'FREQ' => 'DAILY|WEEKLY|MONTHLY|YEARLY',
 *        'INTERVAL' => 1...n,
 *           'UNTIL' => DateTime,
 *           'COUNT' => 1..n,   // number of times
 *                      // + more properties (see http://www.kanzaki.com/docs/ical/recur.html)
 *          'EXDATE' => array(),  // list of DateTime objects of exception Dates/Times
 *      'EXCEPTIONS' => array(<event>),  list of event objects which denote exceptions in the recurrence chain
 *    ),
 * 'recurrence_id' => 'ID of the recurrence group',   // usually the ID of the starting event
 *     '_instance' => 'ID of the recurring instance',   // identifies an instance within a recurrence chain
 *    'categories' => 'Event category',
 *     'free_busy' => 'free|busy|outofoffice|tentative',  // Show time as
 *        'status' => 'TENTATIVE|CONFIRMED|CANCELLED',    // event status according to RFC 2445
 *      'priority' => 0-9,     // Event priority (0=undefined, 1=highest, 9=lowest)
 *   'sensitivity' => 'public|private|confidential',   // Event sensitivity
 *        'alarms' => '-15M:DISPLAY',  // DEPRECATED Reminder settings inspired by valarm definition (e.g. display alert 15 minutes before event)
 *       'valarms' => array(           // List of reminders (new format), each represented as a hash array:
 *                  array(
 *                     'trigger' => '-PT90M',     // ISO 8601 period string prefixed with '+' or '-', or DateTime object
 *                      'action' => 'DISPLAY|EMAIL|AUDIO',
 *                    'duration' => 'PT15M',      // ISO 8601 period string
 *                      'repeat' => 0,            // number of repetitions
 *                 'description' => '',        // text to display for DISPLAY actions
 *                     'summary' => '',        // message text for EMAIL actions
 *                   'attendees' => array(),   // list of email addresses to receive alarm messages
 *                  ),
 *   ),
 *   'attachments' => array(   // List of attachments
 *            'name' => 'File name',
 *        'mimetype' => 'Content type',
 *            'size' => 1..n, // in bytes
 *              'id' => 'Attachment identifier'
 *   ),
 * 'deleted_attachments' => array(), // array of attachment identifiers to delete when event is updated
 *     'attendees' => array(   // List of event participants
 *            'name' => 'Participant name',
 *           'email' => 'Participant e-mail address',  // used as identifier
 *            'role' => 'ORGANIZER|REQ-PARTICIPANT|OPT-PARTICIPANT|CHAIR',
 *          'status' => 'NEEDS-ACTION|UNKNOWN|ACCEPTED|TENTATIVE|DECLINED'
 *            'rsvp' => true|false,
 *    ),
 *
 *     '_savemode' => 'all|future|current|new',   // How changes on recurring event should be handled
 *       '_notify' => true|false,  // whether to notify event attendees about changes
 * '_fromcalendar' => 'Calendar identifier where the event was stored before',
 *  );
 */

/**
 * Interface definition for calendar driver classes
 */
abstract class calendar_driver
{
  const FILTER_ALL           = 0;
  const FILTER_WRITEABLE     = 1;
  const FILTER_INSERTABLE    = 2;
  const FILTER_ACTIVE        = 4;
  const FILTER_PERSONAL      = 8;
  const FILTER_PRIVATE       = 16;
  const FILTER_CONFIDENTIAL  = 32;
  const BIRTHDAY_CALENDAR_ID = '__bdays__';

  // features supported by backend
  public $alarms = false;
  public $attendees = false;
  public $freebusy = false;
  public $attachments = false;
  public $undelete = false;
  public $history = false;
  public $categoriesimmutable = false;
  public $alarm_types = array('DISPLAY');
  public $alarm_absolute = true;
  public $last_error;

  protected $default_categories = array(
    'Personal' => 'c0c0c0',
    'Work'     => 'ff0000',
    'Family'   => '00ff00',
    'Holiday'  => 'ff6600',
  );

  /**
   * Get a list of available calendars from this source
   *
   * @param integer Bitmask defining filter criterias.
   *          See FILTER_* constants for possible values.
   * @return array List of calendars
   */
  abstract function list_calendars($filter = 0);

  /**
   * Create a new calendar assigned to the current user
   *
   * @param array Hash array with calendar properties
   *        name: Calendar name
   *       color: The color of the calendar
   *  showalarms: True if alarms are enabled
   * @return mixed ID of the calendar on success, False on error
   */
  abstract function create_calendar($prop);

  /**
   * Update properties of an existing calendar
   *
   * @param array Hash array with calendar properties
   *          id: Calendar Identifier
   *        name: Calendar name
   *       color: The color of the calendar
   *  showalarms: True if alarms are enabled (if supported)
   * @return boolean True on success, Fales on failure
   */
  abstract function edit_calendar($prop);
  
  /**
   * Set active/subscribed state of a calendar
   *
   * @param array Hash array with calendar properties
   *          id: Calendar Identifier
   *      active: True if calendar is active, false if not
   * @return boolean True on success, Fales on failure
   */
  abstract function subscribe_calendar($prop);

  /**
   * Delete the given calendar with all its contents
   *
   * @param array Hash array with calendar properties
   *      id: Calendar Identifier
   * @return boolean True on success, Fales on failure
   */
  abstract function delete_calendar($prop);

  /**
   * Search for shared or otherwise not listed calendars the user has access
   *
   * @param string Search string
   * @param string Section/source to search
   * @return array List of calendars
   */
  abstract function search_calendars($query, $source);

  /**
   * Add a single event to the database
   *
   * @param array Hash array with event properties (see header of this file)
   * @return mixed New event ID on success, False on error
   */
  abstract function new_event($event);

  /**
   * Update an event entry with the given data
   *
   * @param array Hash array with event properties (see header of this file)
   * @return boolean True on success, False on error
   */
  abstract function edit_event($event);

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
    return $this->edit_event($event);
  }

  /**
   * Update the participant status for the given attendee
   *
   * @param array  Hash array with event properties
   * @param array  List of hash arrays each represeting an updated attendee
   * @return boolean True on success, False on error
   */
  public function update_attendees(&$event, $attendees)
  {
    return $this->edit_event($event);
  }

  /**
   * Move a single event
   *
   * @param array Hash array with event properties:
   *      id: Event identifier
   *   start: Event start date/time as DateTime object
   *     end: Event end date/time as DateTime object
   *  allday: Boolean flag if this is an all-day event
   * @return boolean True on success, False on error
   */
  abstract function move_event($event);

  /**
   * Resize a single event
   *
   * @param array Hash array with event properties:
   *      id: Event identifier
   *   start: Event start date/time as DateTime object with timezone
   *     end: Event end date/time as DateTime object with timezone
   * @return boolean True on success, False on error
   */
  abstract function resize_event($event);

  /**
   * Remove a single event from the database
   *
   * @param array   Hash array with event properties:
   *      id: Event identifier
   * @param boolean Remove event irreversible (mark as deleted otherwise,
   *                if supported by the backend)
   *
   * @return boolean True on success, False on error
   */
  abstract function remove_event($event, $force = true);

  /**
   * Restores a single deleted event (if supported)
   *
   * @param array Hash array with event properties:
   *      id: Event identifier
   *
   * @return boolean True on success, False on error
   */
  public function restore_event($event)
  {
    return false;
  }

  /**
   * Return data of a single event
   *
   * @param mixed  UID string or hash array with event properties:
   *         id: Event identifier
   *        uid: Event UID
   *  _instance: Instance identifier in combination with uid (optional)
   *   calendar: Calendar identifier (optional)
   * @param integer Bitmask defining the scope to search events in.
   *          See FILTER_* constants for possible values.
   * @param boolean If true, recurrence exceptions shall be added
   *
   * @return array Event object as hash array
   */
  abstract function get_event($event, $scope = 0, $full = false);

  /**
   * Get events from source.
   *
   * @param  integer Date range start (unix timestamp)
   * @param  integer Date range end (unix timestamp)
   * @param  string  Search query (optional)
   * @param  mixed   List of calendar IDs to load events from (either as array or comma-separated string)
   * @param  boolean Include virtual/recurring events (optional)
   * @param  integer Only list events modified since this time (unix timestamp)
   * @return array A list of event objects (see header of this file for struct of an event)
   */
  abstract function load_events($start, $end, $query = null, $calendars = null, $virtual = 1, $modifiedsince = null);

  /**
   * Get number of events in the given calendar
   *
   * @param  mixed   List of calendar IDs to count events (either as array or comma-separated string)
   * @param  integer Date range start (unix timestamp)
   * @param  integer Date range end (unix timestamp)
   * @return array   Hash array with counts grouped by calendar ID
   */
  abstract function count_events($calendars, $start, $end = null);

  /**
   * Get a list of pending alarms to be displayed to the user
   *
   * @param  integer Current time (unix timestamp)
   * @param  mixed   List of calendar IDs to show alarms for (either as array or comma-separated string)
   * @return array A list of alarms, each encoded as hash array:
   *         id: Event identifier
   *        uid: Unique identifier of this event
   *      start: Event start date/time as DateTime object
   *        end: Event end date/time as DateTime object
   *     allday: Boolean flag if this is an all-day event
   *      title: Event title/summary
   *   location: Location string
   */
  abstract function pending_alarms($time, $calendars = null);

  /**
   * (User) feedback after showing an alarm notification
   * This should mark the alarm as 'shown' or snooze it for the given amount of time
   *
   * @param  string  Event identifier
   * @param  integer Suspend the alarm for this number of seconds
   */
  abstract function dismiss_alarm($event_id, $snooze = 0);

  /**
   * Check the given event object for validity
   *
   * @param array Event object as hash array
   * @return boolean True if valid, false if not
   */
  public function validate($event)
  {
    $valid = true;

    if (!is_object($event['start']) || !is_a($event['start'], 'DateTime'))
      $valid = false;
    if (!is_object($event['end']) || !is_a($event['end'], 'DateTime'))
      $valid = false;

    return $valid;
  }


  /**
   * Get list of event's attachments.
   * Drivers can return list of attachments as event property.
   * If they will do not do this list_attachments() method will be used.
   *
   * @param array $event Hash array with event properties:
   *         id: Event identifier
   *   calendar: Calendar identifier
   *
   * @return array List of attachments, each as hash array:
   *         id: Attachment identifier
   *       name: Attachment name
   *   mimetype: MIME content type of the attachment
   *       size: Attachment size
   */
  public function list_attachments($event) { }

  /**
   * Get attachment properties
   *
   * @param string $id    Attachment identifier
   * @param array  $event Hash array with event properties:
   *         id: Event identifier
   *   calendar: Calendar identifier
   *
   * @return array Hash array with attachment properties:
   *         id: Attachment identifier
   *       name: Attachment name
   *   mimetype: MIME content type of the attachment
   *       size: Attachment size
   */
  public function get_attachment($id, $event) { }

  /**
   * Get attachment body
   *
   * @param string $id    Attachment identifier
   * @param array  $event Hash array with event properties:
   *         id: Event identifier
   *   calendar: Calendar identifier
   *
   * @return string Attachment body
   */
  public function get_attachment_body($id, $event) { }

  /**
   * Build a struct representing the given message reference
   *
   * @param object|string $uri_or_headers rcube_message_header instance holding the message headers
   *                         or an URI from a stored link referencing a mail message.
   * @param string $folder  IMAP folder the message resides in
   *
   * @return array An struct referencing the given IMAP message
   */
  public function get_message_reference($uri_or_headers, $folder = null)
  {
      // to be implemented by the derived classes
      return false;
  }

  /**
   * List availabale categories
   * The default implementation reads them from config/user prefs
   */
  public function list_categories()
  {
    $rcmail = rcube::get_instance();
    return $rcmail->config->get('calendar_categories', $this->default_categories);
  }

  /**
   * Create a new category
   */
  public function add_category($name, $color) { }

  /**
   * Remove the given category
   */
  public function remove_category($name) { }

  /**
   * Update/replace a category
   */
  public function replace_category($oldname, $name, $color) { }

  /**
   * Fetch free/busy information from a person within the given range
   *
   * @param string  E-mail address of attendee
   * @param integer Requested period start date/time as unix timestamp
   * @param integer Requested period end date/time as unix timestamp
   *
   * @return array  List of busy timeslots within the requested range
   */
  public function get_freebusy_list($email, $start, $end)
  {
    return false;
  }

  /**
   * Create instances of a recurring event
   *
   * @param array  Hash array with event properties
   * @param object DateTime Start date of the recurrence window
   * @param object DateTime End date of the recurrence window
   * @return array List of recurring event instances
   */
  public function get_recurring_events($event, $start, $end = null)
  {
    $events = array();

    if ($event['recurrence']) {
      // include library class
      require_once(dirname(__FILE__) . '/../lib/calendar_recurrence.php');

      $rcmail = rcmail::get_instance();
      $recurrence = new calendar_recurrence($rcmail->plugins->get_plugin('calendar'), $event);
      $recurrence_id_format = libcalendaring::recurrence_id_format($event);

      // determine a reasonable end date if none given
      if (!$end) {
        switch ($event['recurrence']['FREQ']) {
          case 'YEARLY':  $intvl = 'P100Y'; break;
          case 'MONTHLY': $intvl = 'P20Y';  break;
          default:        $intvl = 'P10Y';  break;
        }

        $end = clone $event['start'];
        $end->add(new DateInterval($intvl));
      }

      $i = 0;
      while ($next_event = $recurrence->next_instance()) {
        // add to output if in range
        if (($next_event['start'] <= $end && $next_event['end'] >= $start)) {
          $next_event['_instance'] = $next_event['start']->format($recurrence_id_format);
          $next_event['id'] = $next_event['uid'] . '-' . $next_event['_instance'];
          $next_event['recurrence_id'] = $event['uid'];
          $events[] = $next_event;
        }
        else if ($next_event['start'] > $end) {  // stop loop if out of range
          break;
        }

        // avoid endless recursion loops
        if (++$i > 1000) {
          break;
        }
      }
    }

    return $events;
  }

  /**
   * Provide a list of revisions for the given event
   *
   * @param array  $event Hash array with event properties:
   *         id: Event identifier
   *   calendar: Calendar identifier
   *
   * @return array List of changes, each as a hash array:
   *         rev: Revision number
   *        type: Type of the change (create, update, move, delete)
   *        date: Change date
   *        user: The user who executed the change
   *          ip: Client IP
   * destination: Destination calendar for 'move' type
   */
  public function get_event_changelog($event)
  {
    return false;
  }

  /**
   * Get a list of property changes beteen two revisions of an event
   *
   * @param array  $event Hash array with event properties:
   *         id: Event identifier
   *   calendar: Calendar identifier
   * @param mixed  $rev   Revisions: "from:to"
   *
   * @return array List of property changes, each as a hash array:
   *    property: Revision number
   *         old: Old property value
   *         new: Updated property value
   */
  public function get_event_diff($event, $rev)
  {
    return false;
  }

  /**
   * Return full data of a specific revision of an event
   *
   * @param mixed  UID string or hash array with event properties:
   *        id: Event identifier
   *  calendar: Calendar identifier
   * @param mixed  $rev Revision number
   *
   * @return array Event object as hash array
   * @see self::get_event()
   */
  public function get_event_revison($event, $rev)
  {
    return false;
  }

  /**
   * Command the backend to restore a certain revision of an event.
   * This shall replace the current event with an older version.
   *
   * @param mixed  UID string or hash array with event properties:
   *        id: Event identifier
   *  calendar: Calendar identifier
   * @param mixed  $rev Revision number
   *
   * @return boolean True on success, False on failure
   */
  public function restore_event_revision($event, $rev)
  {
    return false;
  }


  /**
   * Callback function to produce driver-specific calendar create/edit form
   *
   * @param string Request action 'form-edit|form-new'
   * @param array  Calendar properties (e.g. id, color)
   * @param array  Edit form fields
   *
   * @return string HTML content of the form
   */
  public function calendar_form($action, $calendar, $formfields)
  {
    $html = '';
    foreach ($formfields as $field) {
      $html .= html::div('form-section',
        html::label($field['id'], $field['label']) .
        $field['value']);
    }

    return $html;
  }

  /**
   * Compose a list of birthday events from the contact records in the user's address books.
   *
   * This is a default implementation using Roundcube's address book API.
   * It can be overriden with a more optimized version by the individual drivers.
   *
   * @param  integer Event's new start (unix timestamp)
   * @param  integer Event's new end (unix timestamp)
   * @param  string  Search query (optional)
   * @param  integer Only list events modified since this time (unix timestamp)
   * @return array A list of event records
   */
  public function load_birthday_events($start, $end, $search = null, $modifiedsince = null)
  {
    // ignore update requests for simplicity reasons
    if (!empty($modifiedsince)) {
      return array();
    }

    // convert to DateTime for comparisons
    $start  = new DateTime('@'.$start);
    $end    = new DateTime('@'.$end);
    // extract the current year
    $year   = $start->format('Y');
    $year2  = $end->format('Y');

    $events = array();
    $search = mb_strtolower($search);
    $rcmail = rcmail::get_instance();
    $cache  = $rcmail->get_cache('calendar.birthdays', 'db', 3600);
    $cache->expunge();

    $alarm_type = $rcmail->config->get('calendar_birthdays_alarm_type', '');
    $alarm_offset = $rcmail->config->get('calendar_birthdays_alarm_offset', '-1D');
    $alarms = $alarm_type ? $alarm_offset . ':' . $alarm_type : null;

    // let the user select the address books to consider in prefs
    $selected_sources = $rcmail->config->get('calendar_birthday_adressbooks');
    $sources = $selected_sources ?: array_keys($rcmail->get_address_sources(false, true));
    foreach ($sources as $source) {
      $abook = $rcmail->get_address_book($source);

      // skip LDAP address books unless selected by the user
      if (!$abook || ($abook instanceof rcube_ldap && empty($selected_sources))) {
        continue;
      }

      $abook->set_pagesize(10000);

      // check for cached results
      $cache_records = array();
      $cached = $cache->get($source);

      // iterate over (cached) contacts
      foreach (($cached ?: $abook->search('*', '', 2, true, true, array('birthday'))) as $contact) {
        if (is_array($contact) && !empty($contact['birthday'])) {
          try {
            if (is_array($contact['birthday']))
              $contact['birthday'] = reset($contact['birthday']);

            $bday = $contact['birthday'] instanceof DateTime ? $contact['birthday'] :
                      new DateTime($contact['birthday'], new DateTimezone('UTC'));
            $birthyear = $bday->format('Y');
          }
          catch (Exception $e) {
            rcube::raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => 'BIRTHDAY PARSE ERROR: ' . $e),
              true, false);
            continue;
          }

          $display_name = rcube_addressbook::compose_display_name($contact);
          $event_title = $rcmail->gettext(array('name' => 'birthdayeventtitle', 'vars' => array('name' => $display_name)), 'calendar');

          // add stripped record to cache
          if (empty($cached)) {
            $cache_records[] = array(
              'ID' => $contact['ID'],
              'name' => $display_name,
              'birthday' => $bday->format('Y-m-d'),
            );
          }

          // filter by search term (only name is involved here)
          if (!empty($search) && strpos(mb_strtolower($event_title), $search) === false) {
            continue;
          }

          // quick-and-dirty recurrence computation: just replace the year
          $bday->setDate($year, $bday->format('n'), $bday->format('j'));
          $bday->setTime(12, 0, 0);

          // date range reaches over multiple years: use end year if not in range
          if (($bday > $end || $bday < $start) && $year2 != $year) {
            $bday->setDate($year2, $bday->format('n'), $bday->format('j'));
            $year = $year2;
          }

          // birthday is within requested range
          if ($bday <= $end && $bday >= $start) {
            $age = $year - $birthyear;
            $event = array(
              'id'          => rcube_ldap::dn_encode('bday:' . $source . ':' . $contact['ID'] . ':' . $year),
              'calendar'    => self::BIRTHDAY_CALENDAR_ID,
              'title'       => $event_title,
              'description' => $rcmail->gettext(array('name' => 'birthdayage', 'vars' => array('age' => $age)), 'calendar'),
              // Add more contact information to description block?
              'allday'      => true,
              'start'       => $bday,
              'alarms'      => $alarms,
            );
            $event['end'] = clone $bday;
            $event['end']->add(new DateInterval('PT1H'));

            $events[] = $event;
          }
        }
      }

      // store collected contacts in cache
      if (empty($cached)) {
        $cache->write($source, $cache_records);
      }
    }

    return $events;
  }

  /**
   * Get a single birthday calendar event
   */
  public function get_birthday_event($id)
  {
    // decode $id
    list(,$source,$contact_id,$year) = explode(':', rcube_ldap::dn_decode($id));

    $rcmail = rcmail::get_instance();

    if ($source && $contact_id && ($abook = $rcmail->get_address_book($source))) {
      $contact = $abook->get_record($contact_id, true);

      if (is_array($contact) && !empty($contact['birthday'])) {
        try {
          if (is_array($contact['birthday']))
            $contact['birthday'] = reset($contact['birthday']);

          $bday = $contact['birthday'] instanceof DateTime ? $contact['birthday'] :
                    new DateTime($contact['birthday'], new DateTimezone('UTC'));
          $birthyear = $bday->format('Y');
        }
        catch (Exception $e) {
          rcube::raise_error(array(
              'code' => 600, 'type' => 'php',
              'file' => __FILE__, 'line' => __LINE__,
              'message' => 'BIRTHDAY PARSE ERROR: ' . $e),
            true, false);

          return null;
        }

        $display_name = rcube_addressbook::compose_display_name($contact);
        $event_title = $rcmail->gettext(array('name' => 'birthdayeventtitle', 'vars' => array('name' => $display_name)), 'calendar');

        $event = array(
          'id'          => rcube_ldap::dn_encode('bday:' . $source . ':' . $contact['ID'] . ':' . $year),
          'uid'         => rcube_ldap::dn_encode('bday:' . $source . ':' . $contact['ID'] . ':' . $birthyear),
          'calendar'    => self::BIRTHDAY_CALENDAR_ID,
          'title'       => $event_title,
          'description' => '',
          'allday'      => true,
          'start'       => $bday,
          'recurrence'  => array('FREQ' => 'YEARLY', 'INTERVAL' => 1),
          'free_busy'   => 'free',
        );
        $event['end'] = clone $bday;
        $event['end']->add(new DateInterval('PT1H'));

        return $event;
      }
    }

    return null;
  }

  /**
   * Store alarm dismissal for birtual birthay events
   *
   * @param  string  Event identifier
   * @param  integer Suspend the alarm for this number of seconds
   */
  public function dismiss_birthday_alarm($event_id, $snooze = 0)
  {
    $rcmail = rcmail::get_instance();
    $cache  = $rcmail->get_cache('calendar.birthdayalarms', 'db', 86400 * 30);
    $cache->remove($event_id);

    // compute new notification time or disable if not snoozed
    $notifyat = $snooze > 0 ? time() + $snooze : null;
    $cache->set($event_id, array('snooze' => $snooze, 'notifyat' => $notifyat));

    return true;
  }

  /**
   * Handler for user_delete plugin hook
   *
   * @param array Hash array with hook arguments
   * @return array Return arguments for plugin hooks
   */
  public function user_delete($args)
  {
    // TO BE OVERRIDDEN
    return $args;
  }
}
