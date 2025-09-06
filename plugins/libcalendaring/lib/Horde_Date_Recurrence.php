<?php

/**
 * This is a modified copy of Horde/Date/Recurrence.php
 * Pull the latest version of this file from the PEAR channel of the Horde
 * project at http://pear.horde.org by installing the Horde_Date package.
 */

if (!class_exists('Horde_Date'))
	require_once(dirname(__FILE__) . '/Horde_Date.php');

// minimal required implementation of Horde_Date_Translation to avoid a huge dependency nightmare
class Horde_Date_Translation
{
	function t($arg) { return $arg; }
	function ngettext($sing, $plur, $num) { return ($num > 1 ? $plur : $sing); }
}


/**
 * This file contains the Horde_Date_Recurrence class and according constants.
 *
 * Copyright 2007-2012 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category Horde
 * @package  Date
 */

/**
 * The Horde_Date_Recurrence class implements algorithms for calculating
 * recurrences of events, including several recurrence types, intervals,
 * exceptions, and conversion from and to vCalendar and iCalendar recurrence
 * rules.
 *
 * All methods expecting dates as parameters accept all values that the
 * Horde_Date constructor accepts, i.e. a timestamp, another Horde_Date
 * object, an ISO time string or a hash.
 *
 * @author   Jan Schneider <jan@horde.org>
 * @category Horde
 * @package  Date
 */
class Horde_Date_Recurrence
{
    /** No Recurrence **/
    const RECUR_NONE = 0;

    /** Recurs daily. */
    const RECUR_DAILY = 1;

    /** Recurs weekly. */
    const RECUR_WEEKLY = 2;

    /** Recurs monthly on the same date. */
    const RECUR_MONTHLY_DATE = 3;

    /** Recurs monthly on the same week day. */
    const RECUR_MONTHLY_WEEKDAY = 4;

    /** Recurs yearly on the same date. */
    const RECUR_YEARLY_DATE = 5;

    /** Recurs yearly on the same day of the year. */
    const RECUR_YEARLY_DAY = 6;

    /** Recurs yearly on the same week day. */
    const RECUR_YEARLY_WEEKDAY = 7;

    /**
     * The start time of the event.
     *
     * @var Horde_Date
     */
    public $start;

    /**
     * The end date of the recurrence interval.
     *
     * @var Horde_Date
     */
    public $recurEnd = null;

    /**
     * The number of recurrences.
     *
     * @var integer
     */
    public $recurCount = null;

    /**
     * The type of recurrence this event follows. RECUR_* constant.
     *
     * @var integer
     */
    public $recurType = self::RECUR_NONE;

    /**
     * The length of time between recurrences. The time unit depends on the
     * recurrence type.
     *
     * @var integer
     */
    public $recurInterval = 1;

    /**
     * Any additional recurrence data.
     *
     * @var integer
     */
    public $recurData = null;

    /**
     * BYDAY recurrence number
     *
     * @var integer
     */
    public $recurNthDay = null;

    /**
     * BYMONTH recurrence data
     *
     * @var array
     */
    public $recurMonths = array();

    /**
     * RDATE recurrence values
     *
     * @var array
     */
    public $rdates = array();

    /**
     * All the exceptions from recurrence for this event.
     *
     * @var array
     */
    public $exceptions = array();

    /**
     * All the dates this recurrence has been marked as completed.
     *
     * @var array
     */
    public $completions = array();

    /**
     * Constructor.
     *
     * @param Horde_Date $start  Start of the recurring event.
     */
    public function __construct($start)
    {
        $this->start = new Horde_Date($start);
    }

    /**
     * Resets the class properties.
     */
    public function reset()
    {
        $this->recurEnd = null;
        $this->recurCount = null;
        $this->recurType = self::RECUR_NONE;
        $this->recurInterval = 1;
        $this->recurData = null;
        $this->exceptions = array();
        $this->completions = array();
    }

    /**
     * Checks if this event recurs on a given day of the week.
     *
     * @param integer $dayMask  A mask consisting of Horde_Date::MASK_*
     *                          constants specifying the day(s) to check.
     *
     * @return boolean  True if this event recurs on the given day(s).
     */
    public function recurOnDay($dayMask)
    {
        return ($this->recurData & $dayMask);
    }

    /**
     * Specifies the days this event recurs on.
     *
     * @param integer $dayMask  A mask consisting of Horde_Date::MASK_*
     *                          constants specifying the day(s) to recur on.
     */
    public function setRecurOnDay($dayMask)
    {
        $this->recurData = $dayMask;
    }

    /**
     *
     * @param integer $nthDay The nth weekday of month to repeat events on
     */
    public function setRecurNthWeekday($nth)
    {
        $this->recurNthDay = (int)$nth;
    }

    /**
     *
     * @return integer  The nth weekday of month to repeat events.
     */
    public function getRecurNthWeekday()
    {
        return isset($this->recurNthDay) ? $this->recurNthDay : ceil($this->start->mday / 7);
    }

    /**
     * Specifies the months for yearly (weekday) recurrence
     *
     * @param array $months  List of months (integers) this event recurs on.
     */
    function setRecurByMonth($months)
    {
        $this->recurMonths = (array)$months;
    }

    /**
     * Returns a list of months this yearly event recurs on
     *
     * @return array List of months (integers) this event recurs on.
     */
    function getRecurByMonth()
    {
        return $this->recurMonths;
    }

    /**
     * Returns the days this event recurs on.
     *
     * @return integer  A mask consisting of Horde_Date::MASK_* constants
     *                  specifying the day(s) this event recurs on.
     */
    public function getRecurOnDays()
    {
        return $this->recurData;
    }

    /**
     * Returns whether this event has a specific recurrence type.
     *
     * @param integer $recurrence  RECUR_* constant of the
     *                             recurrence type to check for.
     *
     * @return boolean  True if the event has the specified recurrence type.
     */
    public function hasRecurType($recurrence)
    {
        return ($recurrence == $this->recurType);
    }

    /**
     * Sets a recurrence type for this event.
     *
     * @param integer $recurrence  A RECUR_* constant.
     */
    public function setRecurType($recurrence)
    {
        $this->recurType = $recurrence;
    }

