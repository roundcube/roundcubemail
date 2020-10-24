<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Show contact photo                                                  |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_photo extends rcmail_action_contacts_index
{
    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        // Get contact ID and source ID from request
        $cids    = self::get_cids();
        $source  = key($cids);
        $cid     = $cids ? array_shift($cids[$source]) : null;

        $file_id = rcube_utils::get_input_value('_photo', rcube_utils::INPUT_GPC);

        // read the referenced file
        if ($file_id && !empty($_SESSION['contacts']['files'][$file_id])) {
            $tempfile = $_SESSION['contacts']['files'][$file_id];
            $tempfile = $rcmail->plugins->exec_hook('attachment_display', $tempfile);

            if (!empty($tempfile['status'])) {
                if (!empty($tempfile['data'])) {
                    $data = $tempfile['data'];
                }
                else if ($tempfile['path']) {
                    $data = file_get_contents($tempfile['path']);
                }
            }
        }
        else {
            // by email, search for contact first
            if ($email = rcube_utils::get_input_value('_email', rcube_utils::INPUT_GPC)) {
                foreach ($rcmail->get_address_sources() as $s) {
                    $abook = $rcmail->get_address_book($s['id']);
                    $result = $abook->search(array('email'), $email, 1, true, true, 'photo');
                    while ($result && ($record = $result->iterate())) {
                        if ($record['photo']) {
                            break 2;
                        }
                    }
                }
            }

            // by contact id
            if (empty($record) && $cid) {
                // Initialize addressbook source
                $CONTACTS  = self::contact_source($source, true);
                $SOURCE_ID = $source;
                // read contact record
                $record = $CONTACTS->get_record($cid, true);
            }

            if (!empty($record['photo'])) {
                $data = is_array($record['photo']) ? $record['photo'][0] : $record['photo'];
                if (!preg_match('![^a-z0-9/=+-]!i', $data)) {
                    $data = base64_decode($data, true);
                }
            }
        }

        // let plugins do fancy things with contact photos
        $plugin = $rcmail->plugins->exec_hook('contact_photo',
            ['record' => $record, 'email' => $email, 'data' => $data]);

        // redirect to url provided by a plugin
        if (!empty($plugin['url'])) {
            $rcmail->output->redirect($plugin['url']);
        }

        $data = $plugin['data'];

        // detect if photo data is a URL
        if (strlen($data) < 1024 && filter_var($data, FILTER_VALIDATE_URL)) {
            $rcmail->output->redirect($data);
        }

        // cache for one day if requested by email
        if (!$cid && $email) {
            $rcmail->output->future_expire_header(86400);
        }

        if ($data) {
            header('Content-Type: ' . rcube_mime::image_content_type($data));
            echo $data;
        }
        else if (!empty($_GET['_error'])) {
            header('HTTP/1.0 404 Photo not found');
        }
        else {
            header('Content-Type: image/gif');
            echo base64_decode(rcmail_output::BLANK_GIF);
        }

        exit;
    }
}
