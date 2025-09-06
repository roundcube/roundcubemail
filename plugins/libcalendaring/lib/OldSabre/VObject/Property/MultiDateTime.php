<?php

namespace OldSabre\VObject\Property;

use OldSabre\VObject;

/**
 * Multi-DateTime property
 *
 * This element is used for iCalendar properties such as the EXDATE property.
 * It basically provides a few helper functions that make it easier to deal
 * with these. It supports both DATE-TIME and DATE values.
 *
 * In order to use this correctly, you must call setDateTimes and getDateTimes
 * to retrieve and modify dates respectively.
 *
 * If you use the 'value' or properties directly, this object does not keep
 * reference and results might appear incorrectly.
 *
 * @copyright Copyright (C) 2011-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class MultiDateTime extends VObject\Property {

    /**
     * DateTime representation
     *
     * @var DateTime[]
     */
    protected $dateTimes;

    /**
     * dateType
     *
     * This is one of the OldSabre\VObject\Property\DateTime constants.
     *
     * @var int
     */
    protected $dateType;

    /**
     * Updates the value
     *
     * @param array $dt Must be an array of DateTime objects.
     * @param int $dateType
     * @return void
     */
    public function setDateTimes(array $dt, $dateType = VObject\Property\DateTime::LOCALTZ) {

        foreach($dt as $i)
            if (!$i instanceof \DateTime)
                throw new \InvalidArgumentException('You must pass an array of DateTime objects');

        $this->offsetUnset('VALUE');
        $this->offsetUnset('TZID');
        switch($dateType) {

            case DateTime::LOCAL :
                $val = array();
                foreach($dt as $i) {
                    $val[] = $i->format('Ymd\\THis');
                }
                $this->setValue(implode(',',$val));
                $this->offsetSet('VALUE','DATE-TIME');
                break;
            case DateTime::UTC :
                $val = array();
                foreach($dt as $i) {
                    $i->setTimeZone(new \DateTimeZone('UTC'));
                    $val[] = $i->format('Ymd\\THis\\Z');
                }
                $this->setValue(implode(',',$val));
                $this->offsetSet('VALUE','DATE-TIME');
                break;
            case DateTime::LOCALTZ :
                $val = array();
                foreach($dt as $i) {
                    $val[] = $i->format('Ymd\\THis');
                }
                $this->setValue(implode(',',$val));
                $this->offsetSet('VALUE','DATE-TIME');
                $this->offsetSet('TZID', $dt[0]->getTimeZone()->getName());
                break;
            case DateTime::DATE :
                $val = array();
                foreach($dt as $i) {
                    $val[] = $i->format('Ymd');
                }
                $this->setValue(implode(',',$val));
                $this->offsetSet('VALUE','DATE');
                break;
            default :
                throw new \InvalidArgumentException('You must pass a valid dateType constant');

        }
        $this->dateTimes = $dt;
        $this->dateType = $dateType;

    }

    /**
     * Returns the current DateTime value.
     *
     * If no value was set, this method returns null.
     *
     * @return array|null
     */
    public function getDateTimes() {

        if ($this->dateTimes)
            return $this->dateTimes;

        $dts = array();

        if (!$this->value) {
            $this->dateTimes = null;
            $this->dateType = null;
            return null;
        }

        foreach(explode(',',$this->value) as $val) {
            list(
                $type,
                $dt
            ) = DateTime::parseData($val, $this);
            $dts[] = $dt;
            $this->dateType = $type;
        }
        $this->dateTimes = $dts;
        return $this->dateTimes;

    }

    /**
     * Returns the type of Date format.
     *
     * This method returns one of the format constants. If no date was set,
     * this method will return null.
     *
     * @return int|null
     */
    public function getDateType() {

        if ($this->dateType)
            return $this->dateType;

        if (!$this->value) {
            $this->dateTimes = null;
            $this->dateType = null;
            return null;
        }

        $dts = array();
        foreach(explode(',',$this->value) as $val) {
            list(
                $type,
                $dt
            ) = DateTime::parseData($val, $this);
            $dts[] = $dt;
            $this->dateType = $type;
        }
        $this->dateTimes = $dts;
        return $this->dateType;

    }

    /**
     * This method will return true, if the property had a date and a time, as
     * opposed to only a date.
     *
     * @return bool
     */
    public function hasTime() {

        return $this->getDateType()!==DateTime::DATE;

    }

}
