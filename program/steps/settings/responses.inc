<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/responses.inc                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2013, The Roundcube Dev Team                            |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Manage and save canned response texts                               |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/


if (!empty($_POST['_insert'])) {
    $name = get_input_value('_name', RCUBE_INPUT_POST);
    $text = trim(get_input_value('_text', RCUBE_INPUT_POST));

    if (!empty($name) && !empty($text)) {
        $dupes = 0;
        $responses = $RCMAIL->config->get('compose_responses', array());
        foreach ($responses as $resp) {
            if (strcasecmp($name, preg_replace('/\s\(\d+\)$/', '', $resp['name'])) == 0)
                $dupes++;
        }
        if ($dupes) {  // require a unique name
            $name .= ' (' . ++$dupes . ')';
        }

        $response = array('name' => $name, 'text' => $text, 'format' => 'text', 'key' => substr(md5($name), 0, 16));
        $responses[] = $response;

        if ($RCMAIL->user->save_prefs(array('compose_responses' => $responses))) {
            $RCMAIL->output->command('add_response_item', $response);
            $RCMAIL->output->command('display_message', rcube_label('successfullysaved'), 'confirmation');
        }
        else {
            $RCMAIL->output->command('display_message', rcube_label('errorsaving'), 'error');
        }
    }
}

// send response
$RCMAIL->output->send();

