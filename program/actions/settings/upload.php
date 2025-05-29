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
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();
        $from   = rcube_utils::get_input_string('_from', rcube_utils::INPUT_GET);
        $type   = preg_replace('/(add|edit)-/', '', $from);

        // Validate URL input.
        if (!rcube_utils::is_simple_string($type)) {
            rcmail::write_log('errors', 'The URL parameter "_from" contains disallowed characters and the request is thus rejected.');
            $rcmail->output->command('display_message', 'Invalid input', 'error');
            $rcmail->output->send('iframe');
        }

        // Plugins in Settings may use this file for some uploads (#5694)
        // Make sure it does not contain a dot, which is a special character
        // when using rcube_session::append() below
        $type = str_replace('.', '-', $type);

        // Supported image format types
        $IMAGE_TYPES = explode(',', 'jpeg,jpg,jp2,tiff,tif,bmp,eps,gif,png,png8,png24,png32,svg,ico');

        // clear all stored output properties (like scripts and env vars)
        $rcmail->output->reset();

        $max_size = $rcmail->config->get($type . '_image_size', 64) * 1024;
        $uploadid = rcube_utils::get_input_string('_uploadid', rcube_utils::INPUT_GET);

        if (!empty($_FILES['_file']['tmp_name']) && is_array($_FILES['_file']['tmp_name'])) {
            $multiple = count($_FILES['_file']['tmp_name']) > 1;

            foreach ($_FILES['_file']['tmp_name'] as $i => $filepath) {
                $err        = $_FILES['_file']['error'][$i];
                $imageprop  = null;
                $attachment = null;

                // Process uploaded attachment if there is no error
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
                if (!empty($imageprop)) {
                    $attachment = $rcmail->plugins->exec_hook('attachment_upload', [
                        'path'     => $filepath,
                        'size'     => $_FILES['_file']['size'][$i],
                        'name'     => $_FILES['_file']['name'][$i],
                        'mimetype' => 'image/' . $imageprop['type'],
                        'group'    => $type,
                    ]);
                }

                if (!$err && !empty($attachment['status']) && empty($attachment['abort'])) {
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
                    $error_label = null;
                    if ($err == 'type_error') {
                        $error_label = 'invalidimageformat';
                    }
                    else if ($err == 'size_error') {
                        $error_label = ['name' => 'filesizeerror', 'vars' => ['size' => self::show_bytes($max_size)]];
                    }

                    self::upload_error($err, $attachment, $error_label);
                }
            }
        }
        else if (self::upload_failure()) {
            $rcmail->output->command('remove_from_attachment_list', $uploadid);
        }

        $rcmail->output->send('iframe');
    }
}