    /**
     * Returns recurrence type of this event.
     *
     * @return integer  A RECUR_* constant.
     */
    public function getRecurType()
    {
        return $this->recurType;
    }

    /**
     * Returns a description of this event's recurring type.
     *
     * @return string  Human readable recurring type.
     */
    public function getRecurName()
    {
        switch ($this->getRecurType()) {
        case self::RECUR_NONE: return Horde_Date_Translation::t("No recurrence");
        case self::RECUR_DAILY: return Horde_Date_Translation::t("Daily");
        case self::RECUR_WEEKLY: return Horde_Date_Translation::t("Weekly");
        case self::RECUR_MONTHLY_DATE:
        case self::RECUR_MONTHLY_WEEKDAY: return Horde_Date_Translation::t("Monthly");
        case self::RECUR_YEARLY_DATE:
        case self::RECUR_YEARLY_DAY:
        case self::RECUR_YEARLY_WEEKDAY: return Horde_Date_Translation::t("Yearly");
        }
    }

    /**
     * Sets the length of time between recurrences of this event.
     *
     * @param integer $interval  The time between recurrences.
     */
    public function setRecurInterval($interval)
    {
        if ($interval > 0) {
            $this->recurInterval = $interval;
        }
    }

    /**
     * Retrieves the length of time between recurrences of this event.
     *
     * @return integer  The number of seconds between recurrences.
     */
    public function getRecurInterval()
    {
        return $this->recurInterval;
    }

    /**
     * Sets the number of recurrences of this event.
     *
     * @param integer $count  The number of recurrences.
     */
    public function setRecurCount($count)
    {
        if ($count > 0) {
            $this->recurCount = (int)$count;
            // Recurrence counts and end dates are mutually exclusive.
            $this->recurEnd = null;
        } else {
            $this->recurCount = null;
        }
    }

    /**
     * Retrieves the number of recurrences of this event.
     *
     * @return integer  The number recurrences.
     */
    public function getRecurCount()
    {
        return $this->recurCount;
    }

    /**
     * Returns whether this event has a recurrence with a fixed count.
     *
     * @return boolean  True if this recurrence has a fixed count.
     */
    public function hasRecurCount()
    {
        return isset($this->recurCount);
    }

    /**
     * Sets the start date of the recurrence interval.
     *
     * @param Horde_Date $start  The recurrence start.
     */
    public function setRecurStart($start)
    {
        $this->start = clone $start;
    }

    /**
     * Retrieves the start date of the recurrence interval.
     *
     * @return Horde_Date  The recurrence start.
     */
    public function getRecurStart()
    {
        return $this->start;
    }

    /**
     * Sets the end date of the recurrence interval.
     *
     * @param Horde_Date $end  The recurrence end.
     */
    public function setRecurEnd($end)
    {
        if (!empty($end)) {
            // Recurrence counts and end dates are mutually exclusive.
            $this->recurCount = null;
            $this->recurEnd = clone $end;
        } else {
            $this->recurEnd = $end;
        }
    }

    /**
     * Retrieves the end date of the recurrence interval.
     *
     * @return Horde_Date  The recurrence end.
     */
    public function getRecurEnd()
    {
        return $this->recurEnd;
    }

    /**
     * Returns whether this event has a recurrence end.
     *
     * @return boolean  True if this recurrence ends.
     */
    public function hasRecurEnd()
    {
        return isset($this->recurEnd) && isset($this->recurEnd->year) &&
            $this->recurEnd->year != 9999;
    }

