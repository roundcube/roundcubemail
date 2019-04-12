<?php

/**
 * Recurrence computation class for shared use
 *
 * Uitility class to compute reccurrence dates from the given rules
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
class libcalendaring_recurrence
{
    protected $lib;
    protected $start;
    protected $next;
    protected $engine;
    protected $recurrence;
    protected $dateonly = false;
    protected $hour = 0;

    /**
     * Default constructor
     *
     * @param object calendar The calendar plugin instance
     */
    function __construct($lib)
    {
      // use Horde classes to compute recurring instances
      // TODO: replace with something that has less than 6'000 lines of code
      require_once(__DIR__ . '/Horde_Date_Recurrence.php');

      $this->lib = $lib;
    }

    /**
     * Initialize recurrence engine
     *
     * @param array  The recurrence properties
     * @param object DateTime The recurrence start date
     */
    public function init($recurrence, $start = null)
    {
        $this->recurrence = $recurrence;

        $this->engine = new Horde_Date_Recurrence($start);
        $this->engine->fromRRule20(libcalendaring::to_rrule($recurrence));

        $this->set_start($start);

        if (is_array($recurrence['EXDATE'])) {
            foreach ($recurrence['EXDATE'] as $exdate) {
                if (is_a($exdate, 'DateTime')) {
                    $this->engine->addException($exdate->format('Y'), $exdate->format('n'), $exdate->format('j'));
                }
            }
        }
        if (is_array($recurrence['RDATE'])) {
            foreach ($recurrence['RDATE'] as $rdate) {
                if (is_a($rdate, 'DateTime')) {
                    $this->engine->addRDate($rdate->format('Y'), $rdate->format('n'), $rdate->format('j'));
                }
            }
        }
    }

    /**
     * Setter for (new) recurrence start date
     *
     * @param object DateTime The recurrence start date
     */
    public function set_start($start)
    {
        $this->start = $start;
        $this->dateonly = $start->_dateonly;
        $this->next = new Horde_Date($start, $this->lib->timezone->getName());
        $this->hour = $this->next->hour;
        $this->engine->setRecurStart($this->next);
    }

    /**
     * Get date/time of the next occurence of this event
     *
     * @return mixed DateTime object or False if recurrence ended
     */
    public function next()
    {
        $time = false;
        $after = clone $this->next;
        $after->mday = $after->mday + 1;
        if ($this->next && ($next = $this->engine->nextActiveRecurrence($after))) {
            // avoid endless loops if recurrence computation fails
            if (!$next->after($this->next)) {
                return false;
            }
            // fix time for all-day events
            if ($this->dateonly) {
                $next->hour = $this->hour;
                $next->min = 0;
            }

            $time = $next->toDateTime();
            $this->next = $next;
        }

        return $time;
    }

    /**
     * Get the end date of the occurence of this recurrence cycle
     *
     * @return DateTime|bool End datetime of the last occurence or False if recurrence exceeds limit
     */
    public function end()
    {
        // recurrence end date is given
        if ($this->recurrence['UNTIL'] instanceof DateTime) {
            return $this->recurrence['UNTIL'];
        }

        // take the last RDATE entry if set
        if (is_array($this->recurrence['RDATE']) && !empty($this->recurrence['RDATE'])) {
            $last = end($this->recurrence['RDATE']);
            if ($last instanceof DateTime) {
              return $last;
            }
        }

        // run through all items till we reach the end
        if ($this->recurrence['COUNT']) {
            $last = $this->start;
            $this->next = new Horde_Date($this->start, $this->lib->timezone->getName());
            while (($next = $this->next()) && $c < 1000) {
                $last = $next;
                $c++;
            }
        }

        return $last;
    }

}
