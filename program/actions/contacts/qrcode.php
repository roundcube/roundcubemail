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
                'Content-Type: image/png',
                'Content-Length: ' . strlen($data)
            ];

            $rcmail->output->sendExit($data, $headers);
        }

        $rcmail->output->sendExit('', ['HTTP/1.0 404 Contact not found']);
    }

    public static function contact_qrcode($contact)
    {
        if (empty($contact)) {
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

        $renderer = new BaconQrCode\Renderer\ImageRenderer(
            new BaconQrCode\Renderer\RendererStyle\RendererStyle(300, 1),
            new BaconQrCode\Renderer\Image\ImagickImageBackEnd()
        );
        $writer = new BaconQrCode\Writer($renderer);
        return $writer->writeString($data);
    }
}