    /**
     * Finds the next recurrence of this event that's after $afterDate.
     *
     * @param Horde_Date|string $after  Return events after this date.
     *
     * @return Horde_Date|boolean  The date of the next recurrence or false
     *                             if the event does not recur after
     *                             $afterDate.
     */
    public function nextRecurrence($after)
    {
        if (!($after instanceof Horde_Date)) {
            $after = new Horde_Date($after);
        } else {
            $after = clone($after);
        }

        // Make sure $after and $this->start are in the same TZ
        $after->setTimezone($this->start->timezone);
        if ($this->start->compareDateTime($after) >= 0) {
            return clone $this->start;
        }

        if ($this->recurInterval == 0 && empty($this->rdates)) {
            return false;
        }

        switch ($this->getRecurType()) {
        case self::RECUR_DAILY:
            $diff = $this->start->diff($after);
            $recur = ceil($diff / $this->recurInterval);
            if ($this->recurCount && $recur >= $this->recurCount) {
                return false;
            }

            $recur *= $this->recurInterval;
            $next = $this->start->add(array('day' => $recur));
            if ((!$this->hasRecurEnd() ||
                 $next->compareDateTime($this->recurEnd) <= 0) &&
                $next->compareDateTime($after) >= 0) {
                return $next;
            }
            break;

        case self::RECUR_WEEKLY:
            if (empty($this->recurData)) {
                return false;
            }

            $start_week = Horde_Date_Utils::firstDayOfWeek($this->start->format('W'),
                                                           $this->start->year);
            $start_week->timezone = $this->start->timezone;
            $start_week->hour = $this->start->hour;
            $start_week->min  = $this->start->min;
            $start_week->sec  = $this->start->sec;

            // Make sure we are not at the ISO-8601 first week of year while
            // still in month 12...OR in the ISO-8601 last week of year while
            // in month 1 and adjust the year accordingly.
            $week = $after->format('W');
            if ($week == 1 && $after->month == 12) {
                $theYear = $after->year + 1;
            } elseif ($week >= 52 && $after->month == 1) {
                $theYear = $after->year - 1;
            } else {
                $theYear = $after->year;
            }

            $after_week = Horde_Date_Utils::firstDayOfWeek($week, $theYear);
            $after_week->timezone = $this->start->timezone;
            $after_week_end = clone $after_week;
            $after_week_end->mday += 7;

            $diff = $start_week->diff($after_week);
            $interval = $this->recurInterval * 7;
            $repeats = floor($diff / $interval);
            if ($diff % $interval < 7) {
                $recur = $diff;
            } else {
                /**
                 * If the after_week is not in the first week interval the
                 * search needs to skip ahead a complete interval. The way it is
                 * calculated here means that an event that occurs every second
                 * week on Monday and Wednesday with the event actually starting
                 * on Tuesday or Wednesday will only have one incidence in the
                 * first week.
                 */
                $recur = $interval * ($repeats + 1);
            }

            if ($this->hasRecurCount()) {
                $recurrences = 0;
                /**
                 * Correct the number of recurrences by the number of events
                 * that lay between the start of the start week and the
                 * recurrence start.
                 */
                $next = clone $start_week;
                while ($next->compareDateTime($this->start) < 0) {
                    if ($this->recurOnDay((int)pow(2, $next->dayOfWeek()))) {
                        $recurrences--;
                    }
                    ++$next->mday;
                }
                if ($repeats > 0) {
                    $weekdays = $this->recurData;
                    $total_recurrences_per_week = 0;
                    while ($weekdays > 0) {
                        if ($weekdays % 2) {
                            $total_recurrences_per_week++;
                        }
                        $weekdays = ($weekdays - ($weekdays % 2)) / 2;
                    }
                    $recurrences += $total_recurrences_per_week * $repeats;
                }
            }

            $next = clone $start_week;
            $next->mday += $recur;
            while ($next->compareDateTime($after) < 0 &&
                   $next->compareDateTime($after_week_end) < 0) {
                if ($this->hasRecurCount()
                    && $next->compareDateTime($after) < 0
                    && $this->recurOnDay((int)pow(2, $next->dayOfWeek()))) {
                    $recurrences++;
                }
                ++$next->mday;
            }
            if ($this->hasRecurCount() &&
                $recurrences >= $this->recurCount) {
                return false;
            }
            if (!$this->hasRecurEnd() ||
                $next->compareDateTime($this->recurEnd) <= 0) {
                if ($next->compareDateTime($after_week_end) >= 0) {
                    return $this->nextRecurrence($after_week_end);
                }
                while (!$this->recurOnDay((int)pow(2, $next->dayOfWeek())) &&
                       $next->compareDateTime($after_week_end) < 0) {
                    ++$next->mday;
                }
                if (!$this->hasRecurEnd() ||
                    $next->compareDateTime($this->recurEnd) <= 0) {
                    if ($next->compareDateTime($after_week_end) >= 0) {
                        return $this->nextRecurrence($after_week_end);
                    } else {
                        return $next;
                    }
                }
            }
            break;

        case self::RECUR_MONTHLY_DATE:
            $start = clone $this->start;
            if ($after->compareDateTime($start) < 0) {
                $after = clone $start;
            } else {
                $after = clone $after;
            }

            // If we're starting past this month's recurrence of the event,
            // look in the next month on the day the event recurs.
            if ($after->mday > $start->mday) {
                ++$after->month;
                $after->mday = $start->mday;
            }

            // Adjust $start to be the first match.
            $offset = ($after->month - $start->month) + ($after->year - $start->year) * 12;
            $offset = floor(($offset + $this->recurInterval - 1) / $this->recurInterval) * $this->recurInterval;

            if ($this->recurCount &&
                ($offset / $this->recurInterval) >= $this->recurCount) {
                return false;
            }
            $start->month += $offset;
            $count = $offset / $this->recurInterval;

            do {
                if ($this->recurCount &&
                    $count++ >= $this->recurCount) {
                    return false;
                }

                // Bail if we've gone past the end of recurrence.
                if ($this->hasRecurEnd() &&
                    $this->recurEnd->compareDateTime($start) < 0) {
                    return false;
                }
                if ($start->isValid()) {
                    return $start;
                }

                // If the interval is 12, and the date isn't valid, then we
                // need to see if February 29th is an option. If not, then the
                // event will _never_ recur, and we need to stop checking to
                // avoid an infinite loop.
                if ($this->recurInterval == 12 && ($start->month != 2 || $start->mday > 29)) {
                    return false;
                }

                // Add the recurrence interval.
                $start->month += $this->recurInterval;
            } while (true);

            break;

        case self::RECUR_MONTHLY_WEEKDAY:
            // Start with the start date of the event.
            $estart = clone $this->start;

            // What day of the week, and week of the month, do we recur on?
            if (isset($this->recurNthDay)) {
                $nth = $this->recurNthDay;
                $weekday = log($this->recurData, 2);
            } else {
                $nth = ceil($this->start->mday / 7);
                $weekday = $estart->dayOfWeek();
            }

            // Adjust $estart to be the first candidate.
            $offset = ($after->month - $estart->month) + ($after->year - $estart->year) * 12;
            $offset = floor(($offset + $this->recurInterval - 1) / $this->recurInterval) * $this->recurInterval;

            // Adjust our working date until it's after $after.
            $estart->month += $offset - $this->recurInterval;

            $count = $offset / $this->recurInterval;
            do {
                if ($this->recurCount &&
                    $count++ >= $this->recurCount) {
                    return false;
                }

                $estart->month += $this->recurInterval;

                $next = clone $estart;
                $next->setNthWeekday($weekday, $nth);

                if ($next->compareDateTime($after) < 0) {
                    // We haven't made it past $after yet, try again.
                    continue;
                }
                if ($this->hasRecurEnd() &&
                    $next->compareDateTime($this->recurEnd) > 0) {
                    // We've gone past the end of recurrence; we can give up
                    // now.
                    return false;
                }

                // We have a candidate to return.
                break;
            } while (true);

            return $next;

        case self::RECUR_YEARLY_DATE:
            // Start with the start date of the event.
            $estart = clone $this->start;
            $after = clone $after;

            if ($after->month > $estart->month ||
                ($after->month == $estart->month && $after->mday > $estart->mday)) {
                ++$after->year;
                $after->month = $estart->month;
                $after->mday = $estart->mday;
            }

            // Seperate case here for February 29th
            if ($estart->month == 2 && $estart->mday == 29) {
                while (!Horde_Date_Utils::isLeapYear($after->year)) {
                    ++$after->year;
                }
            }

            // Adjust $estart to be the first candidate.
            $offset = $after->year - $estart->year;
            if ($offset > 0) {
                $offset = floor(($offset + $this->recurInterval - 1) / $this->recurInterval) * $this->recurInterval;
                $estart->year += $offset;
            }

            // We've gone past the end of recurrence; give up.
            if ($this->recurCount &&
                $offset >= $this->recurCount) {
                return false;
            }
            if ($this->hasRecurEnd() &&
                $this->recurEnd->compareDateTime($estart) < 0) {
                return false;
            }

            return $estart;

        case self::RECUR_YEARLY_DAY:
            // Check count first.
            $dayofyear = $this->start->dayOfYear();
            $count = ($after->year - $this->start->year) / $this->recurInterval + 1;
            if ($this->recurCount &&
                ($count > $this->recurCount ||
                 ($count == $this->recurCount &&
                  $after->dayOfYear() > $dayofyear))) {
                return false;
            }

            // Start with a rough interval.
            $estart = clone $this->start;
            $estart->year += floor($count - 1) * $this->recurInterval;

            // Now add the difference to the required day of year.
            $estart->mday += $dayofyear - $estart->dayOfYear();

            // Add an interval if the estimation was wrong.
            if ($estart->compareDate($after) < 0) {
                $estart->year += $this->recurInterval;
                $estart->mday += $dayofyear - $estart->dayOfYear();
            }

            // We've gone past the end of recurrence; give up.
            if ($this->hasRecurEnd() &&
                $this->recurEnd->compareDateTime($estart) < 0) {
                return false;
            }

            return $estart;

        case self::RECUR_YEARLY_WEEKDAY:
            // Start with the start date of the event.
            $estart = clone $this->start;

            // What day of the week, and week of the month, do we recur on?
            if (isset($this->recurNthDay)) {
                $nth = $this->recurNthDay;
                $weekday = log($this->recurData, 2);
            } else {
                $nth = ceil($this->start->mday / 7);
                $weekday = $estart->dayOfWeek();
            }

            // Adjust $estart to be the first candidate.
            $offset = floor(($after->year - $estart->year + $this->recurInterval - 1) / $this->recurInterval) * $this->recurInterval;

            // Adjust our working date until it's after $after.
            $estart->year += $offset - $this->recurInterval;

            $count = $offset / $this->recurInterval;
            do {
                if ($this->recurCount &&
                    $count++ >= $this->recurCount) {
                    return false;
                }

                $estart->year += $this->recurInterval;

                $next = clone $estart;
                $next->setNthWeekday($weekday, $nth);

                if ($next->compareDateTime($after) < 0) {
                    // We haven't made it past $after yet, try again.
                    continue;
                }
                if ($this->hasRecurEnd() &&
                    $next->compareDateTime($this->recurEnd) > 0) {
                    // We've gone past the end of recurrence; we can give up
                    // now.
                    return false;
                }

                // We have a candidate to return.
                break;
            } while (true);

            return $next;
        }

        // fall-back to RDATE properties
        if (!empty($this->rdates)) {
            $next = clone $this->start;
            foreach ($this->rdates as $rdate) {
                $next->year  = $rdate->year;
                $next->month = $rdate->month;
                $next->mday  = $rdate->mday;
                if ($next->compareDateTime($after) >= 0) {
                    return $next;
                }
            }
        }

        // We didn't find anything, the recurType was bad, or something else
        // went wrong - return false.
        return false;
    }

