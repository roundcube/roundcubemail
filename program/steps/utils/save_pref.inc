<?php
/*

 +-----------------------------------------------------------------------+
 | program/steps/utils/save_pref.inc                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Save preferences setting in database                                |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id$

*/

$name = get_input_value('_name', RCUBE_INPUT_POST);
$value = get_input_value('_value', RCUBE_INPUT_POST);

// save preference value
$RCMAIL->user->save_prefs(array($name => $value));

// update also session if requested
if ($sessname = get_input_value('_session', RCUBE_INPUT_POST)) {
    // Support multidimensional arrays...
    $vars = explode('/', $sessname);

    // ... up to 3 levels
    if (count($vars) == 1)
        $_SESSION[$vars[0]] = $value;
    else if (count($vars) == 2)
        $_SESSION[$vars[0]][$vars[1]] = $value;
    else if (count($vars) == 3)
        $_SESSION[$vars[0]][$vars[1]][$vars[2]] = $value;
}

$OUTPUT->reset();
$OUTPUT->send();


