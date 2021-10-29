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
 |   Show contact details                                                |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_show extends rcmail_action_contacts_index
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

        // read contact record (or get the one defined in 'save' action)
        if (!empty($args['contact'])) {
            self::$contact = $args['contact'];
        }
        else if ($cid) {
            self::$contact = self::$CONTACTS->get_record($cid, true);
        }

        if ($cid && self::$contact) {
            $rcmail->output->set_env('readonly', self::$CONTACTS->readonly || !empty(self::$contact['readonly']));
            $rcmail->output->set_env('cid', self::$contact['ID']);

            // remember current search request ID (if in search mode)
            if ($search = rcube_utils::get_input_string('_search', rcube_utils::INPUT_GET)) {
                $rcmail->output->set_env('search_request', $search);
            }
        }

        // get address book name (for display)
        self::set_sourcename(self::$CONTACTS);

        $rcmail->output->add_handlers([
                'contacthead'    => [$this, 'contact_head'],
                'contactdetails' => [$this, 'contact_details'],
                'contactphoto'   => [$this, 'contact_photo'],
        ]);

        $rcmail->output->send('contact');
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
                    'source'       => ['type' => 'text'],
                    'prefix'       => ['type' => 'text'],
                    'firstname'    => ['type' => 'text'],
                    'middlename'   => ['type' => 'text'],
                    'surname'      => ['type' => 'text'],
                    'suffix'       => ['type' => 'text'],
                    'name'         => ['type' => 'text'],
                    'nickname'     => ['type' => 'text'],
                    'organization' => ['type' => 'text'],
                    'department'   => ['type' => 'text'],
                    'jobtitle'     => ['type' => 'text'],
                ],
            ],
        ];

        unset($attrib['name']);

        return self::contact_form($form, self::$contact, $attrib);
    }

    public static function contact_details($attrib)
    {
        $rcmail = rcmail::get_instance();

        // check if we have a valid result
        if (!self::$contact) {
            return false;
        }

        $i_size       = !empty($attrib['size']) ? $attrib['size'] : 40;
        $short_labels = self::get_bool_attr($attrib, 'short-legend-labels');

        $form = [
            'contact' => [
                'name'    => $rcmail->gettext('properties'),
                'content' => [
                    'email'   => ['size' => $i_size, 'render_func' => 'rcmail_action_contacts_show::render_email_value'],
                    'phone'   => ['size' => $i_size, 'render_func' => 'rcmail_action_contacts_show::render_phone_value'],
                    'address' => [],
                    'website' => ['size' => $i_size, 'render_func' => 'rcmail_action_contacts_show::render_url_value'],
                    'im'      => ['size' => $i_size],
                ],
            ],
            'personal' => [
                'name'    => $rcmail->gettext($short_labels ? 'personal' : 'personalinfo'),
                'content' => [
                    'gender'      => ['size' => $i_size],
                    'maidenname'  => ['size' => $i_size],
                    'birthday'    => ['size' => $i_size],
                    'anniversary' => ['size' => $i_size],
                    'manager'     => ['size' => $i_size],
                    'assistant'   => ['size' => $i_size],
                    'spouse'      => ['size' => $i_size],
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
            $form['groups'] = [
                'name'    => $rcmail->gettext('groups'),
                'content' => self::contact_record_groups(self::$contact['ID']),
            ];
        }

        return self::contact_form($form, self::$contact, $attrib);
    }

    public static function render_email_value($email)
    {
        $rcmail = rcmail::get_instance();

        return html::a([
                'href'    => 'mailto:' . $email,
                'onclick' => sprintf(
                    "return %s.command('compose','%s',this)",
                    rcmail_output::JS_OBJECT_NAME,
                    rcube::JQ($email)
                ),
                'title'   => $rcmail->gettext('composeto'),
                'class'   => 'email',
            ],
            rcube::Q($email)
        );
    }

    public static function render_phone_value($phone)
    {
        $attrs = [
            'href'  => 'tel:' . preg_replace('/[^0-9+,;-]/', '', $phone),
            'class' => 'phone',
        ];

        return html::a($attrs, rcube::Q($phone));
    }

    public static function render_url_value($url)
    {
        $prefix = preg_match('!^(http|ftp)s?://!', $url) ? '' : 'http://';

        return html::a([
                'href'   => $prefix . $url,
                'target' => '_blank',
                'class'  => 'url',
            ],
            rcube::Q($url)
        );
    }

    public static function contact_record_groups($contact_id)
    {
        $groups = self::$CONTACTS->list_groups();

        if (empty($groups)) {
            return '';
        }

        $rcmail   = rcmail::get_instance();
        $source   = rcube_utils::get_input_string('_source', rcube_utils::INPUT_GPC);
        $members  = self::$CONTACTS->get_record_groups($contact_id);
        $table    = new html_table(['tagname' => 'ul', 'cols' => 1, 'class' => 'proplist simplelist']);
        $checkbox = new html_checkbox(['name' => '_gid[]', 'class' => 'groupmember', 'disabled' => self::$CONTACTS->readonly]);

        foreach ($groups as $group) {
            $gid   = $group['ID'];
            $input = $checkbox->show(!empty($members[$gid]) ? $gid : null, ['value' => $gid]);
            $table->add(null, html::label(null, $input . rcube::Q($group['name'])));
        }

        $hiddenfields = new html_hiddenfield(['name' => '_source', 'value' => $source]);
        $hiddenfields->add(['name' => '_cid', 'value' => $contact_id]);

        $form_attrs = [
            'name'    => 'form',
            'method'  => 'post',
            'task'    => $rcmail->task,
            'action'  => 'save',
            'request' => 'save.' . intval($contact_id),
            'noclose' => true,
        ];

        $form_start = $rcmail->output->request_form($form_attrs, $hiddenfields->show());
        $form_end   = '</form>';

        $rcmail->output->add_gui_object('editform', 'form');
        $rcmail->output->add_label('addingmember', 'removingmember');

        return $form_start . html::tag('fieldset', 'contactfieldgroup contactgroups', $table->show()) . $form_end;
    }
}