    /**
     * Returns whether this event has any date that matches the recurrence
     * rules and is not an exception.
     *
     * @return boolean  True if an active recurrence exists.
     */
    public function hasActiveRecurrence()
    {
        if (!$this->hasRecurEnd()) {
            return true;
        }

        $next = $this->nextRecurrence(new Horde_Date($this->start));
        while (is_object($next)) {
            if (!$this->hasException($next->year, $next->month, $next->mday) &&
                !$this->hasCompletion($next->year, $next->month, $next->mday)) {
                return true;
            }

            $next = $this->nextRecurrence($next->add(array('day' => 1)));
        }

        return false;
    }

    /**
     * Returns the next active recurrence.
     *
     * @param Horde_Date $afterDate  Return events after this date.
     *
     * @return Horde_Date|boolean The date of the next active
     *                             recurrence or false if the event
     *                             has no active recurrence after
     *                             $afterDate.
     */
    public function nextActiveRecurrence($afterDate)
    {
        $next = $this->nextRecurrence($afterDate);
        while (is_object($next)) {
            if (!$this->hasException($next->year, $next->month, $next->mday) &&
                !$this->hasCompletion($next->year, $next->month, $next->mday)) {
                return $next;
            }
            $next->mday++;
            $next = $this->nextRecurrence($next);
        }

        return false;
    }

    /**
     * Adds an absolute recurrence date.
     *
     * @param integer $year   The year of the instance.
     * @param integer $month  The month of the instance.
     * @param integer $mday   The day of the month of the instance.
     */
    public function addRDate($year, $month, $mday)
    {
        $this->rdates[] = new Horde_Date($year, $month, $mday);
    }

    /**
     * Adds an exception to a recurring event.
     *
     * @param integer $year   The year of the execption.
     * @param integer $month  The month of the execption.
     * @param integer $mday   The day of the month of the exception.
     */
    public function addException($year, $month, $mday)
    {
        $this->exceptions[] = sprintf('%04d%02d%02d', $year, $month, $mday);
    }

    /**
     * Deletes an exception from a recurring event.
     *
     * @param integer $year   The year of the execption.
     * @param integer $month  The month of the execption.
     * @param integer $mday   The day of the month of the exception.
     */
    public function deleteException($year, $month, $mday)
    {
        $key = array_search(sprintf('%04d%02d%02d', $year, $month, $mday), $this->exceptions);
        if ($key !== false) {
            unset($this->exceptions[$key]);
        }
    }

    /**
     * Checks if an exception exists for a given reccurence of an event.
     *
     * @param integer $year   The year of the reucrance.
     * @param integer $month  The month of the reucrance.
     * @param integer $mday   The day of the month of the reucrance.
     *
     * @return boolean  True if an exception exists for the given date.
     */
    public function hasException($year, $month, $mday)
    {
        return in_array(sprintf('%04d%02d%02d', $year, $month, $mday),
                        $this->getExceptions());
    }

    /**
     * Retrieves all the exceptions for this event.
     *
     * @return array  Array containing the dates of all the exceptions in
     *                YYYYMMDD form.
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }

    /**
     * Adds a completion to a recurring event.
     *
     * @param integer $year   The year of the execption.
     * @param integer $month  The month of the execption.
     * @param integer $mday   The day of the month of the completion.
     */
    public function addCompletion($year, $month, $mday)
    {
        $this->completions[] = sprintf('%04d%02d%02d', $year, $month, $mday);
    }

