<?php

/*
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
 |   This class provides API for access to uploaded files information.   |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * A trait providing access to metadata of files uploaded in a session.
 */
trait rcube_uploads
{
    /**
     * Get an uploaded file information.
     *
     * @param int $id Upload ID
     *
     * @return array|null Hash array with file upload metadata, NULL if not found
     */
    public function get_uploaded_file($id)
    {
        if (!($session_id = $this->get_session_id())) {
            return null;
        }

        $sql_result = $this->db->query(
            'SELECT * FROM ' . $this->db->table_name('uploads', true)
                . ' WHERE `session_id` = ? AND `upload_id` = ?',
            $session_id, $id
        );

        if ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            $sql_arr['id'] = $sql_arr['upload_id'];
            $sql_arr += json_decode($sql_arr['metadata'], true);
            unset($sql_arr['upload_id'], $sql_arr['session_id'], $sql_arr['metadata']);

            return $sql_arr;
        }

        return null;
    }

    /**
     * Return a list of all uploaded files (in the current session).
     *
     * @param string $group The upload context group
     *
     * @return array List of uploaded files
     */
    public function list_uploaded_files($group)
    {
        if (!($session_id = $this->get_session_id())) {
            return [];
        }

        $sql_result = $this->db->query(
            'SELECT * FROM ' . $this->db->table_name('uploads', true)
                . ' WHERE `session_id` = ? AND `group` = ?'
                . ' ORDER BY `created`',
            $session_id, $group
        );

        $result = [];

        while ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            $sql_arr['id'] = $sql_arr['upload_id'];
            $sql_arr += json_decode($sql_arr['metadata'], true);
            unset($sql_arr['upload_id'], $sql_arr['session_id'], $sql_arr['metadata']);
            $result[] = $sql_arr;
        }

        return $result;
    }

    /**
     * Update a specific upload record.
     *
     * @param int   $id   Upload ID
     * @param array $data Hash array with col->value pairs to update
     *
     * @return bool True if saved successfully, false if nothing changed
     */
    public function update_uploaded_file($id, $data)
    {
        if (!($file = $this->get_uploaded_file($id))) {
            return false;
        }

        $metadata = $this->prepare_upload_metadata(array_merge($file, $data));

        $sql = 'UPDATE ' . $this->db->table_name('uploads', true)
            . ' SET `metadata` = ?'
            . ' WHERE `upload_id` = ? AND `session_id` = ?';

        $update = $this->db->query($sql, $metadata, $id, $this->get_session_id());

        return $this->db->affected_rows($update) > 0;
    }

    /**
     * Create a new uploaded file record in the session.
     *
     * @param array  $data Hash array with col->value pairs to save
     *                     It must include: id, group, name, data (or path), size.
     *                     It can include: mimetype, charset, content_id or any other properties.
     *                     It will be modified by the attachment's handling plugin(s).
     * @param string $hook Optional plugin API hook name (attachment_upload or attachment_save)
     *
     * @return bool True on success or False on error
     */
    public function insert_uploaded_file(&$data, $hook = null)
    {
        if (!($session_id = $this->get_session_id())) {
            return false;
        }

        $data = $this->plugins->exec_hook($hook ?: 'attachment_upload', $data);

        if (empty($data['status']) || !empty($data['abort'])) {
            return false;
        }

        $metadata = $this->prepare_upload_metadata($data);

        $sql = 'INSERT INTO ' . $this->db->table_name('uploads', true)
            . ' (`created`, `session_id`, `upload_id`, `group`, `metadata`)'
            . ' VALUES (' . $this->db->now() . ', ?, ?, ?, ?)';

        $insert = $this->db->query($sql, $session_id, $data['id'], $data['group'] ?? null, $metadata);

        return $this->db->affected_rows($insert) > 0;
    }

    /**
     * Delete an upload record
     *
     * @param int $id Upload ID
     *
     * @return bool True if deleted successfully, False otherwise
     */
    public function delete_uploaded_file($id)
    {
        if (!($session_id = $this->get_session_id())) {
            return false;
        }

        if ($attachment = $this->get_uploaded_file($id)) {
            $attachment = $this->plugins->exec_hook('attachment_delete', $attachment);
        }

        if (empty($attachment['status'])) {
            return false;
        }

        $this->db->query(
            'DELETE FROM ' . $this->db->table_name('uploads', true)
                . ' WHERE `session_id` = ? AND `upload_id` = ?',
            $session_id,
            $id
        );

        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete all upload records (in the current session) by group name.
     *
     * @param string $group Group name
     *
     * @return bool True if deleted successfully, False otherwise
     */
    public function delete_uploaded_files($group)
    {
        if (!($session_id = $this->get_session_id())) {
            return false;
        }

        $this->plugins->exec_hook('attachments_cleanup', ['group' => $group]);

        $this->db->query(
            'DELETE FROM ' . $this->db->table_name('uploads', true)
                . ' WHERE `session_id` = ? AND `group` = ?',
            $session_id,
            $group
        );

        return $this->db->affected_rows() > 0;
    }

    /**
     * Outputs uploaded file content (with image thumbnails support)
     *
     * @param array $file      Uploaded file data
     * @param bool  $thumbnail Generate and return a thumbnail image
     */
    public function display_uploaded_file($file, $thumbnail = false)
    {
        $file = $this->plugins->exec_hook('attachment_display', $file);

        if (!empty($file['status'])) {
            if (empty($file['size'])) {
                $file['size'] = !empty($file['data']) ? strlen($file['data']) : @filesize($file['path']);
            }

            // generate image thumbnail for file browser in HTML editor
            if ($thumbnail) {
                $thumbnail_size = 80;
                $mimetype = $file['mimetype'];
                $file_ident = $file['id'] . ':' . $file['mimetype'] . ':' . $file['size'];
                $thumb_name = 'thumb' . md5($file_ident . ':' . $this->user->ID . ':' . $thumbnail_size);
                $cache_file = rcube_utils::temp_filename($thumb_name, false, false);

                // render thumbnail image if not done yet
                if (!is_file($cache_file)) {
                    if (empty($file['path'])) {
                        $orig_name = $filename = $cache_file . '.tmp';
                        file_put_contents($orig_name, $file['data']);
                    } else {
                        $filename = $file['path'];
                    }

                    $image = new rcube_image($filename);
                    if ($imgtype = $image->resize($thumbnail_size, $cache_file, true)) {
                        $mimetype = 'image/' . $imgtype;

                        if (!empty($orig_name)) {
                            unlink($orig_name);
                        }
                    }
                }

                if (is_file($cache_file)) {
                    // cache for 1h
                    $this->output->future_expire_header(3600);
                    header('Content-Type: ' . $mimetype);
                    header('Content-Length: ' . filesize($cache_file));

                    readfile($cache_file);
                    exit;
                }
            }

            header('Content-Type: ' . $file['mimetype']);
            header('Content-Length: ' . $file['size']);

            if (isset($file['data']) && is_string($file['data'])) {
                echo $file['data'];
            } elseif (!empty($file['path'])) {
                readfile($file['path']);
            }
        }
    }

    /**
     * Prepare upload metadata to store in DB
     */
    protected function prepare_upload_metadata($data)
    {
        // Remove unwanted properties
        $data = array_diff_key($data, array_fill_keys(['id', 'group', 'status', 'abort', 'error', 'data', 'created'], 1));

        // Remove null values
        $data = array_filter($data, static function ($v) {
            return $v !== null;
        });

        // Convert to string
        $data = json_encode($data, \JSON_INVALID_UTF8_IGNORE);

        return $data;
    }
}
