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
 |   Show contact data as QR code                                        |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_qrcode extends rcmail_action_contacts_index
{
    protected static $mode = self::MODE_HTTP;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        // Get contact ID and source ID from request
        $cids   = self::get_cids();
        $source = key($cids);
        $cid    = $cids ? array_first($cids[$source]) : null;
        $rcmail = rcmail::get_instance();

        // read contact record
        $abook   = self::contact_source($source, true);
        $contact = $abook->get_record($cid, true);

        // generate QR code image
        if ($data = self::contact_qrcode($contact)) {
            $headers = [
                'Content-Type: ' . self::check_support(),
                'Content-Length: ' . strlen($data)
            ];

            $rcmail->output->sendExit($data, $headers);
        }

        $rcmail->output->sendExit('', ['HTTP/1.0 404 Contact not found']);
    }

    /**
     * Generate a QR-code image for a contact
     *
     * @param array $contact Contact record
     *
     * @return string|null Image content, Null on error or missing PHP extensions
     */
    public static function contact_qrcode($contact)
    {
        if (empty($contact)) {
            return null;
        }

        $type = self::check_support();

        if (empty($type)) {
            return null;
        }

        $vcard = new rcube_vcard();

        // QR code input is limited, use only common fields
        $fields = ['name', 'firstname', 'surname', 'middlename', 'nickname',
            'organization', 'phone', 'email', 'jobtitle', 'prefix', 'suffix'];

        foreach ($contact as $field => $value) {
            if (strpos($field, ':') !== false) {
                list($field, $section) = explode(':', $field, 2);
            }
            else {
                $section = null;
            }

            if (in_array($field, $fields)) {
                foreach ((array) $value as $v) {
                    $vcard->set($field, $v, $section);
                }
            }
        }

        $data = $vcard->export();

        if (empty($data)) {
            return null;
        }

        $renderer_style = new BaconQrCode\Renderer\RendererStyle\RendererStyle(300, 1);
        $renderer_image = $type == 'image/png'
            ? new BaconQrCode\Renderer\Image\ImagickImageBackEnd()
            : new BaconQrCode\Renderer\Image\SvgImageBackEnd();

        $renderer = new BaconQrCode\Renderer\ImageRenderer($renderer_style, $renderer_image);
        $writer   = new BaconQrCode\Writer($renderer);

        return $writer->writeString($data);
    }

    /**
     * Check required extensions and classes for QR code generation
     *
     * @return string|null Content-type of the image result
     */
    public static function check_support()
    {
        if (extension_loaded('iconv') && class_exists('BaconQrCode\Renderer\ImageRenderer')) {
            if (extension_loaded('xmlwriter')) {
                return 'image/svg+xml';
            }

            if (extension_loaded('imagick')) {
                return 'image/png';
            }
        }
    }
}