    /**
     * Deletes a completion from a recurring event.
     *
     * @param integer $year   The year of the execption.
     * @param integer $month  The month of the execption.
     * @param integer $mday   The day of the month of the completion.
     */
    public function deleteCompletion($year, $month, $mday)
    {
        $key = array_search(sprintf('%04d%02d%02d', $year, $month, $mday), $this->completions);
        if ($key !== false) {
            unset($this->completions[$key]);
        }
    }

    /**
     * Checks if a completion exists for a given reccurence of an event.
     *
     * @param integer $year   The year of the reucrance.
     * @param integer $month  The month of the recurrance.
     * @param integer $mday   The day of the month of the recurrance.
     *
     * @return boolean  True if a completion exists for the given date.
     */
    public function hasCompletion($year, $month, $mday)
    {
        return in_array(sprintf('%04d%02d%02d', $year, $month, $mday),
                        $this->getCompletions());
    }

    /**
     * Retrieves all the completions for this event.
     *
     * @return array  Array containing the dates of all the completions in
     *                YYYYMMDD form.
     */
    public function getCompletions()
    {
        return $this->completions;
    }

    /**
     * Parses a vCalendar 1.0 recurrence rule.
     *
     * @link http://www.imc.org/pdi/vcal-10.txt
     * @link http://www.shuchow.com/vCalAddendum.html
     *
     * @param string $rrule  A vCalendar 1.0 conform RRULE value.
     */
    public function fromRRule10($rrule)
    {
        $this->reset();

        if (!$rrule) {
            return;
        }

        if (!preg_match('/([A-Z]+)(\d+)?(.*)/', $rrule, $matches)) {
            // No recurrence data - event does not recur.
            $this->setRecurType(self::RECUR_NONE);
        }

        // Always default the recurInterval to 1.
        $this->setRecurInterval(!empty($matches[2]) ? $matches[2] : 1);

        $remainder = trim($matches[3]);

        switch ($matches[1]) {
        case 'D':
            $this->setRecurType(self::RECUR_DAILY);
            break;

        case 'W':
            $this->setRecurType(self::RECUR_WEEKLY);
            if (!empty($remainder)) {
                $mask = 0;
                while (preg_match('/^ ?[A-Z]{2} ?/', $remainder, $matches)) {
                    $day = trim($matches[0]);
                    $remainder = substr($remainder, strlen($matches[0]));
                    $mask |= $maskdays[$day];
                }
                $this->setRecurOnDay($mask);
            } else {
                // Recur on the day of the week of the original recurrence.
                $maskdays = array(
                    Horde_Date::DATE_SUNDAY => Horde_Date::MASK_SUNDAY,
                    Horde_Date::DATE_MONDAY => Horde_Date::MASK_MONDAY,
                    Horde_Date::DATE_TUESDAY => Horde_Date::MASK_TUESDAY,
                    Horde_Date::DATE_WEDNESDAY => Horde_Date::MASK_WEDNESDAY,
                    Horde_Date::DATE_THURSDAY => Horde_Date::MASK_THURSDAY,
                    Horde_Date::DATE_FRIDAY => Horde_Date::MASK_FRIDAY,
                    Horde_Date::DATE_SATURDAY => Horde_Date::MASK_SATURDAY,
                );
                $this->setRecurOnDay($maskdays[$this->start->dayOfWeek()]);
            }
            break;

        case 'MP':
            $this->setRecurType(self::RECUR_MONTHLY_WEEKDAY);
            break;

        case 'MD':
            $this->setRecurType(self::RECUR_MONTHLY_DATE);
            break;

        case 'YM':
            $this->setRecurType(self::RECUR_YEARLY_DATE);
            break;

        case 'YD':
            $this->setRecurType(self::RECUR_YEARLY_DAY);
            break;
        }

        // We don't support modifiers at the moment, strip them.
        while ($remainder && !preg_match('/^(#\d+|\d{8})($| |T\d{6})/', $remainder)) {
               $remainder = substr($remainder, 1);
        }
        if (!empty($remainder)) {
            if (strpos($remainder, '#') === 0) {
                $this->setRecurCount(substr($remainder, 1));
            } else {
                list($year, $month, $mday) = sscanf($remainder, '%04d%02d%02d');
                $this->setRecurEnd(new Horde_Date(array('year' => $year,
                                                        'month' => $month,
                                                        'mday' => $mday,
                                                        'hour' => 23,
                                                        'min' => 59,
                                                        'sec' => 59)));
            }
        }
    }

    /**
     * Creates a vCalendar 1.0 recurrence rule.
     *
     * @link http://www.imc.org/pdi/vcal-10.txt
     * @link http://www.shuchow.com/vCalAddendum.html
     *
     * @param Horde_Icalendar $calendar  A Horde_Icalendar object instance.
     *
     * @return string  A vCalendar 1.0 conform RRULE value.
     */
    public function toRRule10($calendar)
    {
        switch ($this->recurType) {
        case self::RECUR_NONE:
            return '';

        case self::RECUR_DAILY:
            $rrule = 'D' . $this->recurInterval;
            break;

        case self::RECUR_WEEKLY:
            $rrule = 'W' . $this->recurInterval;
            $vcaldays = array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA');

            for ($i = 0; $i <= 7; ++$i) {
                if ($this->recurOnDay(pow(2, $i))) {
                    $rrule .= ' ' . $vcaldays[$i];
                }
            }
            break;

        case self::RECUR_MONTHLY_DATE:
            $rrule = 'MD' . $this->recurInterval . ' ' . trim($this->start->mday);
            break;

        case self::RECUR_MONTHLY_WEEKDAY:
            $nth_weekday = (int)($this->start->mday / 7);
            if (($this->start->mday % 7) > 0) {
                $nth_weekday++;
            }

            $vcaldays = array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA');
            $rrule = 'MP' . $this->recurInterval . ' ' . $nth_weekday . '+ ' . $vcaldays[$this->start->dayOfWeek()];

            break;

        case self::RECUR_YEARLY_DATE:
            $rrule = 'YM' . $this->recurInterval . ' ' . trim($this->start->month);
            break;

        case self::RECUR_YEARLY_DAY:
            $rrule = 'YD' . $this->recurInterval . ' ' . $this->start->dayOfYear();
            break;

        default:
            return '';
        }

        if ($this->hasRecurEnd()) {
            $recurEnd = clone $this->recurEnd;
            return $rrule . ' ' . $calendar->_exportDateTime($recurEnd);
        }

        return $rrule . ' #' . (int)$this->getRecurCount();
    }

