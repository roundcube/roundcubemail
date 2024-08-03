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
 |   Provide functionality for user's settings & preferences             |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_index extends rcmail_action
{
    /**
     * Deprecated action aliases.
     *
     * @var array
     */
    public static $aliases = [
        'rename-folder'    => 'folder-rename',
        'subscribe'        => 'folder-subscribe',
        'unsubscribe'      => 'folder-unsubscribe',
        'purge'            => 'folder-purge',
        'add-folder'       => 'folder-create',
        'add-identity'     => 'identity-create',
        'add-response'     => 'response-create',
        'delete-folder'    => 'folder-delete',
        'delete-identity'  => 'identity-delete',
        'delete-response'  => 'response-delete',
        'edit-folder'      => 'folder-edit',
        'edit-identity'    => 'identity-edit',
        'edit-prefs'       => 'prefs-edit',
        'edit-response'    => 'response-edit',
        'save-folder'      => 'folder-save',
        'save-identity'    => 'identity-save',
        'save-prefs'       => 'prefs-save',
        'save-response'    => 'response-save',
    ];

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        if ($rcmail->output->type == 'html') {
            $rcmail->output->set_pagetitle($rcmail->gettext('preferences'));

            // register UI objects
            $rcmail->output->add_handlers([
                    'settingstabs' => [$this, 'settings_tabs'],
                    'sectionslist' => [$this, 'sections_list'],
            ]);
        }
    }

    /**
     * Render and initialize the settings sections table
     *
     * @param array $attrib Template object attributes
     *
     * @return string HTML content
     */
    public static function sections_list($attrib)
    {
        $rcmail = rcmail::get_instance();

        // add id to message list table if not specified
        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmsectionslist';
        }

        list($list, $cols) = self::user_prefs();

        // create XHTML table
        $out = self::table_output($attrib, $list, $cols, 'id');

        // set client env
        $rcmail->output->add_gui_object('sectionslist', $attrib['id']);
        $rcmail->output->include_script('list.js');

        return $out;
    }

    public static function user_prefs($current = null)
    {
        $rcmail = rcmail::get_instance();

        $sections['general']     = ['id' => 'general', 'section' => $rcmail->gettext('uisettings')];
        $sections['mailbox']     = ['id' => 'mailbox', 'section' => $rcmail->gettext('mailboxview')];
        $sections['mailview']    = ['id' => 'mailview','section' => $rcmail->gettext('messagesdisplaying')];
        $sections['compose']     = ['id' => 'compose', 'section' => $rcmail->gettext('messagescomposition')];
        $sections['addressbook'] = ['id' => 'addressbook','section' => $rcmail->gettext('contacts')];
        $sections['folders']     = ['id' => 'folders', 'section' => $rcmail->gettext('specialfolders')];
        $sections['server']      = ['id' => 'server',  'section' => $rcmail->gettext('serversettings')];
        $sections['encryption']  = ['id' => 'encryption', 'section' => $rcmail->gettext('encryption')];

        // hook + define list cols
        $plugin = $rcmail->plugins->exec_hook('preferences_sections_list', [
                'list' => $sections,
                'cols' => ['section']
        ]);

        $sections    = $plugin['list'];
        $config      = $rcmail->config->all();
        $no_override = array_flip((array) $rcmail->config->get('dont_override'));

        foreach ($sections as $idx => $sect) {
            $sections[$idx]['class'] = !empty($sect['class']) ? $sect['class'] : $idx;

            if ($current && $sect['id'] != $current) {
                continue;
            }

            $blocks = [];

            switch ($sect['id']) {

            // general
            case 'general':
                $blocks = [
                    'main'    => ['name' => rcube::Q($rcmail->gettext('mainoptions'))],
                    'skin'    => ['name' => rcube::Q($rcmail->gettext('skin'))],
                    'browser' => ['name' => rcube::Q($rcmail->gettext('browseroptions'))],
                    'advanced'=> ['name' => rcube::Q($rcmail->gettext('advancedoptions'))],
                ];

                // language selection
                if (!isset($no_override['language'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $a_lang = $rcmail->list_languages();
                    asort($a_lang);

                    $field_id = 'rcmfd_lang';
                    $select = new html_select([
                            'name'  => '_language',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add(array_values($a_lang), array_keys($a_lang));

                    $blocks['main']['options']['language'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('language'))),
                        'content' => $select->show($rcmail->user->language),
                    ];
                }

                // timezone selection
                if (!isset($no_override['timezone'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_timezone';
                    $select = new html_select([
                            'name'  => '_timezone',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('autodetect'), 'auto');

                    $zones = [];
                    foreach (DateTimeZone::listIdentifiers() as $i => $tzs) {
                        if ($data = self::timezone_standard_time_data($tzs)) {
                            $zones[$data['key']] = [$tzs, $data['offset']];
                        }
                    }

                    ksort($zones);

                    foreach ($zones as $zone) {
                        list($tzs, $offset) = $zone;
                        $select->add('(GMT ' . $offset . ') ' . self::timezone_label($tzs), $tzs);
                    }

                    $blocks['main']['options']['timezone'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('timezone'))),
                        'content' => $select->show((string)$config['timezone']),
                    ];
                }

                // date/time formatting
                if (!isset($no_override['time_format'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $reftime  = mktime(7,30,0);
                    $defaults = ['G:i', 'H:i', 'g:i a', 'h:i A'];
                    $formats  = (array) $rcmail->config->get('time_formats', $defaults);
                    $field_id = 'rcmfd_time_format';
                    $select   = new html_select([
                            'name'  => '_time_format',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    foreach ($formats as $choice) {
                        $select->add(date($choice, $reftime), $choice);
                    }

                    $blocks['main']['options']['time_format'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('timeformat'))),
                        'content' => $select->show($rcmail->config->get('time_format')),
                    ];
                }

                if (!isset($no_override['date_format'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $refdate  = mktime(12,30,0,7,24);
                    $defaults = ['Y-m-d','d-m-Y','Y/m/d','m/d/Y','d/m/Y','d.m.Y','j.n.Y'];
                    $formats  = (array) $rcmail->config->get('date_formats', $defaults);
                    $field_id = 'rcmfd_date_format';
                    $select   = new html_select([
                            'name'  => '_date_format',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    foreach ($formats as $choice) {
                        $select->add(date($choice, $refdate), $choice);
                    }

                    $blocks['main']['options']['date_format'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('dateformat'))),
                        'content' => $select->show($config['date_format']),
                    ];
                }

                // Show checkbox for toggling 'pretty dates'
                if (!isset($no_override['prettydate'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_prettydate';
                    $input    = new html_checkbox([
                            'name'  => '_pretty_date',
                            'id'    => $field_id,
                            'value' => 1
                    ]);

                    $blocks['main']['options']['prettydate'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('prettydate'))),
                        'content' => $input->show($config['prettydate']?1:0),
                    ];
                }

                // "display after delete" checkbox
                if (!isset($no_override['display_next'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_displaynext';
                    $input    = new html_checkbox([
                            'name'  => '_display_next',
                            'id'    => $field_id,
                            'value' => 1
                    ]);

                    $blocks['main']['options']['display_next'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('displaynext'))),
                        'content' => $input->show($config['display_next']?1:0),
                    ];
                }

                if (!isset($no_override['refresh_interval'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_refresh_interval';
                    $select   = new html_select([
                            'name'  => '_refresh_interval',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('never'), 0);
                    foreach ([1, 3, 5, 10, 15, 30, 60] as $min) {
                        if (!$config['min_refresh_interval'] || $config['min_refresh_interval'] <= $min * 60) {
                            $label = $rcmail->gettext(['name' => 'everynminutes', 'vars' => ['n' => $min]]);
                            $select->add($label, $min);
                        }
                    }

                    $blocks['main']['options']['refresh_interval'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('refreshinterval'))),
                        'content' => $select->show($config['refresh_interval']/60),
                    ];
                }

                // show drop-down for available skins
                if (!isset($no_override['skin'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $skins = self::get_skins();

                    if (count($skins) > 1) {
                        sort($skins);

                        $field_id = 'rcmfd_skin';
                        $input    = new html_radiobutton(['name' => '_skin']);

                        foreach ($skins as $skin) {
                            $skinname     = ucfirst($skin);
                            $author_link  = '';
                            $license_link = '';
                            $meta         = @json_decode(@file_get_contents(INSTALL_PATH . "skins/$skin/meta.json"), true);

                            if (is_array($meta) && !empty($meta['name'])) {
                                $skinname     = $meta['name'];
                                $author_link  = !empty($meta['url']) ? html::a(['href' => $meta['url'], 'target' => '_blank'], rcube::Q($meta['author'])) : rcube::Q($meta['author']);
                                $license_link = !empty($meta['license-url']) ? html::a(['href' => $meta['license-url'], 'target' => '_blank', 'tabindex' => '-1'], rcube::Q($meta['license'])) : rcube::Q($meta['license']);
                            }

                            $img = html::img([
                                    'src'     => $rcmail->output->asset_url("skins/$skin/thumbnail.png"),
                                    'class'   => 'skinthumbnail',
                                    'alt'     => $skin,
                                    'width'   => 64,
                                    'height'  => 64,
                                    'onerror' => "this.onerror = null; this.src = 'data:image/gif;base64," . rcmail_output::BLANK_GIF ."';",
                            ]);

                            $blocks['skin']['options'][$skin]['content'] = html::label(['class' => 'skinselection'],
                                html::span('skinitem', $input->show($config['skin'], ['value' => $skin, 'id' => $field_id.$skin])) .
                                html::span('skinitem', $img) .
                                html::span('skinitem', html::span('skinname', rcube::Q($skinname)) . html::br() .
                                    html::span('skinauthor', $author_link ? 'by ' . $author_link : '') . html::br() .
                                    html::span('skinlicense', $license_link ? $rcmail->gettext('license').':&nbsp;' . $license_link : ''))
                            );
                        }
                    }
                }

                // standard_windows option decides if new windows should be
                // opened as popups or standard windows (which can be handled by browsers as tabs)
                if (!isset($no_override['standard_windows'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_standard_windows';
                    $checkbox = new html_checkbox([
                            'name'  => '_standard_windows',
                            'id'    => $field_id,
                            'value' => 1
                    ]);

                    $blocks['browser']['options']['standard_windows'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('standardwindows'))),
                        'content' => $checkbox->show($config['standard_windows']?1:0),
                    ];
                }

                if ($current) {
                    $product_name = $rcmail->config->get('product_name', 'Roundcube Webmail');
                    $rcmail->output->add_script(sprintf("%s.check_protocol_handler('%s', '#mailtoprotohandler');",
                        rcmail_output::JS_OBJECT_NAME, rcube::JQ($product_name)), 'docready');
                }

                $blocks['browser']['options']['mailtoprotohandler'] = [
                    'content' => html::a(['href' => '#', 'id' => 'mailtoprotohandler'],
                    rcube::Q($rcmail->gettext('mailtoprotohandler'))) .
                    html::span('mailtoprotohandler-status', ''),
                ];

            break;

            // Mailbox view (mail screen)
            case 'mailbox':
                $blocks = [
                    'main'        => ['name' => rcube::Q($rcmail->gettext('mainoptions'))],
                    'new_message' => ['name' => rcube::Q($rcmail->gettext('newmessage'))],
                    'advanced'    => ['name' => rcube::Q($rcmail->gettext('advancedoptions'))],
                ];

                if (!isset($no_override['layout']) && count($config['supported_layouts']) > 1) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_layout';
                    $select   = new html_select([
                            'name'  => '_layout',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $layouts = [
                        'widescreen' => 'layoutwidescreendesc',
                        'desktop'    => 'layoutdesktopdesc',
                        'list'       => 'layoutlistdesc'
                    ];

                    $available_layouts = array_intersect_key($layouts, array_flip($config['supported_layouts']));
                    foreach ($available_layouts as $val => $label) {
                        $select->add($rcmail->gettext($label), $val);
                    }

                    $blocks['main']['options']['layout'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('layout'))),
                        'content' => $select->show($config['layout'] ?: 'widescreen'),
                    ];
                }

                // show config parameter for auto marking the previewed message as read
                if (!isset($no_override['mail_read_time'])) {
                    if (!$current) {
                        continue 2;
                    }

                    // apply default if config option is not set at all
                    $config['mail_read_time'] = intval($rcmail->config->get('mail_read_time'));

                    $field_id = 'rcmfd_mail_read_time';
                    $select   = new html_select([
                            'name'  => '_mail_read_time',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('never'), -1);
                    $select->add($rcmail->gettext('immediately'), 0);

                    foreach ([5, 10, 20, 30] as $sec) {
                        $label = $rcmail->gettext(['name' => 'afternseconds', 'vars' => ['n' => $sec]]);
                        $select->add($label, $sec);
                    }

                    $blocks['main']['options']['mail_read_time'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('automarkread'))),
                        'content' => $select->show($config['mail_read_time']),
                    ];
                }

                if (!isset($no_override['autoexpand_threads'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $storage   = $rcmail->get_storage();
                    $supported = $storage->get_capability('THREAD');

                    if ($supported) {
                        $field_id = 'rcmfd_autoexpand_threads';
                        $select   = new html_select([
                                'name'  => '_autoexpand_threads',
                                'id'    => $field_id,
                                'class' => 'custom-select'
                        ]);

                        $select->add($rcmail->gettext('never'), 0);
                        $select->add($rcmail->gettext('do_expand'), 1);
                        $select->add($rcmail->gettext('expand_only_unread'), 2);

                        $blocks['main']['options']['autoexpand_threads'] = [
                            'title'   => html::label($field_id, rcube::Q($rcmail->gettext('autoexpand_threads'))),
                            'content' => $select->show($config['autoexpand_threads']),
                        ];
                    }
                }

                // show page size selection
                if (!isset($no_override['mail_pagesize'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $size     = intval($config['mail_pagesize'] ?: $config['pagesize']);
                    $field_id = 'rcmfd_mail_pagesize';
                    $input    = new html_inputfield([
                            'name'  => '_mail_pagesize',
                            'id'    => $field_id,
                            'size'  => 5,
                            'class' => 'form-control'
                    ]);

                    $blocks['main']['options']['pagesize'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('pagesize'))),
                        'content' => $input->show($size ?: 50),
                    ];
                }

                if (!isset($no_override['check_all_folders'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_check_all_folders';
                    $input    = new html_checkbox([
                            'name'  => '_check_all_folders',
                            'id'    => $field_id,
                            'value' => 1
                    ]);

                    $blocks['new_message']['options']['check_all_folders'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('checkallfolders'))),
                        'content' => $input->show($config['check_all_folders']?1:0),
                    ];
                }

                break;

            // Message viewing
            case 'mailview':
                $blocks = [
                    'main'     => ['name' => rcube::Q($rcmail->gettext('mainoptions'))],
                    'advanced' => ['name' => rcube::Q($rcmail->gettext('advancedoptions'))],
                ];

                // show checkbox to open message view in new window
                if (!isset($no_override['message_extwin'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_message_extwin';
                    $input    = new html_checkbox(['name' => '_message_extwin', 'id' => $field_id, 'value' => 1]);

                    $blocks['main']['options']['message_extwin'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('showinextwin'))),
                        'content' => $input->show($config['message_extwin']?1:0),
                    ];
                }

                // show checkbox to show email instead of name
                if (!isset($no_override['message_show_email'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_message_show_email';
                    $input    = new html_checkbox(['name' => '_message_show_email', 'id' => $field_id, 'value' => 1]);

                    $blocks['main']['options']['message_show_email'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('showemail'))),
                        'content' => $input->show($config['message_show_email']?1:0),
                    ];
                }

                // show checkbox for HTML/plaintext messages
                if (!isset($no_override['prefer_html'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_htmlmsg';
                    $input    = new html_checkbox([
                            'name'     => '_prefer_html',
                            'id'       => $field_id,
                            'value'    => 1,
                            'onchange' => "$('#rcmfd_show_images').prop('disabled', !this.checked).val(0)"
                    ]);

                    $blocks['main']['options']['prefer_html'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('preferhtml'))),
                        'content' => $input->show($config['prefer_html']?1:0),
                    ];
                }

                if (!isset($no_override['default_charset'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_default_charset';

                    $blocks['advanced']['options']['default_charset'] = [
                        'title' => html::label($field_id, rcube::Q($rcmail->gettext('defaultcharset'))),
                        'content' => $rcmail->output->charset_selector([
                                'id'       => $field_id,
                                'name'     => '_default_charset',
                                'selected' => $config['default_charset'],
                                'class'    => 'custom-select',
                        ])
                    ];
                }

                if (!isset($no_override['show_images'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_show_images';
                    $input    = new html_select([
                            'name'     => '_show_images',
                            'id'       => $field_id,
                            'class'    => 'custom-select',
                            'disabled' => empty($config['prefer_html'])
                    ]);

                    $input->add($rcmail->gettext('never'), 0);
                    $input->add($rcmail->gettext('frommycontacts'), 1);
                    $input->add($rcmail->gettext('fromtrustedsenders'), 3);
                    $input->add($rcmail->gettext('always'), 2);

                    $blocks['main']['options']['show_images'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('allowremoteresources'))),
                        'content' => $input->show(!empty($config['prefer_html']) ? $config['show_images'] : 0),
                    ];
                }

                if (!isset($no_override['mdn_requests'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_mdn_requests';
                    $select   = new html_select([
                            'name'  => '_mdn_requests',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('askuser'), 0);
                    $select->add($rcmail->gettext('autosend'), 1);
                    $select->add($rcmail->gettext('autosendknown'), 3);
                    $select->add($rcmail->gettext('autosendknownignore'), 4);
                    $select->add($rcmail->gettext('autosendtrusted'), 5);
                    $select->add($rcmail->gettext('autosendtrustedignore'), 6);
                    $select->add($rcmail->gettext('ignorerequest'), 2);

                    $blocks['main']['options']['mdn_requests'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('mdnrequests'))),
                        'content' => $select->show($config['mdn_requests']),
                    ];
                }

                if (!isset($no_override['inline_images'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_inline_images';
                    $input    = new html_checkbox(['name' => '_inline_images', 'id' => $field_id, 'value' => 1]);

                    $blocks['main']['options']['inline_images'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('showinlineimages'))),
                        'content' => $input->show($config['inline_images']?1:0),
                    ];
                }

                break;

            // Mail composition
            case 'compose':
                $blocks = [
                    'main'       => ['name' => rcube::Q($rcmail->gettext('mainoptions'))],
                    'sig'        => ['name' => rcube::Q($rcmail->gettext('signatureoptions'))],
                    'spellcheck' => ['name' => rcube::Q($rcmail->gettext('spellcheckoptions'))],
                    'advanced'   => ['name' => rcube::Q($rcmail->gettext('advancedoptions'))],
                ];

                // show checkbox to compose messages in a new window
                if (!isset($no_override['compose_extwin'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfdcompose_extwin';
                    $input    = new html_checkbox(['name' => '_compose_extwin', 'id' => $field_id, 'value' => 1]);

                    $blocks['main']['options']['compose_extwin'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('composeextwin'))),
                        'content' => $input->show($config['compose_extwin']?1:0),
                    ];
                }

                if (!isset($no_override['htmleditor'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_htmleditor';
                    $select   = new html_select([
                            'name'  => '_htmleditor',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('never'), 0);
                    $select->add($rcmail->gettext('htmlonreply'), 2);
                    $select->add($rcmail->gettext('htmlonreplyandforward'), 3);
                    $select->add($rcmail->gettext('always'), 1);
                    $select->add($rcmail->gettext('alwaysbutplain'), 4);

                    $blocks['main']['options']['htmleditor'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('htmleditor'))),
                        'content' => $select->show(intval($config['htmleditor'])),
                    ];
                }

                if (!isset($no_override['draft_autosave'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_autosave';
                    $select   = new html_select([
                            'name'     => '_draft_autosave',
                            'id'       => $field_id,
                            'class'    => 'custom-select',
                            'disabled' => empty($config['drafts_mbox'])
                    ]);

                    $select->add($rcmail->gettext('never'), 0);
                    foreach ([1, 3, 5, 10] as $i => $min) {
                        $label = $rcmail->gettext(['name' => 'everynminutes', 'vars' => ['n' => $min]]);
                        $select->add($label, $min * 60);
                    }

                    $blocks['main']['options']['draft_autosave'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('autosavedraft'))),
                        'content' => $select->show($config['draft_autosave']),
                    ];
                }

                if (!isset($no_override['mime_param_folding'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_param_folding';
                    $select   = new html_select([
                            'name'  => '_mime_param_folding',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('2231folding'), 0);
                    $select->add($rcmail->gettext('miscfolding'), 1);
                    $select->add($rcmail->gettext('2047folding'), 2);

                    $blocks['advanced']['options']['mime_param_folding'] = [
                        'title'    => html::label($field_id, rcube::Q($rcmail->gettext('mimeparamfolding'))),
                        'content'  => $select->show($config['mime_param_folding']),
                    ];
                }

                if (!isset($no_override['force_7bit'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_force_7bit';
                    $input    = new html_checkbox(['name' => '_force_7bit', 'id' => $field_id, 'value' => 1]);

                    $blocks['advanced']['options']['force_7bit'] = [
                        'title'    => html::label($field_id, rcube::Q($rcmail->gettext('force7bit'))),
                        'content'  => $input->show($config['force_7bit']?1:0),
                    ];
                }

                if (!isset($no_override['mdn_default'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_mdn_default';
                    $input    = new html_checkbox(['name' => '_mdn_default', 'id' => $field_id, 'value' => 1]);

                    $blocks['main']['options']['mdn_default'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('reqmdn'))),
                        'content' => $input->show($config['mdn_default']?1:0),
                    ];
                }

                if (!isset($no_override['dsn_default'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_dsn_default';
                    $input    = new html_checkbox(['name' => '_dsn_default', 'id' => $field_id, 'value' => 1]);

                    $blocks['main']['options']['dsn_default'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('reqdsn'))),
                        'content' => $input->show($config['dsn_default']?1:0),
                    ];
                }

                if (!isset($no_override['reply_same_folder'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_reply_same_folder';
                    $input    = new html_checkbox(['name' => '_reply_same_folder', 'id' => $field_id, 'value' => 1]);

                    $blocks['main']['options']['reply_same_folder'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('replysamefolder'))),
                        'content' => $input->show($config['reply_same_folder']?1:0),
                    ];
                }

                if (!isset($no_override['reply_mode'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_reply_mode';
                    $select   = new html_select(['name' => '_reply_mode', 'id' => $field_id, 'class' => 'custom-select']);

                    $select->add($rcmail->gettext('replyempty'), -1);
                    $select->add($rcmail->gettext('replybottomposting'), 0);
                    $select->add($rcmail->gettext('replytopposting'), 1);
                    $select->add($rcmail->gettext('replytoppostingnoindent'), 2);

                    $blocks['main']['options']['reply_mode'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('whenreplying'))),
                        'content' => $select->show(intval($config['reply_mode'])),
                    ];
                }

                if (!isset($no_override['spellcheck_before_send']) && $config['enable_spellcheck']) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_spellcheck_before_send';
                    $input    = new html_checkbox([
                            'name'  => '_spellcheck_before_send',
                            'id'    => $field_id,
                            'value' => 1
                    ]);

                    $blocks['spellcheck']['options']['spellcheck_before_send'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('spellcheckbeforesend'))),
                        'content' => $input->show($config['spellcheck_before_send']?1:0),
                    ];
                }

                if ($config['enable_spellcheck']) {
                    if (!$current) {
                        continue 2;
                    }

                    foreach (['syms', 'nums', 'caps'] as $key) {
                        $key = 'spellcheck_ignore_' . $key;
                        if (!isset($no_override[$key])) {
                            $input = new html_checkbox(['name' => '_' . $key, 'id' => 'rcmfd_' . $key, 'value' => 1]);

                            $blocks['spellcheck']['options'][$key] = [
                                'title'   => html::label('rcmfd_' . $key, rcube::Q($rcmail->gettext(str_replace('_', '', $key)))),
                                'content' => $input->show($config[$key]?1:0),
                            ];
                        }
                    }
                }

                if (!isset($no_override['show_sig'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_show_sig';
                    $select   = new html_select([
                            'name'  => '_show_sig',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('never'), 0);
                    $select->add($rcmail->gettext('always'), 1);
                    $select->add($rcmail->gettext('newmessageonly'), 2);
                    $select->add($rcmail->gettext('replyandforwardonly'), 3);

                    $blocks['sig']['options']['show_sig'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('autoaddsignature'))),
                        'content' => $select->show($rcmail->config->get('show_sig', 1)),
                    ];
                }

                if (!isset($no_override['sig_below'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_sig_below';
                    $input    = new html_checkbox(['name' => '_sig_below', 'id' => $field_id, 'value' => 1]);

                    $blocks['sig']['options']['sig_below'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('sigbelow'))),
                        'content' => $input->show($rcmail->config->get('sig_below') ? 1 : 0),
                    ];
                }

                if (!isset($no_override['strip_existing_sig'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_strip_existing_sig';
                    $input    = new html_checkbox([
                            'name'  => '_strip_existing_sig',
                            'id'    => $field_id,
                            'value' => 1
                    ]);

                    $blocks['sig']['options']['strip_existing_sig'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('replyremovesignature'))),
                        'content' => $input->show($config['strip_existing_sig']?1:0),
                    ];
                }

                if (!isset($no_override['sig_separator'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_sig_separator';
                    $input    = new html_checkbox(['name' => '_sig_separator', 'id' => $field_id, 'value' => 1]);

                    $blocks['sig']['options']['sig_separator'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('sigseparator'))),
                        'content' => $input->show($rcmail->config->get('sig_separator') ? 1 : 0),
                    ];
                }

                if (!isset($no_override['forward_attachment'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_forward_attachment';
                    $select = new html_select([
                            'name'  => '_forward_attachment',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('inline'), 0);
                    $select->add($rcmail->gettext('asattachment'), 1);

                    $blocks['main']['options']['forward_attachment'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('forwardmode'))),
                        'content' => $select->show(intval($config['forward_attachment'])),
                    ];
                }

                if (!isset($no_override['default_font']) || !isset($no_override['default_font_size'])) {
                    if (!$current) {
                        continue 2;
                    }

                    // Default font size
                    $field_id = 'rcmfd_default_font_size';
                    $select_size = new html_select([
                            'name'  => '_default_font_size',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $fontsizes = ['', '8pt', '9pt', '10pt', '11pt', '12pt', '14pt', '18pt', '24pt', '36pt'];
                    foreach ($fontsizes as $size) {
                        $select_size->add($size, $size);
                    }

                    // Default font
                    $field_id = 'rcmfd_default_font';
                    $select_font = new html_select([
                            'name' => '_default_font',
                            'id' => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select_font->add('', '');

                    $fonts = self::font_defs();
                    foreach (array_keys($fonts) as $fname) {
                        $select_font->add($fname, $fname);
                    }

                    $blocks['main']['options']['default_font'] = [
                        'title' => html::label($field_id, rcube::Q($rcmail->gettext('defaultfont'))),
                        'content' => html::div('input-group',
                            $select_font->show($rcmail->config->get('default_font', 1)) .
                            $select_size->show($rcmail->config->get('default_font_size', 1))
                        )
                    ];
                }

                if (!isset($no_override['reply_all_mode'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_reply_all_mode';
                    $select   = new html_select([
                            'name'  => '_reply_all_mode',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('replyalldefault'), 0);
                    $select->add($rcmail->gettext('replyalllist'), 1);

                    $blocks['main']['options']['reply_all_mode'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('replyallmode'))),
                        'content' => $select->show(intval($config['reply_all_mode'])),
                    ];
                }

                if (!isset($no_override['compose_save_localstorage'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_compose_save_localstorage';
                    $input    = new html_checkbox([
                            'name'  => '_compose_save_localstorage',
                            'id'    => $field_id,
                            'value' => 1
                    ]);

                    $blocks['advanced']['options']['compose_save_localstorage'] = [
                        'title'    => html::label($field_id, rcube::Q($rcmail->gettext('savelocalstorage'))),
                        'content'  => $input->show($config['compose_save_localstorage']?1:0),
                    ];
                }

                break;

            // Addressbook config
            case 'addressbook':
                $blocks = [
                    'main'      => ['name' => rcube::Q($rcmail->gettext('mainoptions'))],
                    'collected' => ['name' => rcube::Q($rcmail->gettext('collectedaddresses'))],
                    'advanced'  => ['name' => rcube::Q($rcmail->gettext('advancedoptions'))],
                ];

                if (!isset($no_override['default_addressbook'])
                    && (!$current || ($books = $rcmail->get_address_sources(true, true)))
                ) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_default_addressbook';
                    $select   = new html_select([
                            'name'  => '_default_addressbook',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    if (!empty($books)) {
                        foreach ($books as $book) {
                            $select->add(html_entity_decode($book['name'], ENT_COMPAT, 'UTF-8'), $book['id']);
                        }
                    }

                    $blocks['main']['options']['default_addressbook'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('defaultabook'))),
                        'content' => $select->show($config['default_addressbook']),
                    ];
                }

                // show addressbook listing mode selection
                if (!isset($no_override['addressbook_name_listing'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_addressbook_name_listing';
                    $select   = new html_select([
                            'name'  => '_addressbook_name_listing',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('name'), 0);
                    $select->add($rcmail->gettext('firstname') . ' '  . $rcmail->gettext('surname'), 1);
                    $select->add($rcmail->gettext('surname')   . ' '  . $rcmail->gettext('firstname'), 2);
                    $select->add($rcmail->gettext('surname')   . ', ' . $rcmail->gettext('firstname'), 3);

                    $blocks['main']['options']['list_name_listing'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('listnamedisplay'))),
                        'content' => $select->show($config['addressbook_name_listing']),
                    ];
                }

                // show addressbook sort column
                if (!isset($no_override['addressbook_sort_col'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_addressbook_sort_col';
                    $select   = new html_select([
                            'name'  => '_addressbook_sort_col',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('name'), 'name');
                    $select->add($rcmail->gettext('firstname'), 'firstname');
                    $select->add($rcmail->gettext('surname'), 'surname');

                    $blocks['main']['options']['sort_col'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('listsorting'))),
                        'content' => $select->show($config['addressbook_sort_col']),
                    ];
                }

                // show addressbook page size selection
                if (!isset($no_override['addressbook_pagesize'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $size     = intval($config['addressbook_pagesize'] ?: $config['pagesize']);
                    $field_id = 'rcmfd_addressbook_pagesize';
                    $input    = new html_inputfield([
                            'name'  => '_addressbook_pagesize',
                            'id'    => $field_id,
                            'size'  => 5,
                            'class' => 'form-control'
                    ]);

                    $blocks['main']['options']['pagesize'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('pagesize'))),
                        'content' => $input->show($size ?: 50),
                    ];
                }

                if (!isset($no_override['contact_form_mode'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $mode     = $config['contact_form_mode'] == 'business' ? 'business' : 'private';
                    $field_id = 'rcmfd_contact_form_mode';
                    $select   = new html_select([
                            'name'  => '_contact_form_mode',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('privatemode'), 'private');
                    $select->add($rcmail->gettext('businessmode'), 'business');

                    $blocks['main']['options']['contact_form_mode'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('contactformmode'))),
                        'content' => $select->show($mode),
                    ];
                }

                if (!isset($no_override['autocomplete_single'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_autocomplete_single';
                    $checkbox = new html_checkbox(['name' => '_autocomplete_single', 'id' => $field_id, 'value' => 1]);

                    $blocks['main']['options']['autocomplete_single'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('autocompletesingle'))),
                        'content' => $checkbox->show($config['autocomplete_single']?1:0),
                    ];
                }

                if (!isset($no_override['collected_recipients'])) {
                    if (!$current) {
                        continue 2;
                    }

                    if (!isset($books)) {
                        $books = $rcmail->get_address_sources(true, true);
                    }

                    $field_id = 'rcmfd_collected_recipients';
                    $select   = new html_select([
                            'name'  => '_collected_recipients',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add('---', '');
                    $select->add($rcmail->gettext('collectedrecipients'), (string) rcube_addressbook::TYPE_RECIPIENT);

                    foreach ($books as $book) {
                        $select->add(html_entity_decode($book['name'], ENT_COMPAT, 'UTF-8'), $book['id']);
                    }

                    $selected = $config['collected_recipients'];
                    if (is_bool($selected)) {
                        $selected = $selected ? rcube_addressbook::TYPE_RECIPIENT : '';
                    }

                    $blocks['collected']['options']['collected_recipients'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('collectedrecipientsopt'))),
                        'content' => $select->show((string) $selected),
                    ];
                }

                if (!isset($no_override['collected_senders'])) {
                    if (!$current) {
                        continue 2;
                    }

                    if (!isset($books)) {
                        $books = $rcmail->get_address_sources(true, true);
                    }

                    $field_id = 'rcmfd_collected_senders';
                    $select   = new html_select([
                            'name'  => '_collected_senders',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('trustedsenders'), (string) rcube_addressbook::TYPE_TRUSTED_SENDER);

                    foreach ($books as $book) {
                        $select->add(html_entity_decode($book['name'], ENT_COMPAT, 'UTF-8'), $book['id']);
                    }

                    $selected = $config['collected_senders'];
                    if (is_bool($selected)) {
                        $selected = $selected ? rcube_addressbook::TYPE_TRUSTED_SENDER : '';
                    }

                    $blocks['collected']['options']['collected_senders'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('collectedsendersopt'))),
                        'content' => $select->show((string) $selected),
                    ];
                }

                break;

            // Special IMAP folders
            case 'folders':
                $blocks = [
                    'main'     => ['name' => rcube::Q($rcmail->gettext('mainoptions'))],
                    'advanced' => ['name' => rcube::Q($rcmail->gettext('advancedoptions'))],
                ];

                if (!isset($no_override['show_real_foldernames'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'show_real_foldernames';
                    $input    = new html_checkbox(['name' => '_show_real_foldernames', 'id' => $field_id, 'value' => 1]);

                    $blocks['main']['options']['show_real_foldernames'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('show_real_foldernames'))),
                        'content' => $input->show($config['show_real_foldernames']?1:0),
                    ];
                }

                // Configure special folders
                $set = ['drafts_mbox', 'sent_mbox', 'junk_mbox', 'trash_mbox'];

                if ($current && count(array_intersect($no_override, $set)) < 4) {
                    $select = self::folder_selector([
                            'noselection'   => '---',
                            'realnames'     => true,
                            'maxlength'     => 30,
                            'folder_filter' => 'mail',
                            'folder_rights' => 'w',
                            'class'         => 'custom-select',
                    ]);

                    // #1486114, #1488279, #1489219
                    $onchange = "if ($(this).val() == 'INBOX') $(this).val('')";
                }
                else {
                    $onchange = null;
                    $select   = new html_select();
                }

                if (!isset($no_override['drafts_mbox'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $attrs = ['id' => '_drafts_mbox', 'name' => '_drafts_mbox', 'onchange' => $onchange];
                    $blocks['main']['options']['drafts_mbox'] = [
                        'title'   => html::label($attrs['id'], rcube::Q($rcmail->gettext('drafts'))),
                        'content' => $select->show($config['drafts_mbox'], $attrs),
                    ];
                }

                if (!isset($no_override['sent_mbox'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $attrs = ['id' => '_sent_mbox', 'name' => '_sent_mbox', 'onchange' => ''];
                    $blocks['main']['options']['sent_mbox'] = [
                        'title'   => html::label($attrs['id'], rcube::Q($rcmail->gettext('sent'))),
                        'content' => $select->show($config['sent_mbox'], $attrs),
                    ];
                }

                if (!isset($no_override['junk_mbox'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $attrs = ['id' => '_junk_mbox', 'name' => '_junk_mbox', 'onchange' => $onchange];
                    $blocks['main']['options']['junk_mbox'] = [
                        'title'   => html::label($attrs['id'], rcube::Q($rcmail->gettext('junk'))),
                        'content' => $select->show($config['junk_mbox'], $attrs),
                    ];
                }

                if (!isset($no_override['trash_mbox'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $attrs = ['id' => '_trash_mbox', 'name' => '_trash_mbox', 'onchange' => $onchange];
                    $blocks['main']['options']['trash_mbox'] = [
                        'title'   => html::label($attrs['id'], rcube::Q($rcmail->gettext('trash'))),
                        'content' => $select->show($config['trash_mbox'], $attrs),
                    ];
                }

                break;

            // Server settings
            case 'server':
                $blocks = [
                    'main'        => ['name' => rcube::Q($rcmail->gettext('mainoptions'))],
                    'maintenance' => ['name' => rcube::Q($rcmail->gettext('maintenance'))],
                    'advanced'    => ['name' => rcube::Q($rcmail->gettext('advancedoptions'))],
                ];

                if (!isset($no_override['read_when_deleted'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_read_deleted';
                    $input    = new html_checkbox(['name' => '_read_when_deleted', 'id' => $field_id, 'value' => 1]);

                    $blocks['main']['options']['read_when_deleted'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('readwhendeleted'))),
                        'content' => $input->show($config['read_when_deleted']?1:0),
                    ];
                }

                if (!isset($no_override['flag_for_deletion'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_flag_for_deletion';
                    $input    = new html_checkbox(['name' => '_flag_for_deletion', 'id' => $field_id, 'value' => 1]);

                    $blocks['main']['options']['flag_for_deletion'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('flagfordeletion'))),
                        'content' => $input->show($config['flag_for_deletion']?1:0),
                    ];
                }

                // don't show deleted messages
                if (!isset($no_override['skip_deleted'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_skip_deleted';
                    $input    = new html_checkbox(['name' => '_skip_deleted', 'id' => $field_id, 'value' => 1]);

                    $blocks['main']['options']['skip_deleted'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('skipdeleted'))),
                        'content' => $input->show($config['skip_deleted']?1:0),
                    ];
                }

                if (!isset($no_override['delete_junk'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_delete_junk';
                    $input    = new html_checkbox(['name' => '_delete_junk', 'id' => $field_id, 'value' => 1]);

                    $blocks['main']['options']['delete_junk'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('deletejunk'))),
                        'content' => $input->show($config['delete_junk']?1:0),
                    ];
                }

                // Trash purging on logout
                if (!isset($no_override['logout_purge'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_logout_purge';
                    $select   = new html_select([
                            'name'  => '_logout_purge',
                            'id'    => $field_id,
                            'class' => 'custom-select'
                    ]);

                    $select->add($rcmail->gettext('never'), 'never');
                    $select->add($rcmail->gettext('allmessages'), 'all');

                    foreach ([30, 60, 90] as $days) {
                        $select->add($rcmail->gettext(['name' => 'olderxdays', 'vars' => ['x' => $days]]), (string) $days);
                    }

                    $purge = $config['logout_purge'];
                    if (!is_numeric($purge)) {
                        $purge = empty($purge) ? 'never' : 'all';
                    }

                    $blocks['maintenance']['options']['logout_purge'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('logoutclear'))),
                        'content' => $select->show((string) $purge),
                    ];
                }

                // INBOX compacting on logout
                if (!isset($no_override['logout_expunge'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_logout_expunge';
                    $input    = new html_checkbox(['name' => '_logout_expunge', 'id' => $field_id, 'value' => 1]);

                    $blocks['maintenance']['options']['logout_expunge'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('logoutcompact'))),
                        'content' => $input->show($config['logout_expunge']?1:0),
                    ];
                }

                break;

            // Server settings
            case 'encryption':
                $blocks = [
                    'main'       => ['name' => rcube::Q($rcmail->gettext('mainoptions'))],
                    'mailvelope' => ['name' => rcube::Q($rcmail->gettext('mailvelopeoptions'))],
                    'advanced'   => ['name' => rcube::Q($rcmail->gettext('advancedoptions'))],
                ];

                if (!isset($no_override['mailvelope_main_keyring'])) {
                    if (!$current) {
                        continue 2;
                    }

                    $field_id = 'rcmfd_mailvelope_main_keyring';
                    $input    = new html_checkbox(['name' => '_mailvelope_main_keyring', 'id' => $field_id, 'value' => 1]);

                    $blocks['mailvelope']['options']['mailvelope_status'] = [
                        'content' => html::div(
                            ['style' => 'display:none', 'class' => 'boxwarning', 'id' => 'mailvelope-warning'],
                            str_replace(
                                'Mailvelope', '<a href="https://www.mailvelope.com" target="_blank">Mailvelope</a>',
                                rcube::Q($rcmail->gettext('mailvelopenotfound'))
                            )
                            . html::script([], "if (!parent.mailvelope) \$('#mailvelope-warning').show()")
                        )
                    ];

                    $blocks['mailvelope']['options']['mailvelope_main_keyring'] = [
                        'title'   => html::label($field_id, rcube::Q($rcmail->gettext('mailvelopemainkeyring'))),
                        'content' => $input->show(!empty($config['mailvelope_main_keyring']) ? 1 : 0),
                    ];
                }

                break;
            }

            $found = false;
            $data  = $rcmail->plugins->exec_hook('preferences_list', [
                    'section' => $sect['id'],
                    'blocks'  => $blocks,
                    'current' => $current
            ]);

            $advanced_prefs = (array) $rcmail->config->get('advanced_prefs');

            // create output
            foreach ($data['blocks'] as $key => $block) {
                if (!empty($block['content']) || !empty($block['options'])) {
                    $found = true;
                }
                // move some options to the 'advanced' block as configured by admin
                if ($key != 'advanced') {
                    foreach ($advanced_prefs as $opt) {
                        if ($block['options'][$opt]) {
                            $data['blocks']['advanced']['options'][$opt] = $block['options'][$opt];
                            unset($data['blocks'][$key]['options'][$opt]);
                        }
                    }
                }
            }

            // move 'advanced' block to the end of the list
            if (!empty($data['blocks']['advanced'])) {
                $adv = $data['blocks']['advanced'];
                unset($data['blocks']['advanced']);
                $data['blocks']['advanced'] = $adv;
            }

            if (!$found) {
                unset($sections[$idx]);
            }
            else {
                $sections[$idx]['blocks'] = $data['blocks'];
            }

            // allow plugins to add a header to each section
            $data = $rcmail->plugins->exec_hook('preferences_section_header',
                ['section' => $sect['id'], 'header' => '', 'current' => $current]);

            if (!empty($data['header'])) {
                $sections[$idx]['header'] = $data['header'];
            }
        }

        return [$sections, $plugin['cols']];
    }

    /**
     * Get list of installed skins
     *
     * @return array List of skin names
     */
    public static function get_skins()
    {
        $rcmail = rcmail::get_instance();
        $path   = RCUBE_INSTALL_PATH . 'skins';
        $skins  = [];
        $dir    = opendir($path);
        $limit  = (array) $rcmail->config->get('skins_allowed');

        if (!$dir) {
            return false;
        }

        while (($file = readdir($dir)) !== false) {
            $filename = $path . '/' . $file;
            if ($file[0] != '.'
                && (empty($limit) || in_array($file, $limit))
                && is_dir($filename) && is_readable($filename)
            ) {
                $skins[] = $file;
            }
        }

        closedir($dir);

        return $skins;
    }

    /**
     * Render the list of settings sections (AKA tabs)
     *
     * @param array $attrib Template object attributes
     *
     * @return string HTML content
     */
    public static function settings_tabs($attrib)
    {
        $rcmail = rcmail::get_instance();

        // add default attributes
        $attrib += ['tagname' => 'span', 'idprefix' => 'settingstab', 'selclass' => 'selected'];

        $default_actions = [
            ['action' => 'preferences', 'type' => 'link', 'label' => 'preferences', 'title' => 'editpreferences'],
            ['action' => 'folders',     'type' => 'link', 'label' => 'folders',     'title' => 'managefolders'],
            ['action' => 'identities',  'type' => 'link', 'label' => 'identities',  'title' => 'manageidentities'],
            ['action' => 'responses',   'type' => 'link', 'label' => 'responses',   'title' => 'manageresponses'],
        ];

        $disabled_actions = (array) $rcmail->config->get('disabled_actions');

        // get all identities from DB and define list of cols to be displayed
        $plugin = $rcmail->plugins->exec_hook('settings_actions', [
                'actions' => $default_actions,
                'attrib'  => $attrib,
        ]);

        $selected = !empty($rcmail->action) ? $rcmail->action : 'preferences';
        $attrib   = $plugin['attrib'];
        $tagname  = $attrib['tagname'];
        $tabs     = [];

        foreach ($plugin['actions'] as $action) {
            if (empty($action['command']) && !empty($action['action'])) {
                $action['prop'] = $action['action'];
                $action['command'] = 'show';
            }
            else if (empty($action['command']) || $action['command'] != 'show') {
                // Backwards compatibility, show command added in 1.4
                $action['prop']    = !empty($action['command']) ? $action['command'] : null;
                $action['command'] = 'show';
            }

            $cmd = !empty($action['prop']) ? $action['prop'] : $action['action'];
            $id  = !empty($action['id']) ? $action['id'] : $cmd;

            if (in_array('settings.' . $cmd, $disabled_actions)) {
                continue;
            }

            if (empty($action['href'])) {
                $action['href'] = $rcmail->url(['_action' => $cmd]);
            }

            $button = $rcmail->output->button($action + ['type' => 'link']);
            $attr   = $attrib;

            if (!empty($id)) {
                $attr['id'] = preg_replace('/[^a-z0-9]/i', '', $attrib['idprefix'] . $id);
            }

            $classnames = [];
            if (!empty($attrib['class'])) {
                $classnames[] = $attrib['class'];
            }
            if (!empty($action['class'])) {
                $classnames[] = $action['class'];
            }
            else if (!empty($cmd)) {
                $classnames[] = $cmd;
            }
            if ($cmd == $selected && !empty($attrib['selclass'])) {
                $classnames[] = $attrib['selclass'];
            }

            $attr['class'] = join(' ', $classnames);
            $tabs[] = html::tag($tagname, $attr, $button, html::$common_attrib);
        }

        return join('', $tabs);
    }

    /**
     * Localize timezone identifiers
     *
     * @param string $tz Timezone name
     *
     * @return string Localized timezone name
     */
    public static function timezone_label($tz)
    {
        static $labels;

        if ($labels === null) {
            $labels = [];
            $lang   = $_SESSION['language'];
            if ($lang && $lang != 'en_US') {
                if (file_exists(RCUBE_LOCALIZATION_DIR . "$lang/timezones.inc")) {
                    include RCUBE_LOCALIZATION_DIR . "$lang/timezones.inc";
                }
            }
        }

        if (empty($labels)) {
            return str_replace('_', ' ', $tz);
        }

        $tokens = explode('/', $tz);
        $key    = 'tz';

        foreach ($tokens as $i => $token) {
            $idx   = strtolower($token);
            $token = str_replace('_', ' ', $token);
            $key  .= ":$idx";

            $tokens[$i] = !empty($labels[$key]) ? $labels[$key] : $token;
        }

        return implode('/', $tokens);
    }

    /**
     * Returns timezone offset in standard time
     */
    public static function timezone_standard_time_data($tzname)
    {
        try {
            $tz    = new DateTimeZone($tzname);
            $date  = new DateTime('now', $tz);
            $count = 12;

            // Move back for a month (up to 12 times) until non-DST date is found
            while ($count > 0 && $date->format('I')) {
                $date->sub(new DateInterval('P1M'));
                $count--;
            }

            $offset  = $date->format('Z') + 45000;
            $sortkey = sprintf('%06d.%s', $offset, $tzname);

            return [
                'key'    => $sortkey,
                'offset' => $date->format('P'),
            ];
        }
        catch (Exception $e) {
            // ignore
        }
    }

    /**
     * Attach uploaded images into signature as data URIs
     */
    public static function attach_images($html, $mode)
    {
        $rcmail = rcmail::get_instance();
        $offset = 0;
        $regexp = '/\s(poster|src)\s*=\s*[\'"]*\S+upload-display\S+file=rcmfile(\w+)[\s\'"]*/';

        while (preg_match($regexp, $html, $matches, 0, $offset)) {
            $file_id  = $matches[2];
            $data_uri = ' ';

            if ($file_id && !empty($_SESSION[$mode]['files'][$file_id])) {
                $file = $_SESSION[$mode]['files'][$file_id];
                $file = $rcmail->plugins->exec_hook('attachment_get', $file);

                $data_uri .= 'src="data:' . $file['mimetype'] . ';base64,';
                $data_uri .= base64_encode(!empty($file['data']) ? $file['data'] : file_get_contents($file['path']));
                $data_uri .= '" ';
            }

            $html    = str_replace($matches[0], $data_uri, $html);
            $offset += strlen($data_uri) - strlen($matches[0]) + 1;
        }

        return $html;
    }

    /**
     * Sanity checks/cleanups on HTML body of signature
     */
    public static function wash_html($html)
    {
        // Add header with charset spec., washtml cannot work without that
        $html = '<html><head>'
            . '<meta http-equiv="Content-Type" content="text/html; charset='.RCUBE_CHARSET.'" />'
            . '</head><body>' . $html . '</body></html>';

        // clean HTML with washtml by Frederic Motte
        $wash_opts = [
            'show_washed'   => false,
            'allow_remote'  => 1,
            'charset'       => RCUBE_CHARSET,
            'html_elements' => ['body', 'link'],
            'html_attribs'  => ['rel', 'type'],
            'ignore_elements' => ['body'],
            'add_comments'  => false,
        ];

        // initialize HTML washer
        $washer = new rcube_washtml($wash_opts);

        // Remove non-UTF8 characters (#1487813)
        $html = rcube_charset::clean($html);

        return $washer->wash($html);
    }
}
