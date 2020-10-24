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
 |   Handles image uploads                                               |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_upload extends rcmail_action
{
    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();
        $from   = rcube_utils::get_input_value('_from', rcube_utils::INPUT_GET);
        $type   = preg_replace('/(add|edit)-/', '', $from);

        // Plugins in Settings may use this file for some uploads (#5694)
        // Make sure it does not contain a dot, which is a special character
        // when using rcube_session::append() below
        $type = str_replace('.', '-', $type);

        // Supported image format types
        $IMAGE_TYPES = explode(',', 'jpeg,jpg,jp2,tiff,tif,bmp,eps,gif,png,png8,png24,png32,svg,ico');

        // clear all stored output properties (like scripts and env vars)
        $rcmail->output->reset();

        $max_size  = $rcmail->config->get($type . '_image_size', 64) * 1024;
        $post_size = self::show_bytes(rcube_utils::max_upload_size());
        $uploadid  = rcube_utils::get_input_value('_uploadid', rcube_utils::INPUT_GET);

        if (is_array($_FILES['_file']['tmp_name'])) {
            $multiple = count($_FILES['_file']['tmp_name']) > 1;

            foreach ($_FILES['_file']['tmp_name'] as $i => $filepath) {
                // Process uploaded attachment if there is no error
                $err = $_FILES['_file']['error'][$i];

                if (!$err) {
                    if ($max_size < $_FILES['_file']['size'][$i]) {
                        $err = 'size_error';
                    }
                    // check image file type
                    else {
                        $image     = new rcube_image($filepath);
                        $imageprop = $image->props();

                        if (!in_array(strtolower($imageprop['type']), $IMAGE_TYPES)) {
                            $err = 'type_error';
                        }
                    }
                }

                // save uploaded image in storage backend
                if (!$err) {
                    $attachment = $rcmail->plugins->exec_hook('attachment_upload', [
                        'path'     => $filepath,
                        'size'     => $_FILES['_file']['size'][$i],
                        'name'     => $_FILES['_file']['name'][$i],
                        'mimetype' => 'image/' . $imageprop['type'],
                        'group'    => $type,
                    ]);
                }

                if (!$err && $attachment['status'] && !$attachment['abort']) {
                    $id = $attachment['id'];

                    // store new file in session
                    unset($attachment['status'], $attachment['abort']);
                    $rcmail->session->append($type . '.files', $id, $attachment);

                    $content = rcube::Q($attachment['name']);

                    $rcmail->output->command('add2attachment_list', "rcmfile$id", [
                            'html'      => $content,
                            'name'      => $attachment['name'],
                            'mimetype'  => $attachment['mimetype'],
                            'classname' => rcube_utils::file2class($attachment['mimetype'], $attachment['name']),
                            'complete'  => true
                        ],
                        $uploadid
                    );
                }
                else {
                    if ($err == 'type_error') {
                        $msg = $rcmail->gettext('invalidimageformat');
                    }
                    else if ($err == 'size_error') {
                        $msg = $rcmail->gettext(['name' => 'filesizeerror', 'vars' => ['size' => $max_size]]);
                    }
                    else if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE) {
                        $msg = $rcmail->gettext(['name' => 'filesizeerror', 'vars' => ['size' => $post_size]]);
                    }
                    else if (!empty($attachment['error'])) {
                        $msg = $attachment['error'];
                    }
                    else {
                        $msg = $rcmail->gettext('fileuploaderror');
                    }

                    $rcmail->output->command('display_message', $msg, 'error');
                }
            }
        }
        else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // if filesize exceeds post_max_size then $_FILES array is empty,
            // show filesizeerror instead of fileuploaderror
            if ($maxsize = ini_get('post_max_size')) {
                $msg = $rcmail->gettext([
                    'name' => 'filesizeerror',
                    'vars' => ['size' => self::show_bytes(parse_bytes($maxsize))]
                ]);
            }
            else {
                $msg = $rcmail->gettext('fileuploaderror');
            }

            $rcmail->output->command('display_message', $msg, 'error');
            $rcmail->output->command('remove_from_attachment_list', $uploadid);
        }

        $rcmail->output->send('iframe');
    }
}