    /**
     * Parses an iCalendar 2.0 recurrence rule.
     *
     * @link http://rfc.net/rfc2445.html#s4.3.10
     * @link http://rfc.net/rfc2445.html#s4.8.5
     * @link http://www.shuchow.com/vCalAddendum.html
     *
     * @param string $rrule  An iCalendar 2.0 conform RRULE value.
     */
    public function fromRRule20($rrule)
    {
        $this->reset();

        // Parse the recurrence rule into keys and values.
        $rdata = array();
        $parts = explode(';', $rrule);
        foreach ($parts as $part) {
            list($key, $value) = explode('=', $part, 2);
            $rdata[strtoupper($key)] = $value;
        }

        if (isset($rdata['FREQ'])) {
            // Always default the recurInterval to 1.
            $this->setRecurInterval(isset($rdata['INTERVAL']) ? $rdata['INTERVAL'] : 1);

            $maskdays = array(
                'SU' => Horde_Date::MASK_SUNDAY,
                'MO' => Horde_Date::MASK_MONDAY,
                'TU' => Horde_Date::MASK_TUESDAY,
                'WE' => Horde_Date::MASK_WEDNESDAY,
                'TH' => Horde_Date::MASK_THURSDAY,
                'FR' => Horde_Date::MASK_FRIDAY,
                'SA' => Horde_Date::MASK_SATURDAY,
            );

            switch (strtoupper($rdata['FREQ'])) {
            case 'DAILY':
                $this->setRecurType(self::RECUR_DAILY);
                break;

            case 'WEEKLY':
                $this->setRecurType(self::RECUR_WEEKLY);
                if (isset($rdata['BYDAY'])) {
                    $days = explode(',', $rdata['BYDAY']);
                    $mask = 0;
                    foreach ($days as $day) {
                        $mask |= $maskdays[$day];
                    }
                    $this->setRecurOnDay($mask);
                } else {
                    // Recur on the day of the week of the original
                    // recurrence.
                    $maskdays = array(
                        Horde_Date::DATE_SUNDAY => Horde_Date::MASK_SUNDAY,
                        Horde_Date::DATE_MONDAY => Horde_Date::MASK_MONDAY,
                        Horde_Date::DATE_TUESDAY => Horde_Date::MASK_TUESDAY,
                        Horde_Date::DATE_WEDNESDAY => Horde_Date::MASK_WEDNESDAY,
                        Horde_Date::DATE_THURSDAY => Horde_Date::MASK_THURSDAY,
                        Horde_Date::DATE_FRIDAY => Horde_Date::MASK_FRIDAY,
                        Horde_Date::DATE_SATURDAY => Horde_Date::MASK_SATURDAY);
                    $this->setRecurOnDay($maskdays[$this->start->dayOfWeek()]);
                }
                break;

            case 'MONTHLY':
                if (isset($rdata['BYDAY'])) {
                    $this->setRecurType(self::RECUR_MONTHLY_WEEKDAY);
                    if (preg_match('/(-?[1-4])([A-Z]+)/', $rdata['BYDAY'], $m)) {
                        $this->setRecurOnDay($maskdays[$m[2]]);
                        $this->setRecurNthWeekday($m[1]);
                    }
                } else {
                    $this->setRecurType(self::RECUR_MONTHLY_DATE);
                }
                break;

            case 'YEARLY':
                if (isset($rdata['BYYEARDAY'])) {
                    $this->setRecurType(self::RECUR_YEARLY_DAY);
                } elseif (isset($rdata['BYDAY'])) {
                    $this->setRecurType(self::RECUR_YEARLY_WEEKDAY);
                    if (preg_match('/(-?[1-4])([A-Z]+)/', $rdata['BYDAY'], $m)) {
                        $this->setRecurOnDay($maskdays[$m[2]]);
                        $this->setRecurNthWeekday($m[1]);
                    }
                    if ($rdata['BYMONTH']) {
                        $months = explode(',', $rdata['BYMONTH']);
                        $this->setRecurByMonth($months);
                    }
                } else {
                    $this->setRecurType(self::RECUR_YEARLY_DATE);
                }
                break;
            }

            if (isset($rdata['UNTIL'])) {
                list($year, $month, $mday) = sscanf($rdata['UNTIL'],
                                                    '%04d%02d%02d');
                $this->setRecurEnd(new Horde_Date(array('year' => $year,
                                                        'month' => $month,
                                                        'mday' => $mday,
                                                        'hour' => 23,
                                                        'min' => 59,
                                                        'sec' => 59)));
            }
            if (isset($rdata['COUNT'])) {
                $this->setRecurCount($rdata['COUNT']);
            }
        } else {
            // No recurrence data - event does not recur.
            $this->setRecurType(self::RECUR_NONE);
        }
    }

