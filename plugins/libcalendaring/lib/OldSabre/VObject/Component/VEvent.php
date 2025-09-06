<?php

namespace OldSabre\VObject\Component;
use OldSabre\VObject;

/**
 * VEvent component
 *
 * This component contains some additional functionality specific for VEVENT's.
 *
 * @copyright Copyright (C) 2011-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class VEvent extends VObject\Component {

    /**
     * Returns true or false depending on if the event falls in the specified
     * time-range. This is used for filtering purposes.
     *
     * The rules used to determine if an event falls within the specified
     * time-range is based on the CalDAV specification.
     *
     * @param \DateTime $start
     * @param \DateTime $end
     * @return bool
     */
    public function isInTimeRange(\DateTime $start, \DateTime $end) {

        if ($this->RRULE) {
            $it = new VObject\RecurrenceIterator($this);
            $it->fastForward($start);

            // We fast-forwarded to a spot where the end-time of the
            // recurrence instance exceeded the start of the requested
            // time-range.
            //
            // If the starttime of the recurrence did not exceed the
            // end of the time range as well, we have a match.
            return ($it->getDTStart() < $end && $it->getDTEnd() > $start);

        }

        $effectiveStart = $this->DTSTART->getDateTime();
        if (isset($this->DTEND)) {

            // The DTEND property is considered non inclusive. So for a 3 day
            // event in july, dtstart and dtend would have to be July 1st and
            // July 4th respectively.
            //
            // See:
            // http://tools.ietf.org/html/rfc5545#page-54
            $effectiveEnd = $this->DTEND->getDateTime();

        } elseif (isset($this->DURATION)) {
            $effectiveEnd = clone $effectiveStart;
            $effectiveEnd->add( VObject\DateTimeParser::parseDuration($this->DURATION) );
        } elseif ($this->DTSTART->getDateType() == VObject\Property\DateTime::DATE) {
            $effectiveEnd = clone $effectiveStart;
            $effectiveEnd->modify('+1 day');
        } else {
            $effectiveEnd = clone $effectiveStart;
        }
        return (
            ($start <= $effectiveEnd) && ($end > $effectiveStart)
        );

    }

}
