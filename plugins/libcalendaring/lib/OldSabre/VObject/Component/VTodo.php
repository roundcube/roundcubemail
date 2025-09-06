<?php

namespace OldSabre\VObject\Component;

use OldSabre\VObject;

/**
 * VTodo component
 *
 * This component contains some additional functionality specific for VTODOs.
 *
 * @copyright Copyright (C) 2011-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class VTodo extends VObject\Component {

    /**
     * Returns true or false depending on if the event falls in the specified
     * time-range. This is used for filtering purposes.
     *
     * The rules used to determine if an event falls within the specified
     * time-range is based on the CalDAV specification.
     *
     * @param DateTime $start
     * @param DateTime $end
     * @return bool
     */
    public function isInTimeRange(\DateTime $start, \DateTime $end) {

        $dtstart = isset($this->DTSTART)?$this->DTSTART->getDateTime():null;
        $duration = isset($this->DURATION)?VObject\DateTimeParser::parseDuration($this->DURATION):null;
        $due = isset($this->DUE)?$this->DUE->getDateTime():null;
        $completed = isset($this->COMPLETED)?$this->COMPLETED->getDateTime():null;
        $created = isset($this->CREATED)?$this->CREATED->getDateTime():null;

        if ($dtstart) {
            if ($duration) {
                $effectiveEnd = clone $dtstart;
                $effectiveEnd->add($duration);
                return $start <= $effectiveEnd && $end > $dtstart;
            } elseif ($due) {
                return
                    ($start < $due || $start <= $dtstart) &&
                    ($end > $dtstart || $end >= $due);
            } else {
                return $start <= $dtstart && $end > $dtstart;
            }
        }
        if ($due) {
            return ($start < $due && $end >= $due);
        }
        if ($completed && $created) {
            return
                ($start <= $created || $start <= $completed) &&
                ($end >= $created || $end >= $completed);
        }
        if ($completed) {
            return ($start <= $completed && $end >= $completed);
        }
        if ($created) {
            return ($end > $created);
        }
        return true;

    }

}
