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
 |   Handles contact photo uploads                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_upload_photo extends rcmail_action_contacts_index
{
    /**
     * Supported image format types
     * ImageMagick works with other non-image types (e.g. pdf), we don't want that here
     *
     * @var array
     */
    public static $IMAGE_TYPES = ['jpeg','jpg','jp2','tiff','tif','bmp','eps','gif','png','png8','png24','png32','svg','ico'];

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        // clear all stored output properties (like scripts and env vars)
        $rcmail->output->reset();

        if (!empty($_FILES['_photo']['tmp_name'])) {
            $filepath = $_FILES['_photo']['tmp_name'];

            // check file type and resize image
            $image     = new rcube_image($_FILES['_photo']['tmp_name']);
            $imageprop = $image->props();

            if (
                in_array(strtolower($imageprop['type']), self::$IMAGE_TYPES)
                && $imageprop['width']
                && $imageprop['height']
            ) {
                $maxsize   = intval($rcmail->config->get('contact_photo_size', 160));
                $tmpfname  = rcube_utils::temp_filename('imgconvert');
                $save_hook = 'attachment_upload';

                // scale image to a maximum size
                if (($imageprop['width'] > $maxsize || $imageprop['height'] > $maxsize) && $image->resize($maxsize, $tmpfname)) {
                    $filepath  = $tmpfname;
                    $save_hook = 'attachment_save';
                }

                // save uploaded file in storage backend
                $attachment = $rcmail->plugins->exec_hook($save_hook, [
                        'path'     => $filepath,
                        'size'     => $_FILES['_photo']['size'],
                        'name'     => $_FILES['_photo']['name'],
                        'mimetype' => 'image/' . $imageprop['type'],
                        'group'    => 'contact',
                ]);
            }
            else {
                $attachment = ['error' => $rcmail->gettext('invalidimageformat')];
            }

            if (!empty($attachment['status']) && empty($attachment['abort'])) {
                $file_id = $attachment['id'];
                $_SESSION['contacts']['files'][$file_id] = $attachment;
                $rcmail->output->command('replace_contact_photo', $file_id);
            }
            else {
                // upload failed
                self::upload_error($_FILES['_photo']['error'], $attachment);
            }
        }
        else {
            self::upload_failure();
        }

        $rcmail->output->command('photo_upload_end');
        $rcmail->output->send('iframe');
    }
}
