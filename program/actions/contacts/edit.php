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
 |   Show edit form for a contact entry                                  |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_edit extends rcmail_action_contacts_index
{
    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        if ($rcmail->action == 'edit') {
            // Get contact ID and source ID from request
            $cids   = self::get_cids();
            $source = key($cids);
            $cid    = array_first($cids[$source]);

            // Initialize addressbook
            $CONTACTS = self::contact_source($source, true);

            // Contact edit
            if ($cid && (self::$contact = $CONTACTS->get_record($cid, true))) {
                $rcmail->output->set_env('cid', self::$contact['ID']);
            }

            // editing not allowed here
            if ($CONTACTS->readonly || !empty(self::$contact['readonly'])) {
                $rcmail->output->show_message('sourceisreadonly');
                $rcmail->overwrite_action('show');
                return;
            }

            if (empty(self::$contact)) {
                $rcmail->output->show_message('contactnotfound', 'error');
            }
        }
        else {
            $source = rcube_utils::get_input_string('_source', rcube_utils::INPUT_GPC);

            if (strlen($source)) {
                $CONTACTS = $rcmail->get_address_book($source, true);
            }

            if (empty($CONTACTS) || $CONTACTS->readonly) {
                $CONTACTS = $rcmail->get_address_book(rcube_addressbook::TYPE_DEFAULT, true);
                $source   = $rcmail->get_address_book_id($CONTACTS);
            }

            // Initialize addressbook
            $CONTACTS = self::contact_source($source, true);
        }

        self::$SOURCE_ID = $source;
        self::$CONTACTS  = $CONTACTS;
        self::set_sourcename($CONTACTS);

        // check if we have a valid result
        if (!empty($args['contact'])) {
            self::$contact = $args['contact'];
        }

        $rcmail->output->add_handlers([
                'contactedithead' => [$this, 'contact_edithead'],
                'contacteditform' => [$this, 'contact_editform'],
                'contactphoto'    => [$this, 'contact_photo'],
                'photouploadform' => [$this, 'upload_photo_form'],
                'sourceselector'  => [$this, 'source_selector'],
                'filedroparea'    => [$this, 'photo_drop_area'],
        ]);

        $rcmail->output->set_pagetitle($rcmail->gettext(($rcmail->action == 'add' ? 'addcontact' : 'editcontact')));

        if ($rcmail->action == 'add' && $rcmail->output->template_exists('contactadd')) {
            $rcmail->output->send('contactadd');
        }

        // this will be executed if no template for addcontact exists
        $rcmail->output->send('contactedit');
    }

    public static function contact_edithead($attrib)
    {
        $rcmail = rcmail::get_instance();
        $business_mode = $rcmail->config->get('contact_form_mode') === 'business';

        // check if we have a valid result
        $i_size = !empty($attrib['size']) ? $attrib['size'] : 20;

        $form = [
            'head' => [
                'name' => $rcmail->gettext('contactnameandorg'),
                'content' => [
                    'source'        => ['id' => '_source', 'label' => $rcmail->gettext('addressbook')],
                    'prefix'        => ['size' => $i_size],
                    'firstname'     => ['size' => $i_size, 'visible' => true],
                    'middlename'    => ['size' => $i_size],
                    'surname'       => ['size' => $i_size, 'visible' => true],
                    'suffix'        => ['size' => $i_size],
                    'name'          => ['size' => $i_size * 2],
                    'nickname'      => ['size' => $i_size * 2],
                    'organization'  => ['size' => $i_size * 2, 'visible' => $business_mode],
                    'department'    => ['size' => $i_size * 2, 'visible' => $business_mode],
                    'jobtitle'      => ['size' => $i_size * 2, 'visible' => $business_mode],
                ]
            ]
        ];

        list($form_start, $form_end) = self::get_form_tags($attrib);
        unset($attrib['form'], $attrib['name'], $attrib['size']);

        // return the address edit form
        $out = self::contact_form($form, self::$contact, $attrib);

        return $form_start . $out . $form_end;
    }

    public static function contact_editform($attrib)
    {
        $rcmail   = rcmail::get_instance();
        $addr_tpl = $rcmail->config->get('address_template', '');

        // copy (parsed) address template to client
        if (preg_match_all('/\{([a-z0-9]+)\}([^{]*)/i', $addr_tpl, $templ, PREG_SET_ORDER)) {
            $rcmail->output->set_env('address_template', $templ);
        }

        $i_size       = !empty($attrib['size']) ? $attrib['size'] : 40;
        $t_rows       = !empty($attrib['textarearows']) ? $attrib['textarearows'] : 10;
        $t_cols       = !empty($attrib['textareacols']) ? $attrib['textareacols'] : 40;
        $short_labels = self::get_bool_attr($attrib, 'short-legend-labels');

        $form = [
            'contact' => [
                'name'    => $rcmail->gettext('properties'),
                'content' => [
                    'email'   => ['size' => $i_size, 'maxlength' => 254, 'visible' => true],
                    'phone'   => ['size' => $i_size, 'visible' => true],
                    'address' => ['visible' => true],
                    'website' => ['size' => $i_size],
                    'im'      => ['size' => $i_size],
                ],
            ],
            'personal' => [
                'name'    => $rcmail->gettext($short_labels ? 'personal' : 'personalinfo'),
                'content' => [
                    'gender'      => ['visible' => true],
                    'maidenname'  => ['size' => $i_size],
                    'birthday'    => ['visible' => true],
                    'anniversary' => [],
                    'manager'     => ['size' => $i_size],
                    'assistant'   => ['size' => $i_size],
                    'spouse'      => ['size' => $i_size],
                ],
            ],
        ];

        if (isset(self::$CONTACT_COLTYPES['notes'])) {
            $form['notes'] = [
                'name'    => $rcmail->gettext('notes'),
                'single'  => true,
                'content' => [
                    'notes' => ['size' => $t_cols, 'rows' => $t_rows, 'label' => false, 'visible' => true, 'limit' => 1],
                ],
            ];
        }

        list($form_start, $form_end) = self::get_form_tags($attrib);
        unset($attrib['form']);

        // return the complete address edit form as table
        $out = self::contact_form($form, self::$contact, $attrib);

        return $form_start . $out . $form_end;
    }

    public static function upload_photo_form($attrib)
    {
        $rcmail = rcmail::get_instance();
        $hidden = new html_hiddenfield(['name' => '_cid', 'value' => $rcmail->output->get_env('cid')]);

        $attrib['prefix'] = $hidden->show();
        $input_attr       = ['name' => '_photo', 'accept' => 'image/*'];

        $rcmail->output->add_label('addphoto','replacephoto');

        return self::upload_form($attrib, 'uploadform', 'upload-photo', $input_attr);
    }

    /**
     * similar function as in /steps/settings/edit_identity.inc
     * @todo: Use rcmail_action::get_form_tags()
     */
    public static function get_form_tags($attrib, $action = null, $id = null, $hidden = null)
    {
        static $edit_form;

        $rcmail = rcmail::get_instance();
        $form_start = $form_end = '';

        if (empty($edit_form)) {
            $hiddenfields = new html_hiddenfield();

            if ($rcmail->action == 'edit') {
                $hiddenfields->add(['name' => '_source', 'value' => self::$SOURCE_ID]);
            }

            $hiddenfields->add(['name' => '_gid', 'value' => self::$CONTACTS->group_id]);
            $hiddenfields->add(['name' => '_search', 'value' => rcube_utils::get_input_string('_search', rcube_utils::INPUT_GPC)]);

            if ($cid = $rcmail->output->get_env('cid')) {
                $hiddenfields->add(['name' => '_cid', 'value' => $cid]);
            }

            $form_attrib = [
                'name'    => 'form',
                'method'  => 'post',
                'task'    => $rcmail->task,
                'action'  => 'save',
                'request' => 'save.' . intval($cid),
                'noclose' => true,
            ];

            $form_start = $rcmail->output->request_form($form_attrib + $attrib, $hiddenfields->show());
            $form_end   = empty($attrib['form']) ? '</form>' : '';
            $edit_form  = !empty($attrib['form']) ? $attrib['form'] : 'form';

            $rcmail->output->add_gui_object('editform', $edit_form);
        }

        return [$form_start, $form_end];
    }

    /**
     * Register container as active area to drop photos onto
     */
    public static function photo_drop_area($attrib)
    {
        $rcmail = rcmail::get_instance();

        if (!empty($attrib['id'])) {
            $rcmail->output->add_gui_object('filedrop', $attrib['id']);
            $rcmail->output->set_env('filedrop', [
                    'action'    => 'upload-photo',
                    'fieldname' => '_photo',
                    'single'    => 1,
                    'filter'    => '^image/.+'
            ]);
        }
    }
}
