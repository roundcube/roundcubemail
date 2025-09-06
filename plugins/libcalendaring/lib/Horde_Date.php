<?php

/**
 * This is a concatenated copy of the following files:
 *   Horde/Date/Utils.php, Horde/Date/Recurrence.php
 * Pull the latest version of these files from the PEAR channel of the Horde
 * project at http://pear.horde.org by installing the Horde_Date package.
 */


/**
 * Horde Date wrapper/logic class, including some calculation
 * functions.
 *
 * @category Horde
 * @package  Date
 *
 * @TODO in format():
 *   http://php.net/intldateformatter
 *
 * @TODO on timezones:
 *   http://trac.agavi.org/ticket/1008
 *   http://trac.agavi.org/changeset/3659
 *
 * @TODO on switching to PHP::DateTime:
 *   The only thing ever stored in the database *IS* Unix timestamps. Doing
 *   anything other than that is unmanageable, yet some frameworks use 'server
 *   based' times in their systems, simply because they do not bother with
 *   daylight saving and only 'serve' one timezone!
 *
 *   The second you have to manage 'real' time across timezones then daylight
 *   saving becomes essential, BUT only on the display side! Since the browser
 *   only provides a time offset, this is useless and to be honest should simply
 *   be ignored ( until it is upgraded to provide the correct information ;)
 *   ). So we need a 'display' function that takes a simple numeric epoch, and a
 *   separate timezone id into which the epoch is to be 'converted'. My W3C
 *   mapping works simply because ADOdb then converts that to it's own simple
 *   offset abbreviation - in my case GMT or BST. As long as DateTime passes the
 *   full 64 bit number the date range from 100AD is also preserved ( and
 *   further back if 2 digit years are disabled ). If I want to display the
 *   'real' timezone with this 'time' then I just add it in place of ADOdb's
 *   'timezone'. I am tempted to simply adjust the ADOdb class to take a
 *   timezone in place of the simple GMT switch it currently uses.
 *
 *   The return path is just the reverse and simply needs to take the client
 *   display offset off prior to storage of the UTC epoch. SO we use
 *   DateTimeZone to get an offset value for the clients timezone and simply add
 *   or subtract this from a timezone agnostic display on the client end when
 *   entering new times.
 *
 *
 *   It's not really feasible to store dates in specific timezone, as most
 *   national/local timezones support DST - and that is a pain to support, as
 *   eg.  sorting breaks when some timestamps get repeated. That's why it's
 *   usually better to store datetimes as either UTC datetime or plain unix
 *   timestamp. I usually go with the former - using database datetime type.
 */

/**
 * @category Horde
 * @package  Date
 */
class Horde_Date
{
    const DATE_SUNDAY = 0;
    const DATE_MONDAY = 1;
    const DATE_TUESDAY = 2;
    const DATE_WEDNESDAY = 3;
    const DATE_THURSDAY = 4;
    const DATE_FRIDAY = 5;
    const DATE_SATURDAY = 6;

    const MASK_SUNDAY = 1;
    const MASK_MONDAY = 2;
    const MASK_TUESDAY = 4;
    const MASK_WEDNESDAY = 8;
    const MASK_THURSDAY = 16;
    const MASK_FRIDAY = 32;
    const MASK_SATURDAY = 64;
    const MASK_WEEKDAYS = 62;
    const MASK_WEEKEND = 65;
    const MASK_ALLDAYS = 127;

    const MASK_SECOND = 1;
    const MASK_MINUTE = 2;
    const MASK_HOUR = 4;
    const MASK_DAY = 8;
    const MASK_MONTH = 16;
    const MASK_YEAR = 32;
    const MASK_ALLPARTS = 63;

    const DATE_DEFAULT = 'Y-m-d H:i:s';
    const DATE_JSON = 'Y-m-d\TH:i:s';

    /**
     * Year
     *
     * @var integer
     */
    protected $_year;

    /**
     * Month
     *
     * @var integer
     */
    protected $_month;

    /**
     * Day
     *
     * @var integer
     */
    protected $_mday;

    /**
     * Hour
     *
     * @var integer
     */
    protected $_hour = 0;

    /**
     * Minute
     *
     * @var integer
     */
    protected $_min = 0;

    /**
     * Second
     *
     * @var integer
     */
    protected $_sec = 0;

    /**
     * String representation of the date's timezone.
     *
     * @var string
     */
    protected $_timezone;

    /**
     * Default format for __toString()
     *
     * @var string
     */
    protected $_defaultFormat = self::DATE_DEFAULT;

    /**
     * Default specs that are always supported.
     * @var string
     */
    protected static $_defaultSpecs = '%CdDeHImMnRStTyY';

    /**
     * Internally supported strftime() specifiers.
     * @var string
     */
    protected static $_supportedSpecs = '';

