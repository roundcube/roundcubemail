<?php

namespace Sabre\VObject\Property;

use
    Sabre\VObject\Property;

/**
 * Boolean property.
 *
 * This object represents BOOLEAN values. These are always the case-insenstive
 * string TRUE or FALSE.
 *
 * Automatic conversion to PHP's true and false are done.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Boolean extends Property {

    /**
     * Sets a raw value coming from a mimedir (iCalendar/vCard) file.
     *
     * This has been 'unfolded', so only 1 line will be passed. Unescaping is
     * not yet done, but parameters are not included.
     *
     * @param string $val
     *
     * @return void
     */
    function setRawMimeDirValue($val) {

        $val = strtoupper($val) === 'TRUE' ? true : false;
        $this->setValue($val);

    }

    /**
     * Returns a raw mime-dir representation of the value.
     *
     * @return string
     */
    function getRawMimeDirValue() {

        return $this->value ? 'TRUE' : 'FALSE';

    }

    /**
     * Returns the type of value.
     *
     * This corresponds to the VALUE= parameter. Every property also has a
     * 'default' valueType.
     *
     * @return string
     */
    function getValueType() {

        return 'BOOLEAN';

    }

    /**
     * Hydrate data from a XML subtree, as it would appear in a xCard or xCal
     * object.
     *
     * @param array $value
     *
     * @return void
     */
    function setXmlValue(array $value) {

        $value = array_map(
            function($value) {
                return 'true' === $value;
            },
            $value
        );
        parent::setXmlValue($value);

    }

}