    /**
     * Creates an iCalendar 2.0 recurrence rule.
     *
     * @link http://rfc.net/rfc2445.html#s4.3.10
     * @link http://rfc.net/rfc2445.html#s4.8.5
     * @link http://www.shuchow.com/vCalAddendum.html
     *
     * @param Horde_Icalendar $calendar  A Horde_Icalendar object instance.
     *
     * @return string  An iCalendar 2.0 conform RRULE value.
     */
    public function toRRule20($calendar)
    {
        switch ($this->recurType) {
        case self::RECUR_NONE:
            return '';

        case self::RECUR_DAILY:
            $rrule = 'FREQ=DAILY;INTERVAL='  . $this->recurInterval;
            break;

        case self::RECUR_WEEKLY:
            $rrule = 'FREQ=WEEKLY;INTERVAL=' . $this->recurInterval . ';BYDAY=';
            $vcaldays = array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA');

            for ($i = $flag = 0; $i <= 7; ++$i) {
                if ($this->recurOnDay(pow(2, $i))) {
                    if ($flag) {
                        $rrule .= ',';
                    }
                    $rrule .= $vcaldays[$i];
                    $flag = true;
                }
            }
            break;

        case self::RECUR_MONTHLY_DATE:
            $rrule = 'FREQ=MONTHLY;INTERVAL=' . $this->recurInterval;
            break;

        case self::RECUR_MONTHLY_WEEKDAY:
            if (isset($this->recurNthDay)) {
                $nth_weekday = $this->recurNthDay;
                $day_of_week = log($this->recurData, 2);
            } else {
                $day_of_week = $this->start->dayOfWeek();
                $nth_weekday = (int)($this->start->mday / 7);
                if (($this->start->mday % 7) > 0) {
                    $nth_weekday++;
                }
            }
            $vcaldays = array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA');
            $rrule = 'FREQ=MONTHLY;INTERVAL=' . $this->recurInterval
                . ';BYDAY=' . $nth_weekday . $vcaldays[$day_of_week];
            break;

        case self::RECUR_YEARLY_DATE:
            $rrule = 'FREQ=YEARLY;INTERVAL=' . $this->recurInterval;
            break;

        case self::RECUR_YEARLY_DAY:
            $rrule = 'FREQ=YEARLY;INTERVAL=' . $this->recurInterval
                . ';BYYEARDAY=' . $this->start->dayOfYear();
            break;

        case self::RECUR_YEARLY_WEEKDAY:
            if (isset($this->recurNthDay)) {
                $nth_weekday = $this->recurNthDay;
                $day_of_week = log($this->recurData, 2);
            } else {
                $day_of_week = $this->start->dayOfWeek();
                $nth_weekday = (int)($this->start->mday / 7);
                if (($this->start->mday % 7) > 0) {
                    $nth_weekday++;
                }
             }
            $months = !empty($this->recurMonths) ? join(',', $this->recurMonths) : $this->start->month;
            $vcaldays = array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA');
            $rrule = 'FREQ=YEARLY;INTERVAL=' . $this->recurInterval
                . ';BYDAY='
                . $nth_weekday
                . $vcaldays[$day_of_week]
                . ';BYMONTH=' . $this->start->month;
            break;
        }

        if ($this->hasRecurEnd()) {
            $recurEnd = clone $this->recurEnd;
            $rrule .= ';UNTIL=' . $calendar->_exportDateTime($recurEnd);
        }
        if ($count = $this->getRecurCount()) {
            $rrule .= ';COUNT=' . $count;
        }
        return $rrule;
    }

    /**
     * Parses the recurrence data from a hash.
     *
     * @param array $hash  The hash to convert.
     *
     * @return boolean  True if the hash seemed valid, false otherwise.
     */
    public function fromHash($hash)
    {
        $this->reset();

        if (!isset($hash['interval']) || !isset($hash['cycle'])) {
            $this->setRecurType(self::RECUR_NONE);
            return false;
        }

        $this->setRecurInterval((int)$hash['interval']);

        $month2number = array(
            'january'   => 1,
            'february'  => 2,
            'march'     => 3,
            'april'     => 4,
            'may'       => 5,
            'june'      => 6,
            'july'      => 7,
            'august'    => 8,
            'september' => 9,
            'october'   => 10,
            'november'  => 11,
            'december'  => 12,
        );

        $parse_day = false;
        $set_daymask = false;
        $update_month = false;
        $update_daynumber = false;
        $update_weekday = false;
        $nth_weekday = -1;

        switch ($hash['cycle']) {
        case 'daily':
            $this->setRecurType(self::RECUR_DAILY);
            break;

        case 'weekly':
            $this->setRecurType(self::RECUR_WEEKLY);
            $parse_day = true;
            $set_daymask = true;
            break;

        case 'monthly':
            if (!isset($hash['daynumber'])) {
                $this->setRecurType(self::RECUR_NONE);
                return false;
            }

            switch ($hash['type']) {
            case 'daynumber':
                $this->setRecurType(self::RECUR_MONTHLY_DATE);
                $update_daynumber = true;
                break;

            case 'weekday':
                $this->setRecurType(self::RECUR_MONTHLY_WEEKDAY);
                $this->setRecurNthWeekday($hash['daynumber']);
                $parse_day = true;
                $set_daymask = true;
                break;
            }
            break;

        case 'yearly':
            if (!isset($hash['type'])) {
                $this->setRecurType(self::RECUR_NONE);
                return false;
            }

            switch ($hash['type']) {
            case 'monthday':
                $this->setRecurType(self::RECUR_YEARLY_DATE);
                $update_month = true;
                $update_daynumber = true;
                break;

            case 'yearday':
                if (!isset($hash['month'])) {
                    $this->setRecurType(self::RECUR_NONE);
                    return false;
                }

                $this->setRecurType(self::RECUR_YEARLY_DAY);
                // Start counting days in January.
                $hash['month'] = 'january';
                $update_month = true;
                $update_daynumber = true;
                break;

            case 'weekday':
                if (!isset($hash['daynumber'])) {
                    $this->setRecurType(self::RECUR_NONE);
                    return false;
                }

                $this->setRecurType(self::RECUR_YEARLY_WEEKDAY);
                $this->setRecurNthWeekday($hash['daynumber']);
                $parse_day = true;
                $set_daymask = true;

                if ($hash['month'] && isset($month2number[$hash['month']])) {
                    $this->setRecurByMonth($month2number[$hash['month']]);
                }
                break;
            }
        }

        if (isset($hash['range-type']) && isset($hash['range'])) {
            switch ($hash['range-type']) {
            case 'number':
                $this->setRecurCount((int)$hash['range']);
                break;

            case 'date':
                $recur_end = new Horde_Date($hash['range']);
                $recur_end->hour = 23;
                $recur_end->min = 59;
                $recur_end->sec = 59;
                $this->setRecurEnd($recur_end);
                break;
            }
        }

        // Need to parse <day>?
        $last_found_day = -1;
        if ($parse_day) {
            if (!isset($hash['day'])) {
                $this->setRecurType(self::RECUR_NONE);
                return false;
            }

            $mask = 0;
            $bits = array(
                'monday' => Horde_Date::MASK_MONDAY,
                'tuesday' => Horde_Date::MASK_TUESDAY,
                'wednesday' => Horde_Date::MASK_WEDNESDAY,
                'thursday' => Horde_Date::MASK_THURSDAY,
                'friday' => Horde_Date::MASK_FRIDAY,
                'saturday' => Horde_Date::MASK_SATURDAY,
                'sunday' => Horde_Date::MASK_SUNDAY,
            );
            $days = array(
                'monday' => Horde_Date::DATE_MONDAY,
                'tuesday' => Horde_Date::DATE_TUESDAY,
                'wednesday' => Horde_Date::DATE_WEDNESDAY,
                'thursday' => Horde_Date::DATE_THURSDAY,
                'friday' => Horde_Date::DATE_FRIDAY,
                'saturday' => Horde_Date::DATE_SATURDAY,
                'sunday' => Horde_Date::DATE_SUNDAY,
            );

            foreach ($hash['day'] as $day) {
                // Validity check.
                if (empty($day) || !isset($bits[$day])) {
                    continue;
                }

                $mask |= $bits[$day];
                $last_found_day = $days[$day];
            }

            if ($set_daymask) {
                $this->setRecurOnDay($mask);
            }
        }

        if ($update_month || $update_daynumber || $update_weekday) {
            if ($update_month) {
                if (isset($month2number[$hash['month']])) {
                    $this->start->month = $month2number[$hash['month']];
                }
            }

            if ($update_daynumber) {
                if (!isset($hash['daynumber'])) {
                    $this->setRecurType(self::RECUR_NONE);
                    return false;
                }

                $this->start->mday = $hash['daynumber'];
            }

            if ($update_weekday) {
                $this->setNthWeekday($nth_weekday);
            }
        }

        // Exceptions.
        if (isset($hash['exceptions'])) {
            $this->exceptions = $hash['exceptions'];
        }

        if (isset($hash['completions'])) {
            $this->completions = $hash['completions'];
        }

        return true;
    }

