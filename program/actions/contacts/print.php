<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) Kolab Systems AG                                        |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Print contact details                                               |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_print extends rcmail_action_contacts_index
{
    protected static $mode = self::MODE_HTTP;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        // Get contact ID and source ID from request
        $cids   = self::get_cids();
        $source = key($cids);
        $cid    = $cids ? array_first($cids[$source]) : null;

        // Initialize addressbook source
        self::$CONTACTS  = self::contact_source($source, true);
        self::$SOURCE_ID = $source;

        // read contact record
        if ($cid && self::$CONTACTS) {
            self::$contact = self::$CONTACTS->get_record($cid, true);
        }

        $rcmail->output->add_handlers([
                'contacthead'    => [$this, 'contact_head'],
                'contactdetails' => [$this, 'contact_details'],
                'contactphoto'   => [$this, 'contact_photo'],
        ]);

        $rcmail->output->send('contactprint');
    }

    public static function contact_head($attrib)
    {
        $rcmail = rcmail::get_instance();

        // check if we have a valid result
        if (!self::$contact) {
            $rcmail->output->show_message('contactnotfound', 'error');
            return false;
        }

        $form = [
            'head' => [  // section 'head' is magic!
                'name' => $rcmail->gettext('contactnameandorg'),
                'content' => [
                    'prefix'     => [],
                    'name'       => [],
                    'firstname'  => [],
                    'middlename' => [],
                    'surname'    => [],
                    'suffix'     => [],
                ],
            ],
        ];

        unset($attrib['name']);

        return self::contact_form($form, self::$contact, $attrib);
    }

    public static function contact_details($attrib)
    {
        // check if we have a valid result
        if (!self::$contact) {
            return false;
        }

        $rcmail = rcmail::get_instance();

        $form = [
            'contact' => [
                'name'    => $rcmail->gettext('properties'),
                'content' => [
                    'organization' => [],
                    'department'   => [],
                    'jobtitle'     => [],
                    'email'        => [],
                    'phone'        => [],
                    'address'      => [],
                    'website'      => [],
                    'im'           => [],
                    'groups'       => [],
                ],
            ],
            'personal' => [
                'name'    => $rcmail->gettext('personalinfo'),
                'content' => [
                    'nickname'    => [],
                    'gender'      => [],
                    'maidenname'  => [],
                    'birthday'    => [],
                    'anniversary' => [],
                    'manager'     => [],
                    'assistant'   => [],
                    'spouse'      => [],
                ],
            ],
        ];

        if (isset(rcmail_action_contacts_index::$CONTACT_COLTYPES['notes'])) {
            $form['notes'] = [
                'name'    => $rcmail->gettext('notes'),
                'content' => [
                    'notes' => ['type' => 'textarea', 'label' => false],
                ],
            ];
        }

        if (self::$CONTACTS->groups) {
            $groups = self::$CONTACTS->get_record_groups(self::$contact['ID']);
            if (!empty($groups)) {
                $form['contact']['content']['groups'] = [
                    'value' => rcube::Q(implode(', ', $groups)),
                    'label' => $rcmail->gettext('groups')
                ];
            }
        }

        return self::contact_form($form, self::$contact, $attrib);
    }
}
