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
 |   Show edit form for an identity record                               |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_identity_edit extends rcmail_action
{
    protected static $mode = self::MODE_HTTP;
    protected static $record;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        $IDENTITIES_LEVEL = intval($rcmail->config->get('identities_level', 0));

        // edit-identity
        if ($rcmail->action == 'edit-identity'
            && ($id = rcube_utils::get_input_string('_iid', rcube_utils::INPUT_GPC))
        ) {
            self::$record = $rcmail->user->get_identity($id);

            if (!is_array(self::$record)) {
                $rcmail->output->show_message('dberror', 'error');
                $rcmail->output->send('iframe');
            }

            $rcmail->output->set_env('iid', self::$record['identity_id']);
            $rcmail->output->set_env('mailvelope_main_keyring', $rcmail->config->get('mailvelope_main_keyring'));
            $rcmail->output->set_env('mailvelope_keysize', $rcmail->config->get('mailvelope_keysize'));
        }
        // add-identity
        else {
            if ($IDENTITIES_LEVEL > 1) {
                $rcmail->output->show_message('opnotpermitted', 'error');
                // go to identities page
                $rcmail->overwrite_action('identities');
                return;
            }

            if ($IDENTITIES_LEVEL == 1) {
                self::$record['email'] = $rcmail->get_user_email();
            }
        }

        $rcmail->output->add_handler('identityform', [$this, 'identity_form']);
        $rcmail->output->set_env('identities_level', $IDENTITIES_LEVEL);
        $rcmail->output->add_label('deleteidentityconfirm', 'generate',
            'encryptioncreatekey', 'openmailvelopesettings', 'encryptionprivkeysinmailvelope',
            'encryptionnoprivkeysinmailvelope', 'keypaircreatesuccess');

        $rcmail->output->set_pagetitle($rcmail->gettext(($rcmail->action == 'add-identity' ? 'addidentity' : 'editidentity')));

        if ($rcmail->action == 'add-identity' && $rcmail->output->template_exists('identityadd')) {
            $rcmail->output->send('identityadd');
        }

        $rcmail->output->send('identityedit');
    }

    public static function identity_form($attrib)
    {
        $rcmail = rcmail::get_instance();

        $IDENTITIES_LEVEL = intval($rcmail->config->get('identities_level', 0));

        // Add HTML editor script(s)
        self::html_editor('identity', 'rcmfd_signature');

        // add some labels to client
        $rcmail->output->add_label('noemailwarning', 'converting', 'editorwarning');

        $i_size = !empty($attrib['size']) ? $attrib['size'] : 40;
        $t_rows = !empty($attrib['textarearows']) ? $attrib['textarearows'] : 6;
        $t_cols = !empty($attrib['textareacols']) ? $attrib['textareacols'] : 40;

        // list of available cols
        $form = [
            'addressing' => [
                'name'    => $rcmail->gettext('settings'),
                'content' => [
                    'name'         => ['type' => 'text', 'size' => $i_size],
                    'email'        => ['type' => 'text', 'size' => $i_size],
                    'organization' => ['type' => 'text', 'size' => $i_size],
                    'reply-to'     => ['type' => 'text', 'size' => $i_size],
                    'bcc'          => ['type' => 'text', 'size' => $i_size],
                    'standard'     => ['type' => 'checkbox', 'label' => $rcmail->gettext('setdefault')],
                ]
            ],
            'signature' => [
                'name'    => $rcmail->gettext('signature'),
                'content' => [
                    'signature'      => [
                        'type'       => 'textarea',
                        'size'       => $t_cols,
                        'rows'       => $t_rows,
                        'spellcheck' => true,
                        'data-html-editor' => true
                    ],
                    'html_signature' => [
                        'type' => 'checkbox',
                        'label'   => $rcmail->gettext('htmlsignature'),
                        'onclick' => "return rcmail.command('toggle-editor', {id: 'rcmfd_signature', html: this.checked}, '', event)"
                    ],
                ]
            ],
            'encryption' => [
                'name'    => $rcmail->gettext('identityencryption'),
                'attrs'   => ['class' => 'identity-encryption', 'style' => 'display:none'],
                'content' => html::div('identity-encryption-block', '')
            ]
        ];

        // Enable TinyMCE editor
        if (!empty(self::$record['html_signature'])) {
            $form['signature']['content']['signature']['class']      = 'mce_editor';
            $form['signature']['content']['signature']['is_escaped'] = true;

            // Correctly handle HTML entities in HTML editor (#1488483)
            self::$record['signature'] = htmlspecialchars(self::$record['signature'], ENT_NOQUOTES, RCUBE_CHARSET);
        }

        // hide "default" checkbox if only one identity is allowed
        if ($IDENTITIES_LEVEL > 1) {
            unset($form['addressing']['content']['standard']);
        }

        // disable some field according to access level
        if ($IDENTITIES_LEVEL == 1 || $IDENTITIES_LEVEL == 3) {
            $form['addressing']['content']['email']['disabled'] = true;
            $form['addressing']['content']['email']['class']    = 'disabled';
        }

        if ($IDENTITIES_LEVEL == 4) {
            foreach ($form['addressing']['content'] as $formfield => $value){
                $form['addressing']['content'][$formfield]['disabled'] = true;
                $form['addressing']['content'][$formfield]['class']    = 'disabled';
            }
        }

        if (!empty(self::$record['email'])) {
            self::$record['email'] = rcube_utils::idn_to_utf8(self::$record['email']);
        }

        // Allow plugins to modify identity form content
        $plugin = $rcmail->plugins->exec_hook('identity_form', [
                'form'   => $form,
                'record' => self::$record
        ]);

        $form = $plugin['form'];
        self::$record = $plugin['record'];

        // Set form tags and hidden fields
        list($form_start, $form_end) = self::get_form_tags($attrib, 'save-identity',
            intval(self::$record['identity_id'] ?? 0),
            ['name' => '_iid', 'value' => self::$record['identity_id'] ?? 0]
        );

        unset($plugin);
        unset($attrib['form'], $attrib['id']);

        // return the complete edit form as table
        $out = "$form_start\n";

        foreach ($form as $fieldset) {
            if (empty($fieldset['content'])) {
                continue;
            }

            $content = '';
            if (is_array($fieldset['content'])) {
                $table = new html_table(['cols' => 2]);

                foreach ($fieldset['content'] as $col => $colprop) {
                    $colprop['id'] = 'rcmfd_'.$col;

                    if (!empty($colprop['label'])) {
                        $label = $colprop['label'];
                    }
                    else {
                        $label = $rcmail->gettext(str_replace('-', '', $col));
                    }

                    if (!empty($colprop['value'])) {
                        $value = $colprop['value'];
                    }
                    else {
                        $val   = self::$record[$col] ?? '';
                        $value = rcube_output::get_edit_field($col, $val, $colprop, $colprop['type']);
                    }

                    $table->add('title', html::label($colprop['id'], rcube::Q($label)));
                    $table->add(null, $value);
                }

                $content = $table->show($attrib);
            }
            else {
                $content = $fieldset['content'];
            }

            $content = html::tag('legend', null, rcube::Q($fieldset['name'])) . $content;
            $out .= html::tag('fieldset', !empty($fieldset['attrs']) ? $fieldset['attrs'] : [], $content) . "\n";
        }

        $out .= $form_end;

        // add image upload form
        $max_size = self::upload_init($rcmail->config->get('identity_image_size', 64) * 1024);
        $form_id  = 'identityImageUpload';

        $out .= '<form id="' . $form_id . '" style="display: none">'
            . html::div('hint', $rcmail->gettext(['name' => 'maxuploadsize', 'vars' => ['size' => $max_size]]))
            . '</form>';

        $rcmail->output->add_gui_object('uploadform', $form_id);

        return $out;
    }
}
