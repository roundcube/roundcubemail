<?php

namespace OldSabre\VObject\Splitter;

use OldSabre\VObject;

/**
 * Splitter
 *
 * This class is responsible for splitting up VCard objects.
 *
 * It is assumed that the input stream contains 1 or more VCARD objects. This
 * class checks for BEGIN:VCARD and END:VCARD and parses each encountered
 * component individually.
 *
 * @copyright Copyright (C) 2011-2015 fruux GmbH (https://fruux.com/).
 * @author Dominik Tobschall
 * @author Armin Hackmann
 * @license http://sabre.io/license/ Modified BSD License
 */
class VCard implements SplitterInterface {

    /**
     * File handle
     *
     * @var resource
     */
    protected $input;

    /**
     * Constructor
     *
     * The splitter should receive an readable file stream as it's input.
     *
     * @param resource $input
     */
    public function __construct($input) {

        $this->input = $input;

    }

    /**
     * Every time getNext() is called, a new object will be parsed, until we
     * hit the end of the stream.
     *
     * When the end is reached, null will be returned.
     *
     * @return OldSabre\VObject\Component|null
     */
    public function getNext() {

        $vcard = '';

        do {

            if (feof($this->input)) {
                return false;
            }

            $line = fgets($this->input);
            $vcard .= $line;

        } while(strtoupper(substr($line,0,4))!=="END:");

        $object = VObject\Reader::read($vcard);

        if($object->name !== 'VCARD') {
            throw new \InvalidArgumentException("Thats no vCard!", 1);
        }

        return $object;

    }

}