    /**
     * Map of required correction masks.
     *
     * @see __set()
     *
     * @var array
     */
    protected static $_corrections = array(
        'year'  => self::MASK_YEAR,
        'month' => self::MASK_MONTH,
        'mday'  => self::MASK_DAY,
        'hour'  => self::MASK_HOUR,
        'min'   => self::MASK_MINUTE,
        'sec'   => self::MASK_SECOND,
    );

    protected $_formatCache = array();

    /**
     * Builds a new date object. If $date contains date parts, use them to
     * initialize the object.
     *
     * Recognized formats:
     * - arrays with keys 'year', 'month', 'mday', 'day'
     *   'hour', 'min', 'minute', 'sec'
     * - objects with properties 'year', 'month', 'mday', 'hour', 'min', 'sec'
     * - yyyy-mm-dd hh:mm:ss
     * - yyyymmddhhmmss
     * - yyyymmddThhmmssZ
     * - yyyymmdd (might conflict with unix timestamps between 31 Oct 1966 and
     *   03 Mar 1973)
     * - unix timestamps
     * - anything parsed by strtotime()/DateTime.
     *
     * @throws Horde_Date_Exception
     */
    public function __construct($date = null, $timezone = null)
    {
        if (!self::$_supportedSpecs) {
            self::$_supportedSpecs = self::$_defaultSpecs;
            if (function_exists('nl_langinfo')) {
                self::$_supportedSpecs .= 'bBpxX';
            }
        }

        if (func_num_args() > 2) {
            // Handle args in order: year month day hour min sec tz
            $this->_initializeFromArgs(func_get_args());
            return;
        }

        $this->_initializeTimezone($timezone);

        if (is_null($date)) {
            return;
        }

        if (is_string($date)) {
            $date = trim($date, '"');
        }

        if (is_object($date)) {
            $this->_initializeFromObject($date);
        } elseif (is_array($date)) {
            $this->_initializeFromArray($date);
        } elseif (preg_match('/^(\d{4})-?(\d{2})-?(\d{2})T? ?(\d{2}):?(\d{2}):?(\d{2})(?:\.\d+)?(Z?)$/', $date, $parts)) {
            $this->_year  = (int)$parts[1];
            $this->_month = (int)$parts[2];
            $this->_mday  = (int)$parts[3];
            $this->_hour  = (int)$parts[4];
            $this->_min   = (int)$parts[5];
            $this->_sec   = (int)$parts[6];
            if ($parts[7]) {
                $this->_initializeTimezone('UTC');
            }
        } elseif (preg_match('/^(\d{4})-?(\d{2})-?(\d{2})$/', $date, $parts) &&
                  $parts[2] > 0 && $parts[2] <= 12 &&
                  $parts[3] > 0 && $parts[3] <= 31) {
            $this->_year  = (int)$parts[1];
            $this->_month = (int)$parts[2];
            $this->_mday  = (int)$parts[3];
            $this->_hour = $this->_min = $this->_sec = 0;
        } elseif ((string)(int)$date == $date) {
            // Try as a timestamp.
            $parts = @getdate($date);
            if ($parts) {
                $this->_year  = $parts['year'];
                $this->_month = $parts['mon'];
                $this->_mday  = $parts['mday'];
                $this->_hour  = $parts['hours'];
                $this->_min   = $parts['minutes'];
                $this->_sec   = $parts['seconds'];
            }
        } else {
            // Use date_create() so we can catch errors with PHP 5.2. Use
            // "new DateTime() once we require 5.3.
            $parsed = date_create($date);
            if (!$parsed) {
                throw new Horde_Date_Exception(sprintf(Horde_Date_Translation::t("Failed to parse time string (%s)"), $date));
            }
            $parsed->setTimezone(new DateTimeZone(date_default_timezone_get()));
            $this->_year  = (int)$parsed->format('Y');
            $this->_month = (int)$parsed->format('m');
            $this->_mday  = (int)$parsed->format('d');
            $this->_hour  = (int)$parsed->format('H');
            $this->_min   = (int)$parsed->format('i');
            $this->_sec   = (int)$parsed->format('s');
            $this->_initializeTimezone(date_default_timezone_get());
        }
    }

