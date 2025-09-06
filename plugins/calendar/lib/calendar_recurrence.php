<?php

require_once realpath(__DIR__ . '/../../libcalendaring/lib/libcalendaring_recurrence.php');

/**
 * Recurrence computation class for the Calendar plugin
 *
 * Uitility class to compute instances of recurring events.
 *
 * @author Thomas Bruederli <bruederli@kolabsys.com>
 *
 * Copyright (C) 2012-2014, Kolab Systems AG <contact@kolabsys.com>
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
class calendar_recurrence extends libcalendaring_recurrence
{
  private $event;
  private $duration;

  /**
   * Default constructor
   *
   * @param object calendar The calendar plugin instance
   * @param array The event object to operate on
   */
  function __construct($cal, $event)
  {
    parent::__construct($cal->lib);

    $this->event = $event;

    if (is_object($event['start']) && is_object($event['end']))
      $this->duration = $event['start']->diff($event['end']);

    $event['start']->_dateonly |= $event['allday'];
    $this->init($event['recurrence'], $event['start']);
  }

  /**
   * Alias of libcalendaring_recurrence::next()
   *
   * @return mixed DateTime object or False if recurrence ended
   */
  public function next_start()
  {
    return $this->next();
  }

  /**
   * Get the next recurring instance of this event
   *
   * @return mixed Array with event properties or False if recurrence ended
   */
  public function next_instance()
  {
    if ($next_start = $this->next()) {
      $next = $this->event;
      $next['start'] = $next_start;

      if ($this->duration) {
        $next['end'] = clone $next_start;
        $next['end']->add($this->duration);
      }

      $next['recurrence_date'] = clone $next_start;
      $next['_instance'] = libcalendaring::recurrence_instance_identifier($next);

      unset($next['_formatobj']);

      return $next;
    }

    return false;
  }

}
