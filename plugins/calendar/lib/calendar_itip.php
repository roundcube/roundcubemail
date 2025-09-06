<?php

require_once realpath(__DIR__ . '/../../libcalendaring/lib/libcalendaring_itip.php');

/**
 * iTIP functions for the Calendar plugin
 *
 * Class providing functionality to manage iTIP invitations
 *
 * @version @package_version@
 * @author Thomas Bruederli <bruederli@kolabsys.com>
 * @package @package_name@
 *
 * Copyright (C) 2011, Kolab Systems AG <contact@kolabsys.com>
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
class calendar_itip extends libcalendaring_itip
{
  /**
   * Constructor to set text domain to calendar
   */
  function __construct($plugin, $domain = 'calendar')
  {
    parent::__construct($plugin, $domain);

    $this->db_itipinvitations = $this->rc->db->table_name('itipinvitations', true);
  }

  /**
   * Handler for calendar/itip-status requests
   */
  public function get_itip_status($event, $existing = null)
  {
    $status = parent::get_itip_status($event, $existing);

    // don't ask for deleting events when declining
    if ($this->rc->config->get('kolab_invitation_calendars'))
      $status['saved'] = false;

    return $status;
  }

  /**
   * Find invitation record by token
   *
   * @param string Invitation token
   * @return mixed Invitation record as hash array or False if not found
   */
  public function get_invitation($token)
  {
    if ($parts = $this->decode_token($token)) {
      $result = $this->rc->db->query("SELECT * FROM $this->db_itipinvitations WHERE `token` = ?", $parts['base']);
      if ($result && ($rec = $this->rc->db->fetch_assoc($result))) {
        $rec['event'] = unserialize($rec['event']);
        $rec['attendee'] = $parts['attendee'];
        return $rec;
      }
    }
    
    return false;
  }

  /**
   * Update the attendee status of the given invitation record
   *
   * @param array Invitation record as fetched with calendar_itip::get_invitation()
   * @param string Attendee email address
   * @param string New attendee status
   */
  public function update_invitation($invitation, $email, $newstatus)
  {
    if (is_string($invitation))
      $invitation = $this->get_invitation($invitation);
    
    if ($invitation['token'] && $invitation['event']) {
      // update attendee record in event data
      foreach ($invitation['event']['attendees'] as $i => $attendee) {
        if ($attendee['role'] == 'ORGANIZER') {
          $organizer = $attendee;
        }
        else if ($attendee['email'] == $email) {
          // nothing to be done here
          if ($attendee['status'] == $newstatus)
            return true;
          
          $invitation['event']['attendees'][$i]['status'] = $newstatus;
          $this->sender = $attendee;
        }
      }
      $invitation['event']['changed'] = new DateTime();
      
      // send iTIP REPLY message to organizer
      if ($organizer) {
        $status = strtolower($newstatus);
        if ($this->send_itip_message($invitation['event'], 'REPLY', $organizer, 'itipsubject' . $status, 'itipmailbody' . $status))
          $this->rc->output->command('display_message', $this->plugin->gettext(array('name' => 'sentresponseto', 'vars' => array('mailto' => $organizer['name'] ? $organizer['name'] : $organizer['email']))), 'confirmation');
        else
          $this->rc->output->command('display_message', $this->plugin->gettext('itipresponseerror'), 'error');
      }
      
      // update record in DB
      $query = $this->rc->db->query(
        "UPDATE $this->db_itipinvitations
         SET `event` = ?
         WHERE `token` = ?",
        self::serialize_event($invitation['event']),
        $invitation['token']
      );

      if ($this->rc->db->affected_rows($query))
        return true;
    }
    
    return false;
  }


  /**
   * Create iTIP invitation token for later replies via URL
   *
   * @param array Hash array with event properties
   * @param string Attendee email address
   * @return string Invitation token
   */
  public function store_invitation($event, $attendee)
  {
    static $stored = array();
    
    if (!$event['uid'] || !$attendee)
      return false;
      
    // generate token for this invitation
    $token = $this->generate_token($event, $attendee);
    $base = substr($token, 0, 40);
    
    // already stored this
    if ($stored[$base])
      return $token;

    // delete old entry
    $this->rc->db->query("DELETE FROM $this->db_itipinvitations WHERE `token` = ?", $base);

    $event_uid = $event['uid'] . ($event['_instance'] ? '-' . $event['_instance'] : '');

    $query = $this->rc->db->query(
      "INSERT INTO $this->db_itipinvitations
       (`token`, `event_uid`, `user_id`, `event`, `expires`)
       VALUES(?, ?, ?, ?, ?)",
      $base,
      $event_uid,
      $this->rc->user->ID,
      self::serialize_event($event),
      date('Y-m-d H:i:s', $event['end']->format('U') + 86400 * 2)
    );
    
    if ($this->rc->db->affected_rows($query)) {
      $stored[$base] = 1;
      return $token;
    }
    
    return false;
  }

  /**
   * Mark invitations for the given event as cancelled
   *
   * @param array Hash array with event properties
   */
  public function cancel_itip_invitation($event)
  {
    $event_uid = $event['uid'] . ($event['_instance'] ? '-' . $event['_instance'] : '');

    // flag invitation record as cancelled
    $this->rc->db->query(
      "UPDATE $this->db_itipinvitations
       SET `cancelled` = 1
       WHERE `event_uid` = ? AND `user_id` = ?",
       $event_uid,
       $this->rc->user->ID
    );
  }

  /**
   * Generate an invitation request token for the given event and attendee
   *
   * @param array Event hash array
   * @param string Attendee email address
   */
  public function generate_token($event, $attendee)
  {
    $event_uid = $event['uid'] . ($event['_instance'] ? '-' . $event['_instance'] : '');
    $base = sha1($event_uid . ';' . $this->rc->user->ID);
    $mail = base64_encode($attendee);
    $hash = substr(md5($base . $mail . $this->rc->config->get('des_key')), 0, 6);
    
    return "$base.$mail.$hash";
  }

  /**
   * Decode the given iTIP request token and return its parts
   *
   * @param string Request token to decode
   * @return mixed Hash array with parts or False if invalid
   */
  public function decode_token($token)
  {
    list($base, $mail, $hash) = explode('.', $token);
    
    // validate and return parts
    if ($mail && $hash && $hash == substr(md5($base . $mail . $this->rc->config->get('des_key')), 0, 6)) {
      return array('base' => $base, 'attendee' => base64_decode($mail));
    }
    
    return false;
  }

  /**
   * Helper method to serialize the given event for storing in invitations table
   */
  private static function serialize_event($event)
  {
    $ev = $event;
    $ev['description'] = abbreviate_string($ev['description'], 100);
    unset($ev['attachments']);
    return serialize($ev);
  }

}