    /**
     * Export this object into a hash.
     *
     * @return array  The recurrence hash.
     */
    public function toHash()
    {
        if ($this->getRecurType() == self::RECUR_NONE) {
            return array();
        }

        $day2number = array(
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday'
        );
        $month2number = array(
            1 => 'january',
            2 => 'february',
            3 => 'march',
            4 => 'april',
            5 => 'may',
            6 => 'june',
            7 => 'july',
            8 => 'august',
            9 => 'september',
            10 => 'october',
            11 => 'november',
            12 => 'december'
        );

        $hash = array('interval' => $this->getRecurInterval());
        $start = $this->getRecurStart();

        switch ($this->getRecurType()) {
        case self::RECUR_DAILY:
            $hash['cycle'] = 'daily';
            break;

        case self::RECUR_WEEKLY:
            $hash['cycle'] = 'weekly';
            $bits = array(
                'monday' => Horde_Date::MASK_MONDAY,
                'tuesday' => Horde_Date::MASK_TUESDAY,
                'wednesday' => Horde_Date::MASK_WEDNESDAY,
                'thursday' => Horde_Date::MASK_THURSDAY,
                'friday' => Horde_Date::MASK_FRIDAY,
                'saturday' => Horde_Date::MASK_SATURDAY,
                'sunday' => Horde_Date::MASK_SUNDAY,
            );
            $days = array();
            foreach ($bits as $name => $bit) {
                if ($this->recurOnDay($bit)) {
                    $days[] = $name;
                }
            }
            $hash['day'] = $days;
            break;

        case self::RECUR_MONTHLY_DATE:
            $hash['cycle'] = 'monthly';
            $hash['type'] = 'daynumber';
            $hash['daynumber'] = $start->mday;
            break;

        case self::RECUR_MONTHLY_WEEKDAY:
            $hash['cycle'] = 'monthly';
            $hash['type'] = 'weekday';
            $hash['daynumber'] = $start->weekOfMonth();
            $hash['day'] = array ($day2number[$start->dayOfWeek()]);
            break;

        case self::RECUR_YEARLY_DATE:
            $hash['cycle'] = 'yearly';
            $hash['type'] = 'monthday';
            $hash['daynumber'] = $start->mday;
            $hash['month'] = $month2number[$start->month];
            break;

        case self::RECUR_YEARLY_DAY:
            $hash['cycle'] = 'yearly';
            $hash['type'] = 'yearday';
            $hash['daynumber'] = $start->dayOfYear();
            break;

        case self::RECUR_YEARLY_WEEKDAY:
            $hash['cycle'] = 'yearly';
            $hash['type'] = 'weekday';
            $hash['daynumber'] = $start->weekOfMonth();
            $hash['day'] = array ($day2number[$start->dayOfWeek()]);
            $hash['month'] = $month2number[$start->month];
        }

        if ($this->hasRecurCount()) {
            $hash['range-type'] = 'number';
            $hash['range'] = $this->getRecurCount();
        } elseif ($this->hasRecurEnd()) {
            $date = $this->getRecurEnd();
            $hash['range-type'] = 'date';
            $hash['range'] = $date->datestamp();
        } else {
            $hash['range-type'] = 'none';
            $hash['range'] = '';
        }

        // Recurrence exceptions
        $hash['exceptions'] = $this->exceptions;
        $hash['completions'] = $this->completions;

        return $hash;
    }

    /**
     * Returns a simple object suitable for json transport representing this
     * object.
     *
     * Possible properties are:
     * - t: type
     * - i: interval
     * - e: end date
     * - c: count
     * - d: data
     * - co: completions
     * - ex: exceptions
     *
     * @return object  A simple object.
     */
    public function toJson()
    {
        $json = new stdClass;
        $json->t = $this->recurType;
        $json->i = $this->recurInterval;
        if ($this->hasRecurEnd()) {
            $json->e = $this->recurEnd->toJson();
        }
        if ($this->recurCount) {
            $json->c = $this->recurCount;
        }
        if ($this->recurData) {
            $json->d = $this->recurData;
        }
        if ($this->completions) {
            $json->co = $this->completions;
        }
        if ($this->exceptions) {
            $json->ex = $this->exceptions;
        }
        return $json;
    }

}