    /**
     * Returns a simple string representation of the date object
     *
     * @return string  This object converted to a string.
     */
    public function __toString()
    {
        try {
            return $this->format($this->_defaultFormat);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Returns a DateTime object representing this object.
     *
     * @return DateTime
     */
    public function toDateTime()
    {
        $date = new DateTime(null, new DateTimeZone($this->_timezone));
        $date->setDate($this->_year, $this->_month, $this->_mday);
        $date->setTime($this->_hour, $this->_min, $this->_sec);
        return $date;
    }

    /**
     * Converts a date in the proleptic Gregorian calendar to the no of days
     * since 24th November, 4714 B.C.
     *
     * Returns the no of days since Monday, 24th November, 4714 B.C. in the
     * proleptic Gregorian calendar (which is 24th November, -4713 using
     * 'Astronomical' year numbering, and 1st January, 4713 B.C. in the
     * proleptic Julian calendar).  This is also the first day of the 'Julian
     * Period' proposed by Joseph Scaliger in 1583, and the number of days
     * since this date is known as the 'Julian Day'.  (It is not directly
     * to do with the Julian calendar, although this is where the name
     * is derived from.)
     *
     * The algorithm is valid for all years (positive and negative), and
     * also for years preceding 4714 B.C.
     *
     * Algorithm is from PEAR::Date_Calc
     *
     * @author Monte Ohrt <monte@ispi.net>
     * @author Pierre-Alain Joye <pajoye@php.net>
     * @author Daniel Convissor <danielc@php.net>
     * @author C.A. Woodcock <c01234@netcomuk.co.uk>
     *
     * @return integer  The number of days since 24th November, 4714 B.C.
     */
    public function toDays()
    {
        if (function_exists('GregorianToJD')) {
            return gregoriantojd($this->_month, $this->_mday, $this->_year);
        }

        $day = $this->_mday;
        $month = $this->_month;
        $year = $this->_year;

        if ($month > 2) {
            // March = 0, April = 1, ..., December = 9,
            // January = 10, February = 11
            $month -= 3;
        } else {
            $month += 9;
            --$year;
        }

        $hb_negativeyear = $year < 0;
        $century         = intval($year / 100);
        $year            = $year % 100;

        if ($hb_negativeyear) {
            // Subtract 1 because year 0 is a leap year;
            // And N.B. that we must treat the leap years as occurring
            // one year earlier than they do, because for the purposes
            // of calculation, the year starts on 1st March:
            //
            return intval((14609700 * $century + ($year == 0 ? 1 : 0)) / 400) +
                   intval((1461 * $year + 1) / 4) +
                   intval((153 * $month + 2) / 5) +
                   $day + 1721118;
        } else {
            return intval(146097 * $century / 4) +
                   intval(1461 * $year / 4) +
                   intval((153 * $month + 2) / 5) +
                   $day + 1721119;
        }
    }

    /**
     * Converts number of days since 24th November, 4714 B.C. (in the proleptic
     * Gregorian calendar, which is year -4713 using 'Astronomical' year
     * numbering) to Gregorian calendar date.
     *
     * Returned date belongs to the proleptic Gregorian calendar, using
     * 'Astronomical' year numbering.
     *
     * The algorithm is valid for all years (positive and negative), and
     * also for years preceding 4714 B.C. (i.e. for negative 'Julian Days'),
     * and so the only limitation is platform-dependent (for 32-bit systems
     * the maximum year would be something like about 1,465,190 A.D.).
     *
     * N.B. Monday, 24th November, 4714 B.C. is Julian Day '0'.
     *
     * Algorithm is from PEAR::Date_Calc
     *
     * @author Monte Ohrt <monte@ispi.net>
     * @author Pierre-Alain Joye <pajoye@php.net>
     * @author Daniel Convissor <danielc@php.net>
     * @author C.A. Woodcock <c01234@netcomuk.co.uk>
     *
     * @param int    $days   the number of days since 24th November, 4714 B.C.
     * @param string $format the string indicating how to format the output
     *
     * @return  Horde_Date  A Horde_Date object representing the date.
     */
    public static function fromDays($days)
    {
        if (function_exists('JDToGregorian')) {
            list($month, $day, $year) = explode('/', JDToGregorian($days));
        } else {
            $days = intval($days);

            $days   -= 1721119;
            $century = floor((4 * $days - 1) / 146097);
            $days    = floor(4 * $days - 1 - 146097 * $century);
            $day     = floor($days / 4);

            $year = floor((4 * $day +  3) / 1461);
            $day  = floor(4 * $day +  3 - 1461 * $year);
            $day  = floor(($day +  4) / 4);

            $month = floor((5 * $day - 3) / 153);
            $day   = floor(5 * $day - 3 - 153 * $month);
            $day   = floor(($day +  5) /  5);

            $year = $century * 100 + $year;
            if ($month < 10) {
                $month +=3;
            } else {
                $month -=9;
                ++$year;
            }
        }

        return new Horde_Date($year, $month, $day);
    }

    /**
     * Getter for the date and time properties.
     *
     * @param string $name  One of 'year', 'month', 'mday', 'hour', 'min' or
     *                      'sec'.
     *
     * @return integer  The property value, or null if not set.
     */
    public function __get($name)
    {
        if ($name == 'day') {
            $name = 'mday';
        }

        return $this->{'_' . $name};
    }

    /**
     * Setter for the date and time properties.
     *
     * @param string $name    One of 'year', 'month', 'mday', 'hour', 'min' or
     *                        'sec'.
     * @param integer $value  The property value.
     */
    public function __set($name, $value)
    {
        if ($name == 'timezone') {
            $this->_initializeTimezone($value);
            return;
        }
        if ($name == 'day') {
            $name = 'mday';
        }

        if ($name != 'year' && $name != 'month' && $name != 'mday' &&
            $name != 'hour' && $name != 'min' && $name != 'sec') {
            throw new InvalidArgumentException('Undefined property ' . $name);
        }

        $down = $value < $this->{'_' . $name};
        $this->{'_' . $name} = $value;
        $this->_correct(self::$_corrections[$name], $down);
        $this->_formatCache = array();
    }

    /**
     * Returns whether a date or time property exists.
     *
     * @param string $name  One of 'year', 'month', 'mday', 'hour', 'min' or
     *                      'sec'.
     *
     * @return boolen  True if the property exists and is set.
     */
    public function __isset($name)
    {
        if ($name == 'day') {
            $name = 'mday';
        }
        return ($name == 'year' || $name == 'month' || $name == 'mday' ||
                $name == 'hour' || $name == 'min' || $name == 'sec') &&
            isset($this->{'_' . $name});
    }

    /**
     * Adds a number of seconds or units to this date, returning a new Date
     * object.
     */
    public function add($factor)
    {
        $d = clone($this);
        if (is_array($factor) || is_object($factor)) {
            foreach ($factor as $property => $value) {
                $d->$property += $value;
            }
        } else {
            $d->sec += $factor;
        }

        return $d;
    }

    /**
     * Subtracts a number of seconds or units from this date, returning a new
     * Horde_Date object.
     */
    public function sub($factor)
    {
        if (is_array($factor)) {
            foreach ($factor as &$value) {
                $value *= -1;
            }
        } else {
            $factor *= -1;
        }

        return $this->add($factor);
    }

    /**
     * Converts this object to a different timezone.
     *
     * @param string $timezone  The new timezone.
     *
     * @return Horde_Date  This object.
     */
    public function setTimezone($timezone)
    {
        $date = $this->toDateTime();
        $date->setTimezone(new DateTimeZone($timezone));
        $this->_timezone = $timezone;
        $this->_year     = (int)$date->format('Y');
        $this->_month    = (int)$date->format('m');
        $this->_mday     = (int)$date->format('d');
        $this->_hour     = (int)$date->format('H');
        $this->_min      = (int)$date->format('i');
        $this->_sec      = (int)$date->format('s');
        $this->_formatCache = array();
        return $this;
    }

    /**
     * Sets the default date format used in __toString()
     *
     * @param string $format
     */
    public function setDefaultFormat($format)
    {
        $this->_defaultFormat = $format;
    }

    /**
     * Returns the day of the week (0 = Sunday, 6 = Saturday) of this date.
     *
     * @return integer  The day of the week.
     */
    public function dayOfWeek()
    {
        if ($this->_month > 2) {
            $month = $this->_month - 2;
            $year = $this->_year;
        } else {
            $month = $this->_month + 10;
            $year = $this->_year - 1;
        }

        $day = (floor((13 * $month - 1) / 5) +
                $this->_mday + ($year % 100) +
                floor(($year % 100) / 4) +
                floor(($year / 100) / 4) - 2 *
                floor($year / 100) + 77);

        return (int)($day - 7 * floor($day / 7));
    }

    /**
     * Returns the day number of the year (1 to 365/366).
     *
     * @return integer  The day of the year.
     */
    public function dayOfYear()
    {
        return $this->format('z') + 1;
    }

    /**
     * Returns the week of the month.
     *
     * @return integer  The week number.
     */
    public function weekOfMonth()
    {
        return ceil($this->_mday / 7);
    }

    /**
     * Returns the week of the year, first Monday is first day of first week.
     *
     * @return integer  The week number.
     */
    public function weekOfYear()
    {
        return $this->format('W');
    }

    /**
     * Returns the number of weeks in the given year (52 or 53).
     *
     * @param integer $year  The year to count the number of weeks in.
     *
     * @return integer $numWeeks   The number of weeks in $year.
     */
    public static function weeksInYear($year)
    {
        // Find the last Thursday of the year.
        $date = new Horde_Date($year . '-12-31');
        while ($date->dayOfWeek() != self::DATE_THURSDAY) {
            --$date->mday;
        }
        return $date->weekOfYear();
    }

    /**
     * Sets the date of this object to the $nth weekday of $weekday.
     *
     * @param integer $weekday  The day of the week (0 = Sunday, etc).
     * @param integer $nth      The $nth $weekday to set to (defaults to 1).
     */
    public function setNthWeekday($weekday, $nth = 1)
    {
        if ($weekday < self::DATE_SUNDAY || $weekday > self::DATE_SATURDAY) {
            return;
        }

        if ($nth < 0) {  // last $weekday of month
            $this->_mday = $lastday = Horde_Date_Utils::daysInMonth($this->_month, $this->_year);
            $last = $this->dayOfWeek();
            $this->_mday += ($weekday - $last);
            if ($this->_mday > $lastday)
                $this->_mday -= 7;
        }
        else {
            $this->_mday = 1;
            $first = $this->dayOfWeek();
            if ($weekday < $first) {
                $this->_mday = 8 + $weekday - $first;
            } else {
                $this->_mday = $weekday - $first + 1;
            }
            $diff = 7 * $nth - 7;
            $this->_mday += $diff;
            $this->_correct(self::MASK_DAY, $diff < 0);
        }
    }

    /**
     * Is the date currently represented by this object a valid date?
     *
     * @return boolean  Validity, counting leap years, etc.
     */
    public function isValid()
    {
        return ($this->_year >= 0 && $this->_year <= 9999);
    }

    /**
     * Compares this date to another date object to see which one is
     * greater (later). Assumes that the dates are in the same
     * timezone.
     *
     * @param mixed $other  The date to compare to.
     *
     * @return integer  ==  0 if they are on the same date
     *                  >=  1 if $this is greater (later)
     *                  <= -1 if $other is greater (later)
     */
    public function compareDate($other)
    {
        if (!($other instanceof Horde_Date)) {
            $other = new Horde_Date($other);
        }

        if ($this->_year != $other->year) {
            return $this->_year - $other->year;
        }
        if ($this->_month != $other->month) {
            return $this->_month - $other->month;
        }

        return $this->_mday - $other->mday;
    }

    /**
     * Returns whether this date is after the other.
     *
     * @param mixed $other  The date to compare to.
     *
     * @return boolean  True if this date is after the other.
     */
    public function after($other)
    {
        return $this->compareDate($other) > 0;
    }

    /**
     * Returns whether this date is before the other.
     *
     * @param mixed $other  The date to compare to.
     *
     * @return boolean  True if this date is before the other.
     */
    public function before($other)
    {
        return $this->compareDate($other) < 0;
    }

    /**
     * Returns whether this date is the same like the other.
     *
     * @param mixed $other  The date to compare to.
     *
     * @return boolean  True if this date is the same like the other.
     */
    public function equals($other)
    {
        return $this->compareDate($other) == 0;
    }

    /**
     * Compares this to another date object by time, to see which one
     * is greater (later). Assumes that the dates are in the same
     * timezone.
     *
     * @param mixed $other  The date to compare to.
     *
     * @return integer  ==  0 if they are at the same time
     *                  >=  1 if $this is greater (later)
     *                  <= -1 if $other is greater (later)
     */
    public function compareTime($other)
    {
        if (!($other instanceof Horde_Date)) {
            $other = new Horde_Date($other);
        }

        if ($this->_hour != $other->hour) {
            return $this->_hour - $other->hour;
        }
        if ($this->_min != $other->min) {
            return $this->_min - $other->min;
        }

        return $this->_sec - $other->sec;
    }

    /**
     * Compares this to another date object, including times, to see
     * which one is greater (later). Assumes that the dates are in the
     * same timezone.
     *
     * @param mixed $other  The date to compare to.
     *
     * @return integer  ==  0 if they are equal
     *                  >=  1 if $this is greater (later)
     *                  <= -1 if $other is greater (later)
     */
    public function compareDateTime($other)
    {
        if (!($other instanceof Horde_Date)) {
            $other = new Horde_Date($other);
        }

        if ($diff = $this->compareDate($other)) {
            return $diff;
        }

        return $this->compareTime($other);
    }

    /**
     * Returns number of days between this date and another.
     *
     * @param Horde_Date $other  The other day to diff with.
     *
     * @return integer  The absolute number of days between the two dates.
     */
    public function diff($other)
    {
        return abs($this->toDays() - $other->toDays());
    }

    /**
     * Returns the time offset for local time zone.
     *
     * @param boolean $colon  Place a colon between hours and minutes?
     *
     * @return string  Timezone offset as a string in the format +HH:MM.
     */
    public function tzOffset($colon = true)
    {
        return $colon ? $this->format('P') : $this->format('O');
    }

    /**
     * Returns the unix timestamp representation of this date.
     *
     * @return integer  A unix timestamp.
     */
    public function timestamp()
    {
        if ($this->_year >= 1970 && $this->_year < 2038) {
            return mktime($this->_hour, $this->_min, $this->_sec,
                          $this->_month, $this->_mday, $this->_year);
        }
        return $this->format('U');
    }

    /**
     * Returns the unix timestamp representation of this date, 12:00am.
     *
     * @return integer  A unix timestamp.
     */
    public function datestamp()
    {
        if ($this->_year >= 1970 && $this->_year < 2038) {
            return mktime(0, 0, 0, $this->_month, $this->_mday, $this->_year);
        }
        $date = new DateTime($this->format('Y-m-d'));
        return $date->format('U');
    }

    /**
     * Formats date and time to be passed around as a short url parameter.
     *
     * @return string  Date and time.
     */
    public function dateString()
    {
        return sprintf('%04d%02d%02d', $this->_year, $this->_month, $this->_mday);
    }

    /**
     * Formats date and time to the ISO format used by JSON.
     *
     * @return string  Date and time.
     */
    public function toJson()
    {
        return $this->format(self::DATE_JSON);
    }

    /**
     * Formats date and time to the RFC 2445 iCalendar DATE-TIME format.
     *
     * @param boolean $floating  Whether to return a floating date-time
     *                           (without time zone information).
     *
     * @return string  Date and time.
     */
    public function toiCalendar($floating = false)
    {
        if ($floating) {
            return $this->format('Ymd\THis');
        }
        $dateTime = $this->toDateTime();
        $dateTime->setTimezone(new DateTimeZone('UTC'));
        return $dateTime->format('Ymd\THis\Z');
    }

    /**
     * Formats time using the specifiers available in date() or in the DateTime
     * class' format() method.
     *
     * To format in languages other than English, use strftime() instead.
     *
     * @param string $format
     *
     * @return string  Formatted time.
     */
    public function format($format)
    {
        if (!isset($this->_formatCache[$format])) {
            $this->_formatCache[$format] = $this->toDateTime()->format($format);
        }
        return $this->_formatCache[$format];
    }

    /**
     * Formats date and time using strftime() format.
     *
     * @return string  strftime() formatted date and time.
     */
    public function strftime($format)
    {
        if (preg_match('/%[^' . self::$_supportedSpecs . ']/', $format)) {
            return strftime($format, $this->timestamp());
        } else {
            return $this->_strftime($format);
        }
    }

    /**
     * Formats date and time using a limited set of the strftime() format.
     *
     * @return string  strftime() formatted date and time.
     */
    protected function _strftime($format)
    {
        return preg_replace(
            array('/%b/e',
                  '/%B/e',
                  '/%C/e',
                  '/%d/e',
                  '/%D/e',
                  '/%e/e',
                  '/%H/e',
                  '/%I/e',
                  '/%m/e',
                  '/%M/e',
                  '/%n/',
                  '/%p/e',
                  '/%R/e',
                  '/%S/e',
                  '/%t/',
                  '/%T/e',
                  '/%x/e',
                  '/%X/e',
                  '/%y/e',
                  '/%Y/',
                  '/%%/'),
            array('$this->_strftime(Horde_Nls::getLangInfo(constant(\'ABMON_\' . (int)$this->_month)))',
                  '$this->_strftime(Horde_Nls::getLangInfo(constant(\'MON_\' . (int)$this->_month)))',
                  '(int)($this->_year / 100)',
                  'sprintf(\'%02d\', $this->_mday)',
                  '$this->_strftime(\'%m/%d/%y\')',
                  'sprintf(\'%2d\', $this->_mday)',
                  'sprintf(\'%02d\', $this->_hour)',
                  'sprintf(\'%02d\', $this->_hour == 0 ? 12 : ($this->_hour > 12 ? $this->_hour - 12 : $this->_hour))',
                  'sprintf(\'%02d\', $this->_month)',
                  'sprintf(\'%02d\', $this->_min)',
                  "\n",
                  '$this->_strftime(Horde_Nls::getLangInfo($this->_hour < 12 ? AM_STR : PM_STR))',
                  '$this->_strftime(\'%H:%M\')',
                  'sprintf(\'%02d\', $this->_sec)',
                  "\t",
                  '$this->_strftime(\'%H:%M:%S\')',
                  '$this->_strftime(Horde_Nls::getLangInfo(D_FMT))',
                  '$this->_strftime(Horde_Nls::getLangInfo(T_FMT))',
                  'substr(sprintf(\'%04d\', $this->_year), -2)',
                  (int)$this->_year,
                  '%'),
            $format);
    }

    /**
     * Corrects any over- or underflows in any of the date's members.
     *
     * @param integer $mask  We may not want to correct some overflows.
     * @param integer $down  Whether to correct the date up or down.
     */
    protected function _correct($mask = self::MASK_ALLPARTS, $down = false)
    {
        if ($mask & self::MASK_SECOND) {
            if ($this->_sec < 0 || $this->_sec > 59) {
                $mask |= self::MASK_MINUTE;

                $this->_min += (int)($this->_sec / 60);
                $this->_sec %= 60;
                if ($this->_sec < 0) {
                    $this->_min--;
                    $this->_sec += 60;
                }
            }
        }

        if ($mask & self::MASK_MINUTE) {
            if ($this->_min < 0 || $this->_min > 59) {
                $mask |= self::MASK_HOUR;

                $this->_hour += (int)($this->_min / 60);
                $this->_min %= 60;
                if ($this->_min < 0) {
                    $this->_hour--;
                    $this->_min += 60;
                }
            }
        }

        if ($mask & self::MASK_HOUR) {
            if ($this->_hour < 0 || $this->_hour > 23) {
                $mask |= self::MASK_DAY;

                $this->_mday += (int)($this->_hour / 24);
                $this->_hour %= 24;
                if ($this->_hour < 0) {
                    $this->_mday--;
                    $this->_hour += 24;
                }
            }
        }

        if ($mask & self::MASK_MONTH) {
            $this->_correctMonth($down);
            /* When correcting the month, always correct the day too. Months
             * have different numbers of days. */
            $mask |= self::MASK_DAY;
        }

        if ($mask & self::MASK_DAY) {
            while ($this->_mday > 28 &&
                   $this->_mday > Horde_Date_Utils::daysInMonth($this->_month, $this->_year)) {
                if ($down) {
                    $this->_mday -= Horde_Date_Utils::daysInMonth($this->_month + 1, $this->_year) - Horde_Date_Utils::daysInMonth($this->_month, $this->_year);
                } else {
                    $this->_mday -= Horde_Date_Utils::daysInMonth($this->_month, $this->_year);
                    $this->_month++;
                }
                $this->_correctMonth($down);
            }
            while ($this->_mday < 1) {
                --$this->_month;
                $this->_correctMonth($down);
                $this->_mday += Horde_Date_Utils::daysInMonth($this->_month, $this->_year);
            }
        }
    }

    /**
     * Corrects the current month.
     *
     * This cannot be done in _correct() because that would also trigger a
     * correction of the day, which would result in an infinite loop.
     *
     * @param integer $down  Whether to correct the date up or down.
     */
    protected function _correctMonth($down = false)
    {
        $this->_year += (int)($this->_month / 12);
        $this->_month %= 12;
        if ($this->_month < 1) {
            $this->_year--;
            $this->_month += 12;
        }
    }

    /**
     * Handles args in order: year month day hour min sec tz
     */
    protected function _initializeFromArgs($args)
    {
        $tz = (isset($args[6])) ? array_pop($args) : null;
        $this->_initializeTimezone($tz);

        $args = array_slice($args, 0, 6);
        $keys = array('year' => 1, 'month' => 1, 'mday' => 1, 'hour' => 0, 'min' => 0, 'sec' => 0);
        $date = array_combine(array_slice(array_keys($keys), 0, count($args)), $args);
        $date = array_merge($keys, $date);

        $this->_initializeFromArray($date);
    }

    protected function _initializeFromArray($date)
    {
        if (isset($date['year']) && is_string($date['year']) && strlen($date['year']) == 2) {
            if ($date['year'] > 70) {
                $date['year'] += 1900;
            } else {
                $date['year'] += 2000;
            }
        }

        foreach ($date as $key => $val) {
            if (in_array($key, array('year', 'month', 'mday', 'hour', 'min', 'sec'))) {
                $this->{'_'. $key} = (int)$val;
            }
        }

        // If $date['day'] is present and numeric we may have been passed
        // a Horde_Form_datetime array.
        if (isset($date['day']) &&
            (string)(int)$date['day'] == $date['day']) {
            $this->_mday = (int)$date['day'];
        }
        // 'minute' key also from Horde_Form_datetime
        if (isset($date['minute']) &&
            (string)(int)$date['minute'] == $date['minute']) {
            $this->_min = (int)$date['minute'];
        }

        $this->_correct();
    }

    protected function _initializeFromObject($date)
    {
        if ($date instanceof DateTime) {
            $this->_year  = (int)$date->format('Y');
            $this->_month = (int)$date->format('m');
            $this->_mday  = (int)$date->format('d');
            $this->_hour  = (int)$date->format('H');
            $this->_min   = (int)$date->format('i');
            $this->_sec   = (int)$date->format('s');
            $this->_initializeTimezone($date->getTimezone()->getName());
        } else {
            $is_horde_date = $date instanceof Horde_Date;
            foreach (array('year', 'month', 'mday', 'hour', 'min', 'sec') as $key) {
                if ($is_horde_date || isset($date->$key)) {
                    $this->{'_' . $key} = (int)$date->$key;
                }
            }
            if (!$is_horde_date) {
                $this->_correct();
            } else {
                $this->_initializeTimezone($date->timezone);
            }
        }
    }

    protected function _initializeTimezone($timezone)
    {
        if (empty($timezone)) {
            $timezone = date_default_timezone_get();
        }
        $this->_timezone = $timezone;
    }

}

/**
 * @category Horde
 * @package  Date
 */

/**
 * Horde Date wrapper/logic class, including some calculation
 * functions.
 *
 * @category Horde
 * @package  Date
 */
class Horde_Date_Utils
{
    /**
     * Returns whether a year is a leap year.
     *
     * @param integer $year  The year.
     *
     * @return boolean  True if the year is a leap year.
     */
    public static function isLeapYear($year)
    {
        if (strlen($year) != 4 || preg_match('/\D/', $year)) {
            return false;
        }

        return (($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0);
    }

    /**
     * Returns the date of the year that corresponds to the first day of the
     * given week.
     *
     * @param integer $week  The week of the year to find the first day of.
     * @param integer $year  The year to calculate for.
     *
     * @return Horde_Date  The date of the first day of the given week.
     */
    public static function firstDayOfWeek($week, $year)
    {
        return new Horde_Date(sprintf('%04dW%02d', $year, $week));
    }

    /**
     * Returns the number of days in the specified month.
     *
     * @param integer $month  The month
     * @param integer $year   The year.
     *
     * @return integer  The number of days in the month.
     */
    public static function daysInMonth($month, $year)
    {
        static $cache = array();
        if (!isset($cache[$year][$month])) {
            $date = new DateTime(sprintf('%04d-%02d-01', $year, $month));
            $cache[$year][$month] = $date->format('t');
        }
        return $cache[$year][$month];
    }

    /**
     * Returns a relative, natural language representation of a timestamp
     *
     * @todo Wider range of values ... maybe future time as well?
     * @todo Support minimum resolution parameter.
     *
     * @param mixed $time          The time. Any format accepted by Horde_Date.
     * @param string $date_format  Format to display date if timestamp is
     *                             more then 1 day old.
     * @param string $time_format  Format to display time if timestamp is 1
     *                             day old.
     *
     * @return string  The relative time (i.e. 2 minutes ago)
     */
    public static function relativeDateTime($time, $date_format = '%x',
                                            $time_format = '%X')
    {
        $date = new Horde_Date($time);

        $delta = time() - $date->timestamp();
        if ($delta < 60) {
            return sprintf(Horde_Date_Translation::ngettext("%d second ago", "%d seconds ago", $delta), $delta);
        }

        $delta = round($delta / 60);
        if ($delta < 60) {
            return sprintf(Horde_Date_Translation::ngettext("%d minute ago", "%d minutes ago", $delta), $delta);
        }

        $delta = round($delta / 60);
        if ($delta < 24) {
            return sprintf(Horde_Date_Translation::ngettext("%d hour ago", "%d hours ago", $delta), $delta);
        }

        if ($delta > 24 && $delta < 48) {
            $date = new Horde_Date($time);
            return sprintf(Horde_Date_Translation::t("yesterday at %s"), $date->strftime($time_format));
        }

        $delta = round($delta / 24);
        if ($delta < 7) {
            return sprintf(Horde_Date_Translation::t("%d days ago"), $delta);
        }

        if (round($delta / 7) < 5) {
            $delta = round($delta / 7);
            return sprintf(Horde_Date_Translation::ngettext("%d week ago", "%d weeks ago", $delta), $delta);
        }

        // Default to the user specified date format.
        return $date->strftime($date_format);
    }

    /**
     * Tries to convert strftime() formatters to date() formatters.
     *
     * Unsupported formatters will be removed.
     *
     * @param string $format  A strftime() formatting string.
     *
     * @return string  A date() formatting string.
     */
    public static function strftime2date($format)
    {
        $replace = array(
            '/%a/'  => 'D',
            '/%A/'  => 'l',
            '/%d/'  => 'd',
            '/%e/'  => 'j',
            '/%j/'  => 'z',
            '/%u/'  => 'N',
            '/%w/'  => 'w',
            '/%U/'  => '',
            '/%V/'  => 'W',
            '/%W/'  => '',
            '/%b/'  => 'M',
            '/%B/'  => 'F',
            '/%h/'  => 'M',
            '/%m/'  => 'm',
            '/%C/'  => '',
            '/%g/'  => '',
            '/%G/'  => 'o',
            '/%y/'  => 'y',
            '/%Y/'  => 'Y',
            '/%H/'  => 'H',
            '/%I/'  => 'h',
            '/%i/'  => 'g',
            '/%M/'  => 'i',
            '/%p/'  => 'A',
            '/%P/'  => 'a',
            '/%r/'  => 'h:i:s A',
            '/%R/'  => 'H:i',
            '/%S/'  => 's',
            '/%T/'  => 'H:i:s',
            '/%X/e' => 'Horde_Date_Utils::strftime2date(Horde_Nls::getLangInfo(T_FMT))',
            '/%z/'  => 'O',
            '/%Z/'  => '',
            '/%c/'  => '',
            '/%D/'  => 'm/d/y',
            '/%F/'  => 'Y-m-d',
            '/%s/'  => 'U',
            '/%x/e' => 'Horde_Date_Utils::strftime2date(Horde_Nls::getLangInfo(D_FMT))',
            '/%n/'  => "\n",
            '/%t/'  => "\t",
            '/%%/'  => '%'
        );

        return preg_replace(array_keys($replace), array_values($replace), $format);
    }

}
